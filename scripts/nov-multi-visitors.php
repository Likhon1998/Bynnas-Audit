<?php

use App\Models\AuditPlan;
use App\Models\MonthlyWorkItem;
use App\Services\MonthlyWorklistService;
use Carbon\Carbon;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$worklist = app(MonthlyWorklistService::class);
$plan = AuditPlan::query()->where('fy_label', '2026-2027')->firstOrFail();

// November shakha visits to convert into multi-person (joint) allocations.
// Format: work_item_id => [primary, second, optional third]
$updates = [
    228 => [8, 9],   // Barishal Sadar Branch 4 — Ayesha + Kamal
    211 => [11, 12], // Bhola Branch 2 — Shahidul + Rina
    200 => [13, 14], // Bogura Branch 1 — Arif + Mitu
    295 => [16, 17], // Chattogram Metro Branch 1 — Sajid + Nabila
    214 => [18, 19], // Chattogram Metro Branch 2 — Omar + Tania
    235 => [14, 15], // Bogura Branch 5 — Mitu + Jahidul
];

// Prefer a few well-known Dhaka branches if present.
$extra = MonthlyWorkItem::query()
    ->where('audit_plan_id', $plan->id)
    ->where('month_index', 4)
    ->where('status', 'assigned')
    ->where(function ($q) {
        $q->where('entity_label', 'like', '%Mirpur%')
            ->orWhere('entity_label', 'like', '%Motijheel%')
            ->orWhere('entity_label', 'like', '%Dhaka South Branch 1%');
    })
    ->with('assignment')
    ->get();

$pairPool = [[1, 2], [3, 4], [5, 6], [7, 8]];
foreach ($extra as $i => $item) {
    if (! isset($updates[$item->id]) && $item->assignment) {
        $updates[$item->id] = $pairPool[$i % count($pairPool)];
    }
}

$ok = 0;
foreach ($updates as $itemId => $visitorIds) {
    $item = MonthlyWorkItem::query()->with('assignment')->find($itemId);
    if (! $item?->assignment) {
        echo "SKIP missing item {$itemId}\n";
        continue;
    }

    $a = $item->assignment;
    try {
        $worklist->assign($item, [
            'employee_ids' => $visitorIds,
            'start_date' => $a->start_date->toDateString(),
            'end_date' => $a->end_date->toDateString(),
            'count_off_days' => (bool) $a->count_off_days,
            'override_conflict' => true,
            'remarks' => trim(($a->remarks ? $a->remarks.' · ' : '').'Joint visit (multi auditor)'),
            'purpose' => $a->purpose ?: $item->activityType?->name,
        ], null);

        $item->refresh()->load('assignment.visitors');
        echo 'OK  '.$item->entity_label.' → '.$item->assignment->visitorNames(', ').' ('.$a->start_date->toDateString().' → '.$a->end_date->toDateString().")\n";
        $ok++;
    } catch (Throwable $e) {
        echo 'FAIL '.$item->entity_label.': '.$e->getMessage()."\n";
    }
}

echo "\nUpdated {$ok} November entries with multiple auditors.\n";
