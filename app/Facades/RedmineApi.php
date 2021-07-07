<?php


namespace App\Facades;


use App\Services\RedmineApiService;
use Illuminate\Support\Facades\Facade;

class RedmineApi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return RedmineApiService::class;
    }
}
