<?php

namespace Tests\Feature;

use App\Models\Service;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ServicesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_all_services()
    {
        $service = create('App\Models\Service');

        $this->signIn();
        $response = $this->get(route('services'));

        $response->assertSee($service->name);
        $response->assertSee((string)$service->hours);
    }

    /** @test */
    public function user_can_view_create_service_form()
    {
        $this->signIn();

        $response = $this->get(route('services.new'));
        $response->assertStatus(200);
        $response->assertSee('Название');
        $response->assertSee('Количество часов');

    }

    /** @test */
    public function user_can_add_new_service()
    {
        $this->signIn();

        $response = $this->post(route('services'),[
            'name' => 'Тестирование',
            'hours' => 4
        ]);
        $service = Service::first();

        $response->assertRedirect(route('services'));
        $this->assertNotNull($service);
        $this->assertEquals('Тестирование',$service->name);
        $this->assertEquals(4,$service->hours);
    }

    /** @test */
    public function user_can_view_edit_service_form()
    {
        $this->signIn();
        $service = create('App\Models\Service', [
            'name' => 'Тестирование',
            'hours' => 555
        ]);

        $response = $this->get(route('services.edit', ['id' => $service->id]));
        $response->assertStatus(200);
        $response->assertSee('Тестирование');
        $response->assertSee((string)555);
    }

    /** @test */
    public function user_can_update_service_data()
    {
        $this->signIn();
        $service = create('App\Models\Service', [
            'name' => 'Тестирование',
            'hours' => 555
        ]);

        $response = $this->patch(route('services.update', ['id' => $service->id]), [
            'name' => 'Testing',
            'hours' => 333
        ]);
        $response->assertRedirect(route('services'));

        $service = $service->fresh();

        $this->assertEquals('Testing', $service->name);
        $this->assertEquals(333, $service->hours);

    }

    /** @test */
    public function user_can_delete_chosen_service()
    {
        $this->signIn();
        $service = create('App\Models\Service');
        $this->assertDatabaseHas('services',['id' => $service->id]);

        $response = $this->delete(route('services.delete',['id' => $service->id]));
        $response->assertRedirect(route('services'));
        $this->assertNull(Service::find($service->id));
    }
}
