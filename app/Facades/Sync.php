<?php


namespace App\Facades;


use Illuminate\Support\Facades\Facade;

class Sync extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Sync';
    }
}
