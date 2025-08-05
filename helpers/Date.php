<?php

namespace ShortenIt\helpers;

use DateInterval;
use DatePeriod;
use DateTime;
use Exception;

class Date
{

    public static function daysBetweenDates(string|DateTime $date1, string|DateTime $date2, $absolute = true): bool|int
    {
        try {
            if (!$date1 instanceof DateTime) {
                $date1 = new DateTime($date1);
            }

            if (!$date2 instanceof DateTime) {
                $date2 = new DateTime($date2);
            }

            $interval = $date2->diff($date1);
            // if we have to take in account the relative position (!$absolute) and the relative position is negative,
            // we return negative value otherwise, we return the absolute value
            return (!$absolute and $interval->invert) ? -$interval->days : $interval->days;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function format(?string $date, string $format_from = 'm-d-Y', string $format_to = 'Y-m-d'): string
    {
        if (empty($date)) {
            return '';
        }

        $dateFormatted = DateTime::createFromFormat($format_from, $date);
        return $dateFormatted->format($format_to);
    }

    public static function businessDays(string $startDate, string $endDate = null): int
    {
        if (empty($endDate)) {
            $endDate = date('Y-m-d');
        }

        try {
            $period = new DatePeriod(new DateTime($startDate), new DateInterval('P1D'), new DateTime($endDate));
        } catch (Exception $e) {
            return 0;
        }

        $businessDays = 0;

        foreach ($period as $dt) {
            if (!in_array($dt->format('N'), [6, 7]) && !self::isHoliday($dt->format('Y-m-d'))) {
                $businessDays++;
            }
        }

        return $businessDays;
    }

    public static function isHoliday($date): bool
    {
        $year = date('Y', strtotime($date));

        $holidays = [
            self::observedDate(date('Y-m-d', strtotime("$year-01-01"))),
            date('Y-m-d', strtotime("january $year third monday")),
            date('Y-m-d', strtotime("february $year third monday")),
            date('Y-m-d', strtotime("last monday of May $year")),
            self::observedDate(date('Y-m-d', strtotime("$year-07-04"))),
            date('Y-m-d', strtotime("september $year first monday")),
            date('Y-m-d', strtotime("october $year second monday")),
            self::observedDate(date('Y-m-d', strtotime("$year-11-11"))),
            date('Y-m-d', strtotime("november $year fourth thursday")),
            self::observedDate(date('Y-m-d', strtotime("$year-12-25"))),
        ];

        return in_array($date, $holidays);
    }

    public static function observedDate(string $holiday): int|string
    {
        $day = date("N", strtotime($holiday));

        return match ($day) {
            '6' => date('Y-m-d', strtotime('-1 day', strtotime($holiday))),
            '7' => date('Y-m-d', strtotime('+1 day', strtotime($holiday))),
            default => $holiday,
        };
    }

    public static function dateClass($date): string {
        if (!$date) {
            return 'text-danger';
        }

        $today = new DateTime();
        try {
            $target = new DateTime($date);
        } catch (Exception $e) {
            return 'text-danger';
        }

        $diff = $target->diff($today);
        $daysRemaining = $diff->days * ($diff->invert ? 1 : -1);

        if ($daysRemaining < 0) {
            return 'text-danger';
        } elseif ($daysRemaining < 30) {
            return 'text-warning';
        }

        return '';
    }

    public static function expirationText($date): ?string {
        if (!$date) {
            return 'Date is empty.';
        }

        $daysRemaining = self::daysRemaining($date);

        if ($daysRemaining < 0) {
            return sprintf('Expired %d day(s) ago', abs($daysRemaining));
        } elseif ($daysRemaining < 30) {
            return sprintf('Expires in %d day(s)', $daysRemaining);
        }

        return 'All is ok.';
    }

    public static function daysRemaining($date): int {
        $today = new DateTime();
        try {
            $target = new DateTime($date);
        } catch (Exception $e) {
            return 0; // or handle error as appropriate
        }

        $diff = $target->diff($today);
        return $diff->days * ($diff->invert ? 1 : -1);
    }

}