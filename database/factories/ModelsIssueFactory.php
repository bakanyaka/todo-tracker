<?php

use App\BusinessDate;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\Models\Issue::class, function (Faker $faker) {
    $title = $faker->name . " : " . e($faker->realText(50));
    return [
        'id' => $faker->unique()->randomNumber(5),
        'subject' => $title,
        'department' => '147 отдел информационных технологий',
        'assigned_to' => $faker->name(),
        'created_on' => Carbon::now(),
        'service_id' => function () {
            return factory(App\Models\Service::class)->create()->id;
        },
        'priority_id' => rand(3,7),
        'status_id' => 1
    ];
});

$factory->state(App\Models\Issue::class, 'closed', function (Faker $faker) {
    return [
        'status_id' => 5,
        'created_on' => $created_on = BusinessDate::instance($faker->dateTimeThisMonth),
        'closed_on' => $created_on->copy()->addHours($faker->numberBetween(1,48))
    ];
});

$factory->state(App\Models\Issue::class, 'open', function () {
    return [
        'status_id' => 2,
        'closed_on' => null
    ];
});

$factory->state(App\Models\Issue::class, 'paused', function (Faker $faker) {
    return [
        'status_id' => 4,
        'created_on' => Carbon::now(),
        'status_changed_on' => Carbon::now()
    ];
});

