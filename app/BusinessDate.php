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

        //If current hour is before business hours, start counting from start of the business day
        if ($this->hour < static::BUSINESS_DAY_START_HOUR) {
           $this->hour = static::BUSINESS_DAY_START_HOUR;
        }

        $hoursToAdd = $remainderHours;

        //If resulting hour is after business hours,
        if ($this->hour + $remainderHours > static::BUSINESS_DAY_END_HOUR) {
            //If we are adding hours then start from next day
            if($hours >= 0) {
                $fullDaysToAdd++;
                //Calculate how many hours get transferred to next day
                $currentDayHoursLeft = static::BUSINESS_DAY_END_HOUR - $this->hour;
                $hoursToAdd = $currentDayHoursLeft > 0 ? $remainderHours - $currentDayHoursLeft : $remainderHours;
            } else {
                //If we are subtracting hours then start from the end of current business day
                $this->hour = static::BUSINESS_DAY_END_HOUR;
            }
        } elseif ($this->hour + $remainderHours < static::BUSINESS_DAY_START_HOUR) {
            // If resulting hour is before business hours (only reachable when we are subtracting hours)
            // then start from previous day
            $fullDaysToAdd--;
        }

        if ($fullDaysToAdd > 0 || ($this->isWeekend() && $hours > 0 )) {
            $this->hour(static::BUSINESS_DAY_START_HOUR);
        }
        if ($fullDaysToAdd < 0 || ($this->isWeekend() && $hours < 0 )) {
            $this->hour(static::BUSINESS_DAY_END_HOUR);
        }
        if ($fullDaysToAdd === 0 && $hours < 0 && $this->isWeekend()) {
            $fullDaysToAdd--;
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