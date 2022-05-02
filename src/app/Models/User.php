<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'telegram_id',
        'spotify_access_token',
        'spotify_refresh_token'
    ];

    public $timestamps = false;

    public static function findByTelegramId($telegramId) {
        return self::query()->where('telegram_id', $telegramId)->first();
    }
}
