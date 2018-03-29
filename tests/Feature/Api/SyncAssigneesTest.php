<?php

namespace Tests\Feature\Api;

use App\Facades\Redmine;
use App\Models\Assignee;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SyncAssigneesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_missing_in_db_are_added_when_synchronizing_with_redmine()
    {
        // Given we have user in Redmine that doesn't exist in database
        $user = $this->makeFakeRedmineAssignees();
        Redmine::shouldReceive('getUsers')->once()->andReturn(collect([$user]));

        // When administrator makes request to synchronize users with Redmine
        $this->signInAsAdmin();
        $response = $this->get(route('api.assignees.sync'));
        $response->assertStatus(200);

        // Then it should be created in database
        $this->assertDatabaseHas('assignees', ['id' => $user['id']]);
    }

    private function makeFakeRedmineAssignees($attributes = [], $count = 1)
    {
        $assignees = [];
        for ($i = 0; $i < $count; $i++) {
            $assignees[] = array_merge([
                "id" => $this->faker->unique()->randomNumber(2),
                "login" => $this->faker->userName,
                "firstname" => $this->faker->firstName,
                "lastname" => $this->faker->lastName,
                "mail" => $this->faker->email
            ],$attributes);
        }
        return count($assignees) > 1 ? collect($assignees) : $assignees[0];
    }

    /** @test */
    public function users_existing_in_db_are_updated_when_synchronizing_with_redmine()
    {
        // Given we have a user in Redmine that exists in database with same id but with different properties
        $assigneesDB = create(Assignee::class);
        $assigneesRM = $this->makeFakeRedmineAssignees(['id' => $assigneesDB->id]);

        Redmine::shouldReceive('getUsers')->once()->andReturn(collect([$assigneesRM]));

        // When administrator makes request to synchronize users with Redmine
        $this->signInAsAdmin();
        $response = $this->get(route('api.assignees.sync'));
        $response->assertStatus(200);

        // Then it should be updated in database
        $assigneesDB = $assigneesDB->fresh();
        $this->assertEquals($assigneesRM['login'], $assigneesDB->login);
        $this->assertEquals($assigneesRM['firstname'], $assigneesDB->firstname);
        $this->assertEquals($assigneesRM['lastname'], $assigneesDB->lastname);
        $this->assertEquals($assigneesRM['mail'], $assigneesDB->mail);
    }

    /** @test */
    public function it_saves_sync_timestamp_to_database()
    {
        $now = Carbon::create(2017,12,9,5);
        Carbon::setTestNow($now);
        Redmine::shouldReceive('getUsers')->once()->andReturn(collect());

        $this->signInAsAdmin();
        $response = $this->get(route('api.assignees.sync'));
        $response->assertStatus(200);

        $this->assertDatabaseHas('synchronizations',['completed_at' => $now, 'type' => 'assignees']);

    }

}
