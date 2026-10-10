<?php

namespace App\Services;

use App\Support\AuditDocumentLayout;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class AuditReportPdfService
{
    /** Archived PDFs stored before this moment are re-rendered on download; bump it whenever the PDF layout changes. */
    public const LAYOUT_REVISION = '2026-10-11 01:20:00';

    /** Render passes allowed for the সূচিপত্র page numbers to settle. */
    protected const MAX_PASSES = 3;

    public function output(array $data): string
    {
        // Page numbers in the সূচিপত্র are only known after layout, so render, read where each
        // heading anchor landed, and render again with those pages until they stop moving.
        $pages = [];
        for ($pass = 1; $pass <= self::MAX_PASSES; $pass++) {
            $mpdf = $this->render(['tocPageMap' => $pages] + $data);
            $measured = $this->anchorPages($mpdf);
            if ($measured === $pages) {
                break;
            }
            $pages = $measured;
        }

        return $mpdf->Output('', Destination::STRING_RETURN);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function render(array $data): Mpdf
    {
        $mpdf = $this->makeMpdf();
        $mpdf->SetHTMLFooter(
            '<div style="text-align:right;font-size:10pt;font-family:hindsiliguri;">{PAGENO}</div>'
        );
        $mpdf->WriteHTML(view('audits.pdf', $data)->render());

        return $mpdf;
    }

    /**
     * Physical page (same as the footer number) of every section/finding anchor.
     *
     * @return array<string, int>
     */
    protected function anchorPages(Mpdf $mpdf): array
    {
        $pages = [];
        foreach ((array) $mpdf->internallink as $name => $target) {
            if (is_string($name) && is_array($target) && isset($target['PAGE'])
                && preg_match('/^(finding|section)-/', $name)) {
                $pages[$name] = (int) $target['PAGE'];
            }
        }
        ksort($pages);

        return $pages;
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
