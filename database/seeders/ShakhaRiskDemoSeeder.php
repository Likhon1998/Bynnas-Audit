<?php

namespace Database\Seeders;

use App\Models\Shakha;
use App\Models\ShakhaRiskAssessment;
use Illuminate\Database\Seeder;

class ShakhaRiskDemoSeeder extends Seeder
{
    public function run(): void
    {
        $month = (int) now('Asia/Dhaka')->month;
        $year = (int) now('Asia/Dhaka')->year;

        $profiles = [
            ...$this->profiles('Significant Risk', [68, 72, 76, 80]),
            ...$this->profiles('High Risk', [48, 50, 52, 54, 56, 58, 60, 62, 64, 65]),
            ...$this->profiles('Medium Risk', [28, 30, 32, 34, 36, 38, 40, 42, 44, 45, 33, 37, 41, 29, 35, 39, 43, 31]),
            ...$this->profiles('Low Risk', [12, 14, 16, 18, 20, 22, 24, 25, 13, 15, 17, 19, 21, 23, 11, 10]),
        ];

        $alreadyAssessed = ShakhaRiskAssessment::query()
            ->where('assessment_month', $month)
            ->where('assessment_year', $year)
            ->pluck('shakha_id');

        $shakhas = Shakha::query()
            ->where('status', 'active')
            ->whereNotIn('id', $alreadyAssessed)
            ->orderBy('name')
            ->limit(count($profiles))
            ->get();

        $created = 0;
        foreach ($shakhas as $index => $shakha) {
            $profile = $profiles[$index];
            $severity = match ($profile['risk_category']) {
                'Significant Risk' => 4,
                'High Risk' => 3,
                'Medium Risk' => 2,
                default => 1,
            };

            $expenditure = 700000 + ($index * 17500);
            $incomeRatio = match ($severity) {
                4 => 0.82,
                3 => 0.94,
                2 => 1.06,
                default => 1.25,
            };

            ShakhaRiskAssessment::query()->create([
                'shakha_id' => $shakha->id,
                'assessment_month' => $month,
                'assessment_year' => $year,
                'distance_from_area_office_km' => $severity >= 3 ? 1 : 0,
                'total_income' => round($expenditure * $incomeRatio, 2),
                'total_expenditure' => $expenditure,
                'write_off_principal_amount' => match ($severity) {
                    4 => 180000 + ($index * 2500),
                    3 => 85000 + ($index * 1500),
                    2 => 25000 + ($index * 500),
                    default => 0,
                },
                'savings_adjustment_amount' => match ($severity) {
                    4 => 95000 + ($index * 1200),
                    3 => 42000 + ($index * 800),
                    2 => 12000 + ($index * 250),
                    default => 0,
                },
                'overdue_principal_31_365_days' => match ($severity) {
                    4 => 1500000 + ($index * 30000),
                    3 => 800000 + ($index * 20000),
                    2 => 300000 + ($index * 10000),
                    default => 75000 + ($index * 2500),
                },
                'has_both_bm_and_abm' => $severity < 3,
                'special_audit_last_two_years' => $severity === 1,
                'total_weighted_score' => $profile['score'],
                'risk_category' => $profile['risk_category'],
            ]);

            $created++;
        }

        $this->command?->info(
            "Created {$created} logical demo risk assessments for "
            .date('F', mktime(0, 0, 0, $month, 1))." {$year}. Existing assessments were preserved."
        );
    }

    /**
     * @param  list<int>  $scores
     * @return list<array{risk_category:string,score:int}>
     */
    private function profiles(string $category, array $scores): array
    {
        return array_map(
            fn (int $score) => ['risk_category' => $category, 'score' => $score],
            $scores
        );
    }
}
