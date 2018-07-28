<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\UrlGenerator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     * @return void
     */
    public function boot(UrlGenerator $url)
    {
        \Schema::defaultStringLength(255);

        if(env('REDIRECT_HTTPS')){
            $url->forceScheme('https');
        }

        // Set app protocol url
        \URL::forceScheme(env('APP_PROTOCOL', 'https'));
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
