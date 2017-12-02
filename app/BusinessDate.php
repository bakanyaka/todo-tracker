<?php


namespace App;


use Carbon\Carbon;

class BusinessDate extends Carbon
{
    const BUSINESS_DAY_START_HOUR = 8;
    const BUSINESS_DAY_END_HOUR = 16;

    /**
     * @param int $hours
     * @return static
     */
    public function addBusinessHours(int $hours)
    {

        $hoursInADay = static::BUSINESS_DAY_END_HOUR - static::BUSINESS_DAY_START_HOUR;
        $fullDaysToAdd = (int)($hours / $hoursInADay);
        $remainderHours = $hours % $hoursInADay;

        //If current hour is before business hours, start counting from start of the day
        if ($this->hour < static::BUSINESS_DAY_START_HOUR) {
           $this->hour = static::BUSINESS_DAY_START_HOUR;
        }

        //If resulting hour is after business hours, add additional day and remaining hours
        if ($this->hour + $remainderHours > static::BUSINESS_DAY_END_HOUR) {
            $currentDayHoursLeft = static::BUSINESS_DAY_END_HOUR - $this->hour;
            $fullDaysToAdd++;
            $hoursToAdd = $currentDayHoursLeft > 0 ? $remainderHours - $currentDayHoursLeft : $remainderHours;
        } else {
            $hoursToAdd = $remainderHours;
        }

        if ($fullDaysToAdd > 0 || $this->isWeekend()) {
            $this->hour(static::BUSINESS_DAY_START_HOUR);
        }
        return $this->addWeekdays($fullDaysToAdd)->addHours($hoursToAdd);
    }

    /**
     * @param Carbon $dt
     * @return int
     */
    public function diffInBusinessHours(Carbon $dt)
    {
        return $this->diffInHoursFiltered(function (BusinessDate $date) {
            return $date->isBusinessHour();
        }, $dt);
    }

    public function isBusinessHour()
    {
        return $this->isWeekday() && $this->hour >= static::BUSINESS_DAY_START_HOUR && $this->hour < static::BUSINESS_DAY_END_HOUR;
    }
}