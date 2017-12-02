<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Issue
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $title
 * @property int $issue_id
 * @property string $created_on
 * @property string $due_date
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
}
