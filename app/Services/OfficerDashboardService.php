<?php

namespace App\Services;

use App\Models\AuditReport;
use App\Models\MonthlyAssignment;
use App\Models\Shakha;
use App\Models\ShakhaRiskAssessment;
use App\Models\User;
use App\Models\VisitExecution;
use App\Support\FinancialYear;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class OfficerDashboardService
{
    public function __construct(
        private UserAccessService $access,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(User $user, ?string $fyLabel = null, ?int $monthIndex = null): array
    {
        $user->loadMissing(['employee.position', 'roles', 'assignedShakhas']);

        $fy = $fyLabel && preg_match('/^\d{4}-\d{4}$/', $fyLabel)
            ? FinancialYear::fromLabel($fyLabel)
            : FinancialYear::current(now('Asia/Dhaka'));

        $currentIndex = $fy->monthIndexForDate(now('Asia/Dhaka')) ?? 0;
        $monthIndex = $monthIndex !== null
            ? max(0, min(11, $monthIndex))
            : $currentIndex;

        $monthMeta = $fy->months()[$monthIndex];
        $monthStart = Carbon::create($monthMeta['year'], $monthMeta['month'], 1, 0, 0, 0, 'Asia/Dhaka')->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $daysInMonth = (int) $monthEnd->day;

        $assignments = $this->assignmentsForEmployee($user);
        $monthAssignments = $assignments->filter(function (MonthlyAssignment $a) use ($monthStart, $monthEnd) {
            if (! $a->start_date || ! $a->end_date) {
                return false;
            }

            return $a->start_date->lte($monthEnd) && $a->end_date->gte($monthStart);
        })->values();

        $fyMonthCounts = $this->fyMonthCounts($assignments, $fy);

        $shakhasThisMonth = $monthAssignments
            ->map(fn (MonthlyAssignment $a) => $this->entityLabel($a))
            ->filter()
            ->unique()
            ->count();

        $completed = $monthAssignments->filter(
            fn (MonthlyAssignment $a) => ($a->execution?->status ?? '') === VisitExecution::STATUS_COMPLETED
        )->count();

        $inProgress = $monthAssignments->filter(
            fn (MonthlyAssignment $a) => in_array($a->execution?->status, [
                VisitExecution::STATUS_IN_PROGRESS,
                VisitExecution::STATUS_DELAYED,
            ], true)
        )->count();

        $planned = $monthAssignments->filter(function (MonthlyAssignment $a) {
            $status = $a->execution?->status ?? VisitExecution::STATUS_PLANNED;

            return in_array($status, [VisitExecution::STATUS_PLANNED, VisitExecution::STATUS_RESCHEDULED, ''], true)
                || $a->execution === null;
        })->count();

        $accessibleShakhas = $this->access->accessibleShakhas($user);
        $shakhaRisk = $this->shakhaRiskForAccessible($accessibleShakhas);

        $myDrafts = AuditReport::query()
            ->ownedBy((int) $user->id)
            ->drafts()
            ->with('shakha:id,name')
            ->latest('last_saved_at')
            ->limit(8)
            ->get();

        $delayed = $monthAssignments->filter(
            fn (MonthlyAssignment $a) => ($a->execution?->status ?? '') === VisitExecution::STATUS_DELAYED
        )->count();

        $today = now('Asia/Dhaka')->startOfDay();
        $todayAssignments = $assignments->filter(function (MonthlyAssignment $a) use ($today) {
            if (! $a->start_date || ! $a->end_date) {
                return false;
            }

            return $a->start_date->lte($today) && $a->end_date->gte($today);
        })->values();

        $todayActive = $todayAssignments->filter(function (MonthlyAssignment $a) {
            $status = $a->execution?->status ?? VisitExecution::STATUS_PLANNED;

            return ! in_array($status, [
                VisitExecution::STATUS_COMPLETED,
                VisitExecution::STATUS_CANCELLED,
            ], true);
        })->count();

        $todayCompleted = $todayAssignments->filter(
            fn (MonthlyAssignment $a) => ($a->execution?->status ?? '') === VisitExecution::STATUS_COMPLETED
        )->count();

        $overdue = $assignments->filter(function (MonthlyAssignment $a) use ($today) {
            if (! $a->end_date || $a->end_date->gte($today)) {
                return false;
            }
            $status = $a->execution?->status ?? VisitExecution::STATUS_PLANNED;

            return ! in_array($status, [
                VisitExecution::STATUS_COMPLETED,
                VisitExecution::STATUS_CANCELLED,
            ], true);
        })->count();

        $monthCompletionPct = $monthAssignments->count() > 0
            ? round(($completed / $monthAssignments->count()) * 100, 1)
            : 0.0;

        $todayDay = ($today->year === (int) $monthMeta['year'] && $today->month === (int) $monthMeta['month'])
            ? (int) $today->day
            : null;

        $todayList = $todayAssignments->map(function (MonthlyAssignment $a) {
            $status = $a->execution?->status ?? VisitExecution::STATUS_PLANNED;

            return [
                'id' => $a->id,
                'label' => $this->entityLabel($a),
                'purpose' => $a->purpose ?: ($a->workItem?->activityType?->name ?? 'Visit'),
                'dates' => $a->visitDateRangeLabel(),
                'status' => $status,
                'status_label' => str_replace('_', ' ', $status),
                'tone' => $this->statusTone($status),
                'execution_url' => route('monthly-visits.execution', $a),
            ];
        })->values()->all();

        return [
            'fy' => $fy,
            'monthIndex' => $monthIndex,
            'monthMeta' => $monthMeta,
            'monthLabel' => $monthMeta['label'].' '.$monthMeta['year'],
            'monthOptions' => $fy->months(),
            'todayLabel' => $today->format('d M Y'),
            'fyMonthStrip' => collect($fy->months())->map(fn (array $m) => [
                'index' => $m['index'],
                'label' => $m['label'],
                'year' => $m['year'],
                'count' => $fyMonthCounts[$m['index']] ?? 0,
                'is_current' => $m['index'] === $currentIndex,
                'is_selected' => $m['index'] === $monthIndex,
            ])->all(),
            'stats' => [
                'visits_today' => $todayAssignments->count(),
                'today_active' => $todayActive,
                'today_completed' => $todayCompleted,
                'shakhas_month' => $shakhasThisMonth,
                'visits_month' => $monthAssignments->count(),
                'completed' => $completed,
                'in_progress' => $inProgress,
                'planned' => $planned,
                'delayed' => $delayed,
                'overdue' => $overdue,
                'month_completion_pct' => $monthCompletionPct,
                'total_access' => $accessibleShakhas->count(),
                'drafts' => $myDrafts->count(),
                'slots_left' => max(0, AuditReport::MAX_CONCURRENT_DRAFTS - $myDrafts->count()),
                'risk_significant' => $shakhaRisk['significant'],
                'risk_high' => $shakhaRisk['high'],
                'risk_critical' => $shakhaRisk['significant'] + $shakhaRisk['high'],
                'risk_other' => $shakhaRisk['other'],
                'risk_not_assessed' => $shakhaRisk['not_assessed'],
            ],
            'todayVisits' => $todayList,
            'timeline' => [
                'days_in_month' => $daysInMonth,
                'month_start' => $monthStart,
                'today_day' => $todayDay,
                'lanes' => $monthAssignments->map(function (MonthlyAssignment $a) use ($monthStart, $monthEnd, $daysInMonth) {
                    $start = $a->start_date->copy()->max($monthStart);
                    $end = $a->end_date->copy()->min($monthEnd);
                    $startDay = (int) $start->day;
                    $endDay = (int) $end->day;
                    $status = $a->execution?->status ?? VisitExecution::STATUS_PLANNED;

                    return [
                        'id' => $a->id,
                        'label' => $this->entityLabel($a),
                        'purpose' => $a->purpose ?: ($a->workItem?->activityType?->name ?? ''),
                        'start_day' => $startDay,
                        'end_day' => $endDay,
                        'span' => max(1, $endDay - $startDay + 1),
                        'offset_pct' => (($startDay - 1) / $daysInMonth) * 100,
                        'width_pct' => (max(1, $endDay - $startDay + 1) / $daysInMonth) * 100,
                        'dates_label' => $a->visitDateRangeLabel(),
                        'status' => $status,
                        'status_label' => str_replace('_', ' ', $status),
                        'tone' => $this->statusTone($status),
                        'execution_url' => route('monthly-visits.execution', $a),
                        'shakha_id' => $this->shakhaId($a),
                    ];
                })->values()->all(),
            ],
            'allocations' => $monthAssignments->map(function (MonthlyAssignment $a) {
                $status = $a->execution?->status ?? VisitExecution::STATUS_PLANNED;

                return [
                    'id' => $a->id,
                    'label' => $this->entityLabel($a),
                    'purpose' => $a->purpose ?: ($a->workItem?->activityType?->name ?? 'Visit'),
                    'dates' => $a->visitDateRangeLabel(),
                    'days' => $a->duration_days,
                    'status' => $status,
                    'status_label' => str_replace('_', ' ', $status),
                    'tone' => $this->statusTone($status),
                    'execution_url' => route('monthly-visits.execution', $a),
                    'shakha_id' => $this->shakhaId($a),
                ];
            })->values()->all(),
            'assignedShakhas' => $accessibleShakhas,
            'myDrafts' => $myDrafts,
        ];
    }

    /**
     * Risk breakdown for shakhas this officer can access.
     *
     * @param  Collection<int, Shakha>  $shakhas
     * @return array{significant:int,high:int,other:int,not_assessed:int}
     */
    protected function shakhaRiskForAccessible(Collection $shakhas): array
    {
        $ids = $shakhas->pluck('id')->map(fn ($id) => (int) $id)->all();
        $total = count($ids);

        if ($total === 0 || ! Schema::hasTable('shakha_risk_assessments')) {
            return [
                'significant' => 0,
                'high' => 0,
                'other' => 0,
                'not_assessed' => $total,
            ];
        }

        $latest = ShakhaRiskAssessment::query()
            ->whereIn('shakha_id', $ids)
            ->orderByDesc('assessment_year')
            ->orderByDesc('assessment_month')
            ->orderByDesc('id')
            ->get(['shakha_id', 'risk_category'])
            ->unique('shakha_id');

        $significant = $latest->where('risk_category', 'Significant Risk')->count();
        $high = $latest->where('risk_category', 'High Risk')->count();
        $other = $latest->filter(
            fn (ShakhaRiskAssessment $a) => ! in_array($a->risk_category, ['Significant Risk', 'High Risk'], true)
        )->count();

        return [
            'significant' => $significant,
            'high' => $high,
            'other' => $other,
            'not_assessed' => max(0, $total - $latest->count()),
        ];
    }

    /**
     * @return Collection<int, MonthlyAssignment>
     */
    protected function assignmentsForEmployee(User $user): Collection
    {
        if (! $user->employee_id || ! Schema::hasTable('monthly_assignments')) {
            return collect();
        }

        $employeeId = (int) $user->employee_id;

        return MonthlyAssignment::query()
            ->with([
                'workItem.schedulable',
                'workItem.activityType',
                'execution',
                'visitors',
            ])
            ->where(function ($q) use ($employeeId) {
                $q->where('employee_id', $employeeId)
                    ->orWhereHas('visitors', fn ($v) => $v->where('employees.id', $employeeId));
            })
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->orderBy('start_date')
            ->get();
    }

    /**
     * @param  Collection<int, MonthlyAssignment>  $assignments
     * @return array<int, int>
     */
    protected function fyMonthCounts(Collection $assignments, FinancialYear $fy): array
    {
        $counts = array_fill(0, 12, 0);

        foreach ($assignments as $a) {
            if (! $a->start_date) {
                continue;
            }
            // Count against work-item month when available, else start date within FY
            $index = $a->workItem?->month_index;
            if ($index === null) {
                $index = $fy->monthIndexForDate($a->start_date);
            }
            if ($index !== null && $index >= 0 && $index <= 11) {
                $counts[(int) $index]++;
            }
        }

        return $counts;
    }

    protected function entityLabel(MonthlyAssignment $a): string
    {
        return (string) (
            $a->workItem?->entity_label
            ?: $a->workItem?->schedulable?->name
            ?: 'Visit'
        );
    }

    protected function shakhaId(MonthlyAssignment $a): ?int
    {
        $item = $a->workItem;
        if (! $item || $item->schedulable_type !== Shakha::class) {
            return null;
        }

        return (int) $item->schedulable_id ?: null;
    }

    /**
     * @return array{bg:string,text:string,bar:string,dot:string}
     */
    protected function statusTone(string $status): array
    {
        return match ($status) {
            VisitExecution::STATUS_COMPLETED => [
                'bg' => 'bg-teal-50 border-teal-200/80',
                'text' => 'text-teal-800',
                'bar' => 'bg-gradient-to-r from-teal-500 to-emerald-500',
                'dot' => 'bg-teal-500',
            ],
            VisitExecution::STATUS_IN_PROGRESS => [
                'bg' => 'bg-amber-50 border-amber-200/80',
                'text' => 'text-amber-900',
                'bar' => 'bg-gradient-to-r from-amber-400 to-orange-500',
                'dot' => 'bg-amber-500',
            ],
            VisitExecution::STATUS_DELAYED => [
                'bg' => 'bg-rose-50 border-rose-200/80',
                'text' => 'text-rose-800',
                'bar' => 'bg-gradient-to-r from-rose-500 to-red-500',
                'dot' => 'bg-rose-500',
            ],
            VisitExecution::STATUS_CANCELLED => [
                'bg' => 'bg-slate-100 border-slate-200',
                'text' => 'text-slate-600',
                'bar' => 'bg-slate-400',
                'dot' => 'bg-slate-400',
            ],
            default => [
                'bg' => 'bg-sky-50 border-sky-200/80',
                'text' => 'text-sky-900',
                'bar' => 'bg-gradient-to-r from-sky-500 to-blue-600',
                'dot' => 'bg-sky-500',
            ],
        };
    }
}
