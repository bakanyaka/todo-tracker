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

        //Initial date is outside of business hours
        $date = BusinessDate::create(2017,12,4,18);
        $expectedDate = BusinessDate::create(2017,12,5,9);
        $date->addBusinessHours(1);
        $this->assertEquals($expectedDate->timestamp,$date->timestamp);

        //Initial date is before business hours
        $date = BusinessDate::create(2017,12,4,7);
        $expectedDate = BusinessDate::create(2017,12,4,9);
        $date->addBusinessHours(1);
        $this->assertEquals($expectedDate->timestamp,$date->timestamp);

        //Initial date is weekEnd
        $date = BusinessDate::create(2017,12,2,13);
        $expectedDate = BusinessDate::create(2017,12,4,9);
        $date->addBusinessHours(1);
        $this->assertEquals($expectedDate->timestamp,$date->timestamp);
    }

    /** @test */
    public function it_calculates_difference_in_business_hours()
    {
        //Same day
        $date = BusinessDate::create(2017,12,4,8);
        $secondDate = BusinessDate::create(2017,12,4,16);
        $this->assertEquals(8, $date->diffInBusinessHours($secondDate));

        //Second date is next date. Should not count non working hours
        $date = BusinessDate::create(2017,12,4,15);
        $secondDate = BusinessDate::create(2017,12,5,9);
        $this->assertEquals(2, $date->diffInBusinessHours($secondDate));

        //Second date is after a weekend. Should not count weekends
        $date = BusinessDate::create(2017,12,1,15);
        $secondDate = BusinessDate::create(2017,12,4,15);
        $this->assertEquals(8, $date->diffInBusinessHours($secondDate));
    }

}
