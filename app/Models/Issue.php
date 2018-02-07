<?php

namespace App\Models;

use App\BusinessDate;
use App\Exceptions\FailedToRetrieveRedmineDataException;
use App\Facades\Redmine;
use App\Filters\IssueFilters;
use App\User;
use Cache;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Issue
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $subject
 * @property BusinessDate $created_on
 * @property BusinessDate $updated_on
 * @property BusinessDate $due_date
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue whereCreatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue whereIssueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $trackedByUsers
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue whereSubject($value)
 * @property-read \App\Models\Service $service
 * @property int|null $service_id
 * @property-read BusinessDate $closed_on
 * @property-read mixed $estimated_hours
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue whereServiceId($value)
 * @property-read mixed $actual_time
 * @property-read mixed $percent_of_time_left
 * @property-read int|null $time_left
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue whereClosedOn($value)
 * @property string|null $department
 * @property int $priority_id
 * @property-read \App\Models\Priority $priority
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue whereDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue wherePriorityId($value)
 * @property string|null $assigned_to
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue open()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue whereAssignedTo($value)
 * @property int $control
 * @property int $status_id
 * @property float $on_pause_hours
 * @property BusinessDate $status_changed_on
 * @property-read mixed $is_paused
 * @property-read \App\Models\Status $status
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue closed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue filter(\App\Filters\IssueFilters $filters)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue markedForControl()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue paused()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue whereControl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue whereOnPauseHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue whereStatusChangedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue whereStatusId($value)
 */
class Issue extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];

    /**
     * Don't auto-apply mass assignment protection.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The relationships to always eager-load.
     *
     * @var array
     */
    protected $with = ['service', 'priority', 'status'];

    /**
     * Don't auto increment id column
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Apply all relevant filters based on request
     *
     * @param Builder $query
     * @param IssueFilters $filters
     * @return Builder
     */
    public function scopeFilter(Builder $query, IssueFilters $filters) {
        return $filters->apply($query);
    }

    /**
     * Scope a query to only include incomplete issues.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOpen($query)
    {
        return $query->whereHas('status', function (Builder $q) {
            $q->where('is_closed', false);
        });
    }

    /**
     * Scope a query to only include complete issues.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeClosed($query)
    {
        return $query->whereHas('status', function (Builder $q) {
            $q->where('is_closed', true);
        });
    }

    /**
     * Scope a query to only include paused issues.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaused($query)
    {
        return $query->whereHas('status', function (Builder $q) {
            $q->where('is_paused', true);
        });
    }

    /**
     * Scope a query to only include issues created between given dates
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $startDate
     * @param $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCreatedWithin($query, $startDate, $endDate)
    {
        $query->where('created_on', '>', $startDate);
        $query->where('created_on', '<', $endDate);
        return $query;
    }

    /**
     * Scope a query to only include issues closed between given dates
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $startDate
     * @param $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeClosedWithin($query, $startDate, $endDate)
    {
        $query->where('closed_on', '>', $startDate);
        $query->where('closed_on', '<', $endDate);
        return $query;
    }


    /**
     * Scope a query to only include issues marked for control.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMarkedForControl($query)
    {
        return $query->where(['control' => true]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('App\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo('App\Models\Service');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function priority()
    {
        return $this->belongsTo('App\Models\Priority');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo('App\Models\Project');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo('App\Models\Status');
    }

    /**
     * @param $value
     * @return BusinessDate
     */
    public function getCreatedOnAttribute($value)
    {
        return BusinessDate::parse($value);
    }

    /**
     * @param $value
     * @return BusinessDate
     */
    public function getClosedOnAttribute($value)
    {
        return $value ? BusinessDate::parse($value) : null;
    }

    /**
     * @param $value
     * @return BusinessDate
     */
    public function getUpdatedOnAttribute($value)
    {
        return $value ? BusinessDate::parse($value) : null;
    }


    /**
     * @param $value
     * @return BusinessDate
     */
    public function getStatusChangedOnAttribute($value)
    {
        return BusinessDate::parse($value);
    }

    /**
     * Calculates due date based on created on timestamp and estimated hours
     * @param $value
     * @return BusinessDate | null
     */
    public function getDueDateAttribute($value)
    {
        return $this->estimatedHours ? $this->created_on->addBusinessHours($this->estimatedHours)->addBusinessHours($this->on_pause_hours) : null;
    }

    /**
     * @return int | null
     */
    public function getEstimatedHoursAttribute()
    {
        return optional($this->service)->hours;
    }

    /**
     *  Calculates difference in business hours between current timestamp and due date
     * @return int|null
     */
    public function getTimeLeftAttribute()
    {
        if (is_null($this->due_date)) {
            return null;
        }

        if ($this->is_paused) {
            $timestamp = $this->status_changed_on;
        } elseif (is_null($this->closed_on)) {
            $timestamp = BusinessDate::now();
        } else {
            $timestamp = $this->closed_on;
        }

        $difference = $timestamp->diffInBusinessHours($this->due_date);
        if ($this->due_date->gte($timestamp)) {
            return $difference;
        } else {
            return $difference * -1;
        }
    }

    public function getPercentOfTimeLeftAttribute()
    {
        return is_null(optional($this->service)->hours) ? null : $this->time_left / $this->service->hours * 100;
    }

    public function getActualTimeAttribute()
    {
        return is_null($this->closed_on) ? null : $this->created_on->diffInBusinessHours($this->closed_on);
    }

    public function getIsPausedAttribute()
    {
        return $this->status->is_paused;
    }

    /**
     *
     * @param  string  $value
     * @return void
     */
    public function setStatusIdAttribute($value)
    {
        if (array_key_exists('status_id', $this->attributes) && $this->attributes['status_id'] == $value) {
            return;
        }
        if ($this->status && $this->status->is_paused) {
            $this->on_pause_hours += $this->status_changed_on->diffInBusinessHours(now());
        }
        $this->attributes['status_id'] = $value;
        $this->status_changed_on = now();
    }

    public function isTrackedBy(User $user)
    {
        return $this->users()->find($user->id) !== null;
    }

    /**
     * Add this issue to user's tracked issues
     * @param User $user
     */
    public function track(User $user)
    {
        if (!$this->users()->find($user->id)) {
            $this->users()->attach($user);
        }
    }

    /**
     * Remove issue from user's tracked issues
     * @param User $user
     */
    public function untrack(User $user)
    {
        $this->users()->detach($user);
    }

    /**
     * Update model data with data loaded from Redmine API
     *
     */
    public function updateFromRedmine()
    {
        $redmineIssue = Redmine::getIssue($this->id);
        return $this->updateFromRedmineIssue($redmineIssue);
    }

    public function updateFromRedmineIssue(array $redmineIssue)
    {
        $this->subject = $redmineIssue['subject'];
        $this->department = $redmineIssue['department'];
        $this->assigned_to = $redmineIssue['assigned_to'];
        $this->created_on = $redmineIssue['created_on'];
        $this->closed_on = $redmineIssue['closed_on'];
        $this->control = $redmineIssue['control'] == 1 ? true : false;
        $priority = Priority::find($redmineIssue['priority_id']);
        if (!is_null($priority)) {
            $this->priority_id = $priority->id;
        }
        $status = Status::find($redmineIssue['status_id']);
        if (!is_null($status)) {
            $this->status_id = $status->id;
        }
        $project = Project::find($redmineIssue['project_id']);
        if (!is_null($project)) {
            $this->project_id = $project->id;
        }
        $service = Service::where('name', $redmineIssue['service'])->first();
        $this->service()->associate($service);
        return $this;
    }

    public static function defaultSort($a, $b)
    {
        if (is_null($a->time_left) && !is_null($b->time_left)) {
            return 1;
        } elseif (is_null($b->time_left) && !is_null($a->time_left)) {
            return -1;
        } elseif ($a->priority_id === $b->priority_id) {
            if ($a->time_left > $b->time_left) {
                return 1;
            } elseif ($a->time_left < $b->time_left) {
                return -1;
            } else {
                return 0;
            }
        }
        return $b->priority_id - $a->priority_id;
    }
}
