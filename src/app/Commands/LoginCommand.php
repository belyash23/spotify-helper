<?php

namespace App\Commands;

use Illuminate\Support\Facades\App;
use SpotifyWebAPI\Session;
use WeStacks\TeleBot\Handlers\CommandHandler;

class LoginCommand extends CommandHandler
{
    protected static $aliases = ['/login'];
    protected static $description = 'Авторизация';
    protected static $scopes = [
        'playlist-read-private',
        'playlist-modify-public',
        'playlist-modify-private',
        'user-library-modify',
        'user-library-read'
    ];

    public function handle()
    {
        $userId = $this->update->user()->id;

        $options = [
            'scope' => self::$scopes,
            'state' => $userId
        ];

        $session = App::make(Session::class);
        $url = $session->getAuthorizeUrl($options);

        $this->sendMessage(
            [
                'text' => $url
            ]
        );
    }
}
