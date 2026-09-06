<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Simple authority-facing month brief for irregularities.
 */
class FindingsAuthoritySummaryExcelExporter
{
    public function __construct(
        private AuditSummaryService $summary,
    ) {}

    public function download(int $month, int $year): StreamedResponse
    {
        $data = $this->summary->getAuthorityMonthSummary($month, $year);
        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        $overview = new Worksheet($spreadsheet, 'Overview');
        $spreadsheet->addSheet($overview, 0);
        $overview->getTabColor()->setRGB('1F4E79');
        $this->paintOverview($overview, $data);

        $branches = new Worksheet($spreadsheet, 'Branches');
        $spreadsheet->addSheet($branches, 1);
        $branches->getTabColor()->setRGB('C00000');
        $this->paintBranches($branches, $data);

        $issues = new Worksheet($spreadsheet, 'Top issues');
        $spreadsheet->addSheet($issues, 2);
        $issues->getTabColor()->setRGB('ED7D31');
        $this->paintIssues($issues, $data);

        $spreadsheet->setActiveSheetIndex(0);
        $filename = sprintf(
            'findings-summary-%04d-%02d.xlsx',
            $data['year'],
            $data['month']
        );

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
     * @param  array<string, mixed>  $data
     */
    protected function paintOverview(Worksheet $sheet, array $data): void
    {
        $sheet->setCellValue('A1', 'Monthly Irregularities Summary — '.$data['period_label']);
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $sheet->setCellValue('A2', $data['headline']);
        $sheet->mergeCells('A2:D2');
        $sheet->getStyle('A2')->getFont()->setSize(10)->setItalic(true);

        $rows = [
            ['Total irregularities', $data['total_irregularities'], $this->deltaText($data['deltas']['irregularities']), 'vs '.$data['prev_period_label']],
            ['Amount under observation (৳)', $data['total_amount'], $this->deltaText($data['deltas']['amount']), 'vs '.$data['prev_period_label']],
            ['Branches with findings', $data['branches_with_findings'], $this->deltaText($data['deltas']['branches']), $data['coverage_pct'].'% of active branches'],
            ['Indicators hit', $data['indicators_hit'], $this->deltaText($data['deltas']['indicators_hit']), 'Major risk hits: '.$data['major_risk_hits']],
            ['Samples checked', $data['total_samples'], '', 'Defect rate: '.$data['defect_rate'].'%'],
        ];

        $sheet->setCellValue('A4', 'Metric');
        $sheet->setCellValue('B4', 'Value');
        $sheet->setCellValue('C4', 'Change');
        $sheet->setCellValue('D4', 'Note');
        $sheet->getStyle('A4:D4')->applyFromArray($this->headerStyle());

        $r = 5;
        foreach ($rows as $row) {
            $sheet->setCellValue('A'.$r, $row[0]);
            $sheet->setCellValue('B'.$r, $row[1]);
            $sheet->setCellValue('C'.$r, $row[2]);
            $sheet->setCellValue('D'.$r, $row[3]);
            if ($r === 6) {
                $sheet->getStyle('B'.$r)->getNumberFormat()->setFormatCode('#,##0.00');
            }
            $r++;
        }
        $sheet->getStyle('A5:D'.($r - 1))->applyFromArray($this->borderStyle());

        $sheet->setCellValue('A'.($r + 1), 'By category');
        $sheet->getStyle('A'.($r + 1))->getFont()->setBold(true);
        $sheet->setCellValue('A'.($r + 2), 'Category');
        $sheet->setCellValue('B'.($r + 2), 'Issues');
        $sheet->setCellValue('C'.($r + 2), 'Amount');
        $sheet->getStyle('A'.($r + 2).':C'.($r + 2))->applyFromArray($this->headerStyle());
        $cr = $r + 3;
        foreach ($data['categories'] as $cat) {
            $sheet->setCellValue('A'.$cr, $cat['name']);
            $sheet->setCellValue('B'.$cr, $cat['hits']);
            $sheet->setCellValue('C'.$cr, $cat['amount']);
            $sheet->getStyle('C'.$cr)->getNumberFormat()->setFormatCode('#,##0.00');
            $cr++;
        }
        if ($cr > $r + 3) {
            $sheet->getStyle('A'.($r + 3).':C'.($cr - 1))->applyFromArray($this->borderStyle());
        }

        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function paintBranches(Worksheet $sheet, array $data): void
    {
        $sheet->setCellValue('A1', 'Branches — '.$data['period_label'].' (ranked by irregularities)');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C00000']],
        ]);

        $headers = ['#', 'Shakha', 'Code', 'Irregularities', 'Amount', 'Samples', 'Defect %'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValue(chr(ord('A') + $i).'3', $header);
        }
        $sheet->getStyle('A3:G3')->applyFromArray($this->headerStyle());

        $r = 4;
        foreach ($data['branch_rows'] as $i => $row) {
            $sheet->setCellValue('A'.$r, $i + 1);
            $sheet->setCellValue('B'.$r, $row['name']);
            $sheet->setCellValue('C'.$r, $row['code']);
            $sheet->setCellValue('D'.$r, $row['irregularities']);
            $sheet->setCellValue('E'.$r, $row['amount']);
            $sheet->setCellValue('F'.$r, $row['samples']);
            $sheet->setCellValue('G'.$r, $row['defect_rate']);
            $sheet->getStyle('E'.$r)->getNumberFormat()->setFormatCode('#,##0.00');
            if ((int) $row['irregularities'] > 0) {
                $sheet->getStyle('D'.$r)->getFont()->setBold(true)->getColor()->setRGB('B91C1C');
            }
            $r++;
        }
        if ($r === 4) {
            $sheet->setCellValue('A4', 'No branch findings this month.');
        } else {
            $sheet->getStyle('A4:G'.($r - 1))->applyFromArray($this->borderStyle());
            $sheet->setAutoFilter('A3:G'.($r - 1));
        }
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->freezePane('A4');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function paintIssues(Worksheet $sheet, array $data): void
    {
        $sheet->setCellValue('A1', 'Top issues — '.$data['period_label'].' (ranked by irregularities)');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'ED7D31']],
        ]);

        $headers = ['#', 'Code', 'Issue', 'Category', 'Risk', 'Irregularities', 'Branches', 'Amount'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValue(chr(ord('A') + $i).'3', $header);
        }
        $sheet->getStyle('A3:H3')->applyFromArray($this->headerStyle());

        $r = 4;
        foreach ($data['issue_rows'] as $i => $row) {
            $sheet->setCellValue('A'.$r, $i + 1);
            $sheet->setCellValue('B'.$r, $row['code']);
            $sheet->setCellValue('C'.$r, $row['title']);
            $sheet->setCellValue('D'.$r, $row['category']);
            $sheet->setCellValue('E'.$r, $row['risk_rating']);
            $sheet->setCellValue('F'.$r, $row['irregularities']);
            $sheet->setCellValue('G'.$r, $row['objected_branches']);
            $sheet->setCellValue('H'.$r, $row['amount']);
            $sheet->getStyle('H'.$r)->getNumberFormat()->setFormatCode('#,##0.00');
            $r++;
        }
        if ($r === 4) {
            $sheet->setCellValue('A4', 'No issues this month.');
        } else {
            $sheet->getStyle('A4:H'.($r - 1))->applyFromArray($this->borderStyle());
            $sheet->setAutoFilter('A3:H'.($r - 1));
        }
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->freezePane('A4');
    }

    /**
     * @param  array{diff:float|int,pct:float|null,direction:string}  $delta
     */
    protected function deltaText(array $delta): string
    {
        $sign = $delta['diff'] > 0 ? '+' : '';
        $pct = $delta['pct'] === null ? '' : ' ('.$sign.$delta['pct'].'%)';

        return $sign.$delta['diff'].$pct;
    }

    /**
     * @return array<string, mixed>
     */
    protected function headerStyle(): array
    {
        return [
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '334155']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']],
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
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
            ],
        ];
    }
}
