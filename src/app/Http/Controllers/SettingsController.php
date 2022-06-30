<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveSettingsFormRequest;
use App\Models\Artist;
use App\Models\Playlist;
use App\Models\Settings;
use App\Models\User;
use Illuminate\Http\Request;
use SpotifyWebAPI\Session;
use WeStacks\TeleBot\Laravel\TeleBot;

class SettingsController extends Controller
{
    const KEY = 'WebAppData';

    public function getView(Request $request)
    {
        $dataCheckString = $request->get('initData');

        $userData = $this->validateData($dataCheckString);

        if ($userData) {
            $userData = json_decode($userData);
            $user = User::findByTelegramId($userData->id);
            $user->associatePlaylists();
            $playlists = $user->playlists()->with('artists')->get();
            $defaultPlaylistId = $user->settings()->where(['key' => 'defaultPlaylistId'])->first()->value;
            $minTracks = $user->settings()->where(['key' => 'minTracks'])->first()->value;

            return view(
                'settings',
                [
                    'telegramId' => $userData->id,
                    'validated' => true,
                    'playlists' => $playlists,
                    'defaultPlaylistId' => $defaultPlaylistId,
                    'minTracks' => $minTracks
                ]
            )->render();
        } else {
            return response('data is invalid', 422);
        }
    }

    protected function validateData($dataCheckString)
    {
        $dataCheckArr = explode('&', rawurldecode($dataCheckString));
        $hashNeedle = 'hash=';
        $userDataNeedle = 'user=';
        foreach ($dataCheckArr as &$val) {
            if (str_starts_with($val, $hashNeedle)) {
                $hash = substr_replace($val, '', 0, strlen($hashNeedle));
                $val = null;
            } elseif (str_starts_with($val, $userDataNeedle)) {
                $userData = substr($val, strlen($userDataNeedle));
            }
        }

        if (!isset($hash)) {
            return false;
        }
        $dataCheckArr = array_filter($dataCheckArr);
        sort($dataCheckArr);

        $dataCheckString = implode("\n", $dataCheckArr);

        $secretKey = hash_hmac('sha256', env('TELEGRAM_BOT_TOKEN'), self::KEY, true);

        if (bin2hex(hash_hmac('sha256', $dataCheckString, $secretKey, true)) == $hash) {
            return $userData;
        } else {
            return false;
        }
    }

    protected function getToken()
    {
        $session = new Session(
            config('services.spotify.client_id'),
            config('services.spotify.client_secret')
        );

        $session->requestCredentialsToken();
        $token = $session->getAccessToken();

        return response($token);
    }

    public function saveSettings(SaveSettingsFormRequest $request)
    {
        $telegramId = $request->input('telegramId');
        $defaultPlaylistId = $request->input('settings.defaultPlaylistId');
        $minTracksCount = $request->input('settings.minTracksCount');
        $playlists = $request->input('settings.playlists');

        $this->syncPlaylists($playlists);

        $userId = User::findByTelegramId($telegramId)->id;

        $defaultPlaylistSettings = Settings::where(
            [
                'user_id' => $userId,
                'key' => 'defaultPlaylistId'
            ]
        )->first();
        $defaultPlaylistSettings->value = $defaultPlaylistId;
        $defaultPlaylistSettings->save();

        $minTracksSettings = Settings::where(
            [
                'user_id' => $userId,
                'key' => 'minTracks'
            ]
        )->first();
        $minTracksSettings->value = $minTracksCount;
        $minTracksSettings->save();

        TeleBot::sendMessage(
            [
                'chat_id' => $telegramId,
                'text' => 'Настройки сохранены',
            ]
        );

        return response()->noContent();
    }

    protected function syncPlaylists($playlists)
    {
        foreach ($playlists as $playlist) {
            $artistsIds = [];
            foreach ($playlist['artists'] as $item) {
                $spotifyId = $item['id'];
                $name = $item['name'];
                $artist = Artist::firstOrCreate(
                    [
                        'spotify_id' => $spotifyId,
                        'name' => $name
                    ]
                );
                $artist->img = $item['img'];
                $artist->save();

                $artistsIds [] = $artist->id;
            }

            Playlist::find($playlist['id'])->artists()->sync($artistsIds);
        }
    }
}
