<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Service::class, function (Faker $faker) {
    return [
        'name' => $faker->sentence(4),
        'hours' => $faker->numberBetween(1,48)
    ];
});
