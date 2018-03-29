<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Assignee
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $login
 * @property string $firstname
 * @property string $lastname
 * @property string $mail
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Issue[] $participatedIssues
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Assignee whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Assignee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Assignee whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Assignee whereLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Assignee whereMail($value)
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
