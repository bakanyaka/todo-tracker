<?php

namespace Tests\Feature\Api;

use App\Models\Tracker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetTrackersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_get_trackers_list()
    {
        $trackers = create(Tracker::class, [], 2);
        $this->signIn();
        $response = $this->get(route('api.trackers'));
        $response->assertJsonCount(2, 'data');
        $trackers->each(function ($tracker) use ($response) {
            $response->assertJsonFragment([
                [
                    'id' => $tracker->id,
                    'name' => $tracker->name,
                ]
            ]);
        });
    }
}
