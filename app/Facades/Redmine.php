<?php


namespace App\Facades;


use Illuminate\Support\Facades\Facade;

class Redmine extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Redmine';
    }
}