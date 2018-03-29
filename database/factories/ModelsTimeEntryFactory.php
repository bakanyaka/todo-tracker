<?php

use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\Models\TimeEntry::class, function (Faker $faker) {
    return [
        "id" => $this->faker->unique()->randomNumber(6),
        "assignee_id" => function () {
            return factory(App\Models\Assignee::class)->create()->id;
        },
        "project_id" => function () {
            return factory(App\Models\Project::class)->create()->id;
        },
        "issue_id" => function () {
            return factory(App\Models\Issue::class)->create()->id;
        },
        "hours" => $this->faker->randomNumber(1),
        "comments" => $this->faker->sentence,
        "spent_on" => Carbon::parse($this->faker->date),
    ];
});
