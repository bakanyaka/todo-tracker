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
}
