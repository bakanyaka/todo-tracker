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
    public function addBusinessHours(int $hours) {

        $hoursInADay = static::BUSINESS_DAY_END_HOUR - static::BUSINESS_DAY_START_HOUR;
        $fullDaysToAdd = (int)($hours / $hoursInADay);
        $remainderHours = $hours % $hoursInADay;
        if ($this->hour + $remainderHours > static::BUSINESS_DAY_END_HOUR) {
            $currentDayHoursLeft = static::BUSINESS_DAY_END_HOUR - $this->hour;
            $fullDaysToAdd++;
            $hoursToAdd = $remainderHours - $currentDayHoursLeft;
        } else {
            $hoursToAdd = $remainderHours;
        }

        if ($fullDaysToAdd > 0) {
            $this->hour(static::BUSINESS_DAY_START_HOUR);
        }
        return $this->addWeekdays($fullDaysToAdd)->addHours($hoursToAdd);
    }
}