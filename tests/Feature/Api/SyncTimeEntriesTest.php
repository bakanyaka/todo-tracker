<?php

namespace Tests\Feature\Api;

use App\Jobs\SyncTimeEntries;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SyncTimeEntriesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_force_time_entries_update_spent_on_since_specified_date()
    {
        Bus::fake();

        $this->signIn();

        $response = $this->get(route('api.time-entries.sync', ['spent_since' => '2018-03-01']));
        $response->assertStatus(200);

        Bus::assertDispatched(SyncTimeEntries::class, function ($job) {
            return $job->date->toDateString() === Carbon::parse('2018-03-01')->toDateString();
        });
    }
}
