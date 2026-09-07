<?php

namespace App\Http\Controllers;

use App\Models\AuditReport;
use App\Models\AuditReportChecklistFile;
use App\Models\AuditReportSend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditReportController extends Controller
{
    public function index(): View
    {
        return view('audits.index');
    }

    public function sendHistory(Request $request): View
    {
        $userId = (int) (auth()->id() ?? 0);
        abort_unless($userId > 0, 403);

        $month = (int) $request->integer('month', (int) now('Asia/Dhaka')->month);
        $year = (int) $request->integer('year', (int) now('Asia/Dhaka')->year);
        if ($month < 1 || $month > 12) {
            $month = (int) now('Asia/Dhaka')->month;
        }
        if ($year < 2000 || $year > 2100) {
            $year = (int) now('Asia/Dhaka')->year;
        }

        $periodLabel = date('F', mktime(0, 0, 0, $month, 1)).' '.$year;

        $sends = AuditReportSend::query()
            ->where('sent_by_user_id', $userId)
            ->whereHas('report', function ($q) use ($month, $year) {
                $q->where('report_month', $month)->where('report_year', $year);
            })
            ->with(['report.shakha'])
            ->latest('sent_at')
            ->latest('id')
            ->get();

        $monthsWithData = AuditReportSend::query()
            ->where('sent_by_user_id', $userId)
            ->whereHas('report', fn ($q) => $q->where('report_year', $year))
            ->with('report:id,report_month,report_year')
            ->get()
            ->groupBy(fn (AuditReportSend $send) => (int) ($send->report?->report_month ?? 0))
            ->map->count();

        $monthStrip = collect(range(1, 12))->map(function (int $m) use ($monthsWithData, $month, $year) {
            return [
                'month' => $m,
                'label' => date('M', mktime(0, 0, 0, $m, 1)),
                'active' => $m === $month,
                'has_data' => ((int) ($monthsWithData->get($m) ?? 0)) > 0,
                'url' => route('audits.send-history', ['month' => $m, 'year' => $year]),
            ];
        });

        return view('audits.send-history', [
            'sends' => $sends,
            'month' => $month,
            'year' => $year,
            'periodLabel' => $periodLabel,
            'monthStrip' => $monthStrip,
            'yearOptions' => range(now()->year + 1, now()->year - 6),
            'sentCount' => $sends->where('status', AuditReportSend::STATUS_SENT)->count(),
            'failedCount' => $sends->where('status', AuditReportSend::STATUS_FAILED)->count(),
        ]);
    }

    public function checklist(AuditReport $report): View
    {
        $userId = (int) (auth()->id() ?? 0);
        abort_unless($userId > 0 && (int) $report->user_id === $userId, 403);

        return view('audits.checklist', [
            'report' => $report,
        ]);
    }

    public function downloadChecklistFile(AuditReport $report, AuditReportChecklistFile $file): StreamedResponse
    {
        $userId = (int) (auth()->id() ?? 0);
        abort_unless($userId > 0 && (int) $report->user_id === $userId, 403);
        abort_unless((int) $file->audit_report_id === (int) $report->id, 404);
        abort_unless($file->stored_path && Storage::disk('public')->exists($file->stored_path), 404);

        return Storage::disk('public')->download($file->stored_path, $file->original_name);
    }
}
