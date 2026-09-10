<?php

namespace App\Http\Controllers;

use App\Models\ActivityType;
use App\Models\AuditPlan;
use App\Models\Employee;
use App\Models\MonthlyAssignment;
use App\Models\MonthlyWorkItem;
use App\Models\VisitExecution;
use App\Services\AnnualPlanGenerator;
use App\Services\MonthlyScheduleReportBuilder;
use App\Services\MonthlyWorklistService;
use App\Services\UserAccessService;
use App\Services\WorkingCalendarService;
use App\Support\FinancialYear;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MonthlyVisitController extends Controller
{
    public function __construct(
        private MonthlyWorklistService $worklist,
        private WorkingCalendarService $calendar,
        private MonthlyScheduleReportBuilder $scheduleReport,
        private UserAccessService $access,
    ) {}

    public function index(Request $request): View
    {
        $plan = $this->worklist->resolvePlan($request->string('fy')->toString() ?: null);
        $fy = FinancialYear::fromLabel($plan->fy_label);
        $monthIndex = $request->filled('month')
            ? max(0, min(11, $request->integer('month')))
            : $this->worklist->currentMonthIndex($plan);

        $user = $request->user();
        $officerView = $this->access->isAllocationScoped($user);

        // Auto-pull yearly allocations for the selected month so shakhas appear immediately.
        // Officers only execute — managers refresh the worklist.
        if ($plan->generated_at && ! $officerView) {
            $this->worklist->refreshFromYearly($plan, $monthIndex, $user?->id);
        }

        $items = $this->worklist->workItemsForMonth($plan, $monthIndex);
        if ($officerView) {
            $items = $this->access->filterWorkItemsForUser($items, $user);
        }

        $performance = $officerView
            ? $this->officerPerformance($items)
            : $this->worklist->performanceSummary($plan, $monthIndex);
        $defaultStart = $fy->dateForMonthIndex($monthIndex);
        $monthEnd = $defaultStart->copy()->endOfMonth();

        // Default visit window: next 5 working days from month start (auto, not manual).
        $workingDates = $this->calendar->workingDates($defaultStart, $defaultStart->copy()->addDays(20), false);
        $defaultEnd = isset($workingDates[4])
            ? Carbon::parse($workingDates[4])
            : $defaultStart->copy()->addDays(4);

        $allocatePayload = $items->map(function (MonthlyWorkItem $item) use ($defaultStart, $defaultEnd, $user) {
            $assignment = $item->assignment;
            $lastUpto = $this->worklist->computeLastAuditUpto($item);

            return [
                'id' => $item->id,
                'entity_label' => $item->entity_label,
                'activity' => $item->activityType?->name,
                'category' => str_replace('_', ' ', $item->category),
                'is_special' => $item->isSpecial(),
                'status' => $item->status,
                'assign_url' => route('monthly-visits.assign.store', $item),
                'visitor_ids' => $assignment
                    ? $assignment->visitorList()->pluck('id')->map(fn ($id) => (int) $id)->values()->all()
                    : [],
                'start_date' => $assignment?->start_date?->toDateString() ?? $defaultStart->toDateString(),
                'end_date' => $assignment?->end_date?->toDateString() ?? $defaultEnd->toDateString(),
                'count_off_days' => (bool) ($assignment?->count_off_days ?? false),
                'duration_days' => $assignment?->duration_days,
                'last_audit_upto' => $lastUpto?->toDateString(),
                'last_audit_upto_label' => $this->worklist->formatLastAuditUptoLabel($lastUpto) ?? 'No prior audit on record',
                'purpose' => $item->activityType?->name,
                'remarks' => $assignment?->remarks,
                'is_locked' => (bool) ($assignment?->is_locked),
                'locked_by_name' => $assignment?->lockedBy?->name,
                'can_modify' => $assignment
                    ? $assignment->canBeModifiedBy($user)
                    : true,
            ];
        })->values();

        $calendarPayload = $this->calendar->modalCalendarPayload($defaultStart, $monthEnd);

        $employeeAvailability = $officerView
            ? collect()
            : $this->calendar->employeeAvailabilityForMonth($defaultStart, $monthEnd);

        return view('monthly-visits.index', [
            'plan' => $plan,
            'fy' => $fy,
            'monthIndex' => $monthIndex,
            'monthOptions' => $this->worklist->monthOptions($fy),
            'availablePlans' => AuditPlan::query()->orderByDesc('start_date')->get(['id', 'fy_label', 'status']),
            'items' => $items,
            'unassigned' => $officerView ? collect() : $items->where('status', MonthlyWorkItem::STATUS_UNASSIGNED)->values(),
            'assigned' => $items->where('status', MonthlyWorkItem::STATUS_ASSIGNED)->values(),
            'performance' => $performance,
            'employees' => $officerView ? collect() : Employee::query()->with('position')->orderBy('name')->get(),
            'employeeAvailability' => $employeeAvailability,
            'calendarPayload' => $calendarPayload,
            'activityTypes' => ActivityType::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'monthLabel' => $fy->months()[$monthIndex]['label'].' '.$fy->months()[$monthIndex]['year'],
            'allocatePayload' => $allocatePayload,
            'openAllocateId' => $officerView ? null : ($request->integer('allocate') ?: null),
            'conflictFlash' => session('conflicts'),
            'conflictWarning' => session('conflict_warning'),
            'officerView' => $officerView,
            'employeeLinked' => (bool) $user?->employee_id,
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, MonthlyWorkItem>  $items
     * @return array{byCategory: array<string, array<string, int>>, totals: array<string, int>}
     */
    protected function officerPerformance($items): array
    {
        $assigned = $items->where('status', MonthlyWorkItem::STATUS_ASSIGNED);
        $execStatuses = $assigned->map(fn ($i) => $i->assignment?->execution?->status);
        $completed = $execStatuses->filter(fn ($s) => $s === VisitExecution::STATUS_COMPLETED)->count();
        $cancelled = $execStatuses->filter(fn ($s) => $s === VisitExecution::STATUS_CANCELLED)->count();
        $overdue = $execStatuses->filter(fn ($s) => $s === VisitExecution::STATUS_DELAYED)->count();

        $totals = [
            'planned' => $assigned->count(),
            'assigned' => $assigned->count(),
            'completed' => $completed,
            'pending' => 0,
            'cancelled' => $cancelled,
            'overdue' => $overdue,
        ];

        return [
            'byCategory' => [],
            'totals' => $totals,
        ];
    }

    public function generate(Request $request): RedirectResponse
    {
        $plan = $this->worklist->resolvePlan($request->string('fy')->toString() ?: null);
        $monthIndex = max(0, min(11, $request->integer('month')));

        $result = $this->worklist->refreshFromYearly($plan, $monthIndex, $request->user()?->id);

        return redirect()
            ->route('monthly-visits.index', ['fy' => $plan->fy_label, 'month' => $monthIndex])
            ->with('status', "Worklist refreshed: {$result['created']} new, {$result['updated']} linked from yearly plan ({$result['total']} schedules in month).");
    }

    public function bulkAllocate(Request $request): RedirectResponse
    {
        $plan = $this->worklist->resolvePlan($request->string('fy')->toString() ?: null);
        $monthIndex = max(0, min(11, $request->integer('month')));

        // Allocate with any working-day length (1, 2, 3 …). Never place the same person on overlapping dates.
        $result = $this->worklist->bulkAllocateMonth($plan, $monthIndex, $request->user()?->id);

        $msg = "Auto-allocated {$result['assigned']} of {$result['total']} offices (conflict-safe)";
        if (! empty($result['repacked'])) {
            $msg .= "; month rebalanced (cleared {$result['cleared']} prior plans; visit days shared evenly)";
        }
        if ($result['skipped']) {
            $msg .= "; {$result['skipped']} still unassigned (not enough auditor capacity without date conflicts)";
        }

        return redirect()
            ->route('monthly-visits.index', ['fy' => $plan->fy_label, 'month' => $monthIndex])
            ->with('status', $msg.'.');
    }

    public function assignForm(Request $request, MonthlyWorkItem $workItem): RedirectResponse
    {
        return redirect()->route('monthly-visits.index', [
            'fy' => $workItem->fy_label,
            'month' => $workItem->month_index,
            'allocate' => $workItem->id,
        ]);
    }

    public function assign(Request $request, MonthlyWorkItem $workItem): RedirectResponse
    {
        $validated = $request->validate([
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer', 'distinct', 'exists:employees,id'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'visit_date' => ['nullable', 'date'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'count_off_days' => ['nullable', 'boolean'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'last_audit_upto' => ['nullable', 'date'],
            'lock_schedule' => ['nullable', 'boolean'],
        ]);

        try {
            $validated['lock_schedule'] = $request->boolean('lock_schedule');
            $this->worklist->assign($workItem, $validated, $request->user()?->id);
        } catch (\InvalidArgumentException $e) {
            if (str_contains(strtolower($e->getMessage()), 'overlapping') || str_contains(strtolower($e->getMessage()), 'same person')) {
                $conflicts = $this->worklist->findConflictsForEmployees(
                    array_map('intval', $validated['employee_ids']),
                    Carbon::parse($validated['start_date']),
                    Carbon::parse($validated['end_date']),
                    $workItem->assignment?->id,
                );

                return redirect()
                    ->route('monthly-visits.index', [
                        'fy' => $workItem->fy_label,
                        'month' => $workItem->month_index,
                        'allocate' => $workItem->id,
                    ])
                    ->withInput()
                    ->with('conflict_warning', $e->getMessage())
                    ->with('conflicts', $conflicts->map(fn ($c) => [
                        'names' => $c->visitorNames(', ') ?: ($c->employee?->name ?? 'Staff'),
                        'dates' => trim(($c->start_date?->format('d M Y') ?? '').' – '.($c->end_date?->format('d M Y') ?? ''), ' –'),
                        'entity' => $c->workItem?->entity_label ?? 'another place',
                    ])->all());
            }

            return redirect()
                ->route('monthly-visits.index', [
                    'fy' => $workItem->fy_label,
                    'month' => $workItem->month_index,
                    'allocate' => $workItem->id,
                ])
                ->withInput()
                ->withErrors(['assign' => $e->getMessage()]);
        }

        return redirect()
            ->route('monthly-visits.index', [
                'fy' => $workItem->fy_label,
                'month' => $workItem->month_index,
            ])
            ->with('status', 'Visit allocated successfully.');
    }

    public function executionForm(MonthlyAssignment $assignment): View
    {
        abort_unless($this->access->userCanAccessAssignment(auth()->user(), $assignment), 403);

        $assignment->load(['workItem.activityType', 'employee', 'visitors', 'execution', 'statusLogs']);

        return view('monthly-visits.execution', [
            'assignment' => $assignment,
            'employees' => Employee::query()->orderBy('name')->get(),
            'statuses' => [
                VisitExecution::STATUS_PLANNED,
                VisitExecution::STATUS_IN_PROGRESS,
                VisitExecution::STATUS_COMPLETED,
                VisitExecution::STATUS_DELAYED,
                VisitExecution::STATUS_RESCHEDULED,
                VisitExecution::STATUS_CANCELLED,
            ],
        ]);
    }

    public function updateExecution(Request $request, MonthlyAssignment $assignment): RedirectResponse
    {
        abort_unless($this->access->userCanAccessAssignment($request->user(), $assignment), 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                VisitExecution::STATUS_PLANNED,
                VisitExecution::STATUS_IN_PROGRESS,
                VisitExecution::STATUS_COMPLETED,
                VisitExecution::STATUS_DELAYED,
                VisitExecution::STATUS_RESCHEDULED,
                VisitExecution::STATUS_CANCELLED,
            ])],
            'actual_start_date' => ['nullable', 'date'],
            'actual_end_date' => ['nullable', 'date', 'after_or_equal:actual_start_date'],
            'actual_duration_days' => ['nullable', 'integer', 'min:1', 'max:60'],
            'actual_employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->worklist->updateExecution($assignment, $validated, $request->user()?->id);

        $item = $assignment->workItem;

        return redirect()
            ->route('monthly-visits.index', ['fy' => $item->fy_label, 'month' => $item->month_index])
            ->with('status', 'Execution updated.');
    }

    public function rescheduleForm(MonthlyAssignment $assignment): View
    {
        abort_unless($assignment->canBeModifiedBy(auth()->user()), 403);

        $assignment->load(['workItem', 'employee', 'visitors', 'lockedBy']);

        return view('monthly-visits.reschedule', [
            'assignment' => $assignment,
            'employees' => Employee::query()->with('position')->orderBy('name')->get(),
        ]);
    }

    public function reschedule(Request $request, MonthlyAssignment $assignment): RedirectResponse
    {
        abort_unless($assignment->canBeModifiedBy($request->user()), 403);

        $validated = $request->validate([
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer', 'distinct', 'exists:employees,id'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'visit_date' => ['nullable', 'date'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'count_off_days' => ['nullable', 'boolean'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'reschedule_reason' => ['required', 'string', 'max:1000'],
            'lock_schedule' => ['nullable', 'boolean'],
        ]);

        try {
            $validated['lock_schedule'] = $request->boolean('lock_schedule');
            $this->worklist->reschedule($assignment, $validated, $request->user()?->id);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['reschedule' => $e->getMessage()]);
        }

        $item = $assignment->workItem;

        return redirect()
            ->route('monthly-visits.index', ['fy' => $item->fy_label, 'month' => $item->month_index])
            ->with('status', 'Visit rescheduled. Original dates preserved.');
    }

    public function lock(Request $request, MonthlyAssignment $assignment): RedirectResponse
    {
        try {
            $this->worklist->lockSchedule($assignment, $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['lock' => $e->getMessage()]);
        }

        $item = $assignment->workItem;

        return redirect()
            ->route('monthly-visits.index', ['fy' => $item->fy_label, 'month' => $item->month_index])
            ->with('status', 'Visit schedule locked. Others cannot change it.');
    }

    public function unlock(Request $request, MonthlyAssignment $assignment): RedirectResponse
    {
        try {
            $this->worklist->unlockSchedule($assignment, $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['lock' => $e->getMessage()]);
        }

        $item = $assignment->workItem;

        return redirect()
            ->route('monthly-visits.index', ['fy' => $item->fy_label, 'month' => $item->month_index])
            ->with('status', 'Visit schedule unlocked.');
    }

    public function storeSpecial(Request $request): RedirectResponse
    {
        $plan = $this->worklist->resolvePlan($request->string('fy')->toString() ?: null);
        $monthIndex = max(0, min(11, $request->integer('month')));

        $validated = $request->validate([
            'activity_type_id' => ['required', 'integer', 'exists:activity_types,id'],
            'entity_label' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'schedulable_type' => ['nullable', 'string', Rule::in(AnnualPlanGenerator::SCHEDULABLE_TYPES)],
            'schedulable_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $this->worklist->createSpecial($plan, $monthIndex, $validated, $request->user()?->id);

        return redirect()
            ->route('monthly-visits.index', ['fy' => $plan->fy_label, 'month' => $monthIndex])
            ->with('status', 'Special activity added (not part of yearly plan).');
    }

    public function report(Request $request): View
    {
        $plan = $this->worklist->resolvePlan($request->string('fy')->toString() ?: null);
        $fy = FinancialYear::fromLabel($plan->fy_label);
        $monthIndex = max(0, min(11, $request->integer('month')));
        $type = $request->string('type', 'schedule')->toString();

        $items = $this->worklist->workItemsForMonth($plan, $monthIndex);
        $items = $this->access->filterWorkItemsForUser($items, $request->user());
        $assigned = $items->where('status', MonthlyWorkItem::STATUS_ASSIGNED)->values();
        $monthMeta = $fy->months()[$monthIndex];
        $monthLabel = $monthMeta['label'].' '.$monthMeta['year'];

        if ($type === 'projects') {
            $assigned = $assigned->filter(fn ($i) => in_array($i->category, [
                \App\Models\AuditPolicy::CATEGORY_PROJECT_AUDIT,
                \App\Models\AuditPolicy::CATEGORY_PROJECT_MONITORING,
                \App\Models\AuditPolicy::CATEGORY_PKSF,
            ], true))->values();
        }

        return view('monthly-visits.report', [
            'plan' => $plan,
            'fy' => $fy,
            'monthIndex' => $monthIndex,
            'monthLabel' => $monthLabel,
            'type' => $type,
            'assigned' => $assigned,
            'performance' => $this->access->isAllocationScoped($request->user())
                ? $this->officerPerformance($items)
                : $this->worklist->performanceSummary($plan, $monthIndex),
            'workload' => $this->worklist->staffWorkload($plan, $monthIndex),
        ]);
    }

    public function printSchedule(Request $request): View
    {
        $plan = $this->worklist->resolvePlan($request->string('fy')->toString() ?: null);
        $monthIndex = max(0, min(11, $request->integer('month')));
        $items = $this->scopedMonthItems($request, $plan, $monthIndex);

        return view('monthly-visits.print-schedule', $this->scheduleReport->build($plan, $monthIndex, $items));
    }

    public function exportSchedulePdf(Request $request): Response
    {
        $plan = $this->worklist->resolvePlan($request->string('fy')->toString() ?: null);
        $monthIndex = max(0, min(11, $request->integer('month')));
        $items = $this->scopedMonthItems($request, $plan, $monthIndex);

        $data = $this->scheduleReport->build($plan, $monthIndex, $items) + ['forPdf' => true];
        $pdf = Pdf::loadView('monthly-visits.print-schedule', $data)
            ->setPaper('a4', 'landscape');

        $filename = 'monthly-schedule-'.$plan->fy_label.'-'.$data['monthLabel'].'.pdf';

        return $pdf->download($filename);
    }

    public function exportScheduleDoc(Request $request): Response
    {
        $plan = $this->worklist->resolvePlan($request->string('fy')->toString() ?: null);
        $monthIndex = max(0, min(11, $request->integer('month')));
        $items = $this->scopedMonthItems($request, $plan, $monthIndex);

        return $this->scheduleReport->downloadDoc($plan, $monthIndex, $items);
    }

    public function exportScheduleExcel(Request $request): StreamedResponse
    {
        $plan = $this->worklist->resolvePlan($request->string('fy')->toString() ?: null);
        $monthIndex = max(0, min(11, $request->integer('month')));
        $items = $this->scopedMonthItems($request, $plan, $monthIndex);

        return $this->scheduleReport->downloadExcel($plan, $monthIndex, $items);
    }

    /**
     * @return \Illuminate\Support\Collection<int, MonthlyWorkItem>
     */
    protected function scopedMonthItems(Request $request, AuditPlan $plan, int $monthIndex)
    {
        $user = $request->user();
        if ($plan->generated_at && ! $this->access->isAllocationScoped($user)) {
            $this->worklist->refreshFromYearly($plan, $monthIndex, $user?->id);
        }

        return $this->access->filterWorkItemsForUser(
            $this->worklist->workItemsForMonth($plan, $monthIndex),
            $user
        );
    }
}
