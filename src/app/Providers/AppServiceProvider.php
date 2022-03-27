<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /* 
        Laravel on Replit - Compatibility

        DO NOT REMOVE. App URL is needed for compatibility over php artisan serve.
        If you are working in a local developer environment, do not include ON_REPLIT or set it to false in your .env file.
        */
        if(env('ON_REPLIT') == true){
          \URL::forceRootUrl(\Config::get('app.url'));
          \URL::forceScheme('https');
        }
    }
}
