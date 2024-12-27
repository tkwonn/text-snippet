<?php

namespace Utils;

use Carbon\Carbon;
use Exception;

class DateCalculator
{
    private const DATETIME_FORMAT = 'Y-m-d H:i:s';
    private const NEVER_EXPIRES = '9999-12-31 23:59:59';

    /**
     * @throws Exception When invalid expiration option
     */
    public static function getExpirationDate(string $expiresAt): string
    {
        if ($expiresAt === 'Never') {
            return self::NEVER_EXPIRES;
        }

        return match ($expiresAt) {
            '1min' => Carbon::now()->addMinute()->format(self::DATETIME_FORMAT),
            '10min' => Carbon::now()->addMinutes(10)->format(self::DATETIME_FORMAT),
            '1hour' => Carbon::now()->addHour()->format(self::DATETIME_FORMAT),
            '1day' => Carbon::now()->addDay()->format(self::DATETIME_FORMAT),
            '1week' => Carbon::now()->addWeek()->format(self::DATETIME_FORMAT),
            '1month' => Carbon::now()->addMonth()->format(self::DATETIME_FORMAT),
            default => throw new Exception('Invalid expiration option'),
        };
    }
}
