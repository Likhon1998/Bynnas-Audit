<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class RiskLaw extends Model
{
    public const GROUP_RATIOS = 'ratios';

    public const GROUP_FLAGS = 'flags';

    public const GROUP_CATEGORY = 'category';

    protected $fillable = [
        'key',
        'label',
        'group_key',
        'unit',
        'direction',
        'bands',
        'description',
        'is_active',
        'affects_live_score',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'bands' => 'array',
            'is_active' => 'boolean',
            'affects_live_score' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return list<array{
     *   key:string,label:string,group_key:string,unit:string,direction:string,
     *   bands:array,description:?string,affects_live_score:bool,sort_order:int
     * }>
     */
    public static function defaultDefinitions(): array
    {
        return [
            [
                'key' => 'otr',
                'label' => 'OTR (On-time recovery)',
                'group_key' => self::GROUP_RATIOS,
                'unit' => 'percent',
                'direction' => 'higher_better',
                'bands' => [
                    ['op' => 'gte', 'value' => 98, 'points' => 0],
                    ['op' => 'gte', 'value' => 95, 'points' => 4],
                    ['op' => 'gte', 'value' => 90, 'points' => 8],
                    ['op' => 'gte', 'value' => 85, 'points' => 12],
                    ['op' => 'default', 'points' => 20],
                ],
                'description' => 'From KPI: Current Recovery ÷ Recoverable. Higher OTR = fewer risk points.',
                'affects_live_score' => true,
                'sort_order' => 10,
            ],
            [
                'key' => 'oss',
                'label' => 'OSS (Operational self-sufficiency)',
                'group_key' => self::GROUP_RATIOS,
                'unit' => 'ratio',
                'direction' => 'higher_better',
                'bands' => [
                    ['op' => 'gte', 'value' => 1.20, 'points' => 0],
                    ['op' => 'gte', 'value' => 1.00, 'points' => 4],
                    ['op' => 'gte', 'value' => 0.90, 'points' => 8],
                    ['op' => 'default', 'points' => 12],
                ],
                'description' => 'From manual inputs: Total Income ÷ Total Expenditure.',
                'affects_live_score' => true,
                'sort_order' => 20,
            ],
            [
                'key' => 'profitability',
                'label' => 'Profitability',
                'group_key' => self::GROUP_FLAGS,
                'unit' => 'boolean',
                'direction' => 'boolean',
                'bands' => [
                    'true_points' => 0,
                    'false_points' => 6,
                    'true_label' => 'Surplus / profitable',
                    'false_label' => 'Deficit / loss',
                ],
                'description' => 'From KPI Surplus/Deficit (FY). Profit = 0 points, loss adds risk.',
                'affects_live_score' => true,
                'sort_order' => 30,
            ],
            [
                'key' => 'write_off_ratio',
                'label' => 'Write-off ratio',
                'group_key' => self::GROUP_RATIOS,
                'unit' => 'percent',
                'direction' => 'lower_better',
                'bands' => [
                    ['op' => 'lte', 'value' => 1, 'points' => 0],
                    ['op' => 'lte', 'value' => 3, 'points' => 4],
                    ['op' => 'lte', 'value' => 5, 'points' => 8],
                    ['op' => 'default', 'points' => 12],
                ],
                'description' => 'Write-off amount ÷ Loan Outstanding.',
                'affects_live_score' => true,
                'sort_order' => 40,
            ],
            [
                'key' => 'savings_adjustment',
                'label' => 'Savings adjustment',
                'group_key' => self::GROUP_RATIOS,
                'unit' => 'percent',
                'direction' => 'lower_better',
                'bands' => [
                    ['op' => 'lte', 'value' => 2, 'points' => 0],
                    ['op' => 'lte', 'value' => 5, 'points' => 3],
                    ['op' => 'lte', 'value' => 10, 'points' => 6],
                    ['op' => 'default', 'points' => 10],
                ],
                'description' => 'Savings adjustment ÷ FY Loan Recovery.',
                'affects_live_score' => true,
                'sort_order' => 50,
            ],
            [
                'key' => 'nplr',
                'label' => 'NPLR',
                'group_key' => self::GROUP_RATIOS,
                'unit' => 'percent',
                'direction' => 'lower_better',
                'bands' => [
                    ['op' => 'lte', 'value' => 5, 'points' => 0],
                    ['op' => 'lte', 'value' => 10, 'points' => 4],
                    ['op' => 'lte', 'value' => 15, 'points' => 8],
                    ['op' => 'default', 'points' => 12],
                ],
                'description' => 'From KPI: Total OD Taka ÷ Loan Outstanding.',
                'affects_live_score' => true,
                'sort_order' => 60,
            ],
            [
                'key' => 'dr',
                'label' => 'DR (Delinquency rate)',
                'group_key' => self::GROUP_RATIOS,
                'unit' => 'percent',
                'direction' => 'lower_better',
                'bands' => [
                    ['op' => 'lte', 'value' => 5, 'points' => 0],
                    ['op' => 'lte', 'value' => 8, 'points' => 4],
                    ['op' => 'lte', 'value' => 12, 'points' => 8],
                    ['op' => 'lte', 'value' => 15, 'points' => 10],
                    ['op' => 'default', 'points' => 12],
                ],
                'description' => 'Same OD ratio as NPLR for live scoring (Total OD Taka ÷ Loan Outstanding).',
                'affects_live_score' => true,
                'sort_order' => 70,
            ],
            [
                'key' => 'par',
                'label' => 'PAR (Portfolio at risk)',
                'group_key' => self::GROUP_RATIOS,
                'unit' => 'percent',
                'direction' => 'lower_better',
                'bands' => [
                    ['op' => 'lte', 'value' => 5, 'points' => 0],
                    ['op' => 'lte', 'value' => 8, 'points' => 4],
                    ['op' => 'lte', 'value' => 12, 'points' => 8],
                    ['op' => 'lte', 'value' => 15, 'points' => 10],
                    ['op' => 'default', 'points' => 12],
                ],
                'description' => 'Used on Risk Excel export. Not added into the live total score.',
                'affects_live_score' => false,
                'sort_order' => 75,
            ],
            [
                'key' => 'distance',
                'label' => 'Distance from area office',
                'group_key' => self::GROUP_FLAGS,
                'unit' => 'boolean',
                'direction' => 'boolean',
                'bands' => [
                    'true_points' => 2,
                    'false_points' => 0,
                    'true_label' => 'More than 20 km',
                    'false_label' => 'Within 20 km',
                ],
                'description' => 'Far branches add risk points.',
                'affects_live_score' => true,
                'sort_order' => 80,
            ],
            [
                'key' => 'bm_abm',
                'label' => 'BM and ABM staffing',
                'group_key' => self::GROUP_FLAGS,
                'unit' => 'boolean',
                'direction' => 'boolean',
                'bands' => [
                    'true_points' => 0,
                    'false_points' => 6,
                    'true_label' => 'Has both BM and ABM',
                    'false_label' => 'Missing BM or ABM',
                ],
                'description' => 'Missing either BM or ABM adds risk points.',
                'affects_live_score' => true,
                'sort_order' => 90,
            ],
            [
                'key' => 'special_audit',
                'label' => 'Special audit (last 2 years)',
                'group_key' => self::GROUP_FLAGS,
                'unit' => 'boolean',
                'direction' => 'boolean',
                'bands' => [
                    'true_points' => 0,
                    'false_points' => 4,
                    'true_label' => 'Had special audit',
                    'false_label' => 'No special audit',
                ],
                'description' => 'No special audit coverage adds risk points.',
                'affects_live_score' => true,
                'sort_order' => 100,
            ],
            [
                'key' => 'risk_category',
                'label' => 'Final risk category bands',
                'group_key' => self::GROUP_CATEGORY,
                'unit' => 'category',
                'direction' => 'lower_better',
                'bands' => [
                    ['op' => 'lte', 'value' => 25, 'label' => 'Low Risk'],
                    ['op' => 'lte', 'value' => 45, 'label' => 'Medium Risk'],
                    ['op' => 'lte', 'value' => 65, 'label' => 'High Risk'],
                    ['op' => 'default', 'label' => 'Significant Risk'],
                ],
                'description' => 'Total weighted score is mapped to these category labels.',
                'affects_live_score' => false,
                'sort_order' => 200,
            ],
        ];
    }

    public static function ensureDefaults(): void
    {
        if (static::query()->exists()) {
            return;
        }

        foreach (static::defaultDefinitions() as $definition) {
            static::query()->create($definition + ['is_active' => true]);
        }
    }

    public static function resetToDefaults(): void
    {
        static::query()->delete();
        foreach (static::defaultDefinitions() as $definition) {
            static::query()->create($definition + ['is_active' => true]);
        }
    }

    /**
     * @return Collection<int, static>
     */
    public static function ordered(): Collection
    {
        static::ensureDefaults();

        return static::query()->orderBy('sort_order')->orderBy('id')->get();
    }

    /**
     * @return Collection<string, static>
     */
    public static function activeByKey(): Collection
    {
        return static::ordered()
            ->where('is_active', true)
            ->keyBy('key');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scoreNumeric(float $rawValue): int
    {
        $compare = $this->unit === 'percent' ? $rawValue * 100 : $rawValue;
        $bands = $this->bands ?? [];

        foreach ($bands as $band) {
            $op = $band['op'] ?? 'default';
            if ($op === 'default') {
                return (int) ($band['points'] ?? 0);
            }

            $threshold = (float) ($band['value'] ?? 0);
            $matched = match ($op) {
                'gte' => $compare >= $threshold,
                'lte' => $compare <= $threshold,
                'gt' => $compare > $threshold,
                'lt' => $compare < $threshold,
                default => false,
            };

            if ($matched) {
                return (int) ($band['points'] ?? 0);
            }
        }

        return 0;
    }

    public function scoreBoolean(bool $value): int
    {
        $bands = $this->bands ?? [];

        return $value
            ? (int) ($bands['true_points'] ?? 0)
            : (int) ($bands['false_points'] ?? 0);
    }

    public function categorizeScore(int $totalScore): string
    {
        $bands = $this->bands ?? [];

        foreach ($bands as $band) {
            $op = $band['op'] ?? 'default';
            if ($op === 'default') {
                return (string) ($band['label'] ?? 'Significant Risk');
            }

            $threshold = (float) ($band['value'] ?? 0);
            $matched = match ($op) {
                'lte' => $totalScore <= $threshold,
                'gte' => $totalScore >= $threshold,
                default => false,
            };

            if ($matched) {
                return (string) ($band['label'] ?? 'Significant Risk');
            }
        }

        return 'Significant Risk';
    }

    /**
     * Human-readable band lines for the UI.
     *
     * @return list<string>
     */
    public function bandSummaries(): array
    {
        $bands = $this->bands ?? [];

        if ($this->unit === 'boolean') {
            return [
                ($bands['true_label'] ?? 'Yes').' → '.(int) ($bands['true_points'] ?? 0).' pts',
                ($bands['false_label'] ?? 'No').' → '.(int) ($bands['false_points'] ?? 0).' pts',
            ];
        }

        if ($this->unit === 'category') {
            $lines = [];
            foreach ($bands as $band) {
                $label = (string) ($band['label'] ?? '');
                if (($band['op'] ?? '') === 'default') {
                    $lines[] = 'Else → '.$label;
                } else {
                    $lines[] = 'Score ≤ '.(string) ($band['value'] ?? '').' → '.$label;
                }
            }

            return $lines;
        }

        $suffix = $this->unit === 'percent' ? '%' : '';
        $lines = [];
        foreach ($bands as $band) {
            $points = (int) ($band['points'] ?? 0);
            if (($band['op'] ?? '') === 'default') {
                $lines[] = 'Else → '.$points.' pts';
                continue;
            }
            $op = ($band['op'] ?? '') === 'gte' ? '≥' : '≤';
            $lines[] = $op.' '.(string) ($band['value'] ?? '').$suffix.' → '.$points.' pts';
        }

        return $lines;
    }
}
