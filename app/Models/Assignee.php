<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperAssignee
 */
class Assignee extends Model
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
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function participatedIssues()
    {
        return $this->belongsToMany(Issue::class, 'time_entries');
    }

    public function participatedWithinPeriodIssues($startDate, $endDate)
    {
        return $this->belongsToMany(Issue::class, 'time_entries')
            ->wherePivot('spent_on','>=', $startDate)
            ->wherePivot('spent_on','<', $endDate);
    }

    public function assignedIssues()
    {
        return $this->hasMany(Issue::class, 'assigned_to_id');
    }
}
