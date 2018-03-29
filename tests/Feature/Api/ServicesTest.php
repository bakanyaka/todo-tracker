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
            'hours' => (string)$services[0]->hours
        ]);
    }

    /** @test */
    public function admin_can_create_a_service()
    {
        $this->signInAsAdmin();
        $response = $this->postJson(route('api.services.store'), [
            'name' => 'Тестирование',
            'hours' => '2'
        ]);

        $this->assertDatabaseHas('services', [
            'name' => 'Тестирование',
            'hours' => '2'
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function non_admins_cant_create_a_service()
    {
        $this->withExceptionHandling();
        $this->signIn();
        $response = $this->postJson(route('api.services.store'), [
            'name' => 'Тестирование',
            'hours' => '2'
        ]);
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_a_service()
    {
        $this->signInAsAdmin();
        $service = create(Service::class);

        $response = $this->patchJson(route('api.services.update', ['service' => $service]), [
            'name' => 'Тестирование',
            'hours' => 555
        ]);

        $response->assertStatus(200);
        $service = $service->fresh();
        $this->assertEquals('Тестирование', $service->name);
        $this->assertEquals(555, $service->hours);
    }

    /** @test */
    public function non_admins_cant_update_a_service()
    {
        $this->withExceptionHandling();
        $this->signIn();
        $service = create(Service::class);
        $response = $this->patchJson(route('api.services.update', ['service' => $service]), [
            'name' => 'Тестирование',
            'hours' => 555
        ]);
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_delete_a_service()
    {
        $this->signInAsAdmin();
        $service = create(Service::class);
        $this->assertDatabaseHas('services',['id' => $service->id]);

        $response = $this->deleteJson(route('api.services.destroy',['service' => $service]));
        $response->assertStatus(204);
        $this->assertDatabaseMissing('services',['id' => $service->id]);
    }

    /** @test */
    public function non_admins_cant_delete_a_service()
    {
        $this->withExceptionHandling();
        $this->signIn();
        $service = create(Service::class);
        $response = $this->deleteJson(route('api.services.destroy',['service' => $service]));
        $response->assertStatus(403);
    }


}
