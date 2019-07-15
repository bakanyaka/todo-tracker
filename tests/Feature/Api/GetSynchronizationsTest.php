<?php

namespace Tests\Feature\Api;

use App\Models\Synchronization;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetSynchronizationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_get_last_synchronizations_timestamps()
    {
        $this->signIn();

        $issuesLastSync = Synchronization::create(['completed_at' => Carbon::now()->subHours(1), 'type' => 'issues']);
        $timeEntriesLastSync = Synchronization::create(['completed_at' => Carbon::now()->subHours(2), 'type' => 'time_entries']);
        $assigneesLastSync = Synchronization::create(['completed_at' => Carbon::now()->subHours(3), 'type' => 'assignees']);
        $projectsLastSync = Synchronization::create(['completed_at' => Carbon::now()->subHours(4), 'type' => 'projects']);
        $trackersLastSync = Synchronization::create(['completed_at' => Carbon::now()->subHours(5), 'type' => 'trackers']);

        Synchronization::create(['completed_at' => Carbon::now()->subDay(1), 'type' => 'issues']);
        Synchronization::create(['completed_at' => Carbon::now()->subDay(1), 'type' => 'time_entries']);
        Synchronization::create(['completed_at' => Carbon::now()->subDay(1), 'type' => 'assignees']);
        Synchronization::create(['completed_at' => Carbon::now()->subDay(1), 'type' => 'projects']);
        Synchronization::create(['completed_at' => Carbon::now()->subDay(1), 'type' => 'trackers']);

        $response = $this->get(route('api.synchronizations.index'));

        $response->assertJson([
            'data' => [
                'issues' => [
                    'completed_at' => $issuesLastSync->completed_at->toDateTimeString(),
                    'completed_at_human' => $issuesLastSync->completed_at->diffForHumans(),
                ],
                'time_entries' => [
                    'completed_at' => $timeEntriesLastSync->completed_at->toDateTimeString(),
                    'completed_at_human' => $timeEntriesLastSync->completed_at->diffForHumans(),
                ],
                'assignees' => [
                    'completed_at' => $assigneesLastSync->completed_at->toDateTimeString(),
                    'completed_at_human' => $assigneesLastSync->completed_at->diffForHumans(),
                ],
                'projects' => [
                    'completed_at' => $projectsLastSync->completed_at->toDateTimeString(),
                    'completed_at_human' => $projectsLastSync->completed_at->diffForHumans(),
                ],
                'trackers' => [
                    'completed_at' => $trackersLastSync->completed_at->toDateTimeString(),
                    'completed_at_human' => $trackersLastSync->completed_at->diffForHumans(),
                ],
            ]
        ]);
    }

}
