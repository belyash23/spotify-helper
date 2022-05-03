<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsToMany(Playlist::class);
    }
}
