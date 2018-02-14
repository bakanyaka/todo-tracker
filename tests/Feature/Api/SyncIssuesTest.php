<?php

namespace Tests\Feature\Api;

use App\Jobs\SyncIssues;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SyncIssuesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_force_issue_update_modified_since_specified_date()
    {
        Bus::fake();

        $this->signIn();

        $response = $this->get(route('api.issues.sync', ['updated_since' => '2018-01-01']));
        $response->assertStatus(200);

        Bus::assertDispatched(SyncIssues::class, function ($job) {
            return $job->date->toDateString() === Carbon::parse('2018-01-01')->toDateString();
        });
    }
}
