<?php


namespace App;


use Carbon\Carbon;
use Carbon\CarbonInterval;

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
        $fullDaysToAdd = 0;

        $hoursInADay = static::BUSINESS_DAY_END_HOUR - static::BUSINESS_DAY_START_HOUR;

        //If current hour is before business hours, start counting from the start of the business day
        //Need this only for adding hours
        if ($this->hour < static::BUSINESS_DAY_START_HOUR) {
            $this->hour = static::BUSINESS_DAY_START_HOUR;
        }
        //If current hour is after business hours, start counting from the end of the business day
        //Need this only for subtracting hours
        if ($this->hour > static::BUSINESS_DAY_END_HOUR) {
            $this->hour = static::BUSINESS_DAY_END_HOUR;
        }

        // TODO: Needs refactoring
        if (!$this->isWeekend()) {
            //If resulting hour is after business hours,
            if ($this->hour + $hours > static::BUSINESS_DAY_END_HOUR && $hours >= 0) {
                // Start from next day
                $fullDaysToAdd++;
                //Calculate how many hours get transferred to next day
                $hours = $hours - (static::BUSINESS_DAY_END_HOUR - $this->hour);
            } elseif ($this->hour + $hours < static::BUSINESS_DAY_START_HOUR) {
                // If resulting hour is before business hours (only reachable when we are subtracting hours)
                // then start from previous day
                $fullDaysToAdd--;
                //Calculate how many hours get transferred to next day
                $hours = $hours + ($this->hour - static::BUSINESS_DAY_START_HOUR);
            }
        }

        $fullDaysToAdd += (int)($hours / $hoursInADay);
        $hoursToAdd = $hours % $hoursInADay;


        if ($fullDaysToAdd > 0 || ($this->isWeekend() && $hours > 0)) {
            $this->hour(static::BUSINESS_DAY_START_HOUR);
        }
        if ($fullDaysToAdd < 0 || ($this->isWeekend() && $hours < 0)) {
            $this->hour(static::BUSINESS_DAY_END_HOUR);
        }
        if ($fullDaysToAdd === 0 && $hours < 0 && $this->isWeekend()) {
            $fullDaysToAdd--;
        }
        //TODO: This is temporary patch. Need another solution. Should not work like that
        if ($fullDaysToAdd === -1 && $hoursToAdd === 0) {
            $this->hour(static::BUSINESS_DAY_START_HOUR);
            $fullDaysToAdd = 0;
        }
        return $this->addWeekdays($fullDaysToAdd)->addHours($hoursToAdd);
    }

    /**
     * @param Carbon $dt
     * @return float
     */
    public function diffInBusinessHours(Carbon $dt)
    {
        $start = static::instance($this)->minute(0);
        $end = static::instance($dt)->minute(0);

        $hours = $start->diffFiltered(CarbonInterval::hours(), function (BusinessDate $date) {
            return $date->isBusinessHour();
        }, $end);
        $minutes = $hours * 60 - $this->minute + $dt->minute;
        return round($minutes/60,2);
    }

    public function isBusinessHour()
    {
        return $this->isWeekday() && $this->hour >= static::BUSINESS_DAY_START_HOUR && $this->hour < static::BUSINESS_DAY_END_HOUR;
    }
}