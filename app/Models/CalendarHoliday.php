<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarHoliday extends Model
{
    public const TYPE_NATIONAL = 'national';

    public const TYPE_GOVERNMENT = 'government';

    public const TYPE_NGO = 'ngo';

    public const TYPES = [
        self::TYPE_NATIONAL,
        self::TYPE_GOVERNMENT,
        self::TYPE_NGO,
    ];

    protected $fillable = [
        'holiday_date',
        'name',
        'type',
        'notes',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'holiday_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_NATIONAL => 'National holiday',
            self::TYPE_GOVERNMENT => 'Government holiday',
            self::TYPE_NGO => 'Internal off day',
            default => ucfirst($type),
        };
    }
}
