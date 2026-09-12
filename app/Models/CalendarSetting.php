<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarSetting extends Model
{
    public const KEY_WEEKEND_DAYS = 'weekend_days';

    protected $fillable = [
        'key',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    /**
     * @return list<int>
     */
    public static function weekendDays(): array
    {
        $row = static::query()->where('key', self::KEY_WEEKEND_DAYS)->first();
        $days = is_array($row?->value) ? $row->value : config('working_calendar.weekend_days', [5, 6]);

        $normalized = array_values(array_unique(array_map('intval', $days)));
        $normalized = array_values(array_filter($normalized, fn (int $d) => $d >= 0 && $d <= 6));

        return $normalized !== [] ? $normalized : [5, 6];
    }

    /**
     * @param  list<int>  $days
     */
    public static function setWeekendDays(array $days): self
    {
        $normalized = array_values(array_unique(array_map('intval', $days)));
        $normalized = array_values(array_filter($normalized, fn (int $d) => $d >= 0 && $d <= 6));
        sort($normalized);

        return static::query()->updateOrCreate(
            ['key' => self::KEY_WEEKEND_DAYS],
            ['value' => $normalized]
        );
    }
}
