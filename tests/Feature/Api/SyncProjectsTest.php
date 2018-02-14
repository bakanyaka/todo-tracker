<?php

namespace Tests\Feature\Api;

use App\Facades\Redmine;
use App\Models\Project;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SyncProjectsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function projects_missing_in_db_are_added_when_synchronizing_with_redmine()
    {
        // Given we have project in Redmine that doesn't exist in database
        $projects = $this->makeFakeRedmineProjects();
        Redmine::shouldReceive('getProjects')->once()->andReturn($projects);

        // When administrator makes request to synchronize projects with Redmine
        $this->signIn();
        $response = $this->get(route('api.projects.sync'));
        $response->assertStatus(200);

        // Then it should be created in database
        $this->assertDatabaseHas('projects', ['id' => $projects[0]['id']]);
    }

    /** @test */
    public function projects_existing_in_db_are_updated_when_synchronizing_with_redmine()
    {
        // Given we have project in Redmine that exists in database with same id but with different properties
        $projectsRM = $this->makeFakeRedmineProjects();
        $projectInDB = Project::create([
            'id' => $projectsRM[0]['id'],
            'name' => 'Some Name',
            'identifier' => 'identifier',
            'description' => 'some description'
        ]);
        Redmine::shouldReceive('getProjects')->once()->andReturn($projectsRM);

        // When administrator makes request to synchronize projects with Redmine
        $this->signIn();
        $response = $this->get(route('api.projects.sync'));
        $response->assertStatus(200);

        // Then it should be updated in database
        $projectInDB = $projectInDB->fresh();
        $this->assertEquals($projectsRM[0]['name'], $projectInDB->name);
        $this->assertEquals($projectsRM[0]['identifier'], $projectInDB->identifier);
        $this->assertEquals($projectsRM[0]['description'], $projectInDB->description);
        $this->assertEquals($projectsRM[0]['parent_id'], $projectInDB->parent_id);
    }


    /**
     * @param array $attributes
     * @param int $count
     * @return \Illuminate\Support\Collection
     */
    protected function makeFakeRedmineProjects($attributes = [], $count = 1)
    {
        $projects = [];
        for ($i = 0; $i < $count; $i++) {
            $projects[] = array_merge([
                'id' => $this->faker->unique()->randomNumber(3),
                'name' => $this->faker->unique()->sentence,
                'description' => $this->faker->realText(30),
                'identifier' => $this->faker->unique()->word,
                'parent_id' => $this->faker->randomNumber(3)
            ],$attributes);
        }
        return collect($projects);
    }
}
