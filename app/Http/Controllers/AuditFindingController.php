<?php

namespace App\Http\Controllers;

use App\Models\AuditFinding;
use App\Models\AuditIndicator;
use App\Models\AuditReport;
use App\Models\Shakha;
use App\Services\AuditSummaryService;
use App\Services\UserAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditFindingController extends Controller
{
    public function index(Request $request, AuditSummaryService $summary): View
    {
        $month = (int) $request->integer('month', now('Asia/Dhaka')->month);
        $year = (int) $request->integer('year', now('Asia/Dhaka')->year);

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
                    ->orWhere('category', 'আর্থিক নিরীক্ষা (রিপোর্ট)');
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
        ]);
    }

    public function show(Request $request, AuditIndicator $indicator, AuditSummaryService $summary): View
    {
        $month = (int) $request->integer('month', now('Asia/Dhaka')->month);
        $year = (int) $request->integer('year', now('Asia/Dhaka')->year);

        $branches = $summary->getIndicatorBranchFindings($indicator->id, $month, $year);
        $orgRow = $summary->getOrganizationTotals($month, $year)->firstWhere('indicator_id', $indicator->id);

        return view('audit-findings.show', [
            'indicator' => $indicator,
            'month' => $month,
            'year' => $year,
            'branches' => $branches,
            'orgRow' => $orgRow,
        ]);
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
                ->route('audit-findings.index')
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

        return view('audit-findings.entry', [
            'shakha' => $shakha,
            'month' => $month,
            'year' => $year,
            'indicatorRows' => $indicatorRows,
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
