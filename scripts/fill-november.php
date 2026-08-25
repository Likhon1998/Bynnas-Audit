<?php

use App\Models\AuditPlan;
use App\Models\MonthlyWorkItem;
use App\Services\MonthlyWorklistService;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$plan = AuditPlan::query()->where('fy_label', '2026-2027')->firstOrFail();
$monthIndex = 4;
$worklist = app(MonthlyWorklistService::class);

$before = MonthlyWorkItem::query()
    ->where('audit_plan_id', $plan->id)
    ->where('month_index', $monthIndex)
    ->selectRaw('status, count(*) as c')
    ->groupBy('status')
    ->pluck('c', 'status');
echo 'Before: '.$before->toJson().PHP_EOL;

$result = $worklist->bulkAllocateMonth($plan, $monthIndex, null, true);
echo 'Bulk: assigned='.$result['assigned']
    .' skipped='.$result['skipped']
    .' total='.$result['total']
    .' cleared='.$result['cleared']
    .' repacked='.(($result['repacked'] ?? false) ? 'yes' : 'no')
    .PHP_EOL;

$after = MonthlyWorkItem::query()
    ->where('audit_plan_id', $plan->id)
    ->where('month_index', $monthIndex)
    ->selectRaw('status, count(*) as c')
    ->groupBy('status')
    ->pluck('c', 'status');
echo 'After: '.$after->toJson().PHP_EOL;

$durations = MonthlyWorkItem::query()
    ->where('audit_plan_id', $plan->id)
    ->where('month_index', $monthIndex)
    ->where('status', 'assigned')
    ->whereHas('assignment')
    ->with('assignment')
    ->get()
    ->groupBy(fn ($i) => (int) $i->assignment->duration_days)
    ->map->count()
    ->sortKeys();
echo 'Duration days: '.$durations->toJson().PHP_EOL;

$conflicts = $worklist->resolveOverlappingAllocations($plan, $monthIndex);
echo 'Post-check conflicts fixed='.$conflicts['fixed'].PHP_EOL;

$left = (int) MonthlyWorkItem::query()
    ->where('audit_plan_id', $plan->id)
    ->where('month_index', $monthIndex)
    ->where('status', 'unassigned')
    ->count();
echo $left === 0 ? "SUCCESS: all November offices allocated.\n" : "Still unassigned: {$left}\n";
