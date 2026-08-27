<?php

namespace App\Services;

use App\Models\AuditFinding;
use App\Models\AuditIndicator;
use App\Models\AuditPlan;
use App\Models\AuditReport;
use App\Models\MonthlyAssignment;
use App\Models\MonthlyWorkItem;
use App\Models\Shakha;
use App\Models\ShakhaAnnualKpi;
use App\Models\ShakhaRiskAssessment;
use App\Models\VisitExecution;
use App\Support\FinancialYear;
use Illuminate\Support\Facades\Schema;

/**
 * Ops-focused main dashboard pulse: act-this-week → my work → impact → health.
 */
class DashboardOpsService
{
    public function __construct(
        private MonthlyWorklistService $worklists,
        private AuditSummaryService $findings,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(?string $fyLabel = null, ?int $monthIndex = null, ?int $userId = null): array
    {
        $fy = $fyLabel ? FinancialYear::fromLabel($fyLabel) : FinancialYear::current(now('Asia/Dhaka'));
        $monthIndex ??= $fy->monthIndexForDate(now('Asia/Dhaka')) ?? 0;
        $monthIndex = max(0, min(11, $monthIndex));
        $monthMeta = $fy->months()[$monthIndex];
        $calendarMonth = (int) $monthMeta['month'];
        $calendarYear = (int) $monthMeta['year'];

        $plan = Schema::hasTable('audit_plans')
            ? AuditPlan::query()->where('fy_label', $fy->label)->first()
            : null;

        $visit = $this->visitPipeline($plan, $monthIndex);
        $act = $this->actThisWeek($plan, $monthIndex, $calendarMonth, $calendarYear, $visit);
        $myWork = $this->myWork($userId);
        $impact = $this->impact($calendarMonth, $calendarYear);
        $health = $this->health($fy, $calendarMonth, $calendarYear);

        return [
            'fy_label' => $fy->label,
            'month_index' => $monthIndex,
            'month_label' => $monthMeta['label'].' '.$monthMeta['year'],
            'period_label' => $monthMeta['label'].' '.$monthMeta['year'].' · FY '.$fy->label,
            'calendar_month' => $calendarMonth,
            'calendar_year' => $calendarYear,
            'plan' => $plan,
            'plan_status' => $plan?->status ?? 'missing',
            'month_options' => $fy->months(),
            'fy_options' => $this->fyOptions($fy),
            'visit' => $visit,
            'act' => $act,
            'my_work' => $myWork,
            'impact' => $impact,
            'health' => $health,
        ];
    }

    /**
     * @return list<string>
     */
    protected function fyOptions(FinancialYear $current): array
    {
        return [
            $current->previous()->label,
            $current->label,
            $current->next()->label,
            $current->next()->next()->label,
        ];
    }

    /**
     * @return array{planned:int,assigned:int,unassigned:int,completed:int,delayed:int,overdue_end:int,execution_pct:float}
     */
    protected function visitPipeline(?AuditPlan $plan, int $monthIndex): array
    {
        $empty = [
            'planned' => 0,
            'assigned' => 0,
            'unassigned' => 0,
            'completed' => 0,
            'delayed' => 0,
            'overdue_end' => 0,
            'execution_pct' => 0.0,
        ];

        if (! $plan || ! Schema::hasTable('monthly_work_items')) {
            return $empty;
        }

        $summary = $this->worklists->performanceSummary($plan, $monthIndex);
        $totals = $summary['totals'];

        $today = now('Asia/Dhaka')->toDateString();
        $overdueEnd = MonthlyAssignment::query()
            ->whereHas('workItem', fn ($q) => $q->where('audit_plan_id', $plan->id)->where('month_index', $monthIndex))
            ->whereDate('end_date', '<', $today)
            ->where(function ($q) {
                $q->whereDoesntHave('execution')
                    ->orWhereHas('execution', fn ($e) => $e->whereNotIn('status', [
                        VisitExecution::STATUS_COMPLETED,
                        VisitExecution::STATUS_CANCELLED,
                    ]));
            })
            ->count();

        $planned = (int) $totals['planned'];
        $completed = (int) $totals['completed'];

        return [
            'planned' => $planned,
            'assigned' => (int) $totals['assigned'],
            'unassigned' => (int) $totals['pending'],
            'completed' => $completed,
            'delayed' => (int) $totals['overdue'],
            'overdue_end' => $overdueEnd,
            'execution_pct' => $planned > 0 ? round(($completed / $planned) * 100, 1) : 0.0,
        ];
    }

    /**
     * @param  array{unassigned:int,delayed:int,overdue_end:int,completed:int,planned:int,execution_pct:float}  $visit
     * @return list<array{key:string,label:string,count:int,meta:string,href:string,tone:string,samples:list<string>}>
     */
    protected function actThisWeek(
        ?AuditPlan $plan,
        int $monthIndex,
        int $calendarMonth,
        int $calendarYear,
        array $visit
    ): array {
        $visitsUrl = route('monthly-visits.index', array_filter([
            'fy' => $plan?->fy_label,
            'month' => $monthIndex,
        ]));

        $items = [];

        $items[] = [
            'key' => 'unassigned',
            'label' => 'Unassigned visits',
            'count' => $visit['unassigned'],
            'meta' => 'Need staff allocation this FY month',
            'href' => $visitsUrl,
            'tone' => 'amber',
            'samples' => $this->sampleUnassignedLabels($plan, $monthIndex),
        ];

        $delayedCount = max($visit['delayed'], $visit['overdue_end']);
        $items[] = [
            'key' => 'delayed',
            'label' => 'Delayed / overdue visits',
            'count' => $delayedCount,
            'meta' => $visit['overdue_end'].' past end date · '.$visit['delayed'].' marked delayed',
            'href' => $visitsUrl,
            'tone' => 'rose',
            'samples' => $this->sampleOverdueLabels($plan, $monthIndex),
        ];

        $gap = $this->reportMatrixGap($calendarMonth, $calendarYear);
        $items[] = [
            'key' => 'matrix_gap',
            'label' => 'Report → matrix gap',
            'count' => $gap['count'],
            'meta' => 'Completed reports with no objected findings',
            'href' => route('audit-findings.index', ['month' => $calendarMonth, 'year' => $calendarYear]),
            'tone' => 'orange',
            'samples' => $gap['samples'],
        ];

        $aged = $this->agedDrafts();
        $items[] = [
            'key' => 'aged_drafts',
            'label' => 'Aged draft reports',
            'count' => $aged['count'],
            'meta' => 'Org drafts idle ≥ 7 days',
            'href' => route('audits.index'),
            'tone' => 'slate',
            'samples' => $aged['samples'],
        ];

        $uncovered = $this->highRiskUncovered($plan, $monthIndex);
        $items[] = [
            'key' => 'high_risk',
            'label' => 'High-risk uncovered',
            'count' => $uncovered['count'],
            'meta' => 'High/Significant risk with no visit this month',
            'href' => $visitsUrl,
            'tone' => 'rose',
            'samples' => $uncovered['samples'],
        ];

        return $items;
    }

    /**
     * @return list<string>
     */
    protected function sampleUnassignedLabels(?AuditPlan $plan, int $monthIndex): array
    {
        if (! $plan || ! Schema::hasTable('monthly_work_items')) {
            return [];
        }

        return MonthlyWorkItem::query()
            ->where('audit_plan_id', $plan->id)
            ->where('month_index', $monthIndex)
            ->where('status', MonthlyWorkItem::STATUS_UNASSIGNED)
            ->orderBy('entity_label')
            ->limit(3)
            ->pluck('entity_label')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    protected function sampleOverdueLabels(?AuditPlan $plan, int $monthIndex): array
    {
        if (! $plan || ! Schema::hasTable('monthly_assignments')) {
            return [];
        }

        $today = now('Asia/Dhaka')->toDateString();

        return MonthlyAssignment::query()
            ->whereHas('workItem', fn ($q) => $q->where('audit_plan_id', $plan->id)->where('month_index', $monthIndex))
            ->whereDate('end_date', '<', $today)
            ->where(function ($q) {
                $q->whereDoesntHave('execution')
                    ->orWhereHas('execution', fn ($e) => $e->whereNotIn('status', [
                        VisitExecution::STATUS_COMPLETED,
                        VisitExecution::STATUS_CANCELLED,
                    ]));
            })
            ->with('workItem:id,entity_label')
            ->limit(3)
            ->get()
            ->map(fn (MonthlyAssignment $a) => (string) ($a->workItem?->entity_label ?: 'Assignment #'.$a->id))
            ->values()
            ->all();
    }

    /**
     * @return array{count:int,samples:list<string>}
     */
    protected function reportMatrixGap(int $month, int $year): array
    {
        if (! Schema::hasTable('audit_reports') || ! Schema::hasTable('audit_findings')) {
            return ['count' => 0, 'samples' => []];
        }

        $objectedShakhaIds = AuditFinding::query()
            ->where('audit_month', $month)
            ->where('audit_year', $year)
            ->where(function ($q) {
                $q->where('irregularity_count', '>', 0)
                    ->orWhere('amount', '>', 0)
                    ->orWhere(function ($inner) {
                        $inner->whereNotNull('observation')->where('observation', '!=', '');
                    });
            })
            ->pluck('shakha_id')
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->all();

        $reports = AuditReport::query()
            ->completed()
            ->where('report_month', $month)
            ->where('report_year', $year)
            ->with('shakha:id,name')
            ->get(['id', 'shakha_id', 'shakha_display_name']);

        $gaps = $reports->filter(
            fn (AuditReport $report) => ! in_array((int) $report->shakha_id, $objectedShakhaIds, true)
        );

        return [
            'count' => $gaps->count(),
            'samples' => $gaps->take(3)->map(
                fn (AuditReport $r) => (string) ($r->shakha_display_name ?: $r->shakha?->name ?: 'Report #'.$r->id)
            )->values()->all(),
        ];
    }

    /**
     * @return array{count:int,samples:list<string>}
     */
    protected function agedDrafts(): array
    {
        if (! Schema::hasTable('audit_reports')) {
            return ['count' => 0, 'samples' => []];
        }

        $cutoff = now('Asia/Dhaka')->subDays(7);

        $base = AuditReport::query()
            ->drafts()
            ->where(function ($q) use ($cutoff) {
                $q->where('last_saved_at', '<=', $cutoff)
                    ->orWhere(function ($inner) use ($cutoff) {
                        $inner->whereNull('last_saved_at')->where('updated_at', '<=', $cutoff);
                    });
            });

        $count = (clone $base)->count();
        $drafts = (clone $base)
            ->with('shakha:id,name')
            ->orderByRaw('COALESCE(last_saved_at, updated_at) ASC')
            ->limit(3)
            ->get(['id', 'shakha_id', 'shakha_display_name', 'last_saved_at', 'progress_pct']);

        return [
            'count' => $count,
            'samples' => $drafts->map(
                fn (AuditReport $r) => (string) ($r->shakha_display_name ?: $r->shakha?->name ?: 'Draft #'.$r->id)
                    .' · '.(int) $r->progress_pct.'%'
            )->values()->all(),
        ];
    }

    /**
     * @return array{count:int,samples:list<string>}
     */
    protected function highRiskUncovered(?AuditPlan $plan, int $monthIndex): array
    {
        if (! Schema::hasTable('shakha_risk_assessments')) {
            return ['count' => 0, 'samples' => []];
        }

        $latest = ShakhaRiskAssessment::query()
            ->orderByDesc('assessment_year')
            ->orderByDesc('assessment_month')
            ->orderByDesc('id')
            ->get(['id', 'shakha_id', 'risk_category', 'assessment_month', 'assessment_year'])
            ->unique('shakha_id');

        $highRisk = $latest->filter(
            fn (ShakhaRiskAssessment $a) => in_array($a->risk_category, ['High Risk', 'Significant Risk'], true)
        );

        if ($highRisk->isEmpty()) {
            return ['count' => 0, 'samples' => []];
        }

        $coveredIds = [];
        if ($plan && Schema::hasTable('monthly_work_items')) {
            $coveredIds = MonthlyWorkItem::query()
                ->where('audit_plan_id', $plan->id)
                ->where('month_index', $monthIndex)
                ->where('schedulable_type', Shakha::class)
                ->pluck('schedulable_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $uncovered = $highRisk->filter(
            fn (ShakhaRiskAssessment $a) => ! in_array((int) $a->shakha_id, $coveredIds, true)
        );

        $names = Shakha::query()
            ->whereIn('id', $uncovered->take(3)->pluck('shakha_id'))
            ->pluck('name', 'id');

        return [
            'count' => $uncovered->count(),
            'samples' => $uncovered->take(3)->map(
                fn (ShakhaRiskAssessment $a) => (string) ($names[$a->shakha_id] ?? 'Shakha #'.$a->shakha_id)
                    .' · '.$a->risk_category
            )->values()->all(),
        ];
    }

    /**
     * @return array{ongoing:int,slots_left:int,completed_period:int,drafts:list<array{id:int,label:string,progress:int,href:string}>}
     */
    protected function myWork(?int $userId): array
    {
        if (! $userId || ! Schema::hasTable('audit_reports')) {
            return [
                'ongoing' => 0,
                'slots_left' => AuditReport::MAX_CONCURRENT_DRAFTS,
                'completed_period' => 0,
                'drafts' => [],
            ];
        }

        $drafts = AuditReport::query()
            ->ownedBy($userId)
            ->drafts()
            ->with('shakha:id,name')
            ->latest('last_saved_at')
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $ongoing = AuditReport::query()->ownedBy($userId)->drafts()->count();

        return [
            'ongoing' => $ongoing,
            'slots_left' => max(0, AuditReport::MAX_CONCURRENT_DRAFTS - $ongoing),
            'completed_period' => 0,
            'drafts' => $drafts->map(fn (AuditReport $r) => [
                'id' => $r->id,
                'label' => (string) ($r->shakha_display_name ?: $r->shakha?->name ?: 'Draft #'.$r->id),
                'progress' => (int) $r->progress_pct,
                'href' => route('audits.index'),
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function impact(int $month, int $year): array
    {
        if (! Schema::hasTable('audit_findings') || ! Schema::hasTable('audit_indicators')) {
            return [
                'total_amount_fmt' => '0.00',
                'major_risk_hits' => 0,
                'branches_objected' => 0,
                'top_indicators' => [],
                'top_branches' => [],
                'categories' => [],
            ];
        }

        $insights = $this->findings->getDashboardFindingInsights($month, $year);

        $branchesObjected = (int) AuditFinding::query()
            ->where('audit_month', $month)
            ->where('audit_year', $year)
            ->where(function ($q) {
                $q->where('irregularity_count', '>', 0)
                    ->orWhere('amount', '>', 0)
                    ->orWhere(function ($inner) {
                        $inner->whereNotNull('observation')->where('observation', '!=', '');
                    });
            })
            ->select('shakha_id')
            ->distinct()
            ->count('shakha_id');

        return [
            'total_amount_fmt' => $insights['total_amount_fmt'],
            'major_risk_hits' => $insights['major_risk_hits'],
            'branches_objected' => $branchesObjected,
            'top_indicators' => $insights['top_indicators'],
            'top_branches' => $insights['top_branches'],
            'categories' => $insights['categories'],
        ];
    }

    /**
     * @return list<array{label:string,count:int,meta:string,href:string}>
     */
    protected function health(FinancialYear $fy, int $calendarMonth, int $calendarYear): array
    {
        $activeShakhaIds = Schema::hasTable('shakhas')
            ? Shakha::query()->where('status', 'active')->pluck('id')
            : collect();

        $kpiMissing = 0;
        if (Schema::hasTable('shakha_annual_kpis') && $activeShakhaIds->isNotEmpty()) {
            $withKpi = ShakhaAnnualKpi::query()
                ->where('fy_label', $fy->label)
                ->whereIn('shakha_id', $activeShakhaIds)
                ->pluck('shakha_id')
                ->unique();
            $kpiMissing = $activeShakhaIds->diff($withKpi)->count();
        }

        $riskMissing = 0;
        if (Schema::hasTable('shakha_risk_assessments') && $activeShakhaIds->isNotEmpty()) {
            $assessed = ShakhaRiskAssessment::query()
                ->whereIn('shakha_id', $activeShakhaIds)
                ->pluck('shakha_id')
                ->unique();
            $riskMissing = $activeShakhaIds->diff($assessed)->count();
        }

        $monthStart = now('Asia/Dhaka')->setDate($calendarYear, $calendarMonth, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $newIndicators = Schema::hasTable('audit_indicators')
            ? AuditIndicator::query()
                ->where(function ($query) {
                    $query->where('indicator_code', 'like', 'রিপোর্ট-%')
                        ->orWhere('category', 'আর্থিক নিরীক্ষা (রিপোর্ট)');
                })
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count()
            : 0;

        $conflicts = 0;
        if (Schema::hasTable('monthly_assignments')) {
            $conflicts = MonthlyAssignment::query()
                ->where('is_override_conflict', true)
                ->count();
        }

        return [
            [
                'label' => 'KPI missing (FY)',
                'count' => $kpiMissing,
                'meta' => 'Active shakhas without annual KPI — blocks risk',
                'href' => route('kpis.index'),
            ],
            [
                'label' => 'Risk not assessed',
                'count' => $riskMissing,
                'meta' => 'Active shakhas with no risk assessment',
                'href' => route('shakhas.index'),
            ],
            [
                'label' => 'New report indicators',
                'count' => $newIndicators,
                'meta' => 'Catalog additions from report শিরোনাম',
                'href' => route('audit-findings.index', ['month' => $calendarMonth, 'year' => $calendarYear]),
            ],
            [
                'label' => 'Schedule conflicts',
                'count' => $conflicts,
                'meta' => 'Assignments flagged as override conflict',
                'href' => route('monthly-visits.index'),
            ],
        ];
    }
}
