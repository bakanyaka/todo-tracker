<?php

namespace Tests;

use App\Models\Service;
use App\Models\Tracker;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $faker;

    protected function setUp()
    {
        parent::setUp();

        if(config('database.default') == 'sqlite'){
            $db = app()->make('db');
            $db->connection()->getPdo()->exec("PRAGMA foreign_keys = ON");
        }

        $this->withoutExceptionHandling();
        $this->faker = Factory::create('ru_RU');
    }

    protected function signIn($user = null)
    {
        $user = $user ?: create('App\User');
        $this->actingAs($user);
        return $this;
    }

    protected function signInAsAdmin()
    {
        $user =create('App\User', ['is_admin' => true]);
        $this->actingAs($user);
        return $this;
    }

    protected function makeFakeIssueArray($attributes = [])
    {
        $created_on = Carbon::instance($this->faker->dateTimeThisMonth());

        $issue = array_merge([
            'id' =>  $this->faker->unique()->randomNumber(5),
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
            'created_on' => $created_on,
            'updated_on' => Carbon::instance($this->faker->dateTimeThisMonth()),
            'closed_on' => Carbon::instance($this->faker->dateTimeThisMonth()),
        ],$attributes);
        return $issue;
    }

}
