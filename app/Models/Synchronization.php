<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Synchronization
 *
 * @property int $id
 * @property string $started_at
 * @property string|null $completed_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Synchronization whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Synchronization whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Synchronization whereStartedAt($value)
 * @mixin \Eloquent
 * @property int $updated_items_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Synchronization whereUpdatedIssuesCount($value)
 * @property string $type
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Synchronization whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Synchronization whereUpdatedItemsCount($value)
 */
class Synchronization extends Model
{
    /**
     * Don't auto-apply mass assignment protection.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'started_ad',
        'completed_at'
    ];
}
