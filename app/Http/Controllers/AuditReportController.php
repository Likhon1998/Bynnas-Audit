<?php

namespace App\Http\Controllers;

use App\Models\AuditReport;
use App\Models\AuditReportChecklistFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditReportController extends Controller
{
    public function index(): View
    {
        return view('audits.index');
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
