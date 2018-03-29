<?php

namespace App\Providers;

use App\Services\Redmine;
use App\Services\Sync;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        URL::forceScheme('https');
        Carbon::setLocale(config('app.locale'));
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }

        $this->app->singleton('GuzzleHttp\Client', function() {
            return new \GuzzleHttp\Client([
                'base_uri' => config('services.redmine.uri'),
                'headers' => [
                    'X-Redmine-API-Key' => config('services.redmine.secret')
                ]
            ]);
        });

        $this->app->singleton('Redmine', function ($app) {
            return new Redmine($app->make('GuzzleHttp\Client'));
        });
    }
}
