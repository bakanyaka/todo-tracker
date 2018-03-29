<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Status::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->sentence(4,true),
        'is_closed' => false,
        'is_paused' => false
    ];
});

$factory->state(App\Models\Status::class, 'closed', function (Faker $faker) {
    return [
        'is_closed' => true
    ];
});

$factory->state(App\Models\Status::class, 'open', function (Faker $faker) {
    return [
        'is_closed' => false
    ];
});

$factory->state(App\Models\Status::class, 'paused', function (Faker $faker) {
    return [
        'is_paused' => true
    ];
});

