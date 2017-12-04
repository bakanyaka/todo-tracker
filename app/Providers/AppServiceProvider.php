<?php

namespace App\Providers;

use App\Services\Redmine;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
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
