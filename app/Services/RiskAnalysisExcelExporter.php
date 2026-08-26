<?php

namespace App\Services;

use App\Models\ShakhaRiskAssessment;
use App\Support\FinancialYear;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RiskAnalysisExcelExporter
{
    public function __construct(
        private RiskAssessmentService $riskAssessments,
    ) {}

    public function download(string $fyLabel): StreamedResponse
    {
        @set_time_limit(120);
        @ini_set('memory_limit', '512M');

        $rows = $this->riskAssessments->compileExportRows($fyLabel);
        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        $sheets = [
            ['title' => 'Total Branch', 'filter' => null, 'tab' => '1F4E79'],
            ['title' => 'Significant Risk', 'filter' => 'Significant Risk', 'tab' => 'C00000'],
            ['title' => 'High Risk', 'filter' => 'High Risk', 'tab' => 'ED7D31'],
            ['title' => 'Medium Risk', 'filter' => 'Medium Risk', 'tab' => '7030A0'],
            ['title' => 'Low Risk', 'filter' => 'Low Risk', 'tab' => '548235'],
        ];

        foreach ($sheets as $i => $meta) {
            $sheet = new Worksheet($spreadsheet, $meta['title']);
            $spreadsheet->addSheet($sheet, $i);
            $sheet->getTabColor()->setRGB($meta['tab']);

            $filtered = $meta['filter'] === null
                ? $rows
                : array_values(array_filter($rows, fn ($r) => ($r['risk_category'] ?? '') === $meta['filter']));

            $this->paintBranchSheet($sheet, $fyLabel, $filtered);
        }

        $personSheet = new Worksheet($spreadsheet, 'Engagement Person');
        $spreadsheet->addSheet($personSheet, count($sheets));
        $personSheet->getTabColor()->setRGB('7F7F7F');
        $this->paintEngagementSheet($personSheet, $rows);

        $spreadsheet->setActiveSheetIndex(0);
        $filename = 'risk-branch-analysis-'.$fyLabel.'.xlsx';

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
     * @param  list<array<string, mixed>>  $rows
     */
    protected function paintBranchSheet(Worksheet $sheet, string $fyLabel, array $rows): void
    {
        $lastCol = 'AM';

        // Row 1 group headers
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'Branch, Zone, Area Name & ID Number');
        $sheet->mergeCells('H1:W1');
        $sheet->setCellValue('H1', 'Risk Factors and Performance Metrics (For Audit FY '.$fyLabel.')');
        $sheet->mergeCells('X1:AJ1');
        $sheet->setCellValue('X1', 'Weights / Points');
        $sheet->mergeCells('AK1:AM1');
        $sheet->setCellValue('AK1', 'Performance Grade');

        $sheet->getStyle('A1:G1')->applyFromArray($this->groupHeaderStyle('FFFF99'));
        $sheet->getStyle('H1:W1')->applyFromArray($this->groupHeaderStyle('F4B183'));
        $sheet->getStyle('X1:AJ1')->applyFromArray($this->groupHeaderStyle('F8CBAD'));
        $sheet->getStyle('AK1:AM1')->applyFromArray($this->groupHeaderStyle('2F5496', true));

        // Row 2 accent bar
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->getStyle("A2:{$lastCol}2")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
        $sheet->getRowDimension(2)->setRowHeight(8);

        // Row 3 title strip
        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', 'Risk Analysis Format For Audit (MFI) — FY '.$fyLabel);
        $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(22);

        // Row 4 column headers
        $headers = $this->columnHeaders();
        $sheet->fromArray($headers, null, 'A4', true);
        $sheet->getRowDimension(4)->setRowHeight(46);
        $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
            'font' => ['bold' => true, 'size' => 8, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F5496']],
            'alignment' => [
                'wrapText' => true,
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $sheet->getStyle('X4:AJ4')->getFill()->getStartColor()->setRGB('C55A11');
        $sheet->getStyle('AK4:AM4')->getFill()->getStartColor()->setRGB('1F4E79');

        $dataStart = 5;
        $matrix = [];
        foreach ($rows as $i => $row) {
            $matrix[] = $this->dataLine($row, $i + 1);
        }

        if ($matrix !== []) {
            $sheet->fromArray($matrix, null, 'A'.$dataStart, true);
            $dataEnd = $dataStart + count($matrix) - 1;
            $sheet->getStyle("A{$dataStart}:{$lastCol}{$dataEnd}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'font' => ['size' => 9],
            ]);

            // formats
            foreach (['H', 'I', 'J', 'K', 'L', 'M', 'N', 'O'] as $col) {
                $sheet->getStyle("{$col}{$dataStart}:{$col}{$dataEnd}")
                    ->getNumberFormat()->setFormatCode('0.00%');
            }
            foreach (['P', 'Q', 'R', 'S', 'T'] as $col) {
                $sheet->getStyle("{$col}{$dataStart}:{$col}{$dataEnd}")
                    ->getNumberFormat()->setFormatCode('#,##0.00');
            }
            $sheet->getStyle("G{$dataStart}:G{$dataEnd}")
                ->getNumberFormat()->setFormatCode('d-mmm-yyyy');

            for ($r = $dataStart; $r <= $dataEnd; $r++) {
                $category = (string) $sheet->getCell("AM{$r}")->getValue();
                $rgb = match ($category) {
                    'Low Risk' => 'C6EFCE',
                    'Medium Risk' => 'FFE699',
                    'High Risk' => 'F4B183',
                    'Significant Risk' => 'FF6B6B',
                    default => 'D9D9D9',
                };
                $sheet->getStyle("AM{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($rgb);
                $sheet->getStyle("AM{$r}")->getFont()->setBold(true);
            }
        }

        $widths = [
            'A' => 6, 'B' => 18, 'C' => 14, 'D' => 16, 'E' => 18, 'F' => 10, 'G' => 13,
            'H' => 9, 'I' => 9, 'J' => 9, 'K' => 9, 'L' => 11, 'M' => 11, 'N' => 9, 'O' => 9,
            'P' => 12, 'Q' => 12, 'R' => 12, 'S' => 12, 'T' => 12, 'U' => 12, 'V' => 10, 'W' => 12,
        ];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
        foreach (range(24, 36) as $i) { // X..AJ
            $sheet->getColumnDimensionByColumn($i)->setWidth(8);
        }
        $sheet->getColumnDimension('AK')->setWidth(12);
        $sheet->getColumnDimension('AL')->setWidth(10);
        $sheet->getColumnDimension('AM')->setWidth(14);

        $sheet->freezePane('H5');
        $sheet->setAutoFilter("A4:{$lastCol}4");
    }

    /**
     * @return list<string>
     */
    protected function columnHeaders(): array
    {
        return [
            // A-G branch
            'Sl No.',
            'Focal Person Name',
            'Name of the Zone',
            'Area Name',
            'Branch Name',
            'Code',
            'Branch Opening Date',
            // H-W metrics
            'OTR %',
            'PAR %',
            'DR %',
            'WR %',
            'Write-off Principal Amount',
            'Savings Adjustment %',
            'OSS %',
            'NPLR / OD %',
            'Fraud & Forgery (Tk)',
            'Loan Outstanding (Tk)',
            'Total Income (Tk)',
            'Total Expenditure (Tk)',
            'Surplus / (Deficit) (Tk)',
            'Last Internal Audit Rating',
            'Distance >20km from Area Office',
            'BM & ABM Both Present',
            // X-AJ weights (13 metric-linked scores we track + placeholders to fill span)
            'W-OTR',
            'W-PAR',
            'W-DR',
            'W-WR',
            'W-Write-off',
            'W-Savings Adj',
            'W-OSS',
            'W-NPLR',
            'W-Fraud',
            'W-Profitability',
            'W-Distance',
            'W-BM/ABM',
            'W-Special Audit',
            // AK-AM
            'Total Weighted Score',
            'Total Weight %',
            'Risk Category',
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<mixed>
     */
    protected function dataLine(array $row, int $serial): array
    {
        return [
            $serial,
            $row['focal_person_name'] ?? '',
            $row['zone'] ?? '',
            $row['area_name'] ?? '',
            $row['branch_name'] ?? '',
            $row['code'] ?? '',
            $row['opening_date'] ?? null,
            $row['otr'] ?? null,
            $row['par'] ?? null,
            $row['dr'] ?? null,
            $row['wr'] ?? null,
            $row['write_off_amount'] ?? null,
            $row['savings_adjustment_pct'] ?? null,
            $row['oss'] ?? null,
            $row['nplr'] ?? null,
            $row['fraud_forgery'] ?? null,
            $row['loan_outstanding'] ?? null,
            $row['total_income'] ?? null,
            $row['total_expenditure'] ?? null,
            $row['surplus_deficit'] ?? null,
            $row['last_audit_rating'] ?? '',
            $row['distance_yes_no'] ?? '',
            $row['bm_abm_yes_no'] ?? '',
            $row['w_otr'] ?? 0,
            $row['w_par'] ?? 0,
            $row['w_dr'] ?? 0,
            $row['w_wr'] ?? 0,
            $row['w_write_off'] ?? 0,
            $row['w_savings_adj'] ?? 0,
            $row['w_oss'] ?? 0,
            $row['w_nplr'] ?? 0,
            $row['w_fraud'] ?? 0,
            $row['w_profitability'] ?? 0,
            $row['w_distance'] ?? 0,
            $row['w_bm_abm'] ?? 0,
            $row['w_special_audit'] ?? 0,
            $row['total_weighted_score'] ?? 0,
            100,
            $row['risk_category'] ?? '',
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    protected function paintEngagementSheet(Worksheet $sheet, array $rows): void
    {
        $sheet->setCellValue('A1', 'Engagement / Focal Person Summary');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);

        $sheet->fromArray(['#', 'Focal Person Name', 'Branch Name', 'Area', 'Risk Category'], null, 'A3', true);
        $sheet->getStyle('A3:E3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '595959']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        $matrix = [];
        foreach ($rows as $i => $row) {
            $matrix[] = [
                $i + 1,
                $row['focal_person_name'] ?? '',
                $row['branch_name'] ?? '',
                $row['area_name'] ?? '',
                $row['risk_category'] ?? '',
            ];
        }
        if ($matrix !== []) {
            $sheet->fromArray($matrix, null, 'A4', true);
            $end = 3 + count($matrix);
            $sheet->getStyle("A4:E{$end}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
        }

        foreach (['A' => 6, 'B' => 22, 'C' => 22, 'D' => 18, 'E' => 16] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function groupHeaderStyle(string $rgb, bool $whiteText = false): array
    {
        return [
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['rgb' => $whiteText ? 'FFFFFF' : '000000'],
            ],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rgb]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
    }
}
