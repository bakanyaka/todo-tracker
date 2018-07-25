<?php

namespace Tests\Api\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetEmployeesTest extends TestCase
{
    use RefreshDatabase;


    public function user_can_get_a_list_of_all_employees()
    {
        $this->signIn();

        $response = $this->get(route('api.employees.index'));


    }
    
}
