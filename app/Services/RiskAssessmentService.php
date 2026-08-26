<?php

namespace App\Services;

use App\Models\Shakha;
use App\Models\ShakhaAnnualKpi;
use App\Models\ShakhaRiskAssessment;
use App\Support\FinancialYear;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use RuntimeException;

class RiskAssessmentService
{
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
        $fy = $this->fyForPeriod($month, $year);
        $kpi = $this->findAnnualKpi($shakha, $fy->label);

        if (! $kpi) {
            throw new RuntimeException(
                'No annual KPI found for FY '.$fy->label.'. Complete the Shakha Annual KPI for this financial year before running risk assessment.'
            );
        }

        // From KPI
        $surplusDeficit = (float) $kpi->surplus_deficit_fy;
        $totalOdTaka = (float) $kpi->total_od_taka; // overdue principal

        // Manual
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

        // OSS from manual income / expenditure
        $ossRatio = $this->safeDivide($income, $expenditure);

        // Profitability from KPI Surplus/Deficit
        $isProfitable = $surplusDeficit >= 0;

        // NPLR / DR from KPI Total OD Taka
        $odRatio = $this->safeDivide($totalOdTaka, $loanOs);

        $writeOffRatio = $this->safeDivide($writeOff, $loanOs);
        $savingsAdjustmentPercentage = $this->safeDivide($savingsAdj, $loanRecovery);

        $scoreBreakdown = [
            'otr' => $this->scoreOtr($otr),
            'oss' => $this->scoreOss($ossRatio),
            'profitability' => $this->scoreProfitability($isProfitable),
            'write_off_ratio' => $this->scoreWriteOffRatio($writeOffRatio),
            'savings_adjustment' => $this->scoreSavingsAdjustment($savingsAdjustmentPercentage),
            'nplr' => $this->scoreNplr($odRatio),
            'dr' => $this->scoreDr($odRatio),
            'distance' => $this->scoreDistance($farFromOffice),
            'bm_abm' => $this->scoreBmAbm($hasBothBmAbm),
            'special_audit' => $this->scoreSpecialAudit($specialAudit),
        ];

        $totalWeightedScore = (int) array_sum($scoreBreakdown);
        $riskCategory = $this->categorize($totalWeightedScore);

        return ShakhaRiskAssessment::query()->updateOrCreate(
            [
                'shakha_id' => $shakha->id,
                'assessment_month' => $month,
                'assessment_year' => $year,
            ],
            [
                'distance_from_area_office_km' => $farFromOffice ? 1 : 0,
                'total_income' => $income,
                'total_expenditure' => $expenditure,
                'write_off_principal_amount' => $writeOff,
                'savings_adjustment_amount' => $savingsAdj,
                'overdue_principal_31_365_days' => $totalOdTaka,
                'has_both_bm_and_abm' => $hasBothBmAbm,
                'special_audit_last_two_years' => $specialAudit,
                'total_weighted_score' => $totalWeightedScore,
                'risk_category' => $riskCategory,
            ]
        );
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
     * Rows for Risk Analysis Excel export (template layout).
     *
     * @return list<array<string, mixed>>
     */
    public function compileExportRows(string $fyLabel): array
    {
        $fy = FinancialYear::fromLabel($fyLabel);

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
                'w_otr' => $this->scoreOtr($otr),
                'w_par' => $this->scorePar($par),
                'w_dr' => $this->scoreDr($odRatio),
                'w_wr' => $this->scoreWriteOffRatio($writeOffRatio),
                'w_write_off' => $this->scoreWriteOffRatio($writeOffRatio),
                'w_savings_adj' => $this->scoreSavingsAdjustment($savingsAdjPct),
                'w_oss' => $this->scoreOss($oss),
                'w_nplr' => $this->scoreNplr($odRatio),
                'w_fraud' => 0,
                'w_profitability' => $this->scoreProfitability($isProfitable),
                'w_distance' => $this->scoreDistance($farFromOffice),
                'w_bm_abm' => $this->scoreBmAbm($hasBothBmAbm),
                'w_special_audit' => $this->scoreSpecialAudit($specialAudit),
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

    protected function scoreOtr(float $otr): int
    {
        $pct = $otr * 100;

        return match (true) {
            $pct >= 98 => 0,
            $pct >= 95 => 4,
            $pct >= 90 => 8,
            $pct >= 85 => 12,
            default => 20,
        };
    }

    protected function scorePar(float $par): int
    {
        $pct = $par * 100;

        return match (true) {
            $pct <= 5 => 0,
            $pct <= 8 => 4,
            $pct <= 12 => 8,
            $pct <= 15 => 10,
            default => 12,
        };
    }

    protected function scoreProfitability(bool $isProfitable): int
    {
        return $isProfitable ? 0 : 6;
    }

    protected function scoreOss(float $oss): int
    {
        return match (true) {
            $oss >= 1.20 => 0,
            $oss >= 1.00 => 4,
            $oss >= 0.90 => 8,
            default => 12,
        };
    }

    protected function scoreDr(float $dr): int
    {
        $pct = $dr * 100;

        return match (true) {
            $pct <= 5 => 0,
            $pct <= 8 => 4,
            $pct <= 12 => 8,
            $pct <= 15 => 10,
            default => 12,
        };
    }

    protected function scoreWriteOffRatio(float $ratio): int
    {
        $pct = $ratio * 100;

        return match (true) {
            $pct <= 1 => 0,
            $pct <= 3 => 4,
            $pct <= 5 => 8,
            default => 12,
        };
    }

    protected function scoreSavingsAdjustment(float $ratio): int
    {
        $pct = $ratio * 100;

        return match (true) {
            $pct <= 2 => 0,
            $pct <= 5 => 3,
            $pct <= 10 => 6,
            default => 10,
        };
    }

    protected function scoreNplr(float $nplr): int
    {
        $pct = $nplr * 100;

        return match (true) {
            $pct <= 5 => 0,
            $pct <= 10 => 4,
            $pct <= 15 => 8,
            default => 12,
        };
    }

    protected function scoreDistance(bool $farFromOffice): int
    {
        return $farFromOffice ? 2 : 0;
    }

    protected function scoreBmAbm(bool $hasBoth): int
    {
        return $hasBoth ? 0 : 6;
    }

    protected function scoreSpecialAudit(bool $hadSpecialAudit): int
    {
        return $hadSpecialAudit ? 0 : 4;
    }

    protected function categorize(int $score): string
    {
        return match (true) {
            $score <= 25 => 'Low Risk',
            $score <= 45 => 'Medium Risk',
            $score <= 65 => 'High Risk',
            default => 'Significant Risk',
        };
    }
}
