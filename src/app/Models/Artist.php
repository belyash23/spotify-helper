<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use SpotifyWebAPI\SpotifyWebAPI;

class Artist extends Model
{
    use HasFactory;

    protected $fillable = [
        'spotify_id',
        'name',
    ];

    public $timestamps = false;

    public function playlists()
    {
        return $this->belongsToMany(Playlist::class, 'playlists_artists');
    }

    public static function createWithSpotifyId($spotifyId)
    {
        $api = App::make(SpotifyWebAPI::class, ['clientCredentials' => true]);

        $name = $api->getArtist($spotifyId)->name;

        $artist = self::query()->create(
            [
                'spotify_id' => $spotifyId,
                'name' => $name
            ]
        );

        return $artist;
    }
}
