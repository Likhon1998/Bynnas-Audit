<?php

namespace App\Http\Controllers;

use App\Models\AuditFinding;
use App\Models\AuditIndicator;
use App\Models\AuditReport;
use App\Models\Shakha;
use App\Models\ShakhaEmployee;
use App\Services\AuditSummaryService;
use App\Services\FindingsAuthoritySummaryExcelExporter;
use App\Services\FindingsAuthoritySummaryPptExporter;
use App\Services\FindingsMatrixExcelExporter;
use App\Services\UserAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditFindingController extends Controller
{
    public function summary(Request $request, AuditSummaryService $summary): View
    {
        $month = max(1, min(12, (int) $request->integer('month', now('Asia/Dhaka')->month)));
        $year = max(2000, min(2100, (int) $request->integer('year', now('Asia/Dhaka')->year)));

        $underheadingGroups = $summary->getMonthUnderheadingSummary($month, $year);
        $periodLabel = date('F', mktime(0, 0, 0, $month, 1)).' '.$year;

        $monthsWithData = AuditFinding::query()
            ->where('audit_year', $year)
            ->selectRaw('audit_month, COUNT(*) as cells')
            ->groupBy('audit_month')
            ->get()
            ->keyBy(fn ($r) => (int) $r->audit_month);

        $monthStrip = collect(range(1, 12))->map(function (int $m) use ($monthsWithData, $month, $year) {
            $stat = $monthsWithData->get($m);

            return [
                'month' => $m,
                'label' => date('M', mktime(0, 0, 0, $m, 1)),
                'full' => date('F', mktime(0, 0, 0, $m, 1)),
                'active' => $m === $month,
                'has_data' => $stat !== null && (int) $stat->cells > 0,
                'url' => route('audit-findings.summary', ['month' => $m, 'year' => $year]),
            ];
        });

        $flatRows = collect($underheadingGroups)->flatMap(function (array $group) {
            return collect($group['rows'])->map(fn ($row) => array_merge($row, [
                'category' => $group['category'],
                'sub_category' => $group['sub_category'],
            ]));
        })->values();

        return view('audit-findings.summary', [
            'flatRows' => $flatRows,
            'periodLabel' => $periodLabel,
            'month' => $month,
            'year' => $year,
            'monthStrip' => $monthStrip,
            'yearOptions' => range(now()->year + 1, now()->year - 6),
            'exportUrl' => route('audit-findings.summary.export', ['month' => $month, 'year' => $year]),
            'exportPptUrl' => route('audit-findings.summary.export-ppt', ['month' => $month, 'year' => $year]),
        ]);
    }

    public function exportSummary(Request $request, FindingsAuthoritySummaryExcelExporter $exporter): StreamedResponse
    {
        $month = max(1, min(12, (int) $request->integer('month', now('Asia/Dhaka')->month)));
        $year = max(2000, min(2100, (int) $request->integer('year', now('Asia/Dhaka')->year)));

        return $exporter->download($month, $year);
    }

    public function exportSummaryPpt(Request $request, FindingsAuthoritySummaryPptExporter $exporter): StreamedResponse
    {
        $month = max(1, min(12, (int) $request->integer('month', now('Asia/Dhaka')->month)));
        $year = max(2000, min(2100, (int) $request->integer('year', now('Asia/Dhaka')->year)));

        return $exporter->download($month, $year);
    }

    public function index(Request $request, AuditSummaryService $summary): View
    {
        $month = (int) $request->integer('month', now('Asia/Dhaka')->month);
        $year = (int) $request->integer('year', now('Asia/Dhaka')->year);
        $month = max(1, min(12, $month));
        $year = max(2000, min(2100, $year));

        $totals = $summary->getOrganizationTotals($month, $year);

        $branchesInPeriod = AuditFinding::query()
            ->where('audit_month', $month)
            ->where('audit_year', $year)
            ->pluck('shakha_id')
            ->unique()
            ->count();

        $findingsInPeriod = AuditFinding::query()
            ->where('audit_month', $month)
            ->where('audit_year', $year)
            ->count();

        $hitCount = $totals->filter(fn ($r) => $r->objected_branch_count > 0 || $r->total_irregularities > 0)->count();

        $monthStart = now('Asia/Dhaka')->setDate($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $newIndicatorsThisMonth = AuditIndicator::query()
            ->where(function ($query) {
                $query->where('indicator_code', 'like', 'রিপোর্ট-%')
                    ->orWhere('indicator_code', 'like', '৯০০০-%')
                    ->orWhere('indicator_code', 'like', '9000-%')
                    ->orWhere('category', 'আর্থিক নিরীক্ষা (রিপোর্ট)')
                    ->orWhere('category', 'নিরীক্ষা প্রতিবেদন');
            })
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->latest('created_at')
            ->get(['id', 'indicator_code', 'title', 'category', 'created_at']);

        $newIndicatorIds = $newIndicatorsThisMonth->pluck('id')->map(fn ($id) => (int) $id)->all();

        $rows = $totals->map(fn ($row) => [
            'indicator_id' => $row->indicator_id,
            'category' => (string) $row->category,
            'sub_category' => (string) $row->sub_category,
            'code' => (string) $row->code,
            'title' => (string) $row->title,
            'risk_rating' => (string) $row->risk_rating,
            'total_amount' => (float) $row->total_amount,
            'total_amount_fmt' => number_format($row->total_amount, 2),
            'total_samples_checked' => (int) $row->total_samples_checked,
            'total_irregularities' => (int) $row->total_irregularities,
            'objected_branch_count' => (int) $row->objected_branch_count,
            'is_new' => in_array((int) $row->indicator_id, $newIndicatorIds, true),
            'branches_url' => route('audit-findings.show', [
                'indicator' => $row->indicator_id,
                'month' => $month,
                'year' => $year,
            ]),
        ])
            ->sortByDesc(fn ($row) => $row['is_new'] ? 1 : 0)
            ->values();

        $monthsWithData = AuditFinding::query()
            ->where('audit_year', $year)
            ->selectRaw('audit_month, COUNT(*) as cells, COUNT(DISTINCT shakha_id) as branches')
            ->groupBy('audit_month')
            ->get()
            ->keyBy(fn ($r) => (int) $r->audit_month);

        $monthStrip = collect(range(1, 12))->map(function (int $m) use ($monthsWithData, $month, $year) {
            $stat = $monthsWithData->get($m);

            return [
                'month' => $m,
                'label' => date('M', mktime(0, 0, 0, $m, 1)),
                'full' => date('F', mktime(0, 0, 0, $m, 1)),
                'active' => $m === $month,
                'has_data' => $stat !== null && (int) $stat->cells > 0,
                'cells' => (int) ($stat->cells ?? 0),
                'branches' => (int) ($stat->branches ?? 0),
                'url' => route('audit-findings.index', ['month' => $m, 'year' => $year]),
            ];
        });

        return view('audit-findings.index', [
            'month' => $month,
            'year' => $year,
            'rows' => $rows,
            'branchesInPeriod' => $branchesInPeriod,
            'findingsInPeriod' => $findingsInPeriod,
            'hitCount' => $hitCount,
            'yearOptions' => range(now()->year + 1, now()->year - 6),
            'newIndicatorsThisMonth' => $newIndicatorsThisMonth,
            'newIndicatorsThisMonthCount' => $newIndicatorsThisMonth->count(),
            'newIndicatorsMonthLabel' => $monthStart->format('F Y'),
            'monthStrip' => $monthStrip,
            'exportUrl' => route('audit-findings.export', ['month' => $month, 'year' => $year]),
            'exportYearUrl' => route('audit-findings.export', ['year' => $year, 'scope' => 'year']),
            'monthLabel' => date('F', mktime(0, 0, 0, $month, 1)),
        ]);
    }

    public function export(Request $request, FindingsMatrixExcelExporter $exporter): StreamedResponse
    {
        $year = (int) $request->integer('year', now('Asia/Dhaka')->year);
        $year = max(2000, min(2100, $year));
        $scope = $request->string('scope')->toString();

        if ($scope === 'year') {
            return $exporter->downloadYear($year);
        }

        $month = (int) $request->integer('month', now('Asia/Dhaka')->month);
        $month = max(1, min(12, $month));

        return $exporter->downloadMonth($month, $year);
    }

    public function show(Request $request, AuditIndicator $indicator, AuditSummaryService $summary): View
    {
        $month = (int) $request->integer('month', now('Asia/Dhaka')->month);
        $year = (int) $request->integer('year', now('Asia/Dhaka')->year);

        $branches = $summary->getIndicatorBranchFindings($indicator->id, $month, $year);
        $orgRow = $summary->getOrganizationTotals($month, $year)->firstWhere('indicator_id', $indicator->id);

        $shakhaIds = $branches->pluck('shakha_id')->unique()->filter()->values();
        $employeesByShakha = ShakhaEmployee::query()
            ->whereIn('shakha_id', $shakhaIds)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'shakha_id', 'employee_code', 'name', 'designation'])
            ->groupBy('shakha_id')
            ->map(fn ($group) => $group->map(fn (ShakhaEmployee $e) => [
                'id' => $e->id,
                'code' => (string) $e->employee_code,
                'name' => (string) $e->name,
                'designation' => (string) ($e->designation ?: ''),
            ])->values())
            ->toArray();

        $branchRows = $branches->map(fn (AuditFinding $finding) => [
            'id' => $finding->id,
            'shakha_id' => (int) $finding->shakha_id,
            'shakha_name' => (string) ($finding->shakha?->name ?? '—'),
            'shakha_code' => (string) ($finding->shakha?->code ?? ''),
            'area_name' => (string) ($finding->shakha?->area?->name ?? '—'),
            'amount' => $finding->amount !== null ? number_format((float) $finding->amount, 2) : '—',
            'sample_size_checked' => $finding->sample_size_checked ?? '—',
            'irregularity_count' => $finding->irregularity_count ?? '—',
            'observation' => (string) ($finding->observation ?: '—'),
            'responsible_staff_name' => (string) ($finding->responsible_staff_name ?: ''),
            'staff_save_url' => route('audit-findings.staff.update', $finding),
        ])->values();

        return view('audit-findings.show', [
            'indicator' => $indicator,
            'month' => $month,
            'year' => $year,
            'branches' => $branches,
            'branchRows' => $branchRows,
            'employeesByShakha' => $employeesByShakha,
            'orgRow' => $orgRow,
        ]);
    }

    public function updateStaff(Request $request, AuditFinding $finding): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        if (! app(UserAccessService::class)->canAccessShakha($request->user(), (int) $finding->shakha_id)) {
            abort(403, 'You are not assigned to this shakha.');
        }

        $data = $request->validate([
            'responsible_staff_name' => ['nullable', 'string', 'max:255'],
            'employee_code' => ['nullable', 'string', 'max:80'],
        ]);

        $name = trim((string) ($data['responsible_staff_name'] ?? ''));
        $code = trim((string) ($data['employee_code'] ?? ''));
        $employee = null;

        if ($code !== '') {
            $employee = ShakhaEmployee::query()
                ->where('employee_code', $code)
                ->first();
            if ($employee) {
                $name = $employee->name.' ('.$employee->employee_code.')';
            }
        } elseif ($name !== '') {
            $employee = ShakhaEmployee::query()
                ->where('shakha_id', $finding->shakha_id)
                ->where('status', 'active')
                ->where(function ($q) use ($name) {
                    $q->where('employee_code', $name)
                        ->orWhere('name', $name);
                })
                ->first();
            if ($employee) {
                $name = $employee->name.' ('.$employee->employee_code.')';
            }
        }

        $staffIds = $employee
            ? [(int) $employee->id]
            : app(\App\Services\StaffFinancialOccurrenceService::class)
                ->resolveIdsFromStaffName($name !== '' ? $name : null, (int) $finding->shakha_id);

        $finding->update([
            'responsible_staff_name' => $name !== '' ? $name : null,
            'responsible_staff_ids' => $staffIds !== [] ? $staffIds : null,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'responsible_staff_name' => $finding->responsible_staff_name,
                'responsible_staff_ids' => $finding->responsibleStaffIds(),
            ]);
        }

        return back()->with('status', 'Staff updated.');
    }

    public function entry(Request $request): View|RedirectResponse
    {
        $shakhaId = (int) $request->integer('shakha');
        $month = (int) $request->integer('month', now('Asia/Dhaka')->month);
        $year = (int) $request->integer('year', now('Asia/Dhaka')->year);

        // Convenience: resolve from an audit report wizard draft/completed.
        if ($request->filled('report')) {
            $report = AuditReport::query()->find($request->integer('report'));
            if ($report) {
                $shakhaId = (int) $report->shakha_id;
                $month = (int) $report->report_month;
                $year = (int) $report->report_year;
            }
        }

        $shakha = Shakha::query()->with('area')->find($shakhaId);
        if (! $shakha) {
            return redirect()
                ->route($request->user()?->can('findings.view_all') ? 'audit-findings.index' : 'dashboard')
                ->with('status', 'Select a branch (or open Findings from an Audit Report) to enter data.');
        }

        if (! app(UserAccessService::class)->canAccessShakha($request->user(), (int) $shakha->id)) {
            abort(403, 'You are not assigned to this shakha.');
        }

        $indicators = AuditIndicator::query()
            ->active()
            ->orderBy('category')
            ->orderBy('sub_category')
            ->orderBy('indicator_code')
            ->get();

        $existing = AuditFinding::query()
            ->where('shakha_id', $shakha->id)
            ->where('audit_month', $month)
            ->where('audit_year', $year)
            ->get()
            ->keyBy('audit_indicator_id');

        $indicatorRows = $indicators->map(fn (AuditIndicator $indicator) => [
            'id' => $indicator->id,
            'category' => (string) ($indicator->category ?: ''),
            'sub_category' => (string) ($indicator->sub_category ?: ''),
            'indicator_code' => (string) $indicator->indicator_code,
            'title' => (string) $indicator->title,
            'risk_rating' => (string) ($indicator->risk_rating ?: ''),
            'amount' => $existing->get($indicator->id)?->amount,
            'sample_size_checked' => $existing->get($indicator->id)?->sample_size_checked,
            'irregularity_count' => $existing->get($indicator->id)?->irregularity_count,
            'observation' => $existing->get($indicator->id)?->observation,
            'responsible_staff_name' => $existing->get($indicator->id)?->responsible_staff_name,
        ])->values();

        $employees = ShakhaEmployee::query()
            ->where('shakha_id', $shakha->id)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'employee_code', 'name', 'designation'])
            ->map(fn (ShakhaEmployee $e) => [
                'id' => $e->id,
                'code' => (string) $e->employee_code,
                'name' => (string) $e->name,
                'designation' => (string) ($e->designation ?: ''),
            ])
            ->values();

        return view('audit-findings.entry', [
            'shakha' => $shakha,
            'month' => $month,
            'year' => $year,
            'indicatorRows' => $indicatorRows,
            'employees' => $employees,
        ]);
    }

    public function storeEntry(Request $request, AuditSummaryService $summary): RedirectResponse
    {
        $data = $request->validate([
            'shakha_id' => ['required', 'exists:shakhas,id'],
            'audit_month' => ['required', 'integer', 'min:1', 'max:12'],
            'audit_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'findings' => ['nullable', 'array'],
            'findings.*.amount' => ['nullable', 'numeric'],
            'findings.*.sample_size_checked' => ['nullable', 'integer', 'min:0'],
            'findings.*.irregularity_count' => ['nullable', 'integer', 'min:0'],
            'findings.*.observation' => ['nullable', 'string'],
            'findings.*.responsible_staff_name' => ['nullable', 'string', 'max:255'],
        ]);

        $shakhaId = (int) $data['shakha_id'];
        $month = (int) $data['audit_month'];
        $year = (int) $data['audit_year'];
        $rows = $data['findings'] ?? [];

        if (! app(UserAccessService::class)->canAccessShakha($request->user(), $shakhaId)) {
            abort(403, 'You are not assigned to this shakha.');
        }

        foreach ($rows as $indicatorId => $cell) {
            if (! AuditIndicator::query()->whereKey($indicatorId)->exists()) {
                continue;
            }
            $summary->upsertFinding(
                $shakhaId,
                (int) $indicatorId,
                $month,
                $year,
                is_array($cell) ? $cell : []
            );
        }

        return redirect()
            ->route('audit-findings.entry', [
                'shakha' => $shakhaId,
                'month' => $month,
                'year' => $year,
            ])
            ->with('status', 'Findings saved. Empty rows were cleared (sparse matrix).');
    }
}
