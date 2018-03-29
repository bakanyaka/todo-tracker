<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Assignee::class, function (Faker $faker) {
    return [
        "id" => $this->faker->unique()->randomNumber(2),
        "login" => $this->faker->userName,
        "firstname" => $this->faker->firstName,
        "lastname" => $this->faker->lastName,
        "mail" => $this->faker->email
    ];
});
