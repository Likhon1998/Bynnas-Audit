<?php

namespace App\Support;

class Divisions
{
    public const OPTIONS = [
        'Dhaka',
        'Chattogram',
        'Rajshahi',
        'Khulna',
        'Barishal',
        'Sylhet',
        'Rangpur',
        'Mymensingh',
    ];

    /** @return list<string> */
    public static function all(): array
    {
        return self::OPTIONS;
    }
}
