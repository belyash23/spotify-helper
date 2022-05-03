<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'spotify_id',
        'user_id',
        'name'
    ];

    public $timestamps = false;

    public function artists()
    {
        return $this->belongsToMany(Artist::class);
    }
}
