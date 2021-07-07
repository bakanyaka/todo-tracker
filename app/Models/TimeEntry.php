<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperTimeEntry
 */
class TimeEntry extends Model
{
    /**
     * Don't auto-apply mass assignment protection.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Don't auto increment id column
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'spent_on'
    ];

    /**
     * Scope a query to only include issues created between given dates
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $startDate
     * @param $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSpentWithin($query, $startDate, $endDate)
    {
        $query->whereDate('spent_on', '>=', $startDate);
        $query->whereDate('spent_on', '<=', $endDate);
        return $query;
    }
}
