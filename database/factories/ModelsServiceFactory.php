<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Service::class, function (Faker $faker) {
    return [
        'id' => $faker->unique()->randomNumber(5),
        'name' => $faker->sentence(4),
        'project_id' => factory(\App\Models\Project::class),
        'hours' => $faker->numberBetween(1,48)
    ];
});
