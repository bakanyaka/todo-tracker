<?php

namespace Tests\Feature\Api;

use App\Facades\Redmine;
use App\Models\Project;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncServicesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsAdmin();
    }

    /** @test */
    public function it_creates_service_for_each_redmine_version()
    {
        $versions = $this->makeFakeRedmineVersion();
        $project = factory(Project::class)->create();
        Redmine::shouldReceive('getVersions')->once()->with($project->id)->andReturn($versions);

        $response = $this->get(route('api.services.sync'));

        $response->assertStatus(200);
        $this->assertNotNull(Service::where([
            'id' => $versions[0]['id'],
        ])->first());
    }

    /** @test */
    public function it_updates_services_associated_with_each_redmine_version()
    {
        $versions = $this->makeFakeRedmineVersion();
        $project = factory(Project::class)->create();
        $service = factory(Service::class)->create([
            'id' => $versions[0]['id'],
            'project_id' => $project->id,
        ]);
        Redmine::shouldReceive('getVersions')->once()->with($project->id)->andReturn($versions);

        $response = $this->get(route('api.services.sync'));

        $response->assertStatus(200);
        $service->refresh();
        $this->assertEquals($versions[0]['name'], $service->name);
        $this->assertEquals(1, $service->hours);
    }

    /** @test */
    public function it_deletes_services_that_have_no_matching_redmine_version()
    {
        $versions = $this->makeFakeRedmineVersion();
        $project = factory(Project::class)->create();
        $service = factory(Service::class)->create(['project_id' => $project->id]);
        Redmine::shouldReceive('getVersions')->once()->with($project->id)->andReturn($versions);

        $response = $this->get(route('api.services.sync'));

        $response->assertStatus(200);
        $this->assertNull(Service::find($service->id));
    }

    /** @test */
    public function it_saves_sync_timestamp_to_database()
    {
        $now = Carbon::create(2017, 12, 9, 5);
        Carbon::setTestNow($now);

        $this->signInAsAdmin();
        $response = $this->get(route('api.services.sync'));
        $response->assertStatus(200);

        $this->assertDatabaseHas('synchronizations', ['completed_at' => $now, 'type' => 'services']);

    }

    /**
     * @param  array  $attributes
     * @param  int  $count
     * @return \Illuminate\Support\Collection
     */
    protected function makeFakeRedmineVersion($attributes = [], $count = 1)
    {
        $versions = [];
        for ($i = 0; $i < $count; $i++) {
            $versions[] = array_merge([
                'id' => $this->faker->unique()->randomNumber(3),
                'name' => $this->faker->unique()->sentence,
                'custom_fields' => [
                    [
                        'id' => 82,
                        'name' => 'Время рещения (час)',
                        'value' => '1',
                    ]
                ],
            ], $attributes);
        }
        return collect($versions);
    }


}
