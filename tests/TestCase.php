<?php

namespace Tests;

use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $faker;

    protected function setUp()
    {
        parent::setUp();

        $this->withoutExceptionHandling();
        $this->faker = Factory::create('ru_RU');
    }

    protected function signIn($user = null)
    {
        $user = $user ?: create('App\User');
        $this->actingAs($user);
        return $this;
    }

    protected function makeFakeIssueArray($attributes = [])
    {
        $issue = array_merge([
            'id' =>  $this->faker->unique()->randomNumber(5),
            'status_id' => 2,
            'priority_id' => 4,
            'project_id' => 2,
            'author' => $this->faker->name,
            'assigned_to' => $this->faker->name,
            'subject' => $this->faker->name . ' : ' . $this->faker->realText(60),
            'description' => $this->faker->realText(),
            'department' => '115 Управление информационных систем',
            'service' => 'Организация рабочих мест пользователей',
            'control' => 1,
            'created_on' => Carbon::instance($this->faker->dateTimeThisMonth()),
            'updated_on' => Carbon::instance($this->faker->dateTimeThisMonth()),
            'closed_on' => Carbon::instance($this->faker->dateTimeThisMonth()),
        ],$attributes);
        return $issue;
    }

}