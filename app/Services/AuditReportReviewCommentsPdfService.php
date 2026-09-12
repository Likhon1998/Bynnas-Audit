<?php

namespace App\Services;

use App\Models\AuditReport;
use App\Models\AuditReportReviewAnnotation;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class AuditReportReviewCommentsPdfService
{
    /**
     * @param  iterable<int, AuditReportReviewAnnotation>  $annotations
     */
    public function output(AuditReport $report, iterable $annotations, ?string $extraNote = null): string
    {
        $rows = '';
        $i = 0;
        foreach ($annotations as $ann) {
            $i++;
            $type = $ann->type === AuditReportReviewAnnotation::TYPE_AREA ? 'Area' : 'Text';
            $quote = e((string) ($ann->quote ?: '—'));
            $body = e((string) ($ann->body ?: '—'));
            $author = e((string) ($ann->user?->name ?: 'Reviewer'));
            $color = e((string) $ann->color);
            $rows .= '<tr>'
                .'<td style="padding:6px;border:1px solid #ccc;vertical-align:top;">'.$i.'</td>'
                .'<td style="padding:6px;border:1px solid #ccc;vertical-align:top;">'.$type.'</td>'
                .'<td style="padding:6px;border:1px solid #ccc;vertical-align:top;">'.$color.'</td>'
                .'<td style="padding:6px;border:1px solid #ccc;vertical-align:top;">'.$quote.'</td>'
                .'<td style="padding:6px;border:1px solid #ccc;vertical-align:top;white-space:pre-wrap;">'.$body.'</td>'
                .'<td style="padding:6px;border:1px solid #ccc;vertical-align:top;">'.$author.'</td>'
                .'</tr>';
        }

        if ($rows === '') {
            $rows = '<tr><td colspan="6" style="padding:10px;border:1px solid #ccc;text-align:center;">No marks or comments on this review.</td></tr>';
        }

        $noteHtml = '';
        if (filled($extraNote)) {
            $noteHtml = '<p style="margin:12px 0 0;padding:10px;border:1px solid #ddd;background:#f8fafc;white-space:pre-wrap;">'
                .e(trim((string) $extraNote))
                .'</p>';
        }

        $html = '<html><head><meta charset="utf-8"></head><body style="font-family:hindsiliguri,sans-serif;font-size:11pt;color:#111;">'
            .'<h2 style="margin:0 0 8px;">Review comments</h2>'
            .'<p style="margin:0 0 12px;color:#444;">'
            .e($report->entityDisplayName()).' · '.e($report->periodLabel())
            .' · Report #'.(int) $report->id
            .'</p>'
            .$noteHtml
            .'<table style="width:100%;border-collapse:collapse;margin-top:12px;font-size:10pt;">'
            .'<thead><tr>'
            .'<th style="padding:6px;border:1px solid #ccc;background:#f1f5f9;text-align:left;">#</th>'
            .'<th style="padding:6px;border:1px solid #ccc;background:#f1f5f9;text-align:left;">Type</th>'
            .'<th style="padding:6px;border:1px solid #ccc;background:#f1f5f9;text-align:left;">Color</th>'
            .'<th style="padding:6px;border:1px solid #ccc;background:#f1f5f9;text-align:left;">Marked text / area</th>'
            .'<th style="padding:6px;border:1px solid #ccc;background:#f1f5f9;text-align:left;">Comment</th>'
            .'<th style="padding:6px;border:1px solid #ccc;background:#f1f5f9;text-align:left;">By</th>'
            .'</tr></thead><tbody>'.$rows.'</tbody></table>'
            .'</body></html>';

        $mpdf = $this->makeMpdf();
        $mpdf->WriteHTML($html);

        return $mpdf->Output('', Destination::STRING_RETURN);
    }

    protected function makeMpdf(): Mpdf
    {
        $tempDir = storage_path('app/mpdf');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $fontDirs = (new ConfigVariables)->getDefaults()['fontDir'];
        $fontData = (new FontVariables)->getDefaults()['fontdata'];

        return new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 12,
            'margin_right' => 12,
            'margin_top' => 12,
            'margin_bottom' => 14,
            'tempDir' => $tempDir,
            'fontDir' => array_merge($fontDirs, [storage_path('fonts')]),
            'fontdata' => $fontData + [
                'hindsiliguri' => [
                    'R' => 'HindSiliguri-Regular.ttf',
                    'B' => 'HindSiliguri-Bold.ttf',
                    'useOTL' => 0xFF,
                ],
            ],
            'default_font' => 'hindsiliguri',
            'default_font_size' => 11,
            'autoScriptToLang' => true,
            'autoLangToFont' => false,
        ]);
    }
}
