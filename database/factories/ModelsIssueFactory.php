<?php

use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\Models\Issue::class, function (Faker $faker) {
    $title = $faker->name . " : " . $faker->realText(50);
    return [
        'title' => $title,
        'issue_id' => $faker->unique()->randomNumber(5),
        'created_on' => Carbon::now(),
        'due_date' => Carbon::now()->addDays(10)
    ];
});
