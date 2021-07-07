<?php

namespace Tests;

use App\Models\Tracker;
use Carbon\Carbon;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use ArraySubsetAsserts;
    use WithFaker;

    protected function signIn($user = null)
    {
        $user = $user ?: create('App\Models\User');
        $this->actingAs($user);
        return $this;
    }

    protected function signInAsAdmin(): static
    {
        $user =create('App\Models\User', ['is_admin' => true]);
        $this->actingAs($user);
        return $this;
    }

    protected function makeFakeIssueArray($attributes = []): array
    {
        $created_on = Carbon::instance($this->faker->dateTimeThisMonth());

        $issue = array_merge([
            'id' =>  $this->faker->unique()->randomNumber(5),
            'parent_id' => null,
            'status_id' => 2,
            'priority_id' => 4,
            'tracker_id' => \factory(Tracker::class)->create()->id,
            'project_id' => 2,
            'author' => $this->faker->name,
            'assigned_to' => $this->faker->name,
            'assigned_to_id' => $this->faker->unique()->randomNumber(3),
            'subject' => $this->faker->name . ' : ' . $this->faker->realText(60),
            'description' => $this->faker->realText(),
            'service_id' => 1,
            'start_date' => Carbon::parse($created_on->toDateString()),
            'due_date' => Carbon::parse($created_on->toDateString())->addDays($this->faker->numberBetween(1, 30)),
            'created_on' => $created_on,
            'updated_on' => Carbon::instance($this->faker->dateTimeThisMonth()),
            'closed_on' => Carbon::instance($this->faker->dateTimeThisMonth()),
        ],$attributes);
        return $issue;
    }

}
