<?php

namespace Tests\Feature\Api;

use App\Models\Project;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetProjectsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function project_list_can_be_retrieved()
    {
        $project = create(Project::class);
        $this->signIn();
        $response = $this->get(route('api.projects'));
        $response->assertJsonFragment([
            [
                'id' => $project->id,
                'name' => $project->name,
                'identifier' => $project->identifier,
                'description' => $project->description,
                'parent_id' => $project->parent_id
            ]
        ]);
    }

}
