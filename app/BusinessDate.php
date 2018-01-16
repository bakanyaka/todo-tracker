<?php


namespace App;


use Carbon\Carbon;
use Carbon\CarbonInterval;

class BusinessDate extends Carbon
{
    const BUSINESS_DAY_START_HOUR = 8;
    const BUSINESS_DAY_END_HOUR = 16;

    /**
     * @param float $hoursWithDecimalMinutes
     * @return static
     */
    public function addBusinessHours(float $hoursWithDecimalMinutes)
    {
        $hours = floor($hoursWithDecimalMinutes);
        $minutes = ($hoursWithDecimalMinutes - $hours)*60;

        $minutesLeftThisDay = 0;
        if ($this->isBusinessDay()) {
            //If current hour is before business hours, start counting from the start of the business day
            if ($this->hour < static::BUSINESS_DAY_START_HOUR) {
                $this->hour = static::BUSINESS_DAY_START_HOUR;
                $this->minute = 0;
            }

            $dayEnd = $this->copy()->hour(static::BUSINESS_DAY_END_HOUR)->minute(0);
            $result = $this->copy()->addHours($hours)->addMinutes($minutes);
            //Resulting date is within same forking day
            if ($result->lte($dayEnd)) {
                $this->timestamp = $result->timestamp;
                return $this;
            }
            if($this->lt($dayEnd)) {
                $minutesLeftThisDay = $this->diffInMinutes($dayEnd,false);
            }
        }
        $hoursInADay = static::BUSINESS_DAY_END_HOUR - static::BUSINESS_DAY_START_HOUR;
        $minutesToAdd = ($hours * 60 - $minutesLeftThisDay + $minutes) % ($hoursInADay * 60);
        $daysToAdd = (int)(($hours * 60 - $minutesLeftThisDay + $minutes) / 60 / $hoursInADay + 1);

        // Reset hours and minutes to beginning of the business day
        $this->hour(static::BUSINESS_DAY_START_HOUR)->minute(0);

        return $this->addWeekdays($daysToAdd)->addMinutes($minutesToAdd);
    }

    /**
     * @param Carbon $dt
     * @return float
     */
    public function diffInBusinessHours(Carbon $dt)
    {
        if ($this > $dt) {
            $start = $dt->copy();
            $end = $this->copy();
        } else {
            $start = $this->copy();
            $end = $dt->copy();
        }

        $startMinute = $start->minute;
        $endMinute = $end->minute;

        $start->minute(0)->second(0);
        $end->minute(0)->second(0);

        $hours = $start->diffFiltered(CarbonInterval::hours(), function (BusinessDate $date) {
            return $date->isBusinessHour();
        }, $end);
        $minutes = $hours * 60 - $startMinute + $endMinute;
        return round($minutes/60,2);
    }

    public function isBusinessHour()
    {
        return $this->isWeekday() && $this->hour >= static::BUSINESS_DAY_START_HOUR && $this->hour < static::BUSINESS_DAY_END_HOUR;
    }

    public function isBusinessDay()
    {
        return $this->isWeekday();
    }
}