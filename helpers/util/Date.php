<?php

namespace Ssentezo\Util;

use DateTime;

class Date
{

    public static function getMinutesFromSeconds($seconds)
    {
        return $seconds / 60;
    }
    public static function getHoursFromSeconds($seconds)
    {
        return $seconds / (60 * 60);
    }
    public static function getDaysFromseconds()
    {
    }
    public static function getDateDiffInDays($fromDate, $toDate)
    {
        $fromDate = date("Y-m-d H:i:s", strtotime(str_replace("/", "-", $fromDate)));
        $toDate = date("Y-m-d H:i:s", strtotime(str_replace("/", "-", $toDate)));



        try {
            $earlier = new DateTime($fromDate);
            $later = new DateTime($toDate);

            $diff = $earlier->diff($later)->format("%r%a");
            // echo json_encode(array("earlier" => $earlier, "Later" => $later, "Diff" => $diff));
            return $diff;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public static function getDatediffInHours($fromDate, $toDate)
    {
        $earlier = new DateTime($fromDate);
        $later = new DateTime($toDate);

        $diff = $earlier->diff($later);

        $hours = $diff->h;
        $hours = $hours + ($diff->days * 24);

        return $hours;
    }
    public static function predictNextDate($one, $two)
    {
        $base = $two;
        $one = new DateTime(date("Y-m-d", strtotime($one)));
        $two = new DateTime(date("Y-m-d", strtotime($two)));
        $diff = $one->diff($two);
        $days = $diff->days;
        $predicted = date("Y-m-d", strtotime("$base +$days days"));
        return $predicted;
    }
}
