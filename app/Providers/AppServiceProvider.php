<?php

namespace App\Providers;

use App\Services\RedmineApiService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Pagination\Paginator;
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
//        Schema::defaultStringLength(191);
//        URL::forceScheme('https');
        Carbon::setLocale(config('app.locale'));
        Paginator::useBootstrap();
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

        $this->app->singleton(Client::class, function () {
            return new Client([
                'base_uri' => config('services.redmine.uri'),
                'headers' => [
                    'X-Redmine-API-Key' => config('services.redmine.secret'),
                ],
            ]);
        });

        $this->app->singleton(RedmineApiService::class, function ($app) {
            return new RedmineApiService($app->make(Client::class));
        });
    }
}
