<?php
namespace com\icemalta\kahuna\api\helper;

use \DateInterval;

/**
 * Helper methods to format a DateInterval.
 */
class DateIntervalHelper
{
    /**
     * Formats a DateInterval as a human readable string.
     * @param \DateInterval $interval The DateInterval to format.
     * @return string Returns a DateInterval in a human readable string such as: `2 year(s), 6 month(s), 4 day(s)`
     */
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

    /**
     * Formats a DateInterval as an ISO 8601 string.
     * @param \DateInterval $interval The DateInterval to format.
     * @return string Returns a DateInterval in an ISO 8601 formatted string such as: `P2Y6M4D'
     */
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
