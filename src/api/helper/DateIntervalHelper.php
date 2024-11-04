<?php
namespace com\icemalta\kahuna\api\helper;

use \DateInterval;

class DateIntervalHelper
{
    public static function formatString(DateInterval $interval): string
    {
        if ($interval->invert === 0) {
            $format = '';
            if ($interval->y > 0) {
                $format .= '%y year(s)';
            }
            if ($interval->m > 0) {
                $format .= ', %m month(s)';
            }
            if ($interval->d > 0) {
                $format .= ', %d day(s)';
            }
            return $interval->format(trim($format, ' ,+\n\r\t\v\x00'));
        }
        return 'expired';
    }

    public static function formatISO(DateInterval $interval): string
    {
        if ($interval->invert === 0) {
            if ($interval->y > 0 || $interval->m > 0 || $interval->d > 0) {
                $format = 'P';
            }
            if ($interval->y > 0) {
                $format .= '%yY';
            }
            if ($interval->m > 0) {
                $format .= '%mM';
            }
            if ($interval->d > 0) {
                $format .= '%dD';
            }
            return $interval->format($format);
        }
        return '';
    }
}
