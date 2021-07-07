<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperService
 */
class Service extends Model
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function getHoursAttribute($value)
    {
        if ($this->isIgnored()) {
            return null;
        }
        return $value;
    }

    public function isIgnored()
    {
        return config('redmine.services.ignore.ids') && in_array($this->id, config('redmine.services.ignore.ids'));
    }

}
