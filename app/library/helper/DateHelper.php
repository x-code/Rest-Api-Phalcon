<?php

namespace Helper;

use Phalcon\DI;
use Models\Airport;
use Adkarta\DateHelper\Timezone as TimezoneHelper;

class DateHelper
{

    public static function getDurationInMinutes($start_time = 0, $end_time = 0)
    {
        $duration =  TimezoneHelper::getDuration("", $start_time, "", $end_time);

        return $duration;
    }

    public static function getDurationWithTimezone($from_timezone = "", $start_time = "", $to_timezone = "", $end_time = "")
    {

        $departure_timezone = Airport::findByIata(trim($from_timezone));
        if ($departure_timezone != false) {
            $from_timezone = trim($departure_timezone->timezone);
        }

        $arrival_timezone = Airport::findByIata(trim($to_timezone));
        if ($arrival_timezone != false) {
            $to_timezone = trim($arrival_timezone->timezone);
        }

        $duration =  TimezoneHelper::getDuration($from_timezone, $start_time, $to_timezone, $end_time);

        return $duration;
    }

}