<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Project::class, function (Faker $faker) {
    return [
        'name' => $this->faker->unique()->sentence,
        'identifier' => $this->faker->unique()->word,
        'description' => $faker->realText(30)
    ];
});
