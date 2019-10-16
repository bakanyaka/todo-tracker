<?php

namespace Tests\Feature\Api;

use App\Models\Service;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ServicesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_get_all_services()
    {
        $services = factory(Service::class, 2)->create();

        $this->signInAsAdmin();
        $response = $this->get(route('api.services'));

        $this->assertCount(2, $response->json('data'));
        $response->assertJsonFragment([
            'id' => $services[0]->id,
            'name' => $services[0]->name,
            'hours' => $services[0]->hours
        ]);
    }

}
