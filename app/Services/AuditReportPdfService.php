<?php

namespace App\Services;

use App\Support\AuditDocumentLayout;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class AuditReportPdfService
{
    public function output(array $data): string
    {
        $mpdf = $this->makeMpdf();
        $mpdf->SetHTMLFooter(
            '<div style="text-align:right;font-size:10pt;font-family:hindsiliguri;">{PAGENO}</div>'
        );
        $html = view('audits.pdf', $data)->render();
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
            'margin_left' => AuditDocumentLayout::MARGIN_LEFT,
            'margin_right' => AuditDocumentLayout::MARGIN_RIGHT,
            'margin_top' => AuditDocumentLayout::MARGIN_TOP,
            'margin_bottom' => AuditDocumentLayout::MARGIN_BOTTOM + 3,
            'margin_header' => 0,
            'margin_footer' => 8,
            'tempDir' => storage_path('app/mpdf'),
            'fontDir' => array_merge($fontDirs, [storage_path('fonts')]),
            'fontdata' => $fontData + [
                'hindsiliguri' => [
                    'R' => 'HindSiliguri-Regular.ttf',
                    'B' => 'HindSiliguri-Bold.ttf',
                    // 0x80 = complex scripts (Bengali); required for conjuncts and digits like ১
                    'useOTL' => 0xFF,
                ],
            ],
            'default_font' => 'hindsiliguri',
            'default_font_size' => 11,
            'shrink_tables_to_fit' => 0,
            'autoScriptToLang' => true,
            'autoLangToFont' => false,
            'autoVietnamese' => false,
            'autoArabic' => false,
        ]);
    }
}
