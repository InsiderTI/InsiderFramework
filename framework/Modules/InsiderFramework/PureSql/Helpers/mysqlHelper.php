<?php

namespace Modules\InsiderFramework\PureSql\Helpers;

/**
 * Helper class containing helper functions when using mysql
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\PureSql\Helpers\Mysql
 */
class Mysql
{
    /**
     * Convert a datetime string (mysql) to an array containing data and time
     * separately (and in pt-br format by default)
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\PureSql\Helpers\Mysql
     *
     * @param string $datetime DateTime string
     *
     * @return array Date and time strings
     */
    public function parseTimeStampMysqlToArray(string $datetime, $format = 'pt-br'): array
    {
        $timestamp_tmp =  explode("-", $datetime);

        $year = $timestamp_tmp[0];
        $month = $timestamp_tmp[1];
        $dayhour = $timestamp_tmp[2];

        $day_tmp = explode(" ", $dayhour);
        $day = $day_tmp[0];
        $hour = $day_tmp[1];

        if (intval($day) < 10) {
            $day = "0" . intval($day);
        }
        if (intval($month) < 10) {
            $month = "0" . intval($month);
        }

        switch (strtolower($format)) {
            case 'pt-br':
                return array(
                    'date' => $day . "/" . $month . "/" . $year,
                    'time' => $hour
                );
                break;
            default:
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                    "Format to convert timestampmysql to array not implemented yet: " . $format
                );
                break;
        }
    }
}
