<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TimeEntry
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property int $project_id
 * @property int $issue_id
 * @property float $hours
 * @property string $comments
 * @property \Carbon\Carbon $spent_on
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TimeEntry spentWithin($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TimeEntry whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TimeEntry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TimeEntry whereHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TimeEntry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TimeEntry whereIssueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TimeEntry whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TimeEntry whereSpentOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TimeEntry whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TimeEntry whereUserId($value)
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
