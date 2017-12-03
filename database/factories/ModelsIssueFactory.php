<?php

use App\BusinessDate;
use Faker\Generator as Faker;

$factory->define(App\Models\Issue::class, function (Faker $faker) {
    $title = $faker->name . " : " . $faker->realText(50);
    return [
        'subject' => $title,
        'issue_id' => $faker->unique()->randomNumber(5),
        'created_on' => BusinessDate::now(),
        'due_date' => BusinessDate::now()->addDays(10)
    ];
});
