<?php

namespace App\Models;

use App\BusinessDate;
use App\Facades\Redmine;
use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Issue
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $subject
 * @property BusinessDate $created_on
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
 */
class Issue extends Model
{
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
    protected $with = ['service'];

    /**
     * Don't auto increment id column
     *
     * @var bool
     */
    public $incrementing = false;

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
     * @return BusinessDate | null
     */
    public function getDueDateAttribute($value)
    {
        return $this->estimatedHours ? $this->created_on->addBusinessHours($this->estimatedHours) : null;
    }

    public function users()
    {
        return $this->belongsToMany('App\User');
    }

    public function service()
    {
        return $this->belongsTo('App\Models\Service');
    }

    public function getEstimatedHoursAttribute()
    {
        return optional($this->service)->hours;
    }

    public function track(User $user)
    {
        if(!$this->users()->find($user->id))
        {
            $this->users()->attach($user);
        }
    }

    public function updateFromRedmine()
    {
        $issueData = Redmine::getIssue($this->id);
        $this->subject = $issueData['subject'];
        $this->created_on = $issueData['created_on'];
        $this->closed_on = $issueData['closed_on'];
        $service = Service::where('name', $issueData['service'])->first();
        $this->service()->associate($service);
    }
}
