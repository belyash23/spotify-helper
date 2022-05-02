<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\ServiceProvider;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;

class SpotifyServiceProvider extends ServiceProvider
{

    protected static $options = [
        'auto_refresh' => true
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->bindSession();
        $this->bindSpotify();
    }

    protected function bindSession()
    {
        $this->app->singleton(
            Session::class,
            function () {
                return new Session(
                    config('services.spotify.client_id'),
                    config('services.spotify.client_secret'),
                    config('services.spotify.redirect')
                );
            }
        );
    }

    protected function bindSpotify()
    {
        $this->app->singleton(
            SpotifyWebAPI::class,
            function ($app, $id) {
                if (!$id) {
                    return false;
                }
                $user = User::findByTelegramId($id);
                $accessToken = $user->spotify_access_token;
                $refreshToken = $user->spotify_refresh_token;

                $session = $this->app->make(Session::class);

                $session->setAccessToken($accessToken);
                $session->setRefreshToken($refreshToken);

                $api = new SpotifyWebAPI(self::$options, $session);
                $api->setSession($session);

                $newAccessToken = $session->getAccessToken();
                $newRefreshToken = $session->getRefreshToken();

                $user->update(
                    [
                        'spotify_access_token' => $newAccessToken,
                        'spotify_refresh_token' => $newRefreshToken
                    ]
                );

                return $api;
            }
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
