<?php

namespace App\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class CollectionsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Collection::macro('recursivePluck', function ($needle) {
            $iterator  = new RecursiveArrayIterator($this->toArray());
            $recursive = new RecursiveIteratorIterator(
                $iterator,
                RecursiveIteratorIterator::SELF_FIRST
            );
            $result = [];
            foreach ($recursive as $key => $value) {
                if ($key === $needle) {
                    $result[] = $value;
                }
            }
            return new static($result);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
