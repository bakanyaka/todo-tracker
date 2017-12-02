<?php

namespace Tests\Unit;

use App\BusinessHoursCalculator;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BusinessHoursCalculatorTest extends TestCase
{
    /** @test */
    public function it_adds_business_hours_to_date()
    {
        // Date should be carried over to next day
        $date = Carbon::create(2017,12,4,15);
        $expectedDate = Carbon::create(2017,12,5,10);
        $actualDate = BusinessHoursCalculator::add_hours($date,3);
        $this->assertEquals($expectedDate->timestamp,$actualDate->timestamp);

        // Add one day as full working day
        $date = Carbon::create(2017,12,4,15);
        $expectedDate = Carbon::create(2017,12,6,10);
        $actualDate = BusinessHoursCalculator::add_hours($date,11);
        $this->assertEquals($expectedDate->timestamp,$actualDate->timestamp);

        // It adds weekends as extra days
        $date = Carbon::create(2017,12,1,15);
        $expectedDate = Carbon::create(2017,12,4,10);
        $actualDate = BusinessHoursCalculator::add_hours($date,3);
        $this->assertEquals($expectedDate->timestamp,$actualDate->timestamp);
    }

}
