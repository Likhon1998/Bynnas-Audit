<?php

namespace App\Services;

use App\Models\AuditFinding;
use App\Models\AuditIndicator;
use App\Models\AuditReport;
use App\Models\Shakha;
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
        ];

        $isEmpty = blank($payload['amount'])
            && blank($payload['sample_size_checked'])
            && blank($payload['irregularity_count'])
            && blank($payload['observation'])
            && blank($payload['responsible_staff_name']);

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
                    ->orWhere('category', 'আর্থিক নিরীক্ষা (রিপোর্ট)');
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

        /** @var list<array{indicator_id:int, amount:?float, observation:?string}> $pendingFindings */
        $pendingFindings = [];
        $lastFindingById = [];

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
                $body = trim((string) ($block['body'] ?? ''));
                $observation = $body !== '' ? $body : null;
                $meta = [
                    'indicator_id' => $indicatorId,
                    'amount' => $amount,
                    'observation' => $observation,
                ];
                $pendingFindings[] = $meta;
                $lastFindingById[$indicatorId] = $meta;

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
                    'responsible_staff_name' => null,
                ]
            );
        }

        // Findings that have an amount but no rating box still enter the matrix.
        foreach ($pendingFindings as $findingMeta) {
            $indicatorId = (int) $findingMeta['indicator_id'];
            if ($indicatorId < 1 || ($findingMeta['amount'] ?? null) === null) {
                continue;
            }
            if (isset($byIndicator[$indicatorId])) {
                continue;
            }
            $byIndicator[$indicatorId] = [
                'indicator_id' => $indicatorId,
                'amount' => $findingMeta['amount'],
                'sample_size_checked' => null,
                'irregularity_count' => null,
                'observation' => $findingMeta['observation'],
                'responsible_staff_name' => null,
            ];
        }

        return array_values($byIndicator);
    }

    /**
     * @param  array{indicator_id:int, amount:?float, sample_size_checked:?int, irregularity_count:?int, observation:?string, responsible_staff_name:?string}|null  $existing
     * @param  array{indicator_id:int, amount:?float, sample_size_checked:?int, irregularity_count:?int, observation:?string, responsible_staff_name:?string}  $incoming
     * @return array{indicator_id:int, amount:?float, sample_size_checked:?int, irregularity_count:?int, observation:?string, responsible_staff_name:?string}
     */
    protected function mergeMatrixRow(?array $existing, array $incoming): array
    {
        if ($existing === null) {
            return $incoming;
        }

        return [
            'indicator_id' => $incoming['indicator_id'],
            'amount' => $incoming['amount'] ?? $existing['amount'],
            'sample_size_checked' => $incoming['sample_size_checked'] ?? $existing['sample_size_checked'],
            'irregularity_count' => $incoming['irregularity_count'] ?? $existing['irregularity_count'],
            'observation' => $incoming['observation'] ?? $existing['observation'],
            'responsible_staff_name' => $incoming['responsible_staff_name'] ?? $existing['responsible_staff_name'],
        ];
    }
}
