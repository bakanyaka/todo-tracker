<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Tracker::class, function (Faker $faker) {
    return [
        'id' => $this->faker->unique()->randomNumber(3),
        'name' => $this->faker->unique()->sentence,
    ];
});
