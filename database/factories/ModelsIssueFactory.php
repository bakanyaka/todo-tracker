<?php

use App\BusinessDate;
use Faker\Generator as Faker;

$factory->define(App\Models\Issue::class, function (Faker $faker) {
    $title = $faker->name . " : " . $faker->realText(50);
    return [
        'id' => $faker->unique()->randomNumber(5),
        'subject' => $title,
        'department' => '147 отдел информационных технологий',
        'created_on' => BusinessDate::instance($faker->dateTimeThisMonth),
        'service_id' => $faker->numberBetween(0, 4)
    ];
});
