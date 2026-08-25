<?php

use App\Models\AuditPlan;
use App\Models\Employee;
use App\Models\MonthlyAssignment;
use App\Services\MonthlyWorklistService;
use Carbon\Carbon;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$fyLabel = $argv[1] ?? '2026-2027';
$monthIndex = isset($argv[2]) ? (int) $argv[2] : 4;

$plan = AuditPlan::query()->where('fy_label', $fyLabel)->firstOrFail();
$worklist = app(MonthlyWorklistService::class);

echo "Resolving overlapping allocations for {$fyLabel} month_index={$monthIndex}...\n";
$result = $worklist->resolveOverlappingAllocations($plan, $monthIndex);
echo "Fixed={$result['fixed']} reassigned={$result['reassigned']} unassigned={$result['unassigned']}\n";

// Verify: no employee should have overlapping ranges.
$assignments = MonthlyAssignment::query()
    ->whereHas('workItem', fn ($q) => $q->where('audit_plan_id', $plan->id)->where('month_index', $monthIndex))
    ->with(['visitors', 'employee', 'workItem'])
    ->get();

$byEmp = [];
foreach ($assignments as $a) {
    foreach ($a->visitorList() as $emp) {
        $byEmp[(int) $emp->id][] = $a;
    }
}

$remaining = 0;
foreach ($byEmp as $empId => $list) {
    $name = Employee::find($empId)?->name ?? $empId;
    usort($list, fn ($a, $b) => $a->start_date <=> $b->start_date ?: $a->id <=> $b->id);
    for ($i = 0; $i < count($list); $i++) {
        for ($j = $i + 1; $j < count($list); $j++) {
            $a = $list[$i];
            $b = $list[$j];
            if ($a->start_date->toDateString() <= $b->end_date->toDateString()
                && $a->end_date->toDateString() >= $b->start_date->toDateString()) {
                $remaining++;
                echo "STILL CONFLICT {$name}: {$a->workItem->entity_label} [{$a->start_date->toDateString()}..{$a->end_date->toDateString()}] vs {$b->workItem->entity_label} [{$b->start_date->toDateString()}..{$b->end_date->toDateString()}]\n";
            }
        }
    }
}

echo $remaining === 0 ? "Verification OK — no same-person overlapping dates.\n" : "Remaining conflicts: {$remaining}\n";
