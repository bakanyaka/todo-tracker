<?php

namespace Tests\Feature\Api;

use App\Facades\Redmine;
use App\Models\Project;
use App\Models\Tracker;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SyncTrackersTestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function trackers_missing_in_db_are_added_when_synchronizing_with_redmine()
    {
        // Given we have project in Redmine that doesn't exist in database
        $trackers = $this->makeFakeRedmineTrackers();
        Redmine::shouldReceive('getTrackers')->once()->andReturn($trackers);

        // When administrator makes request to synchronize trackers with Redmine
        $this->signInAsAdmin();
        $response = $this->get(route('api.trackers.sync'));
        $response->assertStatus(200);

        // Then it should be created in database
        $this->assertDatabaseHas('trackers', ['id' => $trackers[0]['id'], 'name' => $trackers[0]['name']]);
    }

    /** @test */
    public function trackers_existing_in_db_are_updated_when_synchronizing_with_redmine()
    {
        // Given we have project in Redmine that exists in database with same id but with different properties
        $trackersRM = $this->makeFakeRedmineTrackers();
        $trackerInDb = Tracker::create([
            'id' => $trackersRM[0]['id'],
            'name' => 'Some Name',
        ]);
        Redmine::shouldReceive('getTrackers')->once()->andReturn($trackersRM);

        // When administrator makes request to synchronize trackers with Redmine
        $this->signIn();
        $response = $this->get(route('api.trackers.sync'));
        $response->assertStatus(200);

        // Then it should be updated in database
        $trackerInDb = $trackerInDb->fresh();
        $this->assertEquals($trackersRM[0]['name'], $trackerInDb->name);
    }

    /** @test */
    public function it_saves_sync_timestamp_to_database()
    {
        $now = Carbon::create(2017,12,9,5);
        Carbon::setTestNow($now);
        Redmine::shouldReceive('getTrackers')->once()->andReturn(collect());

        $this->signInAsAdmin();
        $response = $this->get(route('api.trackers.sync'));
        $response->assertStatus(200);

        $this->assertDatabaseHas('synchronizations',['completed_at' => $now, 'type' => 'trackers']);

    }


    /**
     * @param array $attributes
     * @param int $count
     * @return \Illuminate\Support\Collection
     */
    protected function makeFakeRedmineTrackers($attributes = [], $count = 1)
    {
        $trackers = [];
        for ($i = 0; $i < $count; $i++) {
            $trackers[] = array_merge([
                'id' => $this->faker->unique()->randomNumber(3),
                'name' => $this->faker->unique()->sentence,
            ],$attributes);
        }
        return collect($trackers);
    }
}
