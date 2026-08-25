<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarHoliday extends Model
{
    public const TYPE_NATIONAL = 'national';

    public const TYPE_GOVERNMENT = 'government';

    protected $fillable = [
        'holiday_date',
        'name',
        'type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'holiday_date' => 'date',
            'is_active' => 'boolean',
        ];
    }
}
