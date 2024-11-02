<?php

namespace Ekkazan\Alligator;

class Periods {
    public const PER_MINUTE = 60;
    public const PER_5_MINUTE = 300;
    public const PER_15_MINUTE = 900;
    public const PER_30_MINUTE = 1800;
    public const PER_HOUR = 3600;
    public const PER_12_HOURS = 43200;
    public const PER_DAY = 86400;
    public const PER_WEEK = 604800;

    /**
     * Get the number of seconds in a given number of minutes.
     *
     * @param int $minutes
     * @return int
     */
    public static function perMinutes(int $minutes): int {
        return $minutes * 60;
    }

    /**
     * Get the number of seconds in a given number of hours.
     *
     * @param int $hours
     * @return int
     */
    public static function perHours(int $hours): int {
        return $hours * 3600;
    }
}