<?php

use App\BusinessDate;
use Faker\Generator as Faker;

$factory->define(App\Models\Issue::class, function (Faker $faker) {
    $title = $faker->name . " : " . e($faker->realText(50));
    return [
        'id' => $faker->unique()->randomNumber(5),
        'subject' => $title,
        'department' => '147 отдел информационных технологий',
        'assigned_to' => $faker->name(),
        'created_on' => BusinessDate::instance($faker->dateTimeThisMonth),
        'service_id' => function () {
            return factory(App\Models\Service::class)->create()->id;
        },
        'priority_id' => rand(3,7)
    ];
});

$factory->state(App\Models\Issue::class, 'closed', function (Faker $faker) {
    return [
        'created_on' => $created_on = BusinessDate::instance($faker->dateTimeThisMonth),
        'closed_on' => $created_on->copy()->addHours($faker->numberBetween(1,48))
    ];
});

$factory->state(App\Models\Issue::class, 'open', function (Faker $faker) {
    return [
        'created_on' => $created_on = BusinessDate::instance($faker->dateTimeThisMonth),
        'closed_on' => null
    ];
});
