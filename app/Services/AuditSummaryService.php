<?php

namespace App\Services;

use App\Models\AuditFinding;
use App\Models\AuditIndicator;
use App\Models\AuditReport;
use App\Models\Shakha;
use App\Models\ShakhaEmployee;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Rebuilds Excel “AUDIT FINDINGS CONSOLIDATED” left-side org totals
 * from sparse audit_findings rows (shakha × indicator × month × year).
 */
class AuditSummaryService
{
    /**
     * @return Collection<int, object>
     */
    public function getOrganizationTotals(int $month, int $year): Collection
    {
        $aggregates = AuditFinding::query()
            ->select([
                'audit_indicator_id',
                DB::raw('COALESCE(SUM(amount), 0) as total_amount'),
                DB::raw('COALESCE(SUM(sample_size_checked), 0) as total_samples_checked'),
                DB::raw('COALESCE(SUM(irregularity_count), 0) as total_irregularities'),
                DB::raw('COUNT(*) as reports_with_finding'),
                DB::raw(
                    'SUM(CASE WHEN COALESCE(irregularity_count, 0) > 0
                        OR COALESCE(amount, 0) > 0
                        OR (observation IS NOT NULL AND observation != \'\')
                        THEN 1 ELSE 0 END) as objected_branch_count'
                ),
            ])
            ->where('audit_month', $month)
            ->where('audit_year', $year)
            ->groupBy('audit_indicator_id')
            ->get()
            ->keyBy('audit_indicator_id');

        return AuditIndicator::query()
            ->active()
            ->orderBy('category')
            ->orderBy('sub_category')
            ->orderBy('indicator_code')
            ->get()
            ->map(function (AuditIndicator $indicator) use ($aggregates) {
                $row = $aggregates->get($indicator->id);

                return (object) [
                    'indicator_id' => $indicator->id,
                    'category' => $indicator->category ?: '—',
                    'sub_category' => $indicator->sub_category ?: '—',
                    'code' => $indicator->indicator_code,
                    'title' => $indicator->title,
                    'risk_rating' => $indicator->risk_rating ?: '—',
                    'total_amount' => (float) ($row->total_amount ?? 0),
                    'total_samples_checked' => (int) ($row->total_samples_checked ?? 0),
                    'total_irregularities' => (int) ($row->total_irregularities ?? 0),
                    'objected_branch_count' => (int) ($row->objected_branch_count ?? 0),
                    'reports_with_finding' => (int) ($row->reports_with_finding ?? 0),
                ];
            });
    }

    /**
     * @return Collection<int, AuditFinding>
     */
    public function getIndicatorBranchFindings(int $indicatorId, int $month, int $year): Collection
    {
        return AuditFinding::query()
            ->with(['shakha.area'])
            ->where('audit_indicator_id', $indicatorId)
            ->where('audit_month', $month)
            ->where('audit_year', $year)
            ->get()
            ->sortBy(fn (AuditFinding $f) => $f->shakha?->name ?? '')
            ->values();
    }

    /**
     * Full month matrix: indicator rows × shakha columns (+ org totals).
     *
     * @return array{
     *     month:int,
     *     year:int,
     *     period_label:string,
     *     indicators:list<array{id:int,category:string,sub_category:string,code:string,title:string,risk_rating:string,total_amount:float,total_samples_checked:int,total_irregularities:int,objected_branch_count:int}>,
     *     shakhas:list<array{id:int,name:string,code:string,area:string}>,
     *     cells:array<int, array<int, array{amount:float|null,sample_size_checked:int|null,irregularity_count:int|null,observation:string|null,responsible_staff_name:string|null}>>
     * }
     */
    public function getMonthConsolidatedMatrix(int $month, int $year): array
    {
        $month = max(1, min(12, $month));
        $year = max(2000, min(2100, $year));

        $orgTotals = $this->getOrganizationTotals($month, $year)->keyBy('indicator_id');

        $indicators = AuditIndicator::query()
            ->active()
            ->orderBy('category')
            ->orderBy('sub_category')
            ->orderBy('indicator_code')
            ->get(['id', 'category', 'sub_category', 'indicator_code', 'title', 'risk_rating'])
            ->map(function (AuditIndicator $indicator) use ($orgTotals) {
                $org = $orgTotals->get($indicator->id);

                return [
                    'id' => (int) $indicator->id,
                    'category' => (string) ($indicator->category ?: '—'),
                    'sub_category' => (string) ($indicator->sub_category ?: '—'),
                    'code' => (string) $indicator->indicator_code,
                    'title' => (string) $indicator->title,
                    'risk_rating' => (string) ($indicator->risk_rating ?: '—'),
                    'total_amount' => (float) ($org->total_amount ?? 0),
                    'total_samples_checked' => (int) ($org->total_samples_checked ?? 0),
                    'total_irregularities' => (int) ($org->total_irregularities ?? 0),
                    'objected_branch_count' => (int) ($org->objected_branch_count ?? 0),
                ];
            })
            ->values()
            ->all();

        $findings = AuditFinding::query()
            ->with(['shakha.area'])
            ->where('audit_month', $month)
            ->where('audit_year', $year)
            ->get();

        $shakhaMap = [];
        $cells = [];

        foreach ($findings as $finding) {
            $shakhaId = (int) $finding->shakha_id;
            $indicatorId = (int) $finding->audit_indicator_id;

            if (! isset($shakhaMap[$shakhaId])) {
                $shakhaMap[$shakhaId] = [
                    'id' => $shakhaId,
                    'name' => (string) ($finding->shakha?->name ?: 'Branch #'.$shakhaId),
                    'code' => (string) ($finding->shakha?->code ?: ''),
                    'area' => (string) ($finding->shakha?->area?->name ?: ''),
                ];
            }

            $cells[$indicatorId][$shakhaId] = [
                'amount' => $finding->amount !== null ? (float) $finding->amount : null,
                'sample_size_checked' => $finding->sample_size_checked !== null ? (int) $finding->sample_size_checked : null,
                'irregularity_count' => $finding->irregularity_count !== null ? (int) $finding->irregularity_count : null,
                'observation' => $finding->observation !== null ? (string) $finding->observation : null,
                'responsible_staff_name' => $finding->responsible_staff_name !== null ? (string) $finding->responsible_staff_name : null,
            ];
        }

        $shakhas = collect($shakhaMap)
            ->sortBy(fn ($s) => mb_strtolower($s['name']))
            ->values()
            ->all();

        return [
            'month' => $month,
            'year' => $year,
            'period_label' => date('F', mktime(0, 0, 0, $month, 1)).' '.$year,
            'indicators' => $indicators,
            'shakhas' => $shakhas,
            'cells' => $cells,
        ];
    }

    public function upsertFinding(
        int $shakhaId,
        int $indicatorId,
        int $month,
        int $year,
        array $data
    ): ?AuditFinding {
        $payload = [
            'amount' => $data['amount'] ?? null,
            'sample_size_checked' => $data['sample_size_checked'] ?? null,
            'irregularity_count' => $data['irregularity_count'] ?? null,
            'observation' => $data['observation'] ?? null,
            'responsible_staff_name' => $data['responsible_staff_name'] ?? null,
            'responsible_staff_ids' => $this->normalizeStaffIds($data['responsible_staff_ids'] ?? null),
        ];

        if ($payload['responsible_staff_ids'] === [] && filled($payload['responsible_staff_name'])) {
            $payload['responsible_staff_ids'] = app(StaffFinancialOccurrenceService::class)
                ->resolveIdsFromStaffName((string) $payload['responsible_staff_name'], $shakhaId);
        }

        $isEmpty = blank($payload['amount'])
            && blank($payload['sample_size_checked'])
            && blank($payload['irregularity_count'])
            && blank($payload['observation'])
            && blank($payload['responsible_staff_name'])
            && $payload['responsible_staff_ids'] === [];

        $keys = [
            'shakha_id' => $shakhaId,
            'audit_indicator_id' => $indicatorId,
            'audit_month' => $month,
            'audit_year' => $year,
        ];

        if ($isEmpty) {
            AuditFinding::query()->where($keys)->delete();

            return null;
        }

        return AuditFinding::query()->updateOrCreate($keys, $payload);
    }

    /**
     * Main-dashboard finding insights for a calendar month/year.
     *
     * @return array{
     *     month:int,
     *     year:int,
     *     period_label:string,
     *     findings_cells:int,
     *     branches_with_findings:int,
     *     active_shakhas:int,
     *     indicators_hit:int,
     *     total_amount:float,
     *     total_amount_fmt:string,
     *     total_irregularities:int,
     *     total_samples:int,
     *     defect_rate:float,
     *     major_risk_hits:int,
     *     new_indicators_count:int,
     *     backlog_count:int,
     *     top_indicators:list<array{title:string,code:string,amount_fmt:string,url:string}>,
     *     top_branches:list<array{name:string,amount_fmt:string,cells:int}>,
     *     categories:list<array{name:string,amount_fmt:string,hits:int}>
     * }
     */
    public function getDashboardFindingInsights(int $month, int $year): array
    {
        $periodFindings = AuditFinding::query()
            ->where('audit_month', $month)
            ->where('audit_year', $year);

        $findingsCells = (clone $periodFindings)->count();
        $branchesWithFindings = (int) (clone $periodFindings)
            ->select('shakha_id')
            ->distinct()
            ->count('shakha_id');

        $sums = (clone $periodFindings)
            ->selectRaw('COALESCE(SUM(amount), 0) as total_amount')
            ->selectRaw('COALESCE(SUM(irregularity_count), 0) as total_irregularities')
            ->selectRaw('COALESCE(SUM(sample_size_checked), 0) as total_samples')
            ->first();

        $totalAmount = (float) ($sums->total_amount ?? 0);
        $totalIrregularities = (int) ($sums->total_irregularities ?? 0);
        $totalSamples = (int) ($sums->total_samples ?? 0);
        $defectRate = $totalSamples > 0
            ? round(($totalIrregularities / $totalSamples) * 100, 1)
            : 0.0;

        $totals = $this->getOrganizationTotals($month, $year);
        $hitRows = $totals->filter(
            fn ($row) => $row->objected_branch_count > 0
                || $row->total_irregularities > 0
                || $row->total_amount > 0
        );

        $indicatorsHit = $hitRows->count();
        $majorRiskHits = $hitRows
            ->filter(fn ($row) => str_contains(Str::lower((string) $row->risk_rating), 'major'))
            ->count();

        $topIndicators = $hitRows
            ->sortByDesc('total_amount')
            ->take(5)
            ->values()
            ->map(fn ($row) => [
                'title' => (string) $row->title,
                'code' => (string) $row->code,
                'amount_fmt' => number_format($row->total_amount, 2),
                'url' => route('audit-findings.show', [
                    'indicator' => $row->indicator_id,
                    'month' => $month,
                    'year' => $year,
                ]),
            ])
            ->all();

        $topBranches = AuditFinding::query()
            ->select([
                'shakha_id',
                DB::raw('COALESCE(SUM(amount), 0) as total_amount'),
                DB::raw('COUNT(*) as cells'),
            ])
            ->where('audit_month', $month)
            ->where('audit_year', $year)
            ->groupBy('shakha_id')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->with('shakha:id,name,code')
            ->get()
            ->map(fn (AuditFinding $row) => [
                'name' => (string) ($row->shakha?->name ?: 'Branch #'.$row->shakha_id),
                'amount_fmt' => number_format((float) $row->total_amount, 2),
                'cells' => (int) $row->cells,
            ])
            ->all();

        $categories = $hitRows
            ->groupBy(fn ($row) => (string) $row->category)
            ->map(function (Collection $group, string $name) {
                return [
                    'name' => $name !== '' ? $name : '—',
                    'amount' => (float) $group->sum('total_amount'),
                    'amount_fmt' => number_format($group->sum('total_amount'), 2),
                    'hits' => $group->count(),
                ];
            })
            ->sortByDesc('amount')
            ->take(6)
            ->values()
            ->all();

        $monthStart = now('Asia/Dhaka')->setDate($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $newIndicatorsCount = AuditIndicator::query()
            ->where(function ($query) {
                $query->where('indicator_code', 'like', 'রিপোর্ট-%')
                    ->orWhere('indicator_code', 'like', '৯০০০-%')
                    ->orWhere('indicator_code', 'like', '9000-%')
                    ->orWhere('category', 'আর্থিক নিরীক্ষা (রিপোর্ট)')
                    ->orWhere('category', 'নিরীক্ষা প্রতিবেদন');
            })
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->count();

        $activeShakhas = Shakha::query()->where('status', 'active')->count();

        $backlogCount = 0;
        if (Schema::hasTable('audit_reports')) {
            $reportsInPeriod = AuditReport::query()
                ->where('report_month', $month)
                ->where('report_year', $year)
                ->get(['id', 'shakha_id', 'status']);

            $shakhasWithFindings = AuditFinding::query()
                ->where('audit_month', $month)
                ->where('audit_year', $year)
                ->pluck('shakha_id')
                ->unique()
                ->all();

            $backlogCount = $reportsInPeriod
                ->filter(fn (AuditReport $report) => ! in_array((int) $report->shakha_id, array_map('intval', $shakhasWithFindings), true))
                ->count();
        }

        return [
            'month' => $month,
            'year' => $year,
            'period_label' => $monthStart->format('F Y'),
            'findings_cells' => $findingsCells,
            'branches_with_findings' => $branchesWithFindings,
            'active_shakhas' => $activeShakhas,
            'indicators_hit' => $indicatorsHit,
            'total_amount' => $totalAmount,
            'total_amount_fmt' => number_format($totalAmount, 2),
            'total_irregularities' => $totalIrregularities,
            'total_samples' => $totalSamples,
            'defect_rate' => $defectRate,
            'major_risk_hits' => $majorRiskHits,
            'new_indicators_count' => $newIndicatorsCount,
            'backlog_count' => $backlogCount,
            'top_indicators' => $topIndicators,
            'top_branches' => $topBranches,
            'categories' => $categories,
        ];
    }

    /**
     * Authority / admin month brief — clear irregularities picture for leadership.
     *
     * @return array<string, mixed>
     */
    public function getAuthorityMonthSummary(int $month, int $year): array
    {
        $base = $this->getDashboardFindingInsights($month, $year);

        $prev = now('Asia/Dhaka')->setDate($year, $month, 1)->subMonth();
        $prevInsights = $this->getDashboardFindingInsights((int) $prev->month, (int) $prev->year);

        $delta = function (int|float $current, int|float $previous): array {
            $diff = $current - $previous;
            $pct = $previous != 0 ? round(($diff / abs($previous)) * 100, 1) : null;

            return [
                'diff' => $diff,
                'pct' => $pct,
                'direction' => $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'flat'),
            ];
        };

        $branchRows = AuditFinding::query()
            ->select([
                'shakha_id',
                DB::raw('COALESCE(SUM(amount), 0) as total_amount'),
                DB::raw('COALESCE(SUM(irregularity_count), 0) as total_irregularities'),
                DB::raw('COALESCE(SUM(sample_size_checked), 0) as total_samples'),
                DB::raw('COUNT(*) as cells'),
            ])
            ->where('audit_month', $month)
            ->where('audit_year', $year)
            ->groupBy('shakha_id')
            ->orderByDesc('total_irregularities')
            ->orderByDesc('total_amount')
            ->with('shakha:id,name,code')
            ->get()
            ->map(function (AuditFinding $row) {
                $irregs = (int) $row->total_irregularities;
                $samples = (int) $row->total_samples;

                return [
                    'shakha_id' => (int) $row->shakha_id,
                    'name' => (string) ($row->shakha?->name ?: 'Branch #'.$row->shakha_id),
                    'code' => (string) ($row->shakha?->code ?: ''),
                    'amount' => (float) $row->total_amount,
                    'amount_fmt' => number_format((float) $row->total_amount, 2),
                    'irregularities' => $irregs,
                    'samples' => $samples,
                    'cells' => (int) $row->cells,
                    'defect_rate' => $samples > 0 ? round(($irregs / $samples) * 100, 1) : 0.0,
                ];
            })
            ->values()
            ->all();

        $maxBranchIrregs = max(1, (int) collect($branchRows)->max('irregularities'));

        $issueRows = $this->getOrganizationTotals($month, $year)
            ->filter(fn ($row) => $row->objected_branch_count > 0 || $row->total_irregularities > 0 || $row->total_amount > 0)
            ->sortByDesc('total_irregularities')
            ->take(12)
            ->values()
            ->map(function ($row) use ($month, $year) {
                return [
                    'indicator_id' => (int) $row->indicator_id,
                    'code' => (string) $row->code,
                    'title' => (string) $row->title,
                    'category' => (string) $row->category,
                    'risk_rating' => (string) $row->risk_rating,
                    'amount' => (float) $row->total_amount,
                    'amount_fmt' => number_format($row->total_amount, 2),
                    'irregularities' => (int) $row->total_irregularities,
                    'objected_branches' => (int) $row->objected_branch_count,
                    'url' => route('audit-findings.show', [
                        'indicator' => $row->indicator_id,
                        'month' => $month,
                        'year' => $year,
                    ]),
                ];
            })
            ->all();

        $maxIssueIrregs = max(1, (int) collect($issueRows)->max('irregularities'));

        $categoryMax = max(1, (int) collect($base['categories'])->max('hits'));

        $headline = $base['total_irregularities'] > 0
            ? sprintf(
                '%s irregularities across %d branches · ৳%s under observation',
                number_format($base['total_irregularities']),
                $base['branches_with_findings'],
                $base['total_amount_fmt']
            )
            : 'No irregularities recorded for this month yet.';

        return array_merge($base, [
            'prev_period_label' => $prev->format('F Y'),
            'deltas' => [
                'irregularities' => $delta($base['total_irregularities'], $prevInsights['total_irregularities']),
                'amount' => $delta($base['total_amount'], $prevInsights['total_amount']),
                'branches' => $delta($base['branches_with_findings'], $prevInsights['branches_with_findings']),
                'indicators_hit' => $delta($base['indicators_hit'], $prevInsights['indicators_hit']),
            ],
            'headline' => $headline,
            'branch_rows' => $branchRows,
            'issue_rows' => $issueRows,
            'max_branch_irregularities' => $maxBranchIrregs,
            'max_issue_irregularities' => $maxIssueIrregs,
            'category_max_hits' => $categoryMax,
            'coverage_pct' => $base['active_shakhas'] > 0
                ? round(($base['branches_with_findings'] / $base['active_shakhas']) * 100, 1)
                : 0.0,
        ]);
    }

    /**
     * Month summary grouped by category / sub-category (underheadings).
     * Each indicator row: amount, samples, irregularities, % (irre÷samples),
     * total branch count and branch names as separate fields.
     *
     * @return list<array{
     *   category:string,
     *   sub_category:string,
     *   rows:list<array{
     *     indicator_id:int,
     *     code:string,
     *     title:string,
     *     amount:float,
     *     amount_fmt:string,
     *     samples:int,
     *     irregularities:int,
     *     percentage:float|null,
     *     percentage_fmt:string,
     *     branch_count:int,
     *     branches:string,
     *     branch_rows:list<array{shakha_id:int,label:string,accused_kormi:string,accused_people:list<array{id:?int,label:string,report_count:int,dossier_url:?string}>}>,
     *     url:string
     *   }>
     * }>
     */
    public function getMonthUnderheadingSummary(int $month, int $year): array
    {
        $month = max(1, min(12, $month));
        $year = max(2000, min(2100, $year));

        $findings = AuditFinding::query()
            ->with(['indicator:id,indicator_code,title,category,sub_category', 'shakha:id,name,code'])
            ->where('audit_month', $month)
            ->where('audit_year', $year)
            ->get();

        /** @var array<int, array{indicator:?AuditIndicator, amount:float, samples:int, irregularities:int, branches:array<int, array{shakha_id:int, label:string, accused_kormi:string, accused_people:list<array{id:?int,label:string,report_count:int,dossier_url:?string}>}>}> $byIndicator */
        $byIndicator = [];

        $occurrence = app(StaffFinancialOccurrenceService::class);
        $allStaffIds = [];
        foreach ($findings as $finding) {
            $ids = $finding->responsibleStaffIds();
            if ($ids === [] && filled($finding->responsible_staff_name)) {
                $ids = $occurrence->resolveIdsFromStaffName(
                    (string) $finding->responsible_staff_name,
                    (int) $finding->shakha_id
                );
            }
            foreach ($ids as $id) {
                $allStaffIds[] = $id;
            }
        }
        $lifetimeCounts = $occurrence->lifetimeVisitCounts($allStaffIds);
        $employeesById = ShakhaEmployee::query()
            ->whereIn('id', array_values(array_unique($allStaffIds)))
            ->get(['id', 'employee_code', 'name'])
            ->keyBy('id');

        foreach ($findings as $finding) {
            $indicatorId = (int) $finding->audit_indicator_id;
            if ($indicatorId < 1) {
                continue;
            }

            if (! isset($byIndicator[$indicatorId])) {
                $byIndicator[$indicatorId] = [
                    'indicator' => $finding->indicator,
                    'amount' => 0.0,
                    'samples' => 0,
                    'irregularities' => 0,
                    'branches' => [],
                ];
            }

            $byIndicator[$indicatorId]['amount'] += (float) ($finding->amount ?? 0);
            $byIndicator[$indicatorId]['samples'] += (int) ($finding->sample_size_checked ?? 0);
            $byIndicator[$indicatorId]['irregularities'] += (int) ($finding->irregularity_count ?? 0);

            $staffIds = $finding->responsibleStaffIds();
            if ($staffIds === [] && filled($finding->responsible_staff_name)) {
                $staffIds = $occurrence->resolveIdsFromStaffName(
                    (string) $finding->responsible_staff_name,
                    (int) $finding->shakha_id
                );
            }

            $hasSignal = (float) ($finding->amount ?? 0) > 0
                || (int) ($finding->sample_size_checked ?? 0) > 0
                || (int) ($finding->irregularity_count ?? 0) > 0
                || filled($finding->observation)
                || filled($finding->responsible_staff_name)
                || $staffIds !== [];

            if ($hasSignal && $finding->shakha) {
                $shakhaId = (int) $finding->shakha_id;
                $label = trim((string) $finding->shakha->name);
                if ($finding->shakha->code) {
                    $label .= ' ('.$finding->shakha->code.')';
                }
                $accused = $this->formatAccusedKormiForDisplay(
                    (int) $finding->shakha_id,
                    trim((string) ($finding->responsible_staff_name ?? ''))
                );
                $accusedPeople = $this->buildAccusedPeopleForSummary(
                    $accused,
                    $staffIds,
                    $employeesById,
                    $lifetimeCounts
                );
                $byIndicator[$indicatorId]['branches'][$shakhaId] = [
                    'shakha_id' => $shakhaId,
                    'label' => $label !== '' ? $label : 'Branch #'.$shakhaId,
                    'accused_kormi' => $accused,
                    'accused_people' => $accusedPeople,
                ];
            }
        }

        $groups = [];
        foreach ($byIndicator as $indicatorId => $bag) {
            $indicator = $bag['indicator'];
            if (! $indicator) {
                $indicator = AuditIndicator::query()->find($indicatorId);
            }
            if (! $indicator) {
                continue;
            }

            $amount = (float) $bag['amount'];
            $samples = (int) $bag['samples'];
            $irregs = (int) $bag['irregularities'];
            $branchRows = array_values($bag['branches']);
            usort($branchRows, fn ($a, $b) => strnatcasecmp((string) $a['label'], (string) $b['label']));
            $branchLabels = array_map(fn ($b) => (string) $b['label'], $branchRows);

            if ($amount <= 0 && $samples <= 0 && $irregs <= 0 && $branchRows === []) {
                continue;
            }

            // Percentage = irregularities ÷ samples (same rule as Report Rating Box).
            $percentage = $samples > 0 ? round(($irregs / $samples) * 100, 2) : null;

            $category = (string) ($indicator->category ?: 'Other');
            $subCategory = (string) ($indicator->sub_category ?: '—');
            $groupKey = $category."\0".$subCategory;

            if (! isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'category' => $category,
                    'sub_category' => $subCategory,
                    'rows' => [],
                ];
            }

            $groups[$groupKey]['rows'][] = [
                'indicator_id' => (int) $indicator->id,
                'code' => (string) $indicator->indicator_code,
                'title' => (string) $indicator->title,
                'amount' => $amount,
                'amount_fmt' => number_format($amount, 2),
                'samples' => $samples,
                'irregularities' => $irregs,
                'percentage' => $percentage,
                'percentage_fmt' => $percentage === null ? '—' : number_format($percentage, 2).'%',
                'branch_count' => count($branchRows),
                'branches' => implode(', ', $branchLabels),
                'branch_rows' => $branchRows,
                'url' => route('audit-findings.show', [
                    'indicator' => $indicator->id,
                    'month' => $month,
                    'year' => $year,
                ]),
            ];
        }

        return collect($groups)
            ->sortBy([
                fn ($g) => mb_strtolower($g['category']),
                fn ($g) => mb_strtolower($g['sub_category']),
            ])
            ->values()
            ->map(function (array $group) {
                $group['rows'] = collect($group['rows'])
                    ->sortBy(fn ($r) => mb_strtolower((string) $r['code']))
                    ->values()
                    ->all();

                return $group;
            })
            ->all();
    }

    /**
     * Push Report Rating Box / finding data from a completed (or saved) audit report
     * into the Findings Matrix (shakha × indicator × month × year).
     *
     * Link rule: each stats table belongs to the nearest preceding finding that has
     * an indicator_id (copied onto the stats block as linked_indicator_id).
     *
     * @return int Number of matrix cells upserted/deleted
     */
    public function syncFromReport(AuditReport $report): int
    {
        $shakhaId = (int) ($report->shakha_id ?? 0);
        $month = (int) ($report->report_month ?? 0);
        $year = (int) ($report->report_year ?? 0);

        if ($shakhaId < 1 || $month < 1 || $month > 12 || $year < 2000) {
            return 0;
        }

        $pages = is_array($report->pages_data) ? $report->pages_data : [];
        $page4 = is_array($pages['page4'] ?? null) ? $pages['page4'] : [];
        $blocks = array_values((array) ($page4['reportBlocks'] ?? []));

        $rows = $this->extractMatrixRowsFromBlocks($blocks);
        $touched = 0;

        foreach ($rows as $row) {
            $this->upsertFinding(
                $shakhaId,
                (int) $row['indicator_id'],
                $month,
                $year,
                [
                    'amount' => $row['amount'],
                    'sample_size_checked' => $row['sample_size_checked'],
                    'irregularity_count' => $row['irregularity_count'],
                    'observation' => $row['observation'],
                    'responsible_staff_name' => $row['responsible_staff_name'] ?? null,
                    'responsible_staff_ids' => $row['responsible_staff_ids'] ?? [],
                ]
            );
            $touched++;
        }

        return $touched;
    }

    /**
     * @param  list<array<string, mixed>>  $blocks
     * @return list<array{
     *   indicator_id:int,
     *   amount:?float,
     *   sample_size_checked:?int,
     *   irregularity_count:?int,
     *   observation:?string,
     *   responsible_staff_name:?string
     * }>
     */
    public function extractMatrixRowsFromBlocks(array $blocks): array
    {
        /** @var array<int, array{indicator_id:int, amount:?float, sample_size_checked:?int, irregularity_count:?int, observation:?string, responsible_staff_name:?string}> $byIndicator */
        $byIndicator = [];

        /** @var list<array{indicator_id:int, amount:?float, observation:?string, responsible_staff_name:?string}> $pendingFindings */
        $pendingFindings = [];
        /** @var array<int, array{indicator_id:int, amount:?float, observation:?string, responsible_staff_name:?string}> $lastFindingById */
        $lastFindingById = [];
        $lastFindingIndicatorId = 0;

        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }

            $type = (string) ($block['type'] ?? '');

            if ($type === 'finding') {
                $indicatorId = (int) ($block['indicator_id'] ?? $block['linked_indicator_id'] ?? 0);
                if ($indicatorId < 1) {
                    continue;
                }

                $amount = \App\Support\BanglaNumerals::toFloat($block['amount'] ?? null);
                // Finding body is usually the শিরোনাম; পর্যবেক্ষণ body + matrix_people override later.
                $body = trim((string) ($block['body'] ?? ''));
                $observation = $body !== '' ? $body : null;
                $meta = [
                    'indicator_id' => $indicatorId,
                    'amount' => $amount,
                    'observation' => $observation,
                    'responsible_staff_name' => null,
                    'responsible_staff_ids' => [],
                ];
                $pendingFindings[] = $meta;
                $lastFindingById[$indicatorId] = $meta;
                $lastFindingIndicatorId = $indicatorId;

                continue;
            }

            if ($type === 'observation') {
                $label = mb_strtolower(trim((string) ($block['label'] ?? '')));
                $isPorjobekkhon = str_contains($label, 'পর্যবেক্ষণ') || str_contains($label, 'observation');
                if (! $isPorjobekkhon || $lastFindingIndicatorId < 1) {
                    continue;
                }

                $obsBody = trim((string) ($block['body'] ?? ''));
                $staffName = $this->formatMatrixPeopleNames($block['matrix_people'] ?? null);
                $staffIds = $this->extractMatrixPeopleIds($block['matrix_people'] ?? null);
                if ($obsBody === '' && $staffName === null && $staffIds === []) {
                    continue;
                }

                $base = $lastFindingById[$lastFindingIndicatorId] ?? [
                    'indicator_id' => $lastFindingIndicatorId,
                    'amount' => null,
                    'observation' => null,
                    'responsible_staff_name' => null,
                    'responsible_staff_ids' => [],
                ];
                $updated = [
                    'indicator_id' => $lastFindingIndicatorId,
                    'amount' => $base['amount'] ?? null,
                    'observation' => $obsBody !== '' ? $obsBody : ($base['observation'] ?? null),
                    'responsible_staff_name' => $staffName ?? ($base['responsible_staff_name'] ?? null),
                    'responsible_staff_ids' => $staffIds !== []
                        ? $staffIds
                        : ($base['responsible_staff_ids'] ?? []),
                ];
                $lastFindingById[$lastFindingIndicatorId] = $updated;

                foreach ($pendingFindings as $i => $pending) {
                    if ((int) ($pending['indicator_id'] ?? 0) === $lastFindingIndicatorId) {
                        $pendingFindings[$i] = array_merge($pending, [
                            'observation' => $updated['observation'],
                            'responsible_staff_name' => $updated['responsible_staff_name'],
                            'responsible_staff_ids' => $updated['responsible_staff_ids'],
                        ]);
                    }
                }

                $byIndicator[$lastFindingIndicatorId] = $this->mergeMatrixRow(
                    $byIndicator[$lastFindingIndicatorId] ?? null,
                    [
                        'indicator_id' => $lastFindingIndicatorId,
                        'amount' => $updated['amount'],
                        'sample_size_checked' => null,
                        'irregularity_count' => null,
                        'observation' => $updated['observation'],
                        'responsible_staff_name' => $updated['responsible_staff_name'],
                        'responsible_staff_ids' => $updated['responsible_staff_ids'],
                    ]
                );

                continue;
            }

            if (! in_array($type, ['stats', 'vat', 'tax'], true)) {
                continue;
            }

            $sampleSum = 0;
            $irregularSum = 0;
            $hasSample = false;
            $hasIrregular = false;

            foreach (array_values((array) ($block['rows'] ?? [])) as $statsRow) {
                if (! is_array($statsRow)) {
                    continue;
                }
                $sample = \App\Support\BanglaNumerals::toInt($statsRow['sample_size'] ?? null);
                $irregular = \App\Support\BanglaNumerals::toInt($statsRow['instances_found'] ?? null);
                if ($sample !== null) {
                    $sampleSum += $sample;
                    $hasSample = true;
                }
                if ($irregular !== null) {
                    $irregularSum += $irregular;
                    $hasIrregular = true;
                }
            }

            // Skip empty rating tables — they must not wipe / create blank matrix cells.
            if (! $hasSample && ! $hasIrregular) {
                continue;
            }

            $indicatorId = (int) ($block['linked_indicator_id'] ?? $block['indicator_id'] ?? 0);

            // Explicit link on the box wins; otherwise FIFO-match the next unused finding
            // so "heading, heading, rating box" still maps the first filled box to the first heading.
            $findingMeta = null;
            if ($indicatorId > 0) {
                $findingMeta = $lastFindingById[$indicatorId] ?? [
                    'indicator_id' => $indicatorId,
                    'amount' => null,
                    'observation' => null,
                    'responsible_staff_name' => null,
                    'responsible_staff_ids' => [],
                ];
            } elseif ($pendingFindings !== []) {
                $findingMeta = array_shift($pendingFindings);
                $indicatorId = (int) $findingMeta['indicator_id'];
            }

            if ($indicatorId < 1) {
                continue;
            }

            $byIndicator[$indicatorId] = $this->mergeMatrixRow(
                $byIndicator[$indicatorId] ?? null,
                [
                    'indicator_id' => $indicatorId,
                    'amount' => $findingMeta['amount'] ?? null,
                    'sample_size_checked' => $hasSample ? $sampleSum : null,
                    'irregularity_count' => $hasIrregular ? $irregularSum : null,
                    'observation' => $findingMeta['observation'] ?? null,
                    'responsible_staff_name' => $findingMeta['responsible_staff_name'] ?? null,
                    'responsible_staff_ids' => $findingMeta['responsible_staff_ids'] ?? [],
                ]
            );
        }

        // Findings that have matrix-worthy data but no rating box still enter the matrix.
        foreach ($pendingFindings as $findingMeta) {
            $indicatorId = (int) $findingMeta['indicator_id'];
            if ($indicatorId < 1) {
                continue;
            }
            $hasAmount = ($findingMeta['amount'] ?? null) !== null;
            $hasStaff = filled($findingMeta['responsible_staff_name'] ?? null)
                || (($findingMeta['responsible_staff_ids'] ?? []) !== []);

            if (isset($byIndicator[$indicatorId])) {
                $byIndicator[$indicatorId] = $this->mergeMatrixRow(
                    $byIndicator[$indicatorId],
                    [
                        'indicator_id' => $indicatorId,
                        'amount' => $findingMeta['amount'] ?? null,
                        'sample_size_checked' => null,
                        'irregularity_count' => null,
                        'observation' => $findingMeta['observation'] ?? null,
                        'responsible_staff_name' => $findingMeta['responsible_staff_name'] ?? null,
                        'responsible_staff_ids' => $findingMeta['responsible_staff_ids'] ?? [],
                    ]
                );

                continue;
            }

            // Do not create a matrix cell from শিরোনাম text alone — need amount and/or staff names.
            if (! $hasAmount && ! $hasStaff) {
                continue;
            }

            $byIndicator[$indicatorId] = [
                'indicator_id' => $indicatorId,
                'amount' => $findingMeta['amount'],
                'sample_size_checked' => null,
                'irregularity_count' => null,
                'observation' => $findingMeta['observation'],
                'responsible_staff_name' => $findingMeta['responsible_staff_name'] ?? null,
                'responsible_staff_ids' => $findingMeta['responsible_staff_ids'] ?? [],
            ];
        }

        return array_values($byIndicator);
    }

    /**
     * Format অভিযুক্ত কর্মী as "Name (EmployeeID)" so duplicate names stay distinguishable.
     *
     * @param  list<array{id?:?int,code?:string,name?:string}>|mixed  $people
     */
    protected function formatMatrixPeopleNames(mixed $people): ?string
    {
        if (! is_array($people)) {
            return null;
        }

        $labels = [];
        foreach (array_values($people) as $person) {
            if (! is_array($person)) {
                continue;
            }
            $name = trim((string) ($person['name'] ?? ''));
            $code = trim((string) ($person['code'] ?? ''));
            if ($name === '' && $code === '') {
                continue;
            }
            if ($name !== '' && $code !== '') {
                $labels[] = $name.' ('.$code.')';
            } else {
                $labels[] = $name !== '' ? $name : $code;
            }
        }

        $labels = array_values(array_unique($labels));

        return $labels !== [] ? implode(', ', $labels) : null;
    }

    /**
     * @param  list<array{id?:?int,code?:string,name?:string}>|mixed  $people
     * @return list<int>
     */
    protected function extractMatrixPeopleIds(mixed $people): array
    {
        if (! is_array($people)) {
            return [];
        }

        $ids = [];
        foreach (array_values($people) as $person) {
            if (! is_array($person)) {
                continue;
            }
            $id = (int) ($person['id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
                continue;
            }
            $code = trim((string) ($person['code'] ?? ''));
            if ($code === '') {
                continue;
            }
            $employeeId = (int) (ShakhaEmployee::query()->where('employee_code', $code)->value('id') ?? 0);
            if ($employeeId > 0) {
                $ids[] = $employeeId;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return list<int>
     */
    protected function normalizeStaffIds(mixed $ids): array
    {
        if (! is_array($ids)) {
            return [];
        }

        return array_values(array_unique(array_filter(
            array_map('intval', $ids),
            fn (int $id) => $id > 0
        )));
    }

    /**
     * @param  list<int>  $staffIds
     * @param  \Illuminate\Support\Collection<int, ShakhaEmployee>  $employeesById
     * @param  array<int, int>  $lifetimeCounts
     * @return list<array{id:?int,label:string,report_count:int,dossier_url:?string}>
     */
    protected function buildAccusedPeopleForSummary(
        string $accusedLabel,
        array $staffIds,
        $employeesById,
        array $lifetimeCounts
    ): array {
        $people = [];

        if ($staffIds !== []) {
            foreach ($staffIds as $id) {
                $emp = $employeesById->get($id);
                if (! $emp) {
                    continue;
                }
                $name = trim((string) $emp->name);
                $code = trim((string) $emp->employee_code);
                $label = ($name !== '' && $code !== '')
                    ? $name.' ('.$code.')'
                    : ($name !== '' ? $name : $code);
                $people[] = [
                    'id' => (int) $emp->id,
                    'label' => $label,
                    'report_count' => (int) ($lifetimeCounts[(int) $emp->id] ?? 0),
                    'dossier_url' => route('shakha-employees.dossier', $emp),
                ];
            }

            return $people;
        }

        $parts = preg_split('/\s*,\s*/u', trim($accusedLabel)) ?: [];
        foreach ($parts as $part) {
            $part = trim((string) $part);
            if ($part === '') {
                continue;
            }
            $people[] = [
                'id' => null,
                'label' => $part,
                'report_count' => 0,
                'dossier_url' => null,
            ];
        }

        return $people;
    }

    /**
     * Ensure each accused person shows as "Name (EmployeeID)" in summary tables.
     * Enriches plain names from the shakha roster when IDs were not stored yet.
     */
    protected function formatAccusedKormiForDisplay(int $shakhaId, string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '' || $shakhaId < 1) {
            return $raw;
        }

        $parts = preg_split('/\s*,\s*/u', $raw) ?: [];
        $parts = array_values(array_filter(array_map('trim', $parts), fn ($p) => $p !== ''));
        if ($parts === []) {
            return '';
        }

        // Already look like "Name (CODE)" — keep as-is.
        $needsLookup = false;
        foreach ($parts as $part) {
            if (! preg_match('/^.+\s+\([^)]+\)$/u', $part)) {
                $needsLookup = true;
                break;
            }
        }
        if (! $needsLookup) {
            return implode(', ', $parts);
        }

        $employees = ShakhaEmployee::query()
            ->where('shakha_id', $shakhaId)
            ->get(['employee_code', 'name']);

        $byName = [];
        foreach ($employees as $emp) {
            $nameKey = mb_strtolower(trim((string) $emp->name));
            if ($nameKey === '') {
                continue;
            }
            $byName[$nameKey][] = trim((string) ($emp->employee_code ?: ''));
        }

        $out = [];
        foreach ($parts as $part) {
            if (preg_match('/^.+\s+\([^)]+\)$/u', $part)) {
                $out[] = $part;
                continue;
            }
            $nameKey = mb_strtolower($part);
            $codes = array_values(array_filter($byName[$nameKey] ?? []));
            if (count($codes) === 1) {
                $out[] = $part.' ('.$codes[0].')';
            } elseif (count($codes) > 1) {
                // Same name on multiple IDs — list all so auditor can tell them apart.
                $out[] = $part.' ('.implode(' / ', $codes).')';
            } else {
                $out[] = $part;
            }
        }

        return implode(', ', $out);
    }

    /**
     * @param  array{indicator_id:int, amount:?float, sample_size_checked:?int, irregularity_count:?int, observation:?string, responsible_staff_name:?string, responsible_staff_ids?:list<int>}|null  $existing
     * @param  array{indicator_id:int, amount:?float, sample_size_checked:?int, irregularity_count:?int, observation:?string, responsible_staff_name:?string, responsible_staff_ids?:list<int>}  $incoming
     * @return array{indicator_id:int, amount:?float, sample_size_checked:?int, irregularity_count:?int, observation:?string, responsible_staff_name:?string, responsible_staff_ids:list<int>}
     */
    protected function mergeMatrixRow(?array $existing, array $incoming): array
    {
        if ($existing === null) {
            $incoming['responsible_staff_ids'] = $this->normalizeStaffIds($incoming['responsible_staff_ids'] ?? []);

            return $incoming;
        }

        $incomingIds = $this->normalizeStaffIds($incoming['responsible_staff_ids'] ?? []);
        $existingIds = $this->normalizeStaffIds($existing['responsible_staff_ids'] ?? []);

        return [
            'indicator_id' => $incoming['indicator_id'],
            'amount' => $incoming['amount'] ?? $existing['amount'],
            'sample_size_checked' => $incoming['sample_size_checked'] ?? $existing['sample_size_checked'],
            'irregularity_count' => $incoming['irregularity_count'] ?? $existing['irregularity_count'],
            // Prefer non-empty incoming so পর্যবেক্ষণ body/staff overwrite finding শিরোনাম fallback.
            'observation' => filled($incoming['observation'] ?? null)
                ? $incoming['observation']
                : ($existing['observation'] ?? null),
            'responsible_staff_name' => filled($incoming['responsible_staff_name'] ?? null)
                ? $incoming['responsible_staff_name']
                : ($existing['responsible_staff_name'] ?? null),
            'responsible_staff_ids' => $incomingIds !== [] ? $incomingIds : $existingIds,
        ];
    }
}
