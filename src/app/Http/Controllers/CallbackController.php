<?php

namespace App\Http\Controllers;

use App\Http\Requests\CallbackFormRequest;
use App\Models\User;
use SpotifyWebAPI\Session;
use WeStacks\TeleBot\Laravel\TeleBot;

class CallbackController extends Controller
{
    protected $session = null;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function __invoke(CallbackFormRequest $request)
    {
        $userId = $request->get('state');
        $code = $request->get('code');

        $this->saveTokens($userId, $code);
        $this->reply($userId);

        return redirect(config('services.telegram.bot_url'));
    }

    protected function saveTokens($userId, $code)
    {
        $this->session->requestAccessToken($code);

        $accessToken = $this->session->getAccessToken();
        $refreshToken = $this->session->getRefreshToken();

        User::findByTelegramId($userId)->update(
            [
                'spotify_access_token' => $accessToken,
                'spotify_refresh_token' => $refreshToken
            ]
        );
    }

    public function reply($userId)
    {
        TeleBot::sendMessage(
            [
                'chat_id' => $userId,
                'text' => 'Вы успешно авторизовались!'
            ]
        );
    }
}
