<?php

namespace App\Console\Commands;

use App\Models\ShakhaRiskAssessment;
use App\Services\RiskAssessmentService;
use Illuminate\Console\Command;
use Throwable;

class RecalculateShakhaRiskCommand extends Command
{
    protected $signature = 'risk:recalculate
                            {--dry-run : Show what would change without saving}
                            {--delete-incomplete : Delete risk rows whose KPI is not ready for scoring}';

    protected $description = 'Recalculate shakha risk scores from annual KPI + stored manual inputs using Risk analysis laws';

    public function handle(RiskAssessmentService $riskAssessments): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $deleteIncomplete = (bool) $this->option('delete-incomplete');
        $updated = 0;
        $deleted = 0;
        $skipped = 0;

        $rows = ShakhaRiskAssessment::query()
            ->with('shakha')
            ->orderBy('id')
            ->get();

        $this->info(($dryRun ? '[dry-run] ' : '').'Recalculating '.$rows->count().' risk assessment(s)…');

        foreach ($rows as $assessment) {
            $shakha = $assessment->shakha;
            if (! $shakha) {
                $skipped++;
                continue;
            }

            $fy = $riskAssessments->fyForPeriod(
                (int) $assessment->assessment_month,
                (int) $assessment->assessment_year
            );
            $kpi = $riskAssessments->findAnnualKpi($shakha, $fy->label);

            if (! $kpi?->isReadyForRisk()) {
                if ($deleteIncomplete) {
                    $this->line("  delete #{$assessment->id} {$shakha->name} — KPI not ready for FY {$fy->label}");
                    if (! $dryRun) {
                        $assessment->delete();
                    }
                    $deleted++;
                } else {
                    $this->line("  skip #{$assessment->id} {$shakha->name} — KPI not ready for FY {$fy->label}");
                    $skipped++;
                }
                continue;
            }

            $before = (int) $assessment->total_weighted_score.' / '.$assessment->risk_category;

            try {
                if ($dryRun) {
                    // Clone scoring path without needing a second service method: compute via temporary call
                    // is avoided in dry-run by describing inputs only.
                    $this->line("  would recalc #{$assessment->id} {$shakha->name} (was {$before})");
                    $updated++;
                    continue;
                }

                $fresh = $riskAssessments->calculateRiskScore(
                    $shakha,
                    (int) $assessment->assessment_month,
                    (int) $assessment->assessment_year,
                    [
                        'total_income' => $assessment->total_income,
                        'total_expenditure' => $assessment->total_expenditure,
                        'write_off_principal_amount' => $assessment->write_off_principal_amount,
                        'savings_adjustment_amount' => $assessment->savings_adjustment_amount,
                        'distance_from_area_office_km' => ((int) $assessment->distance_from_area_office_km) > 0,
                        'has_both_bm_and_abm' => (bool) $assessment->has_both_bm_and_abm,
                        'special_audit_last_two_years' => (bool) $assessment->special_audit_last_two_years,
                    ]
                );

                $after = (int) $fresh->total_weighted_score.' / '.$fresh->risk_category;
                $this->line("  #{$assessment->id} {$shakha->name}: {$before} → {$after}");
                $updated++;
            } catch (Throwable $e) {
                $this->error("  fail #{$assessment->id} {$shakha->name}: ".$e->getMessage());
                $skipped++;
            }
        }

        $this->newLine();
        $this->info("Done. updated={$updated} deleted={$deleted} skipped={$skipped}");

        return self::SUCCESS;
    }
}
