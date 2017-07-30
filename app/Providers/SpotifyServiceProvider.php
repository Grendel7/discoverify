<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SpotifyWebAPI\SpotifyWebAPI;

class SpotifyServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton('spotify', function ($app) {
            $api =  new SpotifyWebAPI();

            if ($user = $app->make('auth')->user()) {
                $api->setAccessToken($user->access_token);
            }

            return $api;
        });
    }

    public function provides()
    {
        return ['spotify'];
    }
}