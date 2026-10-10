<?php

namespace App\Services;

use App\Livewire\MakeAuditReport;
use App\Models\AuditReport;
use Illuminate\Support\Carbon;
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

        // Keep updated_at untouched so it still means "last content edit" for isStale().
        $report->timestamps = false;
        $report->forceFill([
            'storage_pdf_path' => $path,
            'storage_pdf_at' => now(),
        ])->save();
        $report->timestamps = true;

        return $report->fresh();
    }

    /** Stored copy is missing, older than the last edit, or rendered with an older PDF layout. */
    public function isStale(AuditReport $report): bool
    {
        if (! $report->storage_pdf_path || ! Storage::disk('local')->exists($report->storage_pdf_path)) {
            return true;
        }

        $storedAt = $report->storage_pdf_at ? Carbon::parse($report->storage_pdf_at) : null;
        if (! $storedAt || $storedAt->lt(Carbon::parse(AuditReportPdfService::LAYOUT_REVISION))) {
            return true;
        }

        return $report->updated_at && Carbon::parse($report->updated_at)->gt($storedAt);
    }

    public function fresh(AuditReport $report): AuditReport
    {
        return $this->isStale($report) ? $this->archive($report) : $report;
    }
}
