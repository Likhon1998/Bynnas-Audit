<?php

use App\Support\AppTime;
use Carbon\CarbonInterface;

if (! function_exists('bd_zone')) {
    function bd_zone(): string
    {
        return AppTime::ZONE;
    }
}

if (! function_exists('bd_now')) {
    function bd_now(): CarbonInterface
    {
        return AppTime::now();
    }
}

if (! function_exists('bd_datetime')) {
    function bd_datetime(mixed $value, string $format = AppTime::DATETIME, string $fallback = '—'): string
    {
        return AppTime::dateTime($value, $format, $fallback);
    }
}

if (! function_exists('bd_date')) {
    function bd_date(mixed $value, string $format = AppTime::DATE, string $fallback = '—'): string
    {
        return AppTime::date($value, $format, $fallback);
    }
}

if (! function_exists('bd_time')) {
    function bd_time(mixed $value, string $format = AppTime::TIME, string $fallback = '—'): string
    {
        return AppTime::time($value, $format, $fallback);
    }
}

if (! function_exists('bd_today')) {
    function bd_today(): string
    {
        return AppTime::todayYmd();
    }
}
