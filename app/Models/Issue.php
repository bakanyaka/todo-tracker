<?php

namespace App\Models;

use App\BusinessDate;
use App\Enums\OverdueState;
use App\Facades\Redmine;
use App\Filters\IssueFilters;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $trackedByUsers
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue whereSubject($value)
 * @property-read \App\Models\Service $service
 * @property int|null $service_id
 * @property-read BusinessDate $closed_on
 * @property-read mixed $estimated_hours
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
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
 * @property int $project_id
 * @property-read \App\Models\Project $project
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue closedWithin($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue createdWithin($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Issue whereUpdatedOn($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TimeEntry[] $time_entries
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
        'control' => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'created_on',
        'closed_on',
        'updated_on',
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
     * Attribute for time left caching
     *
     * @var float
     */
    protected $timeLeftCached;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

//        static::addGlobalScope('notInProcurement', function (Builder $builder) {
//            $builder->where('assigned_to', '<>', 'Отдел Закупок')
//                ->where(function (Builder $query) {
//                    $query->where('tracker_id', '<>', 12)->orWhereNull('tracker_id');
//                });
//        });
    }

    /**
     * Apply all relevant filters based on request
     */
    public function scopeFilter(Builder $query, IssueFilters $filters): Builder
    {
        return $filters->apply($query);
    }

    /**
     * Scope a query to only include incomplete issues.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereHas(
            'status',
            function (Builder $q) {
                $q->where('is_closed', false);
            }
        );
    }

    /**
     * Scope a query to only include complete issues.
     */
    public function scopeClosed(Builder $query): Builder
    {
        return $query->whereHas(
            'status',
            function (Builder $q) {
                $q->where('is_closed', true);
            }
        );
    }

    /**
     * Scope a query to only include paused issues.
     */
    public function scopePaused(Builder $query): Builder
    {
        return $query->whereHas(
            'status',
            function (Builder $q) {
                $q->where('is_paused', true);
            }
        );
    }


    /**
     * Scope a query to only include active issues.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereHas(
            'status',
            function (Builder $q) {
                $q->where('is_paused', false);
                $q->where('is_closed', false);
            }
        );
    }


    /**
     * Scope a query to only include issues created between given dates
     */
    public function scopeCreatedWithin(Builder $query, $startDate, $endDate): Builder
    {
        $query->whereDate('created_on', '>=', $startDate);
        $query->whereDate('created_on', '<=', $endDate);
        return $query;
    }

    /**
     * Scope a query to only include issues closed between given dates
     */
    public function scopeClosedWithin(Builder $query, $startDate, $endDate): Builder
    {
        $query->whereDate('closed_on', '>=', $startDate);
        $query->whereDate('closed_on', '<=', $endDate);
        return $query;
    }

    /**
     * Scope a query to only include issues that are not in procurement
     */
    public function scopeNotInProcurement($query): Builder
    {
        return $query
            ->where('assigned_to', '<>', 'Отдел Закупок')
            ->where('tracker_id', '<>', 12);
    }

    /**
     * Scope a query to only include issues that are in procurement
     */
    public function scopeInProcurement(Builder $query): Builder
    {
        return $query->where(
            function (Builder $subQuery) {
                $subQuery->where('assigned_to', 'Отдел Закупок')->orWhere('tracker_id', 12);
            }
        );
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo('App\Models\Service');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo('App\Models\Priority');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo('App\Models\Project');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo('App\Models\Status');
    }

    public function time_entries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function getCreatedOnAttribute($value): BusinessDate
    {
        return BusinessDate::parse($value);
    }

    public function getClosedOnAttribute($value): ?BusinessDate
    {
        return $value ? BusinessDate::parse($value) : null;
    }

    public function getUpdatedOnAttribute($value): ?BusinessDate
    {
        return $value ? BusinessDate::parse($value) : null;
    }

    public function getStatusChangedOnAttribute($value): BusinessDate
    {
        return BusinessDate::parse($value);
    }

    /**
     * Calculates due date based on created on timestamp and estimated hours
     */
    public function getDueDateAttribute($value): ?BusinessDate
    {
        return $this->estimatedHours ? $this->created_on->addBusinessHours($this->estimatedHours)->addBusinessHours(
            $this->on_pause_hours
        ) : null;
    }


    public function getEstimatedHoursAttribute(): ?int
    {
        return optional($this->service)->hours;
    }

    /**
     *  Calculates difference in business hours between current timestamp and due date
     */
    public function getTimeLeftAttribute(): ?float
    {
        if (isset($this->timeLeftCached)) {
            return $this->timeLeftCached;
        }
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
        $hoursLeft = $timestamp->diffInBusinessHours($this->due_date);
        if ($this->due_date->lt($timestamp)) {
            $hoursLeft = $hoursLeft * -1;
        }
        $this->timeLeftCached = $hoursLeft;
        return $hoursLeft;
    }

    public function getPercentOfTimeLeftAttribute(): float|int|null
    {
        return is_null(optional($this->service)->hours) ? null : $this->time_left / $this->service->hours * 100;
    }

    public function getActualTimeAttribute(): ?float
    {
        return is_null($this->closed_on) ? null : $this->created_on->diffInBusinessHours($this->closed_on);
    }

    public function getIsPausedAttribute(): int
    {
        return $this->status->is_paused;
    }

    public function getOverdueState(): OverdueState
    {
        if ($this->due_date === null || $this->is_paused) {
            return OverdueState::No();
        }
        if ($this->percent_of_time_left < 0) {
            return OverdueState::Yes();
        }
        if ($this->percent_of_time_left < 30) {
            return OverdueState::Soon();
        }
        return OverdueState::No();
    }

    public function setStatusIdAttribute(string $value)
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

    public function getIsTrackedByCurrentUserAttribute(): bool
    {
        return $this->isTrackedBy(auth()->user());
    }

    public function isTrackedBy(User $user): bool
    {
        return $this->users->where('id', $user->id)->isNotEmpty();
    }

    /**
     * Add this issue to user's tracked issues
     */
    public function track(User $user)
    {
        if (!$this->users()->find($user->id)) {
            $this->users()->attach($user);
        }
    }

    /**
     * Remove issue from user's tracked issues
     * @param  User  $user
     */
    public function untrack(User $user)
    {
        $this->users()->detach($user);
    }

    /**
     * Update model data with data loaded from Redmine API
     */
    public function updateFromRedmine(): static
    {
        $redmineIssue = Redmine::getIssue($this->id);
        return $this->updateFromRedmineIssue($redmineIssue);
    }

    public function updateFromRedmineIssue(array $redmineIssue): static
    {
        $this->subject = $redmineIssue['subject'];
        $this->assigned_to = $redmineIssue['assigned_to'];
        $this->assigned_to_id = $redmineIssue['assigned_to_id'];
        $this->created_on = Carbon::create(
            $redmineIssue['created_on']->year,
            $redmineIssue['created_on']->month,
            $redmineIssue['created_on']->day,
            $redmineIssue['created_on']->hour,
            $redmineIssue['created_on']->minute,
            $redmineIssue['created_on']->second,
            $redmineIssue['created_on']->tz
        );
        $this->closed_on = $redmineIssue['closed_on'];
        $this->updated_on = $redmineIssue['updated_on'];
        $this->control = true;

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

        $tracker = Tracker::find($redmineIssue['tracker_id']);
        if (!is_null($tracker)) {
            $this->tracker_id = $tracker->id;
        }

        $service = Service::find($redmineIssue['service_id']);
        $this->service_id = $service ? $service->id : null;

        return $this;
    }

    public static function defaultSort($a, $b): int
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
