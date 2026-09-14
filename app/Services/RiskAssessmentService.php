<?php

namespace App\Services;

use App\Models\RiskLaw;
use App\Models\Shakha;
use App\Models\ShakhaAnnualKpi;
use App\Models\ShakhaRiskAssessment;
use App\Support\FinancialYear;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use RuntimeException;

class RiskAssessmentService
{
    /** @var Collection<string, RiskLaw>|null */
    protected ?Collection $lawsByKey = null;

    /**
     * Calculate risk score from annual KPI + manual operational inputs, then persist.
     *
     * KPI auto-mapping:
     * - risk.overdue_principal_31_365_days ← kpi.total_od_taka
     * - profitability                      ← kpi.surplus_deficit_fy (>= 0 = profit)
     *
     * Manual inputs (not on KPI):
     * - total_income, total_expenditure (for OSS)
     * - write_off, savings_adjustment, distance, BM/ABM, special audit
     *
     * @param  array{
     *     total_income?: float|int|string,
     *     total_expenditure?: float|int|string,
     *     write_off_principal_amount?: float|int|string,
     *     savings_adjustment_amount?: float|int|string,
     *     distance_from_area_office_km?: bool|int|string,
     *     has_both_bm_and_abm?: bool,
     *     special_audit_last_two_years?: bool
     * }  $manualInputs
     */
    public function calculateRiskScore(Shakha $shakha, int $month, int $year, array $manualInputs = []): ShakhaRiskAssessment
    {
        $preview = $this->previewScore($shakha, $month, $year, $manualInputs);

        return ShakhaRiskAssessment::query()->updateOrCreate(
            [
                'shakha_id' => $shakha->id,
                'assessment_month' => $month,
                'assessment_year' => $year,
            ],
            [
                'distance_from_area_office_km' => $preview['inputs']['far'] ? 1 : 0,
                'total_income' => $preview['inputs']['income'],
                'total_expenditure' => $preview['inputs']['expenditure'],
                'write_off_principal_amount' => $preview['inputs']['write_off'],
                'savings_adjustment_amount' => $preview['inputs']['savings_adj'],
                'overdue_principal_31_365_days' => $preview['inputs']['total_od_taka'],
                'has_both_bm_and_abm' => $preview['inputs']['has_both_bm_abm'],
                'special_audit_last_two_years' => $preview['inputs']['special_audit'],
                'total_weighted_score' => $preview['total'],
                'risk_category' => $preview['category'],
            ]
        );
    }

    /**
     * Score preview for the risk form (does not save).
     *
     * @param  array{
     *     total_income?: float|int|string,
     *     total_expenditure?: float|int|string,
     *     write_off_principal_amount?: float|int|string,
     *     savings_adjustment_amount?: float|int|string,
     *     distance_from_area_office_km?: bool|int|string,
     *     has_both_bm_and_abm?: bool,
     *     special_audit_last_two_years?: bool
     * }  $manualInputs
     * @return array{
     *   total:int,
     *   category:string,
     *   lines:list<array{key:string,label:string,detail:string,points:int}>,
     *   inputs:array{income:float,expenditure:float,write_off:float,savings_adj:float,far:bool,has_both_bm_abm:bool,special_audit:bool,total_od_taka:float}
     * }
     */
    public function previewScore(Shakha $shakha, int $month, int $year, array $manualInputs = []): array
    {
        $fy = $this->fyForPeriod($month, $year);
        $kpi = $this->findAnnualKpi($shakha, $fy->label);

        if (! $kpi) {
            throw new RuntimeException(
                'No annual KPI found for FY '.$fy->label.'. Complete the Shakha Annual KPI for this financial year before running risk assessment.'
            );
        }

        if (! $kpi->isReadyForRisk()) {
            throw new RuntimeException(
                'Annual KPI for FY '.$fy->label.' is incomplete for risk scoring. Enter members, loan outstanding, and recoverable (non-zero) first.'
            );
        }

        $surplusDeficit = (float) $kpi->surplus_deficit_fy;
        $totalOdTaka = (float) $kpi->total_od_taka;

        $income = (float) ($manualInputs['total_income'] ?? 0);
        $expenditure = (float) ($manualInputs['total_expenditure'] ?? 0);
        $writeOff = (float) ($manualInputs['write_off_principal_amount'] ?? 0);
        $savingsAdj = (float) ($manualInputs['savings_adjustment_amount'] ?? 0);
        $farFromOffice = (bool) ($manualInputs['distance_from_area_office_km'] ?? false);
        $hasBothBmAbm = (bool) ($manualInputs['has_both_bm_and_abm'] ?? true);
        $specialAudit = (bool) ($manualInputs['special_audit_last_two_years'] ?? true);

        $loanOs = (float) $kpi->loan_outstanding;
        $loanRecovery = (float) $kpi->fy_loan_recovery;
        $currentRecovery = (float) $kpi->current_recovery;
        $recoverable = (float) $kpi->recoverable;

        $otr = $this->safeDivide($currentRecovery, $recoverable);
        $ossRatio = $this->safeDivide($income, $expenditure);
        $isProfitable = $surplusDeficit >= 0;
        $odRatio = $this->safeDivide($totalOdTaka, $loanOs);
        $writeOffRatio = $this->safeDivide($writeOff, $loanOs);
        $savingsAdjustmentPercentage = $this->safeDivide($savingsAdj, $loanRecovery);

        $lines = [
            [
                'key' => 'otr',
                'label' => 'OTR',
                'detail' => number_format($otr * 100, 2).'% (from KPI recovery)',
                'points' => $this->scoreByLaw('otr', $otr),
            ],
            [
                'key' => 'oss',
                'label' => 'OSS',
                'detail' => $expenditure > 0
                    ? number_format($ossRatio, 2).' (income ÷ expenditure)'
                    : 'n/a — enter income & expenditure',
                'points' => $this->scoreByLaw('oss', $ossRatio),
            ],
            [
                'key' => 'profitability',
                'label' => 'Profitability',
                'detail' => $isProfitable ? 'Surplus from KPI' : 'Loss from KPI',
                'points' => $this->scoreBooleanLaw('profitability', $isProfitable),
            ],
            [
                'key' => 'write_off_ratio',
                'label' => 'Write-off',
                'detail' => number_format($writeOffRatio * 100, 2).'% of loan outstanding',
                'points' => $this->scoreByLaw('write_off_ratio', $writeOffRatio),
            ],
            [
                'key' => 'savings_adjustment',
                'label' => 'Savings adj.',
                'detail' => number_format($savingsAdjustmentPercentage * 100, 2).'% of FY loan recovery',
                'points' => $this->scoreByLaw('savings_adjustment', $savingsAdjustmentPercentage),
            ],
            [
                'key' => 'nplr',
                'label' => 'NPLR',
                'detail' => number_format($odRatio * 100, 2).'% OD / loan outstanding',
                'points' => $this->scoreByLaw('nplr', $odRatio),
            ],
            [
                'key' => 'dr',
                'label' => 'DR',
                'detail' => number_format($odRatio * 100, 2).'% OD / loan outstanding',
                'points' => $this->scoreByLaw('dr', $odRatio),
            ],
            [
                'key' => 'distance',
                'label' => 'Distance',
                'detail' => $farFromOffice ? '> 20 km' : 'Within 20 km',
                'points' => $this->scoreBooleanLaw('distance', $farFromOffice),
            ],
            [
                'key' => 'bm_abm',
                'label' => 'BM / ABM',
                'detail' => $hasBothBmAbm ? 'Has both' : 'Missing BM or ABM',
                'points' => $this->scoreBooleanLaw('bm_abm', $hasBothBmAbm),
            ],
            [
                'key' => 'special_audit',
                'label' => 'Special audit',
                'detail' => $specialAudit ? 'Had special audit' : 'No special audit',
                'points' => $this->scoreBooleanLaw('special_audit', $specialAudit),
            ],
        ];

        $total = (int) array_sum(array_column($lines, 'points'));

        return [
            'total' => $total,
            'category' => $this->categorize($total),
            'lines' => $lines,
            'inputs' => [
                'income' => $income,
                'expenditure' => $expenditure,
                'write_off' => $writeOff,
                'savings_adj' => $savingsAdj,
                'far' => $farFromOffice,
                'has_both_bm_abm' => $hasBothBmAbm,
                'special_audit' => $specialAudit,
                'total_od_taka' => $totalOdTaka,
            ],
        ];
    }

    public function fyForPeriod(int $month, int $year): FinancialYear
    {
        return FinancialYear::current(Carbon::create($year, $month, 1)->startOfDay());
    }

    public function findAnnualKpi(Shakha $shakha, string $fyLabel): ?ShakhaAnnualKpi
    {
        return ShakhaAnnualKpi::query()
            ->where('shakha_id', $shakha->id)
            ->where('fy_label', $fyLabel)
            ->first();
    }

    /**
     * @return array{otr: float, dr: float, nplr: float, surplus: float, total_od_taka: float}
     */
    public function syncedRatios(ShakhaAnnualKpi $kpi): array
    {
        $loanOs = (float) $kpi->loan_outstanding;
        $recoverable = (float) $kpi->recoverable;
        $totalOdTaka = (float) $kpi->total_od_taka;
        $odRatio = $this->safeDivide($totalOdTaka, $loanOs);

        return [
            'otr' => $this->safeDivide((float) $kpi->current_recovery, $recoverable),
            'dr' => $odRatio,
            'nplr' => $odRatio,
            'surplus' => (float) $kpi->surplus_deficit_fy,
            'total_od_taka' => $totalOdTaka,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function compileExportRows(string $fyLabel): array
    {
        $assessments = ShakhaRiskAssessment::query()
            ->with(['shakha.area'])
            ->whereHas('shakha', fn ($q) => $q->where('status', 'active'))
            ->orderByDesc('assessment_year')
            ->orderByDesc('assessment_month')
            ->orderByDesc('id')
            ->get()
            ->unique('shakha_id')
            ->values();

        $kpiByShakha = ShakhaAnnualKpi::query()
            ->where('fy_label', $fyLabel)
            ->whereIn('shakha_id', $assessments->pluck('shakha_id'))
            ->get()
            ->keyBy('shakha_id');

        $rows = [];

        foreach ($assessments as $assessment) {
            $shakha = $assessment->shakha;
            if (! $shakha) {
                continue;
            }

            /** @var ShakhaAnnualKpi|null $kpi */
            $kpi = $kpiByShakha->get($shakha->id);

            $loanOs = (float) ($kpi?->loan_outstanding ?? 0);
            $recoverable = (float) ($kpi?->recoverable ?? 0);
            $currentRecovery = (float) ($kpi?->current_recovery ?? 0);
            $loanRecovery = (float) ($kpi?->fy_loan_recovery ?? 0);
            $totalOdTaka = (float) ($assessment->overdue_principal_31_365_days ?: ($kpi?->total_od_taka ?? 0));
            $dueLoanee = (float) ($kpi?->due_loanee_loan_outstanding ?? 0);
            $surplus = (float) ($kpi?->surplus_deficit_fy ?? 0);
            $income = (float) $assessment->total_income;
            $expenditure = (float) $assessment->total_expenditure;
            $writeOff = (float) $assessment->write_off_principal_amount;
            $savingsAdj = (float) $assessment->savings_adjustment_amount;
            $farFromOffice = ((int) $assessment->distance_from_area_office_km) > 0;
            $hasBothBmAbm = (bool) $assessment->has_both_bm_and_abm;
            $specialAudit = (bool) $assessment->special_audit_last_two_years;

            $otr = $this->safeDivide($currentRecovery, $recoverable);
            $par = $this->safeDivide($dueLoanee, $loanOs);
            $odRatio = $this->safeDivide($totalOdTaka, $loanOs);
            $oss = $this->safeDivide($income, $expenditure);
            $writeOffRatio = $this->safeDivide($writeOff, $loanOs);
            $savingsAdjPct = $this->safeDivide($savingsAdj, $loanRecovery);
            $isProfitable = $surplus >= 0;

            $opening = $shakha->opening_date ?? $shakha->opened_at;

            $rows[] = [
                'focal_person_name' => $shakha->focal_person_name ?: '',
                'zone' => $shakha->area?->division ?: '',
                'area_name' => $shakha->area?->name ?: '',
                'branch_name' => $shakha->name,
                'code' => $shakha->code ?: '',
                'opening_date' => $opening ? ExcelDate::PHPToExcel($opening) : null,
                'otr' => $otr,
                'par' => $par,
                'dr' => $odRatio,
                'wr' => $writeOffRatio,
                'write_off_amount' => $writeOff,
                'savings_adjustment_pct' => $savingsAdjPct,
                'oss' => $oss,
                'nplr' => $odRatio,
                'fraud_forgery' => null,
                'loan_outstanding' => $loanOs,
                'total_income' => $income,
                'total_expenditure' => $expenditure,
                'surplus_deficit' => $surplus,
                'last_audit_rating' => '',
                'distance_yes_no' => $farFromOffice ? 'Yes' : 'No',
                'bm_abm_yes_no' => $hasBothBmAbm ? 'Yes' : 'No',
                'w_otr' => $this->scoreByLaw('otr', $otr),
                'w_par' => $this->scoreByLaw('par', $par),
                'w_dr' => $this->scoreByLaw('dr', $odRatio),
                'w_wr' => $this->scoreByLaw('write_off_ratio', $writeOffRatio),
                'w_write_off' => $this->scoreByLaw('write_off_ratio', $writeOffRatio),
                'w_savings_adj' => $this->scoreByLaw('savings_adjustment', $savingsAdjPct),
                'w_oss' => $this->scoreByLaw('oss', $oss),
                'w_nplr' => $this->scoreByLaw('nplr', $odRatio),
                'w_fraud' => 0,
                'w_profitability' => $this->scoreBooleanLaw('profitability', $isProfitable),
                'w_distance' => $this->scoreBooleanLaw('distance', $farFromOffice),
                'w_bm_abm' => $this->scoreBooleanLaw('bm_abm', $hasBothBmAbm),
                'w_special_audit' => $this->scoreBooleanLaw('special_audit', $specialAudit),
                'total_weighted_score' => (int) $assessment->total_weighted_score,
                'risk_category' => $assessment->risk_category,
            ];
        }

        usort($rows, function (array $a, array $b) {
            return [$a['zone'], $a['area_name'], $a['branch_name']]
                <=> [$b['zone'], $b['area_name'], $b['branch_name']];
        });

        return $rows;
    }

    protected function safeDivide(float $numerator, float $denominator): float
    {
        if ($denominator == 0.0) {
            return 0.0;
        }

        return $numerator / $denominator;
    }

    /**
     * @return Collection<string, RiskLaw>
     */
    protected function lawsByKey(): Collection
    {
        return $this->lawsByKey ??= RiskLaw::ordered()->keyBy('key');
    }

    protected function scoreByLaw(string $key, float $value): int
    {
        $law = $this->lawsByKey()->get($key);
        if (! $law) {
            return $this->fallbackScore($key, $value);
        }
        if (! $law->is_active) {
            return 0;
        }

        return $law->scoreNumeric($value);
    }

    protected function scoreBooleanLaw(string $key, bool $value): int
    {
        $law = $this->lawsByKey()->get($key);
        if (! $law) {
            return $this->fallbackBooleanScore($key, $value);
        }
        if (! $law->is_active) {
            return 0;
        }

        return $law->scoreBoolean($value);
    }

    protected function categorize(int $score): string
    {
        $law = $this->lawsByKey()->get('risk_category');
        if ($law && $law->is_active) {
            return $law->categorizeScore($score);
        }

        return match (true) {
            $score <= 25 => 'Low Risk',
            $score <= 45 => 'Medium Risk',
            $score <= 65 => 'High Risk',
            default => 'Significant Risk',
        };
    }

    protected function fallbackScore(string $key, float $value): int
    {
        $pct = $value * 100;

        return match ($key) {
            'otr' => match (true) {
                $pct >= 98 => 0,
                $pct >= 95 => 4,
                $pct >= 90 => 8,
                $pct >= 85 => 12,
                default => 20,
            },
            'par' => match (true) {
                $pct <= 5 => 0,
                $pct <= 8 => 4,
                $pct <= 12 => 8,
                $pct <= 15 => 10,
                default => 12,
            },
            'oss' => match (true) {
                $value >= 1.20 => 0,
                $value >= 1.00 => 4,
                $value >= 0.90 => 8,
                default => 12,
            },
            'dr' => match (true) {
                $pct <= 5 => 0,
                $pct <= 8 => 4,
                $pct <= 12 => 8,
                $pct <= 15 => 10,
                default => 12,
            },
            'write_off_ratio' => match (true) {
                $pct <= 1 => 0,
                $pct <= 3 => 4,
                $pct <= 5 => 8,
                default => 12,
            },
            'savings_adjustment' => match (true) {
                $pct <= 2 => 0,
                $pct <= 5 => 3,
                $pct <= 10 => 6,
                default => 10,
            },
            'nplr' => match (true) {
                $pct <= 5 => 0,
                $pct <= 10 => 4,
                $pct <= 15 => 8,
                default => 12,
            },
            default => 0,
        };
    }

    protected function fallbackBooleanScore(string $key, bool $value): int
    {
        return match ($key) {
            'profitability' => $value ? 0 : 6,
            'distance' => $value ? 2 : 0,
            'bm_abm' => $value ? 0 : 6,
            'special_audit' => $value ? 0 : 4,
            default => 0,
        };
    }
}
