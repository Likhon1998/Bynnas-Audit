<?php

namespace App\Support;

use App\Support\AppTime;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;

/**
 * Canonical Bangladesh time for the whole app (Asia/Dhaka, UTC+6).
 */
final class AppTime
{
    public const ZONE = 'Asia/Dhaka';

    public const DATETIME = 'd M Y, h:i A';

    public const DATETIME_SHORT = 'd M, h:i A';

    public const DATE = 'd M Y';

    public const TIME = 'h:i A';

    public static function zone(): string
    {
        return self::ZONE;
    }

    public static function now(): CarbonInterface
    {
        return now(self::ZONE);
    }

    public static function parse(mixed $value): ?CarbonInterface
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->copy()->timezone(self::ZONE);
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->timezone(self::ZONE);
        }

        try {
            return Carbon::parse((string) $value, self::ZONE)->timezone(self::ZONE);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function dateTime(mixed $value, string $format = self::DATETIME, string $fallback = '—'): string
    {
        $dt = self::parse($value);

        return $dt ? $dt->format($format) : $fallback;
    }

    public static function date(mixed $value, string $format = self::DATE, string $fallback = '—'): string
    {
        return self::dateTime($value, $format, $fallback);
    }

    public static function time(mixed $value, string $format = self::TIME, string $fallback = '—'): string
    {
        return self::dateTime($value, $format, $fallback);
    }

    public static function todayYmd(): string
    {
        return self::now()->toDateString();
    }

    public static function ensureConfigured(): void
    {
        if (config('app.timezone') !== self::ZONE) {
            config(['app.timezone' => self::ZONE]);
        }

        if (date_default_timezone_get() !== self::ZONE) {
            date_default_timezone_set(self::ZONE);
        }
    }
}
