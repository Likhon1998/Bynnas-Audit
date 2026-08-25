<?php

use App\Models\AuditPlan;
use App\Models\Employee;
use App\Models\MonthlyWorkItem;
use App\Services\MonthlyWorklistService;
use App\Services\WorkingCalendarService;
use App\Support\FinancialYear;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$fyLabel = $argv[1] ?? '2026-2027';
$monthIndex = isset($argv[2]) ? (int) $argv[2] : 4; // Nov = 4 in Jul–Jun FY

$worklist = app(MonthlyWorklistService::class);
$calendar = app(WorkingCalendarService::class);

$plan = AuditPlan::query()->where('fy_label', $fyLabel)->firstOrFail();
$fy = FinancialYear::fromLabel($plan->fy_label);
$monthMeta = $fy->months()[$monthIndex];
$monthStart = $fy->dateForMonthIndex($monthIndex);
$monthEnd = $monthStart->copy()->endOfMonth();

echo "FY {$fyLabel} · {$monthMeta['label']} {$monthMeta['year']} (month_index={$monthIndex})\n";

$worklist->refreshFromYearly($plan, $monthIndex);

$items = MonthlyWorkItem::query()
    ->where('audit_plan_id', $plan->id)
    ->where('month_index', $monthIndex)
    ->where('status', MonthlyWorkItem::STATUS_UNASSIGNED)
    ->with('activityType')
    ->orderBy('category')
    ->orderBy('entity_label')
    ->get();

$employees = Employee::query()->orderBy('id')->get();

echo 'Unassigned offices: '.$items->count()."\n";
echo 'Auditors: '.$employees->count()."\n";

if ($items->isEmpty()) {
    echo "Nothing to allocate.\n";
    exit(0);
}

if ($employees->isEmpty()) {
    echo "No employees in organogram.\n";
    exit(1);
}

// Spread visit windows across the month on working days so auditors don't all collide.
$workingDates = $calendar->workingDates($monthStart, $monthEnd, false);
if (count($workingDates) < 1) {
    echo "No working days in month.\n";
    exit(1);
}

$visitLength = 5; // 5 working days per visit window
$slots = [];
$cursor = 0;
while ($cursor < count($workingDates)) {
    $startIdx = $cursor;
    $endIdx = min($cursor + $visitLength - 1, count($workingDates) - 1);
    $slots[] = [
        'start' => $workingDates[$startIdx],
        'end' => $workingDates[$endIdx],
    ];
    $cursor += $visitLength;
    if ($endIdx === count($workingDates) - 1) {
        break;
    }
}
if ($slots === []) {
    $slots[] = ['start' => $workingDates[0], 'end' => $workingDates[min(4, count($workingDates) - 1)]];
}

$assigned = 0;
$skipped = 0;
$errors = [];

DB::beginTransaction();
try {
    foreach ($items->values() as $i => $item) {
        $slot = $slots[$i % count($slots)];
        $employee = $employees[$i % $employees->count()];

        // Prefer an auditor free on this slot; fall back to round-robin.
        $picked = null;
        for ($offset = 0; $offset < $employees->count(); $offset++) {
            $candidate = $employees[($i + $offset) % $employees->count()];
            $conflicts = $worklist->findConflicts(
                (int) $candidate->id,
                Carbon::parse($slot['start']),
                Carbon::parse($slot['end'])
            );
            if ($conflicts->isEmpty()) {
                $picked = $candidate;
                break;
            }
        }

        if (! $picked) {
            // Last resort: assign with override so November offices are covered.
            $picked = $employee;
            $override = true;
        } else {
            $override = false;
        }

        try {
            $worklist->assign($item, [
                'employee_ids' => [$picked->id],
                'start_date' => $slot['start'],
                'end_date' => $slot['end'],
                'count_off_days' => false,
                'override_conflict' => $override,
                'remarks' => 'Bulk allocated for '.$monthMeta['label'].' '.$monthMeta['year'],
            ], null);
            $assigned++;
            echo "OK  {$item->entity_label} → {$picked->name} ({$slot['start']} → {$slot['end']})".($override ? ' [override]' : '')."\n";
        } catch (Throwable $e) {
            $skipped++;
            $errors[] = $item->entity_label.': '.$e->getMessage();
            echo "SKIP {$item->entity_label}: {$e->getMessage()}\n";
        }
    }
    DB::commit();
} catch (Throwable $e) {
    DB::rollBack();
    echo 'FAILED: '.$e->getMessage()."\n";
    exit(1);
}

echo "\nDone. Assigned={$assigned}, skipped={$skipped}\n";
if ($errors) {
    echo "Errors:\n- ".implode("\n- ", $errors)."\n";
}
