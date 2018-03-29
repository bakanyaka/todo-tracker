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
        Synchronization::create(['completed_at' => Carbon::now()->subDay(1), 'type' => 'issues']);
        Synchronization::create(['completed_at' => Carbon::now()->subDay(1), 'type' => 'time_entries']);
        Synchronization::create(['completed_at' => Carbon::now()->subDay(1), 'type' => 'assignees']);
        Synchronization::create(['completed_at' => Carbon::now()->subDay(1), 'type' => 'projects']);

        $issuesLastSync = Synchronization::create(['completed_at' => Carbon::now()->subHours(1), 'type' => 'issues']);
        $timeEntriesLastSync = Synchronization::create(['completed_at' => Carbon::now()->subHours(2), 'type' => 'time_entries']);
        $AssigneesLastSync = Synchronization::create(['completed_at' => Carbon::now()->subHours(3), 'type' => 'assignees']);
        $ProjectsLastSync = Synchronization::create(['completed_at' => Carbon::now()->subHours(4), 'type' => 'projects']);

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
                    'completed_at' => $AssigneesLastSync->completed_at->toDateTimeString(),
                    'completed_at_human' => $AssigneesLastSync->completed_at->diffForHumans(),
                ],
                'projects' => [
                    'completed_at' => $ProjectsLastSync->completed_at->toDateTimeString(),
                    'completed_at_human' => $ProjectsLastSync->completed_at->diffForHumans(),
                ],

            ]
        ]);
    }

}
