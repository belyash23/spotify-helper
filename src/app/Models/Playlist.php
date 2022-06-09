<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use SpotifyWebAPI\SpotifyWebAPI;

class Playlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'spotify_id',
        'user_id',
        'name'
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function artists()
    {
        return $this->belongsToMany(Artist::class, 'playlists_artists');
    }

    public function associate()
    {
        $artist = $this->findArtist();
        if ($artist) {
            $this->attachArtist($artist);
        }
    }

    public function findArtist()
    {
        $api = App::make(SpotifyWebAPI::class, ['id' => $this->user->telegram_id]);

        $data = $api->search(
            $this->name,
            'artist',
            [
                'limit' => 1
            ]
        );

        $artist = $data->artists->items[0];
        if (empty($artist)) {
            return null;
        } else {
            return $artist;
        }
    }

    public function attachArtist($artist)
    {
        if ($artist) {
            $artist = Artist::query()->firstOrCreate(
                [
                    'spotify_id' => $artist->id,
                    'name' => $artist->name
                ]
            );
            $this->artists()->attach($artist->id);
        }
    }
}
