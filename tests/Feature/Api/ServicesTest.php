<?php

namespace Tests\Feature\Api;

use App\Models\Service;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ServicesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_get_all_services()
    {
        $services = factory(Service::class, 2)->create();

        $this->signIn();
        $response = $this->get(route('api.services'));

        $this->assertCount(2, $response->json('data'));
        $response->assertJsonFragment([
            'id' => $services[0]->id,
            'name' => $services[0]->name,
            'hours' => (string)$services[0]->hours
        ]);
    }

    /** @test */
    public function admin_can_create_a_service()
    {
        $service = make(Service::class);
        $this->signIn();
        $response = $this->postJson(route('api.services.store'), [
            'name' => $service->name,
            'hours' => $service->hours
        ]);

        $this->assertDatabaseHas('services', [
            'name' => $service->name,
            'hours' => $service->hours
        ]);

        $response->assertStatus(200);
    }
}
