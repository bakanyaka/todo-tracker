<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperAssignee
 */
class Assignee extends Model
{
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;

    public function participatedIssues(): BelongsToMany
    {
        return $this->belongsToMany(Issue::class, 'time_entries');
    }

    public function participatedWithinPeriodIssues($startDate, $endDate): BelongsToMany
    {
        return $this->belongsToMany(Issue::class, 'time_entries')
            ->wherePivot('spent_on', '>=', $startDate)
            ->wherePivot('spent_on', '<', $endDate);
    }

    public function assignedIssues(): HasMany
    {
        return $this->hasMany(Issue::class, 'assigned_to_id');
    }

    public function getNameAttribute(): string
    {
        return "$this->lastname $this->firstname";
    }
}
