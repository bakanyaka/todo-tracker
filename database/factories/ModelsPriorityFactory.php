<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Priority::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->word
    ];
});
