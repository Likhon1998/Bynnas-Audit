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
 * Findings Summary export — report-linked underheading table only.
 */
class FindingsAuthoritySummaryExcelExporter
{
    public function __construct(
        private AuditSummaryService $summary,
    ) {}

    public function download(int $month, int $year): StreamedResponse
    {
        $groups = $this->summary->getMonthUnderheadingSummary($month, $year);
        $periodLabel = date('F', mktime(0, 0, 0, $month, 1)).' '.$year;

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Findings Summary');
        $sheet->getTabColor()->setRGB('548235');
        $this->paintUnderheadingSummary($sheet, $groups, $periodLabel);

        $filename = sprintf('findings-summary-%04d-%02d.xlsx', $year, $month);

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
     * @param  list<array<string, mixed>>  $groups
     */
    protected function paintUnderheadingSummary(Worksheet $sheet, array $groups, string $periodLabel): void
    {
        $sheet->setCellValue('A1', 'Findings Summary — '.$periodLabel);
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '548235']],
        ]);

        $headers = ['Heading', 'Sub-heading', 'Code', 'Indicator', 'Amount', 'Sample size', 'Irregularities', 'Irregularity %', 'Total branch', 'Shakha', 'Accused kormi'];
        foreach ($headers as $i => $header) {
            $col = chr(ord('A') + $i);
            $sheet->setCellValue($col.'3', $header);
        }
        $sheet->getStyle('A3:K3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '334155']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']],
            ],
        ]);

        $r = 4;
        foreach ($groups as $group) {
            foreach ($group['rows'] as $row) {
                $branchRows = array_values((array) ($row['branch_rows'] ?? []));
                if ($branchRows === []) {
                    $branchRows = [['label' => '—', 'accused_kormi' => '']];
                }

                foreach ($branchRows as $branchIndex => $branch) {
                    if ($branchIndex === 0) {
                        $sheet->setCellValue('A'.$r, $group['category']);
                        $sheet->setCellValue('B'.$r, $group['sub_category']);
                        $sheet->setCellValue('C'.$r, $row['code']);
                        $sheet->setCellValue('D'.$r, $row['title']);
                        $sheet->setCellValue('E'.$r, $row['amount']);
                        $sheet->setCellValue('F'.$r, $row['samples']);
                        $sheet->setCellValue('G'.$r, $row['irregularities']);
                        $sheet->setCellValue('H'.$r, $row['percentage'] === null ? '—' : $row['percentage'] / 100);
                        $sheet->setCellValue('I'.$r, $row['branch_count']);
                        $sheet->getStyle('E'.$r)->getNumberFormat()->setFormatCode('#,##0.00');
                        if ($row['percentage'] !== null) {
                            $sheet->getStyle('H'.$r)->getNumberFormat()->setFormatCode('0.00%');
                        }
                    }
                    $sheet->setCellValue('J'.$r, (string) ($branch['label'] ?? '—'));
                    $sheet->setCellValue('K'.$r, filled($branch['accused_kormi'] ?? null) ? (string) $branch['accused_kormi'] : '—');
                    $r++;
                }
            }
        }

        if ($r === 4) {
            $sheet->setCellValue('A4', 'No report findings this month.');
        } else {
            $sheet->getStyle('A4:K'.($r - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
                ],
            ]);
            $sheet->setAutoFilter('A3:K'.($r - 1));
        }

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->freezePane('A4');
    }
}
