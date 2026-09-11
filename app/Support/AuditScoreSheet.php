<?php

namespace App\Support;

/**
 * Sample-based Audit Score sheet: C=A*B, E=A-D, F=E*B, G=F/C.
 */
class AuditScoreSheet
{
    public const COLOR_HEADER = '#1F4E79';

    public const COLOR_HEADER_SUB = '#2E75B6';

    public const COLOR_SECTION = '#F8CBAD';

    public const COLOR_SCORE_ROW = '#1F4E79';

    public const COLOR_CALC_GREEN = '#C6EFCE';

    public const COLOR_CALC_MAJOR = '#FCE4D6';

    public const COLOR_G = '#D6DCE4';

    public const COLOR_GRADE = '#F4B183';

    public const COLOR_SUMMARY_LABEL = '#E7E6E6';

    /**
     * @return list<string>
     */
    public static function categoryOptions(): array
    {
        return ['Major', 'Medium', 'Minor', 'Unsatisfactory'];
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    public static function defaultAdjustments(): array
    {
        return [
            ['label' => 'Fraud Report issued (5-15%)', 'value' => ''],
            ['label' => 'Special report (Suspected Fraud) (5-10%)', 'value' => ''],
            ['label' => 'Special report (Other issues) 5%', 'value' => ''],
            ['label' => 'Addition/(Less): Previous year step taken (2-5%)', 'value' => ''],
            ['label' => 'Addition: Present year step taken (2-5%)', 'value' => ''],
            ['label' => 'Reflection of impact of observation (2-10%)', 'value' => ''],
            ['label' => 'Scope Limitation (If any) (2-10%)', 'value' => ''],
        ];
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    public static function defaultSubsequent(): array
    {
        return [
            ['label' => 'Impactful/Critical Subsequent Event (if any)', 'value' => ''],
        ];
    }

    public static function riskWeightForCategory(string $category): string
    {
        $c = strtolower(trim($category));

        return match (true) {
            str_contains($c, 'unsatisfactory') => '4',
            str_contains($c, 'major') => '4',
            str_contains($c, 'medium') => '3',
            str_contains($c, 'minor') => '2',
            default => '3',
        };
    }

    public static function categoryFromFindingRating(string $rating): string
    {
        $r = strtolower($rating);

        return match (true) {
            str_contains($r, 'unsatisfactory') || str_contains($r, '(a)') || str_contains($r, '(f)') => 'Unsatisfactory',
            str_contains($r, 'major') || str_contains($r, '(b)') => 'Major',
            str_contains($r, 'medium') || str_contains($r, '(c)') => 'Medium',
            str_contains($r, 'minor') || str_contains($r, '(d)') => 'Minor',
            default => 'Medium',
        };
    }

    public static function isMajorLike(string $category): bool
    {
        $c = strtolower(trim($category));

        return str_contains($c, 'major') || str_contains($c, 'unsatisfactory');
    }

    /**
     * Background for calculated cells C / E / F (and Major numeric band).
     */
    public static function calcCellBg(string $category): string
    {
        return self::isMajorLike($category) ? self::COLOR_CALC_MAJOR : self::COLOR_CALC_GREEN;
    }

    public static function gCellBg(string $category): string
    {
        return self::isMajorLike($category) ? self::COLOR_CALC_MAJOR : self::COLOR_G;
    }

    /**
     * Major rows tint A/B/D as well (matches official sheet).
     */
    public static function inputCellBg(string $category): string
    {
        return self::isMajorLike($category) ? self::COLOR_CALC_MAJOR : '#FFFFFF';
    }

    public static function parseNumber(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }
        $raw = str_replace(['%', ' '], '', $raw);

        return BanglaNumerals::toFloat($raw);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public static function computeRow(array $row): array
    {
        $a = self::parseNumber($row['sample_size'] ?? null);
        $b = self::parseNumber($row['risk_weight'] ?? null);
        $d = self::parseNumber($row['instance_size'] ?? null);

        $c = ($a !== null && $b !== null) ? $a * $b : null;
        $e = ($a !== null && $d !== null) ? $a - $d : null;
        $f = ($e !== null && $b !== null) ? $e * $b : null;
        $g = ($c !== null && $c != 0.0 && $f !== null) ? ($f / $c) * 100 : null;

        $row['risk_weighted_c'] = self::formatNumber($c);
        $row['samples_not_reported_e'] = self::formatNumber($e);
        $row['risk_weighted_f'] = self::formatNumber($f);
        $row['audit_score_g'] = self::formatPercent($g !== null ? round($g) : null);
        $row['_c'] = $c;
        $row['_e'] = $e;
        $row['_f'] = $f;
        $row['_g'] = $g;

        return $row;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    public static function initialScorePercent(array $rows): ?float
    {
        $sumC = 0.0;
        $sumF = 0.0;
        $any = false;
        foreach ($rows as $row) {
            $computed = self::computeRow(is_array($row) ? $row : []);
            if ($computed['_c'] === null || $computed['_f'] === null) {
                continue;
            }
            $any = true;
            $sumC += (float) $computed['_c'];
            $sumF += (float) $computed['_f'];
        }
        if (! $any || $sumC == 0.0) {
            return null;
        }

        return ($sumF / $sumC) * 100;
    }

    /**
     * @param  list<array{label?:string,value?:string}>  $items
     */
    public static function sumPercentValues(array $items): float
    {
        $sum = 0.0;
        foreach ($items as $item) {
            $n = self::parseNumber(is_array($item) ? ($item['value'] ?? null) : null);
            if ($n !== null) {
                $sum += $n;
            }
        }

        return $sum;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  list<array{label?:string,value?:string}>  $adjustments
     * @param  list<array{label?:string,value?:string}>  $subsequent
     * @return array{initial:?float,final:?float,adjusted:?float,grade:string,audit_score_display:string}
     */
    public static function summarize(array $rows, array $adjustments = [], array $subsequent = []): array
    {
        $initial = self::initialScorePercent($rows);
        $final = $initial;
        if ($final !== null) {
            $final += self::sumPercentValues($adjustments);
        }
        $adjusted = $final;
        if ($adjusted !== null) {
            $adjusted += self::sumPercentValues($subsequent);
        }

        $scoreForGrade = $adjusted ?? $final ?? $initial;

        return [
            'initial' => $initial,
            'final' => $final,
            'adjusted' => $adjusted,
            'grade' => self::performanceGrade($scoreForGrade),
            'audit_score_display' => self::formatPercent($scoreForGrade),
        ];
    }

    public static function performanceGrade(?float $score): string
    {
        if ($score === null) {
            return '';
        }
        if ($score >= 91) {
            return 'Satisfactory (Adequate)';
        }
        if ($score >= 81) {
            return 'Satisfactory (Fair)';
        }
        if ($score >= 71) {
            return 'Improvement Needed';
        }
        if ($score >= 61) {
            return 'Major Improvement Needed';
        }

        return 'Unsatisfactory';
    }

    public static function formatNumber(?float $n): string
    {
        if ($n === null) {
            return '';
        }
        if (abs($n - round($n)) < 0.00001) {
            return (string) (int) round($n);
        }

        return rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
    }

    public static function formatPercent(?float $n): string
    {
        if ($n === null) {
            return '';
        }
        // Overall scores keep 2 decimals when needed (e.g. 65.55%); whole numbers stay clean.
        if (abs($n - round($n)) < 0.005) {
            return ((string) (int) round($n)).'%';
        }

        return number_format($n, 2, '.', '').'%';
    }
}
