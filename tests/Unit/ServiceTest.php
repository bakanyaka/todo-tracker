<?php

namespace Tests\Unit;

use App\Models\Issue;
use App\Models\Service;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;


    /** @test */
    public function when_service_is_deleted_service_id_is_set_to_null_in_all_associated_issues()
    {
        $service = create(Service::class);
        $issue = create(Issue::class, ['service_id' => $service->id]);
        $this->assertDatabaseHas('issues',['id' => $issue->id, 'service_id' => $service->id]);

        $service->delete();

        $this->assertDatabaseHas('issues',['id' => $issue->id, 'service_id' => null]);
    }

}
