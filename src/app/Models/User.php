<?php

namespace App\Models;

use Dotenv\Repository\Adapter\ArrayAdapter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\App;
use Laravel\Sanctum\HasApiTokens;
use SpotifyWebAPI\SpotifyWebAPI;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'telegram_id',
        'spotify_access_token',
        'spotify_refresh_token'
    ];

    public $timestamps = false;
    protected static $playlistsLimit = 50;
    protected static $tracksLimit = 50;
    protected static $defaultSettings = [
        'defaultPlaylistId' => null,
        'minTracks' => null
    ];

    public function playlists()
    {
        return $this->hasMany(Playlist::class);
    }

    public function settings()
    {
        return $this->hasMany(Settings::class);
    }

    public static function findByTelegramId($telegramId)
    {
        return self::query()->where('telegram_id', $telegramId)->first();
    }

    public function associatePlaylists()
    {
        $playlists = $this->getSpotifyPlaylists();

        foreach ($playlists as $playlist) {
            $id = $playlist->id;
            $name = $playlist->name;
            $playlist = Playlist::query()->where('spotify_id', $id)->get();
            if ($playlist->isEmpty()) {
                $playlist = Playlist::create(
                    [
                        'spotify_id' => $id,
                        'user_id' => $this->id,
                        'name' => $name
                    ]
                );
                $playlist->associate();
            }
        }
    }

    public function getSpotifyPlaylists()
    {
        $api = App::make(SpotifyWebAPI::class, ['id' => $this->telegram_id]);

        $playlists = [];
        $offset = 0;
        do {
            $data = $api->getMyPlaylists(
                [
                    'limit' => self::$playlistsLimit,
                    'offset' => $offset
                ]
            );

            $playlists = array_merge($playlists, $data->items);

            $leftPlaylists = $data->total - self::$playlistsLimit - $offset;
            $offset += self::$playlistsLimit;
        } while ($leftPlaylists > 0);

        return $playlists;
    }

    public static function create($telegramId)
    {
        $user = self::findByTelegramId($telegramId);
        if (!$user) {
            $user = static::query()->create(
                [
                    'telegram_id' => $telegramId
                ]
            );

            $settings = [];
            foreach (self::$defaultSettings as $key => $value) {
                $settings [] = [
                    'user_id' => $user->id,
                    'key' => $key,
                    'value' => $value
                ];
            }
            Settings::insert($settings);

            return $user;
        }
        return false;
    }

    public function getSavedTracks($lastSavedTrackDate = null)
    {
        $api = App::make(SpotifyWebAPI::class, ['id' => $this->telegram_id]);

        $tracks = [];
        $offset = 0;
        do {
            $data = $api->getMySavedTracks(
                [
                    'limit' => self::$tracksLimit,
                    'offset' => $offset
                ]
            );

            if ($lastSavedTrackDate && strtotime(end($data->items)->added_at) <= $lastSavedTrackDate) {
                foreach ($data->items as $track) {
                    if (strtotime($track->added_at) <= $lastSavedTrackDate) break;
                    $tracks []= $track;
                }
                break;
            }

            $tracks = array_merge($tracks, $data->items);

            $leftTracks = $data->total - self::$tracksLimit - $offset;
            $offset += self::$tracksLimit;
        } while ($leftTracks > 0);

        return self::filterTracksData($tracks);
    }

    protected static function filterTracksData($tracks)
    {
        return array_map(
            function ($track) {
                return [
                    'date' => strtotime($track->added_at),
                    'artist' => $track->track->artists[0]->id,
                    'uri' => $track->track->uri
                ];
            },
            $tracks
        );
    }

    public function getSettings()
    {
        $settings = $this->settings()->get();

        $filteredSettings = [];
        foreach($settings as $setting)
        {
            $filteredSettings[$setting->key] = $setting->value;
        }

        return $filteredSettings;
    }
}
