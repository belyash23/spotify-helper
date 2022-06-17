<?php

namespace App\Commands;

use App\Models\Artist;
use App\Models\Playlist;
use App\Models\Settings;
use App\Models\User;
use Illuminate\Support\Facades\App;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\SpotifyWebAPIException;
use WeStacks\TeleBot\Handlers\CommandHandler;

class SortCommand extends CommandHandler
{
    protected static $aliases = ['/sort'];
    protected static $description = 'сортировка';
    protected $id;
    protected $api;

    public function handle()
    {
        $this->id = $this->update->user()->id;
        $this->api = App::make(SpotifyWebAPI::class, ['id' => $this->id]);

        $playlists = $this->prepareData();
        $settings = $this->getSettings();

        $this->sort($playlists, $settings);
        $this->updateLastSavedTrack();

        $this->sendSuccessMessage();
    }

    protected function filterUniqueTracks($playlists)
    {
        $this->api = App::make(SpotifyWebAPI::class, ['id' => $this->id]);

        foreach ($playlists as $key => $playlist) {
            if (!$playlist['id']) {
                continue;
            }

            $spotifyTracks = $this->api->getPlaylistTracks($playlist['id'])->items;
            $spotifyTracksUris = array_map(
                fn($track) => $track->track->uri,
                $spotifyTracks
            );

            if ($key == 'default') {
                $tracksUris = array_map(
                    fn($track) => $track['uri'],
                    $playlist['tracks']
                );

                $newTracksUris = array_diff($tracksUris, $spotifyTracksUris);

                $playlists[$key]['tracks'] = array_filter(
                    $playlist['tracks'],
                    fn($track) => in_array($track['uri'], $newTracksUris)
                );
            } else {
                $playlists[$key]['tracks'] = array_diff($playlist['tracks'], $spotifyTracksUris);
            }
            if (!count($playlists[$key]['tracks'])) {
                unset($playlists[$key]);
            }
        }

        return $playlists;
    }

    protected function getInvolvedArtists($tracks)
    {
        $artistsIds = [];

        foreach ($tracks as $track) {
            $artistsIds [] = $track['artist'];
        }

        $artists = Artist::query()->whereIn('spotify_id', $artistsIds)->with('playlists')->get();

        return $artists;
    }

    protected function fillPlaylists($tracks, $defaultPlaylistId = null)
    {
        $defaultPlaylistSpotifyId = $defaultPlaylistId ? Playlist::find($defaultPlaylistId)->spotify_id : null;

        $playlists = [
            'default' => [
                'id' => $defaultPlaylistSpotifyId,
                'tracks' => []
            ]
        ];

        $artists = $this->getInvolvedArtists($tracks);

        foreach ($artists as $artist) {
            if (count($artist->playlists)) {
                $playlists[$artist->spotify_id] = [
                    'id' => $artist->playlists[0]->spotify_id
                ];
            }
        }

        foreach ($tracks as $track) {
            if (isset($playlists[$track['artist']])) {
                $playlists[$track['artist']]['tracks'] [] = $track['uri'];
            } else {
                $playlists['default']['tracks'] [] = [
                    'uri' => $track['uri'],
                    'artist' => $track['artist']
                ];
            }
        }

        return $playlists;
    }

    protected function prepareData()
    {
        $user = User::findByTelegramId($this->id);

        $settings = $user->getSettings();
        $lastSavedTrackDate = $settings['lastSavedTrackDate'] ?? null;
        $defaultPlaylistId = $settings['defaultPlaylistId'];

        $tracks = $user->getSavedTracks($lastSavedTrackDate);

        $playlists = $this->fillPlaylists($tracks, $defaultPlaylistId);

        $playlists = $this->filterUniqueTracks($playlists);

        return $playlists;
    }

    protected function getSettings()
    {
        $user = User::findByTelegramId($this->id);

        return $user->getSettings();
    }

    protected function sort($playlists, $settings)
    {
        foreach ($playlists as $key => $playlist) {
            if ($key == 'default') {
                $this->sortDefaultPlaylist($playlist, $settings);
            } elseif (count($playlist['tracks'])) {
                $this->api->addPlaylistTracks($playlist['id'], array_values($playlist['tracks']));
            }
        }
    }

    protected function sortDefaultPlaylist($playlist, $settings)
    {
        $spotifyTracks = $this->api->getPlaylistTracks($playlist['id'])->items;
        $spotifyTracksFiltered = array_map(
            fn($track) => [
                'uri' => $track->track->uri,
                'artist' => $track->track->artists[0]->id,
            ],
            $spotifyTracks
        );

        if ($settings['minTracks']) {
            $allTracks = $playlist['tracks'];

            if ($settings['defaultPlaylistId']) {
                $allTracks = $this->mergeTracks($playlist['tracks'], $spotifyTracksFiltered);
            }
            $addedTracks = $this->createPlaylists($allTracks, $settings['minTracks']);

            if ($settings['defaultPlaylistId']) {
                $tracksToDelete = ['tracks' => []];
                $spotifyTracksUris = array_map(fn($track) => $track['uri'], $spotifyTracksFiltered);
                foreach ($addedTracks as $track) {
                    if (in_array($track, $spotifyTracksUris)) {
                        $tracksToDelete['tracks'] [] = ['uri' => $track];
                    }
                }
                if (count($tracksToDelete) != 0) {
                    $this->api->deletePlaylistTracks($playlist['id'], $tracksToDelete);
                }
            }
        }

        if ($settings['defaultPlaylistId']) {
            $tracks = $playlist['tracks'];
            if (isset($addedTracks)) {
                foreach ($tracks as $index => $track) {
                    if (in_array($track['uri'], $addedTracks)) {
                        unset($tracks[$index]);
                    }
                }
            }
            $addedTracks = [];
            $spotifyTracksCount = count($spotifyTracksFiltered);
            foreach (array_reverse($spotifyTracksFiltered) as $index => $spotifyTrack) {
                $tracksToAdd = [];
                $position = false;
                foreach ($tracks as $track) {
                    if ($track['artist'] == $spotifyTrack['artist']) {
                        $tracksToAdd [] = $track['uri'];
                        $position = $spotifyTracksCount - $index;
                    }
                }

                if ($position !== false) {
                    foreach ($tracksToAdd as $track) {
                        $this->api->addPlaylistTracks($playlist['id'], $track, ['position' => $position]);
                    }
                    $addedTracks = array_merge($addedTracks, $tracksToAdd);
                }
            }
            $tracksUris = array_map(fn($track) => $track['uri'], $tracks);
            $tracksToAdd = array_diff($tracksUris, $addedTracks);

            if (count($tracksToAdd)) {
                $this->api->addPlaylistTracks($playlist['id'], array_values($tracksToAdd));
            }
        }
    }

    protected function mergeTracks($tracks1, $tracks2)
    {
        $tracks2Uris = array_map(fn($track) => $track['uri'], $tracks2);
        $tracks1Uris = array_map(fn($track) => $track['uri'], $tracks1);

        $allTracksUris = array_unique(array_merge($tracks2Uris, $tracks1Uris));

        $allTracks = [];

        foreach ($allTracksUris as $uri) {
            if (in_array($uri, $tracks1Uris)) {
                foreach ($tracks1 as $track) {
                    if ($track['uri'] == $uri) {
                        $allTracks [] = $track;
                    }
                }
            } elseif (in_array($uri, $tracks2Uris)) {
                foreach ($tracks2 as $track) {
                    if ($track['uri'] == $uri) {
                        $allTracks [] = $track;
                    }
                }
            }
        }

        return $allTracks;
    }

    protected function createPlaylists($tracks, $minTracks)
    {
        $addedTracksUris = [];
        $artists = [];
        foreach ($tracks as $track) {
            if (isset($artists[$track['artist']])) {
                $artists[$track['artist']]['count'] += 1;
                $artists[$track['artist']]['tracks'] [] = $track['uri'];
            } else {
                $artists[$track['artist']] = [
                    'count' => 1,
                    'tracks' => [$track['uri']]
                ];
            }
        }

        foreach ($artists as $id => $artist) {
            if ($artist['count'] >= $minTracks) {
                $playlistName = $this->api->getArtist($id)->name;
                $playlistId = $this->api->createPlaylist(
                    [
                        'name' => $playlistName
                    ]
                )->id;

                for ($crashes = 0; $crashes < 10; $crashes++) {     // иногда выкидывается 404, при повторе работает
                    try {
                        $this->api->addPlaylistTracks($playlistId, $artist['tracks']);
                        $addedTracksUris = array_merge($addedTracksUris, $artist['tracks']);
                        break;
                    } catch (SpotifyWebAPIException $e) {
                        usleep(100);
                        if ($crashes == 9) {
                            $this->sendErrorMessage();
                        }
                    }
                }

                $userId = User::findByTelegramId($this->id)->id;

                $playlist = Playlist::create(
                    [
                        'name' => $playlistName,
                        'spotify_id' => $playlistId,
                        'user_id' => $userId
                    ]
                );

                $qwe = Artist::create(
                    [
                        'spotify_id' => $id,
                        'name' => $playlistName
                    ]
                );
                $qwe->playlists()->attach($playlist->id);
            }
        }

        return $addedTracksUris;
    }

    public function sendErrorMessage()
    {
        $this->sendMessage(
            [
                'text' => 'Ошибка! Попробуйте повторить сортировку.'
            ]
        );
    }

    protected function updateLastSavedTrack()
    {
        $now = time();

        $userId = User::findByTelegramId($this->id)->id;

        Settings::updateOrCreate(
            [
                'user_id' => $userId,
                'key' => 'lastSavedTrackDate'
            ],
            [
                'value' => $now
            ]
        );
    }

    public function sendSuccessMessage()
    {
        $this->sendMessage(
            [
                'text' => 'Сортировка прошла успешно'
            ]
        );
    }
}
