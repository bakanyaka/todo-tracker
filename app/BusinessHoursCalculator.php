<?php


namespace App;


use Carbon\Carbon;

class BusinessHoursCalculator
{
    const DAY_START_HOUR = 8;
    const DAY_END_HOUR = 16;

    /**
     * @param Carbon $date
     * @param int $hours
     * @return Carbon
     */
    public static function add_hours(Carbon $date, int $hours)
    {
        $finalDate = clone $date;
        $hoursInADay = static::DAY_END_HOUR - static::DAY_START_HOUR;
        $fullDaysToAdd = (int)($hours / $hoursInADay);
        $remainderHours = $hours % $hoursInADay;
        if ($date->hour + $remainderHours > static::DAY_END_HOUR) {
            $currentDayHoursLeft = static::DAY_END_HOUR - $date->hour;
            $fullDaysToAdd++;
            $hoursToAdd = $remainderHours - $currentDayHoursLeft;
        } else {
            $hoursToAdd = $remainderHours;
        }

        if ($fullDaysToAdd > 0) {
            $finalDate->hour(static::DAY_START_HOUR);
        }


        return $finalDate->addWeekdays($fullDaysToAdd)->addHours($hoursToAdd);

    }
}