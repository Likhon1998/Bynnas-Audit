<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Monthly findings Excel: indicators × shakhas summary matrix.
 * Year download = one matrix tab per calendar month.
 */
class FindingsMatrixExcelExporter
{
    public function __construct(
        private AuditSummaryService $summary,
    ) {}

    public function downloadMonth(int $month, int $year): StreamedResponse
    {
        @set_time_limit(180);
        @ini_set('memory_limit', '512M');

        $month = max(1, min(12, $month));
        $year = max(2000, min(2100, $year));
        $matrix = $this->summary->getMonthConsolidatedMatrix($month, $year);

        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        $irregSheet = new Worksheet($spreadsheet, 'Irregularities');
        $spreadsheet->addSheet($irregSheet, 0);
        $irregSheet->getTabColor()->setRGB('C00000');
        $this->paintMatrixSheet($irregSheet, $matrix, 'irregularity_count', 'Irregularities');

        $amountSheet = new Worksheet($spreadsheet, 'Amount');
        $spreadsheet->addSheet($amountSheet, 1);
        $amountSheet->getTabColor()->setRGB('2B579A');
        $this->paintMatrixSheet($amountSheet, $matrix, 'amount', 'Amount');

        $detailSheet = new Worksheet($spreadsheet, 'Branch detail');
        $spreadsheet->addSheet($detailSheet, 2);
        $detailSheet->getTabColor()->setRGB('548235');
        $this->paintDetailSheet($detailSheet, $matrix);

        $spreadsheet->setActiveSheetIndex(0);
        $filename = sprintf(
            'findings-matrix-%04d-%02d-%s.xlsx',
            $year,
            $month,
            strtolower(date('M', mktime(0, 0, 0, $month, 1)))
        );

        return $this->stream($spreadsheet, $filename);
    }

    public function downloadYear(int $year): StreamedResponse
    {
        @set_time_limit(240);
        @ini_set('memory_limit', '768M');

        $year = max(2000, min(2100, $year));
        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        $summary = new Worksheet($spreadsheet, 'Year Summary');
        $spreadsheet->addSheet($summary, 0);
        $summary->getTabColor()->setRGB('1F4E79');

        $summary->setCellValue('A1', 'Findings Matrix — Year '.$year);
        $summary->mergeCells('A1:F1');
        $summary->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
        ]);
        $summary->setCellValue('A2', 'Each month tab = indicators × shakhas (irregularities). Open a month tab for the full summary.');
        $summary->mergeCells('A2:F2');

        $headers = ['Month', 'Branches', 'Finding cells', 'Indicators hit', 'Total amount', 'Irregularities'];
        foreach ($headers as $i => $header) {
            $summary->setCellValue(Coordinate::stringFromColumnIndex($i + 1).'4', $header);
        }
        $summary->getStyle('A4:F4')->applyFromArray($this->headerStyle());

        $tabColors = [
            1 => '5B9BD5', 2 => '9DC3E6', 3 => '70AD47', 4 => 'A9D08E',
            5 => 'FFC000', 6 => 'FFE699', 7 => 'ED7D31', 8 => 'F4B183',
            9 => 'C00000', 10 => 'FF6B6B', 11 => '7030A0', 12 => 'B4A7D6',
        ];

        $row = 5;
        for ($month = 1; $month <= 12; $month++) {
            $matrix = $this->summary->getMonthConsolidatedMatrix($month, $year);
            $branches = count($matrix['shakhas']);
            $cells = 0;
            $hits = 0;
            $amount = 0.0;
            $irregs = 0;
            foreach ($matrix['indicators'] as $indicator) {
                $amount += (float) $indicator['total_amount'];
                $irregs += (int) $indicator['total_irregularities'];
                if ((int) $indicator['objected_branch_count'] > 0 || (int) $indicator['total_irregularities'] > 0) {
                    $hits++;
                }
            }
            foreach ($matrix['cells'] as $byShakha) {
                $cells += count($byShakha);
            }

            $summary->setCellValue('A'.$row, $matrix['period_label']);
            $summary->setCellValue('B'.$row, $branches);
            $summary->setCellValue('C'.$row, $cells);
            $summary->setCellValue('D'.$row, $hits);
            $summary->setCellValue('E'.$row, $amount);
            $summary->setCellValue('F'.$row, $irregs);
            $summary->getStyle('E'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
            if ($cells > 0) {
                $summary->getStyle('A'.$row.':F'.$row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F0F9FF');
            }
            $row++;

            $title = sprintf('%02d-%s', $month, date('M', mktime(0, 0, 0, $month, 1)));
            $sheet = new Worksheet($spreadsheet, $title);
            $spreadsheet->addSheet($sheet, $month);
            $sheet->getTabColor()->setRGB($tabColors[$month]);
            $this->paintMatrixSheet($sheet, $matrix, 'irregularity_count', 'Irregularities');
        }

        $summary->getStyle('A5:F'.($row - 1))->applyFromArray($this->borderStyle());
        foreach (range('A', 'F') as $col) {
            $summary->getColumnDimension($col)->setAutoSize(true);
        }
        $summary->freezePane('A5');

        $spreadsheet->setActiveSheetIndex(0);

        return $this->stream($spreadsheet, 'findings-matrix-'.$year.'.xlsx');
    }

    /**
     * @param  array<string, mixed>  $matrix
     */
    protected function paintMatrixSheet(Worksheet $sheet, array $matrix, string $metric, string $metricLabel): void
    {
        $shakhas = $matrix['shakhas'];
        $indicators = $matrix['indicators'];
        $cells = $matrix['cells'];

        $leftHeaders = [
            'Category',
            'Sub-category',
            'Code',
            'Indicator',
            'Risk',
            'Total amount',
            'Total samples',
            'Total irregularities',
            'Objected branches',
        ];
        $leftCount = count($leftHeaders);

        $sheet->setCellValue('A1', $matrix['period_label'].' — Findings Matrix ('.$metricLabel.')');
        $lastColIndex = $leftCount + max(1, count($shakhas));
        $lastCol = Coordinate::stringFromColumnIndex($lastColIndex);
        $sheet->mergeCells('A1:'.$lastCol.'1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2B579A']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        $sheet->setCellValue(
            'A2',
            sprintf(
                'Indicators: %d · Shakhas with findings: %d · Metric: %s · Org totals on the left · each shakha is a column',
                count($indicators),
                count($shakhas),
                $metricLabel
            )
        );
        $sheet->mergeCells('A2:'.$lastCol.'2');
        $sheet->getStyle('A2')->getFont()->setSize(9)->getColor()->setRGB('475569');

        // Header row 4: left labels + shakha names
        foreach ($leftHeaders as $i => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1).'4', $header);
        }

        if ($shakhas === []) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($leftCount + 1).'4', '(No shakha findings this month)');
        } else {
            foreach ($shakhas as $i => $shakha) {
                $col = Coordinate::stringFromColumnIndex($leftCount + $i + 1);
                $label = $shakha['name'].($shakha['code'] !== '' ? ' ('.$shakha['code'].')' : '');
                $sheet->setCellValue($col.'4', $label);
                $sheet->getStyle($col.'4')->getAlignment()->setTextRotation(90)->setWrapText(true);
                $sheet->getColumnDimension($col)->setWidth(6);
            }
        }

        $headerEnd = Coordinate::stringFromColumnIndex($leftCount + max(1, count($shakhas)));
        $sheet->getStyle('A4:'.$headerEnd.'4')->applyFromArray($this->headerStyle());
        $sheet->getRowDimension(4)->setRowHeight(80);

        $r = 5;
        foreach ($indicators as $indicator) {
            $sheet->setCellValue('A'.$r, $indicator['category']);
            $sheet->setCellValue('B'.$r, $indicator['sub_category']);
            $sheet->setCellValue('C'.$r, $indicator['code']);
            $sheet->setCellValue('D'.$r, $indicator['title']);
            $sheet->setCellValue('E'.$r, $indicator['risk_rating']);
            $sheet->setCellValue('F'.$r, $indicator['total_amount']);
            $sheet->setCellValue('G'.$r, $indicator['total_samples_checked']);
            $sheet->setCellValue('H'.$r, $indicator['total_irregularities']);
            $sheet->setCellValue('I'.$r, $indicator['objected_branch_count']);
            $sheet->getStyle('F'.$r)->getNumberFormat()->setFormatCode('#,##0.00');

            $hit = ((int) $indicator['objected_branch_count'] > 0) || ((int) $indicator['total_irregularities'] > 0);
            if ($hit) {
                $sheet->getStyle('H'.$r)->getFont()->setBold(true)->getColor()->setRGB('B91C1C');
            }

            foreach ($shakhas as $i => $shakha) {
                $cell = $cells[$indicator['id']][$shakha['id']] ?? null;
                if ($cell === null) {
                    continue;
                }
                $value = $cell[$metric] ?? null;
                if ($value === null || $value === '') {
                    continue;
                }
                $col = Coordinate::stringFromColumnIndex($leftCount + $i + 1);
                $sheet->setCellValue($col.$r, $value);
                if ($metric === 'amount') {
                    $sheet->getStyle($col.$r)->getNumberFormat()->setFormatCode('#,##0.00');
                }
                if ($metric === 'irregularity_count' && (int) $value > 0) {
                    $sheet->getStyle($col.$r)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('FEE2E2');
                    $sheet->getStyle($col.$r)->getFont()->setBold(true)->getColor()->setRGB('B91C1C');
                }
            }

            $r++;
        }

        $lastDataRow = max(4, $r - 1);
        if ($r > 5) {
            $sheet->getStyle('A5:'.$headerEnd.$lastDataRow)->applyFromArray($this->borderStyle());
        }

        $sheet->getColumnDimension('A')->setWidth(22);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(42);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(10);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(12);
        $sheet->freezePane('J5');
        $sheet->setAutoFilter('A4:'.$headerEnd.$lastDataRow);
    }

    /**
     * @param  array<string, mixed>  $matrix
     */
    protected function paintDetailSheet(Worksheet $sheet, array $matrix): void
    {
        $sheet->setCellValue('A1', $matrix['period_label'].' — Branch detail (every finding cell)');
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '548235']],
        ]);

        $headers = [
            'Shakha', 'Code', 'Area', 'Category', 'Sub-category', 'Indicator code', 'Indicator',
            'Amount', 'Samples', 'Irregularities', 'Observation', 'Staff',
        ];
        foreach ($headers as $i => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1).'3', $header);
        }
        $sheet->getStyle('A3:L3')->applyFromArray($this->headerStyle());

        $indicatorMap = collect($matrix['indicators'])->keyBy('id');
        $shakhaMap = collect($matrix['shakhas'])->keyBy('id');

        $r = 4;
        foreach ($matrix['cells'] as $indicatorId => $byShakha) {
            $indicator = $indicatorMap->get($indicatorId);
            if (! $indicator) {
                continue;
            }
            foreach ($byShakha as $shakhaId => $cell) {
                $shakha = $shakhaMap->get($shakhaId) ?? [
                    'name' => 'Branch #'.$shakhaId,
                    'code' => '',
                    'area' => '',
                ];
                $sheet->setCellValue('A'.$r, $shakha['name']);
                $sheet->setCellValue('B'.$r, $shakha['code']);
                $sheet->setCellValue('C'.$r, $shakha['area']);
                $sheet->setCellValue('D'.$r, $indicator['category']);
                $sheet->setCellValue('E'.$r, $indicator['sub_category']);
                $sheet->setCellValue('F'.$r, $indicator['code']);
                $sheet->setCellValue('G'.$r, $indicator['title']);
                $sheet->setCellValue('H'.$r, $cell['amount']);
                $sheet->setCellValue('I'.$r, $cell['sample_size_checked']);
                $sheet->setCellValue('J'.$r, $cell['irregularity_count']);
                $sheet->setCellValue('K'.$r, $cell['observation']);
                $sheet->setCellValue('L'.$r, $cell['responsible_staff_name']);
                if ($cell['amount'] !== null) {
                    $sheet->getStyle('H'.$r)->getNumberFormat()->setFormatCode('#,##0.00');
                }
                $r++;
            }
        }

        if ($r === 4) {
            $sheet->setCellValue('A4', 'No finding cells for this month.');
        } else {
            $sheet->getStyle('A4:L'.($r - 1))->applyFromArray($this->borderStyle());
            $sheet->setAutoFilter('A3:L'.($r - 1));
        }

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->freezePane('A4');
    }

    protected function stream(Spreadsheet $spreadsheet, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function headerStyle(): array
    {
        return [
            'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '334155']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_BOTTOM,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function borderStyle(): array
    {
        return [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E2E8F0'],
                ],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ];
    }
}
