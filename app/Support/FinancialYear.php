<?php

namespace App\Support;

use Carbon\Carbon;

class FinancialYear
{
    /** @var list<string> */
    public const MONTH_KEYS = ['jul', 'aug', 'sep', 'oct', 'nov', 'dec', 'jan', 'feb', 'mar', 'apr', 'may', 'jun'];

    /** @var list<string> */
    public const MONTH_LABELS = ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];

    public function __construct(
        public readonly string $label,
        public readonly Carbon $startDate,
        public readonly Carbon $endDate,
    ) {}

    public static function fromLabel(string $label): self
    {
        if (! preg_match('/^(\d{4})-(\d{4})$/', $label, $matches)) {
            throw new \InvalidArgumentException("Invalid FY label [{$label}]. Expected YYYY-YYYY.");
        }

        $startYear = (int) $matches[1];
        $endYear = (int) $matches[2];

        if ($endYear !== $startYear + 1) {
            throw new \InvalidArgumentException("Invalid FY range [{$label}].");
        }

        return new self(
            $label,
            Carbon::create($startYear, 7, 1)->startOfDay(),
            Carbon::create($endYear, 6, 30)->endOfDay(),
        );
    }

    public static function fromStartYear(int $startYear): self
    {
        return self::fromLabel(sprintf('%d-%d', $startYear, $startYear + 1));
    }

    /**
     * Bangladesh-style FY: 1 July → 30 June.
     */
    public static function current(?Carbon $asOf = null): self
    {
        $asOf = $asOf ? $asOf->copy() : now();
        $startYear = $asOf->month >= 7 ? $asOf->year : $asOf->year - 1;

        return self::fromStartYear($startYear);
    }

    public static function for2026_2027(): self
    {
        return self::fromLabel('2026-2027');
    }

    public function startYear(): int
    {
        return (int) $this->startDate->year;
    }

    public function next(): self
    {
        return self::fromStartYear($this->startYear() + 1);
    }

    public function previous(): self
    {
        return self::fromStartYear($this->startYear() - 1);
    }

    /**
     * @return list<array{index:int,key:string,label:string,year:int,month:int}>
     */
    public function months(): array
    {
        $months = [];

        for ($index = 0; $index < 12; $index++) {
            $date = $this->startDate->copy()->addMonthsNoOverflow($index);
            $months[] = [
                'index' => $index,
                'key' => self::MONTH_KEYS[$index],
                'label' => self::MONTH_LABELS[$index],
                'year' => (int) $date->year,
                'month' => (int) $date->month,
            ];
        }

        return $months;
    }

    public function dateForMonthIndex(int $index, int $day = 1): Carbon
    {
        return $this->startDate->copy()->addMonthsNoOverflow($index)->day(min($day, 28))->startOfDay();
    }

    public function monthIndexForDate(Carbon $date): ?int
    {
        if ($date->lt($this->startDate) || $date->gt($this->endDate)) {
            return null;
        }

        return (($date->year - $this->startDate->year) * 12) + ($date->month - $this->startDate->month);
    }

    /**
     * @return array{q1:list<int>,q2:list<int>,q3:list<int>,q4:list<int>}
     */
    public static function quarterIndexes(): array
    {
        return [
            'q1' => [0, 1, 2],
            'q2' => [3, 4, 5],
            'q3' => [6, 7, 8],
            'q4' => [9, 10, 11],
        ];
    }
}
