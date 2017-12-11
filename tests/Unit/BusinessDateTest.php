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
        $this->assertEquals($expectedDate,$date);

        // Date should be carried over to several days later
        $date = BusinessDate::create(2017,12,5,11);
        $expectedDate = BusinessDate::create(2017,12,8,11);
        $date->addBusinessHours(24);
        $this->assertEquals($expectedDate,$date);

        // Add one day as full working day
        $date = BusinessDate::create(2017,12,4,15);
        $expectedDate = BusinessDate::create(2017,12,6,10);
        $date->addBusinessHours(11);
        $this->assertEquals($expectedDate,$date);

        // It adds weekends as extra days
        $date = BusinessDate::create(2017,12,1,15);
        $expectedDate = BusinessDate::create(2017,12,4,10);
        $date->addBusinessHours(3);
        $this->assertEquals($expectedDate,$date);

        //Initial date is outside of business hours
        $date = BusinessDate::create(2017,12,4,18);
        $expectedDate = BusinessDate::create(2017,12,5,9);
        $date->addBusinessHours(1);
        $this->assertEquals($expectedDate,$date);

        //Initial date is before business hours
        $date = BusinessDate::create(2017,12,4,7);
        $expectedDate = BusinessDate::create(2017,12,4,9);
        $date->addBusinessHours(1);
        $this->assertEquals($expectedDate,$date);

        //Initial date is weekEnd
        $date = BusinessDate::create(2017,12,2,14);
        $expectedDate = BusinessDate::create(2017,12,4,12);
        $date->addBusinessHours(4);
        $this->assertEquals($expectedDate,$date);
    }

    /** @test */
    public function it_subtracts_business_hours_from_date()
    {
        $date = BusinessDate::create(2017,12,6,18);
        $expectedDate = BusinessDate::create(2017,12,6,15);
        $date->addBusinessHours(-1);
        $this->assertEquals($expectedDate,$date);

        $date = BusinessDate::create(2017,12,6,6);
        $expectedDate = BusinessDate::create(2017,12,5,15);
        $date->addBusinessHours(-1);
        $this->assertEquals($expectedDate,$date);

        $date = BusinessDate::create(2017,12,6,16);
        $expectedDate = BusinessDate::create(2017,12,5,15);
        $date->addBusinessHours(-9);
        $this->assertEquals($expectedDate,$date);

        $date = BusinessDate::create(2017,12,9,12);
        $expectedDate = BusinessDate::create(2017,12,8,14);
        $date->addBusinessHours(-2);
        $this->assertEquals($expectedDate,$date);

        $date = BusinessDate::create(2017,12,4,07);
        $expectedDate = BusinessDate::create(2017,12,1,14);
        $date->addBusinessHours(-2);
        $this->assertEquals($expectedDate,$date);

        $date = BusinessDate::create(2017,12,6,16);
        $expectedDate = BusinessDate::create(2017,12,6,8);
        $date->addBusinessHours(-8);
        $this->assertEquals($expectedDate,$date);

        $date = BusinessDate::create(2017,12,7,10);
        $expectedDate = BusinessDate::create(2017,12,6,14);
        $date->addBusinessHours(-4);
        $this->assertEquals($expectedDate,$date);
    }

    /** @test */
    public function it_calculates_difference_in_business_hours()
    {
        //Same day
        $date = BusinessDate::create(2017,12,4,8);
        $secondDate = BusinessDate::create(2017,12,4,15,30);
        $this->assertEquals(7.5, $date->diffInBusinessHours($secondDate));

        //Second date is next date. Should not count non working hours
        $date = BusinessDate::create(2017,12,4,15,45);
        $secondDate = BusinessDate::create(2017,12,5,9);
        $this->assertEquals(1.25, $date->diffInBusinessHours($secondDate));

        //Second date is after a weekend. Should not count weekends
        $date = BusinessDate::create(2017,12,1,15);
        $secondDate = BusinessDate::create(2017,12,4,15,15);
        $this->assertEquals(8.25, $date->diffInBusinessHours($secondDate));
    }

}
