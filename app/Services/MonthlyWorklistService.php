<?php

namespace App\Services;

use App\Models\ActivityType;
use App\Models\Area;
use App\Models\AssignmentStatusLog;
use App\Models\AuditPlan;
use App\Models\AuditPolicy;
use App\Models\Employee;
use App\Models\HqDepartment;
use App\Models\MonthlyAssignment;
use App\Models\MonthlyWorkItem;
use App\Models\PlanSchedule;
use App\Models\ProjectLocation;
use App\Models\Shakha;
use App\Models\VisitExecution;
use App\Support\FinancialYear;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MonthlyWorklistService
{
    public function __construct(
        private WorkingCalendarService $calendar,
    ) {}

    /** @var array<string, string> */
    protected array $categoryActivitySlug = [
        AuditPolicy::CATEGORY_SHAKHA => 'audit',
        AuditPolicy::CATEGORY_AREA => 'area-office',
        AuditPolicy::CATEGORY_PKSF => 'pksf-maternity',
        AuditPolicy::CATEGORY_HQ => 'hq-concern',
        AuditPolicy::CATEGORY_PROJECT_AUDIT => 'project-audit',
        AuditPolicy::CATEGORY_PROJECT_MONITORING => 'monitoring',
    ];

    public function resolvePlan(?string $fyLabel): AuditPlan
    {
        if ($fyLabel) {
            $plan = AuditPlan::query()->where('fy_label', $fyLabel)->first();
            if ($plan) {
                return $plan;
            }
        }

        $plan = AuditPlan::query()->orderByDesc('start_date')->first();
        if (! $plan) {
            throw new InvalidArgumentException('No annual audit plan found. Generate a yearly plan first.');
        }

        return $plan;
    }

    /**
     * Upsert monthly work items from yearly PlanSchedule for one FY month.
     * Does not modify yearly schedules.
     */
    public function refreshFromYearly(AuditPlan $plan, int $monthIndex, ?int $userId = null): array
    {
        if ($monthIndex < 0 || $monthIndex > 11) {
            throw new InvalidArgumentException('Invalid month index.');
        }

        $schedules = PlanSchedule::query()
            ->where('audit_plan_id', $plan->id)
            ->where('month_index', $monthIndex)
            ->with([
                'schedulable' => function ($morphTo) {
                    $morphTo->morphWith([
                        ProjectLocation::class => ['project'],
                    ]);
                },
            ])
            ->get();

        $created = 0;
        $updated = 0;
        $activityCache = ActivityType::query()->get()->keyBy('slug');

        DB::transaction(function () use ($schedules, $plan, $monthIndex, $userId, $activityCache, &$created, &$updated) {
            foreach ($schedules as $schedule) {
                $activity = $this->activityForCategory($schedule->category, $activityCache);
                if (! $activity) {
                    continue;
                }

                $item = MonthlyWorkItem::query()->updateOrCreate(
                    [
                        'audit_plan_id' => $plan->id,
                        'month_index' => $monthIndex,
                        'category' => $schedule->category,
                        'schedulable_type' => $schedule->schedulable_type,
                        'schedulable_id' => $schedule->schedulable_id,
                        'source' => MonthlyWorkItem::SOURCE_YEARLY,
                    ],
                    [
                        'fy_label' => $plan->fy_label,
                        'activity_type_id' => $activity->id,
                        'plan_schedule_id' => $schedule->id,
                        'entity_label' => $this->entityLabel($schedule->schedulable, $schedule->schedulable_type),
                        'created_by' => $userId,
                    ]
                );

                if ($item->wasRecentlyCreated) {
                    $created++;
                    if (! $item->assignment) {
                        $item->update(['status' => MonthlyWorkItem::STATUS_UNASSIGNED]);
                    }
                } else {
                    $updated++;
                    if ($item->assignment && $item->status !== MonthlyWorkItem::STATUS_ASSIGNED) {
                        $item->update(['status' => MonthlyWorkItem::STATUS_ASSIGNED]);
                    }
                }
            }
        });

        return compact('created', 'updated') + ['total' => $schedules->count()];
    }

    public function workItemsForMonth(AuditPlan $plan, int $monthIndex): Collection
    {
        return MonthlyWorkItem::query()
            ->where('audit_plan_id', $plan->id)
            ->where('month_index', $monthIndex)
            ->with([
                'activityType',
                'assignment.employee.position',
                'assignment.visitors.position',
                'assignment.execution',
                'schedulable',
            ])
            ->orderBy('category')
            ->orderBy('entity_label')
            ->get();
    }

    /**
     * Auto-allocate unassigned offices. Visit length is fully flexible (any working-day
     * count — 1, 2, 3, 4, 5, 6, 7, …). When $repack is true (or gaps cannot fit), clears
     * non-completed assignments and rebalances the month so every office is covered
     * without same-person date overlaps.
     *
     * @return array{assigned:int,skipped:int,total:int,cleared:int,repacked:bool}
     */
    public function bulkAllocateMonth(AuditPlan $plan, int $monthIndex, ?int $userId = null, bool $repack = false): array
    {
        $this->refreshFromYearly($plan, $monthIndex, $userId);

        $cleared = 0;
        $repacked = false;

        if (! $repack) {
            $first = $this->allocateUnassignedFlexible($plan, $monthIndex, $userId);
            if ($first['skipped'] === 0) {
                return $first + ['cleared' => 0, 'repacked' => false];
            }
            // Existing long bookings leave no room — rebalance the month.
            $repack = true;
        }

        if ($repack) {
            $cleared = $this->clearMonthForRepack($plan, $monthIndex, $userId);
            $repacked = true;
        }

        $result = $this->allocateUnassignedFlexible($plan, $monthIndex, $userId, coverageFirst: true);

        return $result + ['cleared' => $cleared, 'repacked' => $repacked];
    }

    /**
     * Unassign planned/non-completed visits so the month can be rebalanced.
     * Completed visits are kept locked.
     */
    public function clearMonthForRepack(AuditPlan $plan, int $monthIndex, ?int $userId = null): int
    {
        $items = MonthlyWorkItem::query()
            ->where('audit_plan_id', $plan->id)
            ->where('month_index', $monthIndex)
            ->where('status', MonthlyWorkItem::STATUS_ASSIGNED)
            ->with(['assignment.execution'])
            ->get();

        $cleared = 0;
        foreach ($items as $item) {
            $status = $item->assignment?->execution?->status;
            if ($status === VisitExecution::STATUS_COMPLETED) {
                continue;
            }
            $this->unassign($item, $userId);
            $cleared++;
        }

        return $cleared;
    }

    /**
     * Place every unassigned office onto free staff/date slots.
     * coverageFirst: round-robin staff and share ALL free working days evenly (any length).
     * Otherwise: try longer windows first, then shorter, down to 1 day.
     *
     * @return array{assigned:int,skipped:int,total:int}
     */
    protected function allocateUnassignedFlexible(
        AuditPlan $plan,
        int $monthIndex,
        ?int $userId = null,
        bool $coverageFirst = false,
    ): array {
        $fy = FinancialYear::fromLabel($plan->fy_label);
        $monthStart = $fy->dateForMonthIndex($monthIndex);
        $monthEnd = $monthStart->copy()->endOfMonth();
        $monthLabel = $fy->months()[$monthIndex]['label'].' '.$fy->months()[$monthIndex]['year'];

        $items = MonthlyWorkItem::query()
            ->where('audit_plan_id', $plan->id)
            ->where('month_index', $monthIndex)
            ->where('status', MonthlyWorkItem::STATUS_UNASSIGNED)
            ->with('activityType')
            ->orderByRaw("CASE category WHEN 'shakha_audit' THEN 0 WHEN 'area' THEN 1 ELSE 2 END")
            ->orderBy('entity_label')
            ->get();

        $employees = Employee::query()->orderBy('id')->get();
        if ($items->isEmpty() || $employees->isEmpty()) {
            return ['assigned' => 0, 'skipped' => 0, 'total' => $items->count()];
        }

        $workingDates = $this->calendar->workingDates($monthStart, $monthEnd, false);
        if ($workingDates === []) {
            throw new InvalidArgumentException('No working days available in this month.');
        }

        $maxLen = count($workingDates);
        $lengthOrder = range($maxLen, 1);
        $slotsByLength = [];
        foreach ($lengthOrder as $length) {
            $slots = [];
            for ($startIdx = 0; $startIdx < count($workingDates); $startIdx++) {
                $endIdx = $startIdx + $length - 1;
                if ($endIdx >= count($workingDates)) {
                    break;
                }
                $slots[] = [
                    'start' => $workingDates[$startIdx],
                    'end' => $workingDates[$endIdx],
                    'days' => $length,
                    'start_idx' => $startIdx,
                    'end_idx' => $endIdx,
                ];
            }
            $slotsByLength[$length] = $slots;
        }

        /** @var array<int, list<array{0:string,1:string}>> $busy */
        $busy = [];
        foreach ($employees as $emp) {
            $busy[(int) $emp->id] = [];
            $existing = MonthlyAssignment::query()
                ->whereHas('workItem', fn ($q) => $q->where('audit_plan_id', $plan->id)->where('month_index', $monthIndex))
                ->where(function ($q) use ($emp) {
                    $q->where('employee_id', $emp->id)
                        ->orWhereHas('visitors', fn ($v) => $v->where('employees.id', $emp->id));
                })
                ->get(['start_date', 'end_date']);
            foreach ($existing as $row) {
                $busy[(int) $emp->id][] = [$row->start_date->toDateString(), $row->end_date->toDateString()];
            }
        }

        $isFree = function (int $employeeId, string $start, string $end) use (&$busy): bool {
            foreach ($busy[$employeeId] ?? [] as [$bStart, $bEnd]) {
                if ($start <= $bEnd && $end >= $bStart) {
                    return false;
                }
            }

            return true;
        };

        $busyDayCount = function (int $employeeId) use (&$busy, $workingDates): int {
            $set = [];
            foreach ($busy[$employeeId] ?? [] as [$bStart, $bEnd]) {
                foreach ($workingDates as $d) {
                    if ($d >= $bStart && $d <= $bEnd) {
                        $set[$d] = true;
                    }
                }
            }

            return count($set);
        };

        $placeItem = function (MonthlyWorkItem $item, array $lengths) use (
            $employees,
            $slotsByLength,
            &$busy,
            $isFree,
            $busyDayCount,
            $monthLabel,
            $userId,
        ): bool {
            $staffOrder = $employees->sortBy(fn (Employee $emp) => $busyDayCount((int) $emp->id))->values();

            foreach ($lengths as $length) {
                foreach ($slotsByLength[$length] ?? [] as $slot) {
                    foreach ($staffOrder as $candidate) {
                        $empId = (int) $candidate->id;
                        if (! $isFree($empId, $slot['start'], $slot['end'])) {
                            continue;
                        }
                        if ($this->findConflicts($empId, Carbon::parse($slot['start']), Carbon::parse($slot['end']))->isNotEmpty()) {
                            continue;
                        }

                        try {
                            $this->assign($item, [
                                'employee_ids' => [$candidate->id],
                                'start_date' => $slot['start'],
                                'end_date' => $slot['end'],
                                'count_off_days' => false,
                                'remarks' => 'Bulk allocated for '.$monthLabel.' ('.$slot['days'].' working day'.($slot['days'] > 1 ? 's' : '').')',
                            ], $userId);
                            $busy[$empId][] = [$slot['start'], $slot['end']];

                            return true;
                        } catch (\Throwable) {
                            continue;
                        }
                    }
                }
            }

            return false;
        };

        $assigned = 0;
        $skipped = 0;

        if ($coverageFirst) {
            // Spread offices across staff with mixed loads so visit lengths vary naturally
            // (short and long — 1…N working days, no fixed cap).
            $itemQueue = $items->values()->all();
            $buckets = [];
            foreach ($employees as $emp) {
                $buckets[(int) $emp->id] = [];
            }

            $freeDaysByEmp = [];
            foreach ($employees as $emp) {
                $eid = (int) $emp->id;
                $free = 0;
                foreach ($workingDates as $date) {
                    if ($isFree($eid, $date, $date)) {
                        $free++;
                    }
                }
                $freeDaysByEmp[$eid] = $free;
            }

            // Preferred visit lengths cycle — any length is allowed; this just adds variety.
            $preferredLens = [7, 6, 5, 4, 3, 2, 1];
            $prefIdx = 0;
            $empIds = $employees->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

            while ($itemQueue !== []) {
                $placedThisRound = false;

                foreach ($empIds as $empId) {
                    if ($itemQueue === []) {
                        break;
                    }

                    $free = $freeDaysByEmp[$empId] ?? 0;
                    if ($free < 1) {
                        continue;
                    }

                    $remainingOffices = count($itemQueue);
                    $otherCapacity = 0;
                    foreach ($empIds as $otherId) {
                        if ($otherId === $empId) {
                            continue;
                        }
                        $otherCapacity += $freeDaysByEmp[$otherId] ?? 0;
                    }

                    // Leave enough 1-day slots on other staff for leftover offices.
                    $maxOfficesHere = min($free, $remainingOffices);
                    while ($maxOfficesHere > 1 && ($remainingOffices - $maxOfficesHere) > $otherCapacity) {
                        $maxOfficesHere--;
                    }
                    if ($maxOfficesHere < 1) {
                        continue;
                    }

                    $wantLen = $preferredLens[$prefIdx % count($preferredLens)];
                    $prefIdx++;
                    // How many offices at ~wantLen? (at least 1 day each)
                    $take = min($maxOfficesHere, max(1, intdiv($free, max(1, $wantLen))));
                    while ($take > 1 && ($remainingOffices - $take) > $otherCapacity) {
                        $take--;
                    }
                    $take = max(1, min($take, $maxOfficesHere));

                    for ($t = 0; $t < $take; $t++) {
                        if ($itemQueue === []) {
                            break;
                        }
                        $buckets[$empId][] = array_shift($itemQueue);
                        $freeDaysByEmp[$empId] = max(0, ($freeDaysByEmp[$empId] ?? 0) - 1); // reserve ≥1 day
                    }
                    $placedThisRound = true;
                }

                if (! $placedThisRound) {
                    // Safety: dump leftovers onto anyone with free capacity (1 day each).
                    foreach ($empIds as $empId) {
                        while ($itemQueue !== [] && ($freeDaysByEmp[$empId] ?? 0) > 0) {
                            $buckets[$empId][] = array_shift($itemQueue);
                            $freeDaysByEmp[$empId]--;
                        }
                    }
                    break;
                }
            }

            foreach ($buckets as $empId => $bucket) {
                if ($bucket === []) {
                    continue;
                }

                // Free working-day indices for this employee (skip locked completed bookings).
                $freeIdx = [];
                foreach ($workingDates as $idx => $date) {
                    if ($isFree($empId, $date, $date)) {
                        $freeIdx[] = $idx;
                    }
                }

                $n = count($bucket);
                $available = count($freeIdx);
                if ($available === 0) {
                    $skipped += $n;
                    continue;
                }

                // Share every free day across this person's offices — any length, no max cap.
                $coverable = min($n, $available);
                $base = intdiv($available, $coverable);
                $rem = $available % $coverable;
                $lengths = [];
                for ($j = 0; $j < $coverable; $j++) {
                    $lengths[$j] = $base + ($j < $rem ? 1 : 0);
                }

                $cursor = 0;
                foreach ($bucket as $j => $item) {
                    if ($j >= $coverable) {
                        $skipped++;
                        continue;
                    }

                    $need = $lengths[$j];

                    // Require a contiguous free working-day run (no locked day in between).
                    $span = null;
                    for ($try = $cursor; $try + $need <= count($freeIdx); $try++) {
                        $ok = true;
                        for ($t = 1; $t < $need; $t++) {
                            if ($freeIdx[$try + $t] !== $freeIdx[$try] + $t) {
                                $ok = false;
                                break;
                            }
                        }
                        if ($ok) {
                            $span = $try;
                            break;
                        }
                    }

                    if ($span === null) {
                        // Shrink until a contiguous run fits.
                        $fitted = false;
                        for ($tryNeed = $need; $tryNeed >= 1; $tryNeed--) {
                            for ($try = $cursor; $try + $tryNeed <= count($freeIdx); $try++) {
                                $ok = true;
                                for ($t = 1; $t < $tryNeed; $t++) {
                                    if ($freeIdx[$try + $t] !== $freeIdx[$try] + $t) {
                                        $ok = false;
                                        break;
                                    }
                                }
                                if ($ok) {
                                    $span = $try;
                                    $need = $tryNeed;
                                    $fitted = true;
                                    break 2;
                                }
                            }
                        }
                        if (! $fitted) {
                            $skipped++;
                            continue;
                        }
                    }

                    $startIdx = $freeIdx[$span];
                    $endIdx = $freeIdx[$span + $need - 1];
                    $start = $workingDates[$startIdx];
                    $end = $workingDates[$endIdx];

                    if (! $isFree($empId, $start, $end)
                        || $this->findConflicts($empId, Carbon::parse($start), Carbon::parse($end))->isNotEmpty()) {
                        $skipped++;
                        continue;
                    }

                    try {
                        $this->assign($item, [
                            'employee_ids' => [$empId],
                            'start_date' => $start,
                            'end_date' => $end,
                            'count_off_days' => false,
                            'remarks' => 'Bulk allocated for '.$monthLabel.' ('.$need.' working day'.($need > 1 ? 's' : '').')',
                        ], $userId);
                        $busy[$empId][] = [$start, $end];
                        $assigned++;
                        $cursor = $span + $need;
                    } catch (\Throwable) {
                        $skipped++;
                    }
                }
            }

            // Any leftovers from failed bucket placement: try flexible fill.
            if ($skipped > 0) {
                $flex = $this->allocateUnassignedFlexible($plan, $monthIndex, $userId, coverageFirst: false);
                $assigned += $flex['assigned'];
                $skipped = $flex['skipped'];
            }

            return ['assigned' => $assigned, 'skipped' => $skipped, 'total' => $items->count()];
        }

        foreach ($items as $item) {
            if ($placeItem($item, $lengthOrder)) {
                $assigned++;
            } else {
                $skipped++;
            }
        }

        return ['assigned' => $assigned, 'skipped' => $skipped, 'total' => $items->count()];
    }

    /**
     * Remove illegal same-person overlapping bookings for a month.
     * Keeps the earliest assignment; drops the person from later joint visits,
     * or reassigns / unassigns sole-visitor conflicts.
     *
     * @return array{fixed:int,unassigned:int,reassigned:int}
     */
    public function resolveOverlappingAllocations(AuditPlan $plan, int $monthIndex, ?int $userId = null): array
    {
        $assignments = MonthlyAssignment::query()
            ->whereHas('workItem', fn ($q) => $q->where('audit_plan_id', $plan->id)->where('month_index', $monthIndex))
            ->with(['workItem.activityType', 'employee', 'visitors', 'execution'])
            ->orderBy('start_date')
            ->orderBy('id')
            ->get();

        $byEmployee = [];
        foreach ($assignments as $assignment) {
            foreach ($assignment->visitorList() as $employee) {
                $byEmployee[(int) $employee->id][] = $assignment;
            }
        }

        $fixed = 0;
        $unassigned = 0;
        $reassigned = 0;

        foreach ($byEmployee as $employeeId => $list) {
            usort($list, function (MonthlyAssignment $a, MonthlyAssignment $b) {
                $cmp = $a->start_date <=> $b->start_date;

                return $cmp !== 0 ? $cmp : $a->id <=> $b->id;
            });

            $kept = [];
            foreach ($list as $assignment) {
                $assignment->refresh();
                if (! $assignment->exists) {
                    continue;
                }
                $assignment->loadMissing(['visitors', 'workItem.activityType', 'execution']);

                $overlapsKept = false;
                foreach ($kept as $prior) {
                    if (! $prior->exists) {
                        continue;
                    }
                    if ($this->datesOverlap($prior->start_date, $prior->end_date, $assignment->start_date, $assignment->end_date)) {
                        $overlapsKept = true;
                        break;
                    }
                }

                if (! $overlapsKept) {
                    $kept[] = $assignment;
                    continue;
                }

                $visitorIds = $assignment->visitorList()->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
                $remaining = array_values(array_filter($visitorIds, fn ($id) => (int) $id !== (int) $employeeId));

                if ($remaining !== []) {
                    $this->syncVisitors($assignment, $remaining);
                    $assignment->update(['employee_id' => $remaining[0], 'is_override_conflict' => false]);
                    $fixed++;
                    continue;
                }

                $replacement = $this->findFreeEmployee(
                    Carbon::parse($assignment->start_date),
                    Carbon::parse($assignment->end_date),
                    $assignment->id
                );

                if ($replacement && $assignment->workItem) {
                    $this->assign($assignment->workItem, [
                        'employee_ids' => [$replacement->id],
                        'start_date' => $assignment->start_date->toDateString(),
                        'end_date' => $assignment->end_date->toDateString(),
                        'count_off_days' => (bool) $assignment->count_off_days,
                        'remarks' => trim(($assignment->remarks ? $assignment->remarks.' · ' : '').'Reassigned (conflict resolved)'),
                    ], $userId);
                    $reassigned++;
                    $fixed++;
                    continue;
                }

                if ($assignment->workItem) {
                    $this->unassign($assignment->workItem, $userId);
                    $unassigned++;
                    $fixed++;
                }
            }
        }

        return compact('fixed', 'unassigned', 'reassigned');
    }

    public function unassign(MonthlyWorkItem $item, ?int $userId = null): void
    {
        DB::transaction(function () use ($item, $userId) {
            $assignment = $item->assignment;
            if ($assignment) {
                AssignmentStatusLog::query()->create([
                    'monthly_assignment_id' => $assignment->id,
                    'from_status' => $assignment->execution?->status ?? 'assigned',
                    'to_status' => 'unassigned',
                    'reason' => 'Unassigned due to same-day conflict resolution',
                    'changed_by' => $userId,
                ]);
                $assignment->visitors()->detach();
                VisitExecution::query()->where('monthly_assignment_id', $assignment->id)->delete();
                AssignmentStatusLog::query()->where('monthly_assignment_id', $assignment->id)->delete();
                $assignment->delete();
            }
            $item->update(['status' => MonthlyWorkItem::STATUS_UNASSIGNED]);
        });
    }

    public function findFreeEmployee(Carbon $start, Carbon $end, ?int $ignoreAssignmentId = null): ?Employee
    {
        foreach (Employee::query()->orderBy('id')->get() as $employee) {
            if ($this->findConflicts((int) $employee->id, $start, $end, $ignoreAssignmentId)->isEmpty()) {
                return $employee;
            }
        }

        return null;
    }

    protected function datesOverlap($startA, $endA, $startB, $endB): bool
    {
        $aStart = Carbon::parse($startA)->toDateString();
        $aEnd = Carbon::parse($endA)->toDateString();
        $bStart = Carbon::parse($startB)->toDateString();
        $bEnd = Carbon::parse($endB)->toDateString();

        return $aStart <= $bEnd && $aEnd >= $bStart;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function assign(MonthlyWorkItem $item, array $data, ?int $userId = null): MonthlyAssignment
    {
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end = Carbon::parse($data['end_date'])->startOfDay();
        if ($end->lt($start)) {
            throw new InvalidArgumentException('End date must be on or after start date.');
        }

        $visitorIds = $this->normalizeVisitorIds($data);
        if ($visitorIds === []) {
            throw new InvalidArgumentException('Select at least one visitor.');
        }

        $countOffDays = ! empty($data['count_off_days']);
        $duration = max(1, $this->calendar->countWorkingDays($start, $end, $countOffDays));
        $mode = 'working';

        $existingAssignmentId = $item->assignment?->id;
        $conflicts = $this->findConflictsForEmployees($visitorIds, $start, $end, $existingAssignmentId);
        if ($conflicts->isNotEmpty()) {
            $names = $conflicts->flatMap(fn ($c) => $c->visitorList()->pluck('name'))->unique()->implode(', ');
            throw new InvalidArgumentException(
                'Blocked: same person cannot audit two places on overlapping dates'
                .($names !== '' ? " (conflict: {$names})" : '')
                .'. Remove that visitor or choose different dates.'
            );
        }

        // Always from DB history for this office — not a manual field.
        $lastUpto = $this->computeLastAuditUpto($item)?->toDateString();

        return DB::transaction(function () use ($item, $data, $visitorIds, $start, $end, $duration, $mode, $countOffDays, $lastUpto, $userId) {
            $assignment = MonthlyAssignment::query()->updateOrCreate(
                ['monthly_work_item_id' => $item->id],
                [
                    'employee_id' => $visitorIds[0],
                    'visit_date' => $data['visit_date'] ?? $start->toDateString(),
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'start_time' => null,
                    'end_time' => null,
                    'duration_days' => $duration,
                    'duration_mode' => $mode,
                    'count_off_days' => $countOffDays,
                    'purpose' => $item->activityType?->name,
                    'remarks' => $data['remarks'] ?? null,
                    'last_audit_upto' => $lastUpto,
                    'last_audit_upto_override' => false,
                    'is_override_conflict' => false,
                    'assigned_by' => $userId,
                ]
            );

            $this->syncVisitors($assignment, $visitorIds);

            $item->update(['status' => MonthlyWorkItem::STATUS_ASSIGNED]);

            VisitExecution::query()->firstOrCreate(
                ['monthly_assignment_id' => $assignment->id],
                [
                    'status' => VisitExecution::STATUS_PLANNED,
                    'created_by' => $userId,
                ]
            );

            AssignmentStatusLog::query()->create([
                'monthly_assignment_id' => $assignment->id,
                'from_status' => 'unassigned',
                'to_status' => 'assigned',
                'reason' => 'Assigned',
                'meta' => [
                    'visitor_ids' => $visitorIds,
                ],
                'changed_by' => $userId,
            ]);

            return $assignment->fresh(['employee', 'visitors', 'execution', 'workItem']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function reschedule(MonthlyAssignment $assignment, array $data, ?int $userId = null): MonthlyAssignment
    {
        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end = Carbon::parse($data['end_date'])->startOfDay();
        if ($end->lt($start)) {
            throw new InvalidArgumentException('End date must be on or after start date.');
        }

        $reason = trim((string) ($data['reschedule_reason'] ?? ''));
        if ($reason === '') {
            throw new InvalidArgumentException('Reschedule reason is required.');
        }

        $visitorIds = $this->normalizeVisitorIds($data);
        if ($visitorIds === []) {
            $visitorIds = $assignment->visitorList()->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        }
        if ($visitorIds === []) {
            throw new InvalidArgumentException('Select at least one visitor.');
        }

        $countOffDays = array_key_exists('count_off_days', $data)
            ? ! empty($data['count_off_days'])
            : (bool) $assignment->count_off_days;
        $duration = max(1, $this->calendar->countWorkingDays($start, $end, $countOffDays));
        $mode = 'working';

        $conflicts = $this->findConflictsForEmployees($visitorIds, $start, $end, $assignment->id);
        if ($conflicts->isNotEmpty()) {
            throw new InvalidArgumentException(
                'Blocked: same person cannot audit two places on overlapping dates. Choose different visitors or dates.'
            );
        }

        return DB::transaction(function () use ($assignment, $data, $visitorIds, $start, $end, $duration, $mode, $countOffDays, $reason, $userId) {
            $from = $assignment->execution?->status ?? 'assigned';

            $assignment->update([
                'original_start_date' => $assignment->original_start_date ?: $assignment->start_date,
                'original_end_date' => $assignment->original_end_date ?: $assignment->end_date,
                'employee_id' => $visitorIds[0],
                'visit_date' => $data['visit_date'] ?? $start->toDateString(),
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'start_time' => null,
                'end_time' => null,
                'duration_days' => $duration,
                'duration_mode' => $mode,
                'count_off_days' => $countOffDays,
                'purpose' => $assignment->workItem?->activityType?->name ?? $assignment->purpose,
                'remarks' => $data['remarks'] ?? $assignment->remarks,
                'reschedule_reason' => $reason,
                'is_override_conflict' => false,
            ]);

            $this->syncVisitors($assignment, $visitorIds);

            $execution = $assignment->execution;
            if ($execution) {
                $execution->update([
                    'status' => VisitExecution::STATUS_RESCHEDULED,
                    'updated_by' => $userId,
                ]);
            }

            AssignmentStatusLog::query()->create([
                'monthly_assignment_id' => $assignment->id,
                'from_status' => $from,
                'to_status' => VisitExecution::STATUS_RESCHEDULED,
                'reason' => $reason,
                'meta' => [
                    'new_start' => $start->toDateString(),
                    'new_end' => $end->toDateString(),
                    'visitor_ids' => $visitorIds,
                ],
                'changed_by' => $userId,
            ]);

            return $assignment->fresh(['employee', 'visitors', 'execution', 'workItem']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateExecution(MonthlyAssignment $assignment, array $data, ?int $userId = null): VisitExecution
    {
        $execution = $assignment->execution
            ?? VisitExecution::query()->create([
                'monthly_assignment_id' => $assignment->id,
                'status' => VisitExecution::STATUS_PLANNED,
                'created_by' => $userId,
            ]);

        $from = $execution->status;
        $to = $data['status'] ?? $from;

        $actualStart = ! empty($data['actual_start_date']) ? Carbon::parse($data['actual_start_date']) : null;
        $actualEnd = ! empty($data['actual_end_date']) ? Carbon::parse($data['actual_end_date']) : null;
        $actualDays = null;
        if ($actualStart && $actualEnd) {
            $actualDays = $this->calculateDurationDays($actualStart, $actualEnd, 'calendar');
        } elseif (isset($data['actual_duration_days'])) {
            $actualDays = max(1, (int) $data['actual_duration_days']);
        }

        $execution->update([
            'status' => $to,
            'actual_start_date' => $actualStart?->toDateString(),
            'actual_end_date' => $actualEnd?->toDateString(),
            'actual_duration_days' => $actualDays,
            'actual_employee_id' => $data['actual_employee_id'] ?? null,
            'remarks' => $data['remarks'] ?? $execution->remarks,
            'updated_by' => $userId,
        ]);

        if ($from !== $to) {
            AssignmentStatusLog::query()->create([
                'monthly_assignment_id' => $assignment->id,
                'from_status' => $from,
                'to_status' => $to,
                'reason' => $data['reason'] ?? 'Execution updated',
                'changed_by' => $userId,
            ]);
        }

        return $execution->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createSpecial(AuditPlan $plan, int $monthIndex, array $data, ?int $userId = null): MonthlyWorkItem
    {
        $activity = ActivityType::query()->findOrFail($data['activity_type_id']);

        return DB::transaction(function () use ($plan, $monthIndex, $data, $activity, $userId) {
            $item = MonthlyWorkItem::query()->create([
                'audit_plan_id' => $plan->id,
                'fy_label' => $plan->fy_label,
                'month_index' => $monthIndex,
                'category' => $activity->category_map ?: 'special',
                'activity_type_id' => $activity->id,
                'schedulable_type' => $data['schedulable_type'] ?? null,
                'schedulable_id' => $data['schedulable_id'] ?? null,
                'plan_schedule_id' => null,
                'source' => MonthlyWorkItem::SOURCE_SPECIAL,
                'status' => MonthlyWorkItem::STATUS_UNASSIGNED,
                'entity_label' => $data['entity_label'],
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            return $item;
        });
    }

    public function findConflicts(int $employeeId, Carbon $start, Carbon $end, ?int $ignoreAssignmentId = null): Collection
    {
        return $this->findConflictsForEmployees([$employeeId], $start, $end, $ignoreAssignmentId);
    }

    /**
     * @param  list<int>  $employeeIds
     */
    public function findConflictsForEmployees(array $employeeIds, Carbon $start, Carbon $end, ?int $ignoreAssignmentId = null): Collection
    {
        $employeeIds = array_values(array_unique(array_filter(array_map('intval', $employeeIds))));
        if ($employeeIds === []) {
            return collect();
        }

        return MonthlyAssignment::query()
            ->when($ignoreAssignmentId, fn ($q) => $q->where('id', '!=', $ignoreAssignmentId))
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString())
            ->where(function ($q) use ($employeeIds) {
                $q->whereIn('employee_id', $employeeIds)
                    ->orWhereHas('visitors', fn ($v) => $v->whereIn('employees.id', $employeeIds));
            })
            ->with(['workItem', 'employee', 'visitors'])
            ->get();
    }

    /**
     * Last month this office/entity was audited or monitored (from DB history).
     * Prefer completed field visits; otherwise the prior yearly schedule month.
     */
    public function computeLastAuditUpto(MonthlyWorkItem $item): ?Carbon
    {
        if (! $item->schedulable_type || ! $item->schedulable_id) {
            return null;
        }

        $completed = VisitExecution::query()
            ->where('status', VisitExecution::STATUS_COMPLETED)
            ->whereHas('assignment.workItem', function ($q) use ($item) {
                $q->where('schedulable_type', $item->schedulable_type)
                    ->where('schedulable_id', $item->schedulable_id);
            })
            ->orderByDesc('actual_end_date')
            ->orderByDesc('id')
            ->first();

        if ($completed?->actual_end_date) {
            return Carbon::parse($completed->actual_end_date)->startOfMonth();
        }

        // Previous completed assignment end for same office (even if execution row missing).
        $priorAssignment = MonthlyAssignment::query()
            ->whereHas('workItem', function ($q) use ($item) {
                $q->where('schedulable_type', $item->schedulable_type)
                    ->where('schedulable_id', $item->schedulable_id)
                    ->where(function ($inner) use ($item) {
                        $inner->where('id', '!=', $item->id);
                    });
            })
            ->whereHas('execution', fn ($q) => $q->where('status', VisitExecution::STATUS_COMPLETED))
            ->orderByDesc('end_date')
            ->first();

        if ($priorAssignment?->end_date) {
            return Carbon::parse($priorAssignment->end_date)->startOfMonth();
        }

        // Fall back to earlier yearly plan schedule for this office.
        $prior = PlanSchedule::query()
            ->where('schedulable_type', $item->schedulable_type)
            ->where('schedulable_id', $item->schedulable_id)
            ->where('month_index', '<=', 11)
            ->where(function ($q) use ($item) {
                $q->where('audit_plan_id', '!=', $item->audit_plan_id)
                    ->orWhere(function ($inner) use ($item) {
                        $inner->where('audit_plan_id', $item->audit_plan_id)
                            ->where('month_index', '<', $item->month_index);
                    });
            })
            ->orderByDesc('planned_date')
            ->orderByDesc('month_index')
            ->first();

        if ($prior?->planned_date) {
            return Carbon::parse($prior->planned_date)->startOfMonth();
        }

        return null;
    }

    public function formatLastAuditUptoLabel(?Carbon $date): ?string
    {
        return $date ? $date->format('F-Y') : null;
    }

    public function performanceSummary(AuditPlan $plan, int $monthIndex): array
    {
        $items = $this->workItemsForMonth($plan, $monthIndex);
        $byCategory = [];

        foreach ($items->groupBy('category') as $category => $group) {
            $assigned = $group->where('status', MonthlyWorkItem::STATUS_ASSIGNED);
            $unassigned = $group->where('status', MonthlyWorkItem::STATUS_UNASSIGNED);
            $execStatuses = $assigned->map(fn ($i) => $i->assignment?->execution?->status);

            $completed = $execStatuses->filter(fn ($s) => $s === VisitExecution::STATUS_COMPLETED)->count();
            $cancelled = $execStatuses->filter(fn ($s) => $s === VisitExecution::STATUS_CANCELLED)->count();
            $overdue = $execStatuses->filter(fn ($s) => $s === VisitExecution::STATUS_DELAYED)->count();

            $byCategory[$category] = [
                'planned' => $group->count(),
                'assigned' => $assigned->count(),
                'completed' => $completed,
                // Awaiting allocation (not yet assigned) — must fall when staff are allocated.
                'pending' => $unassigned->count(),
                'cancelled' => $cancelled,
                'overdue' => $overdue,
            ];
        }

        $totals = [
            'planned' => array_sum(array_column($byCategory, 'planned')),
            'assigned' => array_sum(array_column($byCategory, 'assigned')),
            'completed' => array_sum(array_column($byCategory, 'completed')),
            'pending' => array_sum(array_column($byCategory, 'pending')),
            'cancelled' => array_sum(array_column($byCategory, 'cancelled')),
            'overdue' => array_sum(array_column($byCategory, 'overdue')),
        ];

        return compact('byCategory', 'totals');
    }

    public function staffWorkload(AuditPlan $plan, int $monthIndex): Collection
    {
        $assignments = MonthlyAssignment::query()
            ->whereHas('workItem', fn ($q) => $q->where('audit_plan_id', $plan->id)->where('month_index', $monthIndex))
            ->with(['employee.position', 'visitors.position'])
            ->get();

        $byEmployee = [];

        foreach ($assignments as $assignment) {
            foreach ($assignment->visitorList() as $employee) {
                $id = (int) $employee->id;
                if (! isset($byEmployee[$id])) {
                    $byEmployee[$id] = [
                        'employee' => $employee,
                        'activities' => 0,
                        'total_days' => 0,
                        'assignments' => collect(),
                    ];
                }
                $byEmployee[$id]['activities']++;
                $byEmployee[$id]['total_days'] += (int) $assignment->duration_days;
                $byEmployee[$id]['assignments']->push($assignment);
            }
        }

        return collect($byEmployee)
            ->values()
            ->sortByDesc('total_days')
            ->values();
    }

    public function calculateDurationDays(Carbon $start, Carbon $end, string $mode, bool $countOffDays = false): int
    {
        if ($mode === 'calendar') {
            return max(1, $start->diffInDays($end) + 1);
        }

        // working (default) — BD weekend Fri/Sat + national/govt holidays excluded unless special request
        return max(1, $this->calendar->countWorkingDays($start, $end, $countOffDays));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<int>
     */
    protected function normalizeVisitorIds(array $data): array
    {
        $ids = $data['employee_ids'] ?? null;
        if (is_array($ids) && $ids !== []) {
            return array_values(array_unique(array_filter(array_map('intval', $ids))));
        }

        if (! empty($data['employee_id'])) {
            return [(int) $data['employee_id']];
        }

        return [];
    }

    /**
     * @param  list<int>  $visitorIds
     */
    protected function syncVisitors(MonthlyAssignment $assignment, array $visitorIds): void
    {
        $sync = [];
        foreach (array_values($visitorIds) as $index => $id) {
            $sync[$id] = ['sort_order' => $index];
        }
        $assignment->visitors()->sync($sync);
    }

    protected function activityForCategory(string $category, Collection $cache): ?ActivityType
    {
        $slug = $this->categoryActivitySlug[$category] ?? null;
        if (! $slug) {
            return $cache->get('other');
        }

        return $cache->get($slug) ?? $cache->get('other');
    }

    protected function entityLabel(mixed $entity, ?string $type): string
    {
        if (! $entity) {
            return 'Unknown';
        }

        return match ($type) {
            Shakha::class => $entity->name.($entity->code ? ' ('.$entity->code.')' : ''),
            Area::class => $entity->name.($entity->division ? ' · '.$entity->division : ''),
            ProjectLocation::class => ($entity->project?->name ? $entity->project->name.' — ' : '').$entity->name,
            HqDepartment::class => $entity->name,
            default => $entity->name ?? class_basename($type).' #'.$entity->id,
        };
    }

    public function monthOptions(FinancialYear $fy): array
    {
        return collect($fy->months())->map(fn ($m) => [
            'index' => $m['index'],
            'label' => $m['label'].' '.$m['year'],
            'key' => $m['key'],
        ])->all();
    }
}
