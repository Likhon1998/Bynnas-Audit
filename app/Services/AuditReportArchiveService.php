<?php

namespace App\Services;

use App\Livewire\MakeAuditReport;
use App\Models\AuditReport;
use Illuminate\Support\Facades\Storage;

class AuditReportArchiveService
{
    public function archive(AuditReport $report): AuditReport
    {
        if (! $report->isMakerDone()) {
            return $report;
        }

        $binary = MakeAuditReport::pdfBinaryFor($report);
        $month = max(1, min(12, (int) $report->report_month));
        $year = max(2000, (int) $report->report_year);
        $path = sprintf(
            'report-storage/%d/%04d/%02d/%d.pdf',
            (int) $report->user_id,
            $year,
            $month,
            (int) $report->id
        );

        Storage::disk('local')->put($path, $binary);

        $report->forceFill([
            'storage_pdf_path' => $path,
            'storage_pdf_at' => now(),
        ])->save();

        return $report->fresh();
    }
}
