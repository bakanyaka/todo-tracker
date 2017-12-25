<?php

namespace Tests\Feature\Api;

use App\Models\Synchronization;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RedmineSyncTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_get_last_sync_timestamp()
    {
        $this->signIn();
        $sync = Synchronization::create(['completed_at' => Carbon::now()]);

        $response = $this->get(route('api.synchronizations.last'));

        $response->assertJsonFragment([
            'completed_at_human' => $sync->completed_at->diffForHumans(),
            'completed_at' => $sync->completed_at->toDateTimeString(),
        ]);


    }

}
