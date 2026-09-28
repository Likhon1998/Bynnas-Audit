<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceRule extends Model
{
    public const SOURCE_AUTO = 'auto';

    public const SOURCE_MANUAL = 'manual';

    public const COLORS = [
        'confirmed' => 'bg-emerald-500',
        'first_time' => 'bg-teal-400',
        'perfect' => 'bg-cyan-400',
        'completed' => 'bg-sky-500',
        'sent' => 'bg-violet-500',
        'started' => 'bg-sky-300',
        'maker_done' => 'bg-indigo-400',
        'returned' => 'bg-rose-500',
        'visit_done' => 'bg-orange-400',
        'visit_delayed' => 'bg-amber-500',
        'email' => 'bg-blue-400',
        'checklist' => 'bg-lime-500',
        'active_day' => 'bg-slate-400',
    ];

    public const MANUAL_COLORS = ['bg-fuchsia-500', 'bg-pink-400', 'bg-yellow-400', 'bg-purple-400', 'bg-green-600', 'bg-red-400', 'bg-stone-400'];

    public const SPECIAL_COLOR = 'bg-pink-500';

    protected $fillable = [
        'key',
        'source',
        'label',
        'description',
        'points',
        'monthly_cap',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'float',
            'monthly_cap' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function marks(): HasMany
    {
        return $this->hasMany(PerformanceMark::class);
    }

    public function isManual(): bool
    {
        return $this->source === self::SOURCE_MANUAL;
    }

    public function color(): string
    {
        if ($this->isManual()) {
            return self::MANUAL_COLORS[$this->id % count(self::MANUAL_COLORS)];
        }

        return self::COLORS[$this->key] ?? 'bg-slate-400';
    }

    /**
     * Activities the app can count on its own, keyed by rule key.
     *
     * @return array<string, array{label:string, description:string}>
     */
    public static function activities(): array
    {
        return collect(self::defaults())
            ->mapWithKeys(fn ($row) => [$row['key'] => ['label' => $row['label'], 'description' => $row['description']]])
            ->all();
    }

    /**
     * @return list<array{key:string,source:string,label:string,description:string,points:float,monthly_cap:?int,is_active:bool,sort_order:int}>
     */
    public static function defaults(): array
    {
        $rows = [
            ['confirmed', 'Report confirmed', 'Each report a reviewer confirms and locks.', 10, null],
            ['first_time', 'Confirmed without send-back', 'Bonus when a report is confirmed in the 1st review round.', 5, null],
            ['perfect', 'Totally fixed · 100%', 'Bonus when the reviewer marks the report perfect.', 5, null],
            ['completed', 'Finished writing a report', 'Each report written to 100%.', 4, null],
            ['sent', 'Sent for review', 'Each 1st review or re-review request.', 2, null],
            ['started', 'Started a report', 'Each new report opened.', 1, null],
            ['maker_done', 'Review fixes marked done', 'Each time the maker closes the reviewer\'s marks.', 1, null],
            ['returned', 'Sent back for changes', 'Each time a reviewer returns a report.', -3, null],
            ['visit_done', 'Branch visit finished', 'Each visit completed, including as a team member.', 6, null],
            ['visit_delayed', 'Branch visit delayed', 'Each visit marked delayed.', -2, null],
            ['email', 'Report emailed', 'Each report sent by email.', 1, 20],
            ['checklist', 'Checklist saved', 'Each checklist saved.', 1, 20],
            ['active_day', 'Active day', 'Each day with at least one activity.', 1, 26],
        ];

        return array_map(fn (array $row, int $i) => [
            'key' => $row[0],
            'source' => self::SOURCE_AUTO,
            'label' => $row[1],
            'description' => $row[2],
            'points' => (float) $row[3],
            'monthly_cap' => $row[4],
            'is_active' => true,
            'sort_order' => $i + 1,
        ], $rows, array_keys($rows));
    }
}
