<?php

namespace Database\Seeders;

use App\Models\Shakha;
use App\Models\ShakhaAnnualKpi;
use App\Models\ShakhaRiskAssessment;
use App\Services\RiskAssessmentService;
use App\Support\FinancialYear;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ShakhaRiskDemoSeeder extends Seeder
{
    public function run(): void
    {
        $month = (int) now('Asia/Dhaka')->month;
        $year = (int) now('Asia/Dhaka')->year;
        $fy = FinancialYear::current(now('Asia/Dhaka'));

        $profiles = [
            ...array_fill(0, 4, 'Significant Risk'),
            ...array_fill(0, 10, 'High Risk'),
            ...array_fill(0, 18, 'Medium Risk'),
            ...array_fill(0, 16, 'Low Risk'),
        ];

        $removed = $this->removeLegacyDirectScoreRows($month, $year);

        $alreadyAssessed = ShakhaRiskAssessment::query()
            ->where('assessment_month', $month)
            ->where('assessment_year', $year)
            ->pluck('shakha_id');

        $shakhas = Shakha::query()
            ->where('status', 'active')
            ->whereNotIn('id', $alreadyAssessed)
            ->whereDoesntHave('annualKpis', fn ($q) => $q->where('fy_label', $fy->label))
            ->orderBy('name')
            ->limit(count($profiles))
            ->get();

        if ($shakhas->count() < count($profiles)) {
            throw new RuntimeException(
                'Need '.count($profiles).' active branches without FY '.$fy->label
                .' KPI/risk data, but only '.$shakhas->count().' are available. Existing production data was not overwritten.'
            );
        }

        $service = app(RiskAssessmentService::class);
        $created = 0;

        foreach ($shakhas as $index => $shakha) {
            $expectedCategory = $profiles[$index];

            DB::transaction(function () use (
                $service,
                $shakha,
                $fy,
                $month,
                $year,
                $index,
                $expectedCategory,
                &$created
            ): void {
                [$kpi, $manualInputs] = $this->logicalInputs($shakha, $fy->label, $index, $expectedCategory);
                ShakhaAnnualKpi::query()->create($kpi);

                $assessment = $service->calculateRiskScore($shakha, $month, $year, $manualInputs);
                if ($assessment->risk_category !== $expectedCategory) {
                    throw new RuntimeException(
                        "{$shakha->name}: expected {$expectedCategory}, calculated "
                        ."{$assessment->risk_category} (score {$assessment->total_weighted_score})."
                    );
                }

                $created++;
            });
        }

        $this->command?->info(
            "Removed {$removed} legacy direct-score rows. Created {$created} FY {$fy->label} KPI records "
            .'and calculated their risk through RiskAssessmentService for '
            .date('F', mktime(0, 0, 0, $month, 1))." {$year}. Existing production KPI/risk data was preserved."
        );
    }

    /**
     * Build internally consistent KPI and manual inputs for one target band.
     *
     * @return array{0:array<string,mixed>,1:array<string,mixed>}
     */
    private function logicalInputs(Shakha $shakha, string $fyLabel, int $index, string $category): array
    {
        $loanOutstanding = 10000000 + ($index * 125000);
        $recoverable = 2400000 + ($index * 30000);
        $loanRecovery = 6500000 + ($index * 80000);
        $expenditure = 900000 + ($index * 12500);

        $ratios = match ($category) {
            'Significant Risk' => [
                'otr' => 0.82, 'oss' => 0.82, 'od' => 0.18, 'write_off' => 0.06,
                'savings_adjustment' => 0.12, 'surplus' => -180000, 'far' => true,
                'has_bm_abm' => false, 'special_audit' => false,
            ],
            'High Risk' => [
                'otr' => 0.92, 'oss' => 0.94, 'od' => 0.13, 'write_off' => 0.04,
                'savings_adjustment' => 0.07, 'surplus' => 90000, 'far' => true,
                'has_bm_abm' => true, 'special_audit' => true,
            ],
            'Medium Risk' => [
                'otr' => 0.93, 'oss' => 1.05, 'od' => 0.08, 'write_off' => 0.02,
                'savings_adjustment' => 0.03, 'surplus' => 160000, 'far' => true,
                'has_bm_abm' => true, 'special_audit' => true,
            ],
            default => [
                'otr' => 0.99, 'oss' => 1.25, 'od' => 0.03, 'write_off' => 0.005,
                'savings_adjustment' => 0.01, 'surplus' => 260000, 'far' => false,
                'has_bm_abm' => true, 'special_audit' => true,
            ],
        };

        $overdue = round($loanOutstanding * $ratios['od'], 2);

        return [
            [
                'shakha_id' => $shakha->id,
                'fy_label' => $fyLabel,
                'fo_count' => 8 + ($index % 8),
                'total_samities' => 95 + ($index % 40),
                'total_members' => 3800 + ($index * 35),
                'total_borrowers' => 2400 + ($index * 25),
                'total_od_borrowers' => 80 + ($index % 45),
                'fy_members_admission' => 260 + ($index % 90),
                'fy_members_dropout' => 80 + ($index % 35),
                'fy_disbursement_borrowers' => 1700 + ($index * 15),
                'fy_fully_repayment_borrowers' => 520 + ($index * 8),
                'fy_savings_collection' => 4200000 + ($index * 55000),
                'fy_savings_withdrawal' => 2800000 + ($index * 35000),
                'savings_balance' => 7500000 + ($index * 90000),
                'fy_disbursement_amount' => 18000000 + ($index * 225000),
                'fy_loan_recovery' => $loanRecovery,
                'loan_outstanding' => $loanOutstanding,
                'recoverable' => $recoverable,
                'current_recovery' => round($recoverable * $ratios['otr'], 2),
                'due_recovery' => round($recoverable * (1 - $ratios['otr']), 2),
                'total_od_taka' => $overdue,
                'due_loanee_loan_outstanding' => $overdue,
                'own_fund_until_prior_june' => 3200000 + ($index * 40000),
                'surplus_deficit_fy' => $ratios['surplus'],
                'new_due' => round($overdue * 0.18, 2),
                'due_increase_this_month' => round($overdue * 0.04, 2),
            ],
            [
                'total_income' => round($expenditure * $ratios['oss'], 2),
                'total_expenditure' => $expenditure,
                'write_off_principal_amount' => round($loanOutstanding * $ratios['write_off'], 2),
                'savings_adjustment_amount' => round($loanRecovery * $ratios['savings_adjustment'], 2),
                'distance_from_area_office_km' => $ratios['far'],
                'has_both_bm_and_abm' => $ratios['has_bm_abm'],
                'special_audit_last_two_years' => $ratios['special_audit'],
            ],
        ];
    }

    /**
     * Remove only rows matching the exact fingerprint of the old direct-score demo seeder.
     */
    private function removeLegacyDirectScoreRows(int $month, int $year): int
    {
        $legacyScores = [68, 72, 76, 80, 48, 50, 52, 54, 56, 58, 60, 62, 64, 65,
            28, 30, 32, 34, 36, 38, 40, 42, 44, 45, 33, 37, 41, 29, 35, 39, 43, 31,
            12, 14, 16, 18, 20, 22, 24, 25, 13, 15, 17, 19, 21, 23, 11, 10];

        $ids = ShakhaRiskAssessment::query()
            ->where('assessment_month', $month)
            ->where('assessment_year', $year)
            ->whereIn('total_weighted_score', $legacyScores)
            ->get()
            ->filter(function (ShakhaRiskAssessment $row): bool {
                $expenditure = (float) $row->total_expenditure;
                if ($expenditure < 700000 || fmod($expenditure - 700000, 17500) !== 0.0) {
                    return false;
                }

                $ratio = $expenditure > 0 ? round((float) $row->total_income / $expenditure, 2) : 0;

                return in_array($ratio, [0.82, 0.94, 1.06, 1.25], true);
            })
            ->pluck('id');

        return $ids->isEmpty()
            ? 0
            : ShakhaRiskAssessment::query()->whereIn('id', $ids)->delete();
    }
}
