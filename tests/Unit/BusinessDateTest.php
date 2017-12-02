<?php

namespace Tests\Unit;

use App\BusinessDate;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BusinessDateTest extends TestCase
{
    /** @test */
    public function it_adds_business_hours_to_date()
    {
        // Date should be carried over to next day
        $date = BusinessDate::create(2017,12,4,15);
        $expectedDate = BusinessDate::create(2017,12,5,10);
        $date->addBusinessHours(3);
        $this->assertEquals($expectedDate->timestamp,$date->timestamp);

        // Add one day as full working day
        $date = BusinessDate::create(2017,12,4,15);
        $expectedDate = BusinessDate::create(2017,12,6,10);
        $date->addBusinessHours(11);
        $this->assertEquals($expectedDate->timestamp,$date->timestamp);

        // It adds weekends as extra days
        $date = BusinessDate::create(2017,12,1,15);
        $expectedDate = BusinessDate::create(2017,12,4,10);
        $date->addBusinessHours(3);
        $this->assertEquals($expectedDate->timestamp,$date->timestamp);
    }

}
