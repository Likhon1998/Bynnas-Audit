<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('visits:fix-overlaps {--fy=} {--month=}', function () {
    $worklist = app(\App\Services\MonthlyWorklistService::class);
    $fy = $this->option('fy');
    $plan = $worklist->resolvePlan(is_string($fy) && $fy !== '' ? $fy : null);

    $months = $this->option('month') !== null && $this->option('month') !== ''
        ? [(int) $this->option('month')]
        : range(0, 11);

    $totalFixed = 0;
    $totalUnassigned = 0;
    $totalReassigned = 0;

    foreach ($months as $monthIndex) {
        $monthIndex = max(0, min(11, (int) $monthIndex));
        $result = $worklist->resolveOverlappingAllocations($plan, $monthIndex, null);
        $totalFixed += $result['fixed'];
        $totalUnassigned += $result['unassigned'];
        $totalReassigned += $result['reassigned'];
        if ($result['fixed'] > 0) {
            $this->info("FY {$plan->fy_label} month {$monthIndex}: fixed {$result['fixed']} (reassigned {$result['reassigned']}, unassigned {$result['unassigned']})");
        }
    }

    if ($totalFixed === 0) {
        $this->info("No same-person overlaps found for FY {$plan->fy_label}.");
    } else {
        $this->info("Done. Fixed {$totalFixed} overlaps (reassigned {$totalReassigned}, unassigned {$totalUnassigned}).");
    }
})->purpose('Remove illegal same-person overlapping monthly visit allocations');
