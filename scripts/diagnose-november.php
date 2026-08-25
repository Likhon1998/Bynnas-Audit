<?php

use App\Models\AuditPlan;
use App\Models\Employee;
use App\Models\MonthlyAssignment;
use App\Models\MonthlyWorkItem;
use App\Support\FinancialYear;
use App\Services\WorkingCalendarService;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$plan = AuditPlan::query()->where('fy_label', '2026-2027')->firstOrFail();
$fy = FinancialYear::fromLabel($plan->fy_label);
$monthIndex = 4;
$monthStart = $fy->dateForMonthIndex($monthIndex);
$monthEnd = $monthStart->copy()->endOfMonth();

$cal = app(WorkingCalendarService::class);
$wd = $cal->workingDates($monthStart, $monthEnd, false);
echo 'Working days in Nov: '.count($wd).PHP_EOL;

$items = MonthlyWorkItem::query()
    ->where('audit_plan_id', $plan->id)
    ->where('month_index', $monthIndex)
    ->with(['assignment.visitors'])
    ->get();

echo 'Items: '.$items->count().' assigned='.$items->where('status','assigned')->count().' unassigned='.$items->where('status','unassigned')->count().PHP_EOL;

$employees = Employee::query()->orderBy('id')->get();
$totalBusyWd = 0;
$totalFreeWd = 0;
foreach ($employees as $emp) {
    $busyDates = [];
    foreach ($items as $item) {
        $a = $item->assignment;
        if (! $a || ! $a->start_date || ! $a->end_date) {
            continue;
        }
        $on = (int) $a->employee_id === (int) $emp->id || $a->visitors->contains('id', $emp->id);
        if (! $on) {
            continue;
        }
        foreach ($wd as $d) {
            if ($d >= $a->start_date->toDateString() && $d <= $a->end_date->toDateString()) {
                $busyDates[$d] = true;
            }
        }
    }
    $busy = count($busyDates);
    $free = count($wd) - $busy;
    $totalBusyWd += $busy;
    $totalFreeWd += $free;
    echo $emp->name.' busy_wd='.$busy.' free_wd='.$free.PHP_EOL;
}
echo 'Capacity free_wd_total='.$totalFreeWd.' busy_wd_total='.$totalBusyWd.PHP_EOL;
echo 'Need at least '.$items->where('status','unassigned')->count().' free slots for remaining (1wd each)'.PHP_EOL;

// Show if any employee has contiguous free working days
foreach ($employees as $emp) {
    $free = [];
    foreach ($wd as $d) {
        $blocked = false;
        foreach ($items as $item) {
            $a = $item->assignment;
            if (! $a || ! $a->start_date) {
                continue;
            }
            $on = (int) $a->employee_id === (int) $emp->id || $a->visitors->contains('id', $emp->id);
            if (! $on) {
                continue;
            }
            if ($d >= $a->start_date->toDateString() && $d <= $a->end_date->toDateString()) {
                $blocked = true;
                break;
            }
        }
        if (! $blocked) {
            $free[] = $d;
        }
    }
    if ($free !== []) {
        echo 'FREE '.$emp->name.': '.implode(',', $free).PHP_EOL;
    }
}

$multi = MonthlyAssignment::query()
    ->whereHas('workItem', fn ($q) => $q->where('audit_plan_id', $plan->id)->where('month_index', $monthIndex))
    ->withCount('visitors')
    ->having('visitors_count', '>', 1)
    ->count();
echo "Joint visits (2+ visitors): {$multi}".PHP_EOL;
