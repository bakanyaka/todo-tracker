<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperCategory
 */
class Category extends Model
{
    public $incrementing = false;
    public $timestamps = false;
    public $guarded = [];
}
