<?php

namespace App\Models;

use App\BusinessDate;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Issue
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $subject
 * @property int $issue_id
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
 */
class Issue extends Model
{
    protected $guarded = [];

    public function getCreatedOnAttribute($value)
    {
        return BusinessDate::parse($value);
    }

    public function getDueDateAttribute($value)
    {
        return BusinessDate::parse($value);
    }
}
