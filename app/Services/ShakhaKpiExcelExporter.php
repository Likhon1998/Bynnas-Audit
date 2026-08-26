<?php

namespace App\Services;

use App\Support\FinancialYear;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShakhaKpiExcelExporter
{
    public function __construct(
        private KpiReportService $kpiReports,
    ) {}

    public function download(string $fyLabel, bool $onlyEntered = true): StreamedResponse
    {
        @set_time_limit(120);
        @ini_set('memory_limit', '512M');

        $fy = FinancialYear::fromLabel($fyLabel);
        $rows = $this->kpiReports->compileAllRows($fyLabel, $onlyEntered);
        $headers = $this->headers($fy);
        $colCount = count($headers);
        $lastCol = Coordinate::stringFromColumnIndex($colCount);
        $monthLabel = $fy->endDate->format('M-y');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Annual KPI');

        $sheet->setCellValue('A1', 'Name Of The Month :-');
        $sheet->setCellValue('B1', $monthLabel);
        $sheet->getStyle('B1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
        $sheet->setCellValue('J1', 'Annually');
        $sheet->getStyle('J1')->getFont()->setBold(true);

        // Header labels in one go
        $headerLabels = array_map(fn ($h) => $h['label'], $headers);
        $sheet->fromArray($headerLabels, null, 'A2', true);
        $sheet->getRowDimension(2)->setRowHeight(48);

        $headerStyle = [
            'font' => ['bold' => true, 'size' => 9],
            'alignment' => [
                'wrapText' => true,
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle("A2:{$lastCol}2")->applyFromArray($headerStyle);

        foreach ($headers as $i => $header) {
            if (! empty($header['fill'])) {
                $col = Coordinate::stringFromColumnIndex($i + 1);
                $sheet->getStyle("{$col}2")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($header['fill']);
            }
        }

        $dataStart = 3;
        $matrix = [];
        foreach ($rows as $serial => $row) {
            $line = [];
            foreach ($headers as $header) {
                $line[] = $this->cellValue($header, $row);
            }
            // Keep sequential serial for entered-only export
            $line[0] = $serial + 1;
            $matrix[] = $line;
        }

        if ($matrix !== []) {
            $sheet->fromArray($matrix, null, 'A'.$dataStart, true);
            $dataEnd = $dataStart + count($matrix) - 1;
            $dataRange = "A{$dataStart}:{$lastCol}{$dataEnd}";

            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // Number formats by column (range apply — much faster than per-cell)
            foreach ($headers as $i => $header) {
                $col = Coordinate::stringFromColumnIndex($i + 1);
                $range = "{$col}{$dataStart}:{$col}{$dataEnd}";
                $format = $header['format'] ?? 'text';
                $key = $header['key'];

                if (in_array($key, ['opening_date', 'today_date'], true)) {
                    $sheet->getStyle($range)->getNumberFormat()->setFormatCode('m/d/yyyy');
                } elseif ($format === 'pct') {
                    $sheet->getStyle($range)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
                } elseif ($format === 'money') {
                    $sheet->getStyle($range)->getNumberFormat()->setFormatCode('#,##0.00');
                } elseif ($format === 'int') {
                    $sheet->getStyle($range)->getNumberFormat()->setFormatCode('#,##0');
                } elseif ($format === 'ratio') {
                    $sheet->getStyle($range)->getNumberFormat()->setFormatCode('0.00');
                }

                if (! empty($header['pink'])) {
                    $sheet->getStyle($range)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('FCE4EC');
                }
            }

            $this->mergeAreaColumn($sheet, $rows, $dataStart);
        }

        foreach (range(1, $colCount) as $i) {
            $sheet->getColumnDimensionByColumn($i)->setWidth($i <= 5 ? 14 : 12);
        }
        $sheet->freezePane('E3');

        $filename = 'shakha-annual-kpi-'.$fyLabel.'.xlsx';

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
     * @param  array{key:string,format?:string}  $header
     * @param  array<string, mixed>  $row
     */
    protected function cellValue(array $header, array $row): mixed
    {
        $key = $header['key'];
        $value = $row[$key] ?? null;
        $format = $header['format'] ?? 'text';

        if (in_array($key, ['opening_date', 'today_date'], true)) {
            return $value ? ExcelDate::PHPToExcel($value) : null;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return match ($format) {
            'pct', 'money', 'ratio' => (float) $value,
            'int' => (int) $value,
            default => $value,
        };
    }

    /**
     * @return list<array{key:string,label:string,fill?:string,pink?:bool,format?:string}>
     */
    protected function headers(FinancialYear $fy): array
    {
        $yellow = 'FFFF00';
        $grey = 'D9D9D9';
        $priorJune = 'June-'.$fy->startYear();
        $endJun = 'Jun-'.$fy->endDate->format('Y');

        return [
            ['key' => 'serial', 'label' => '#', 'format' => 'int'],
            ['key' => 'code', 'label' => 'Code'],
            ['key' => 'area_name', 'label' => 'Area Name'],
            ['key' => 'branch_name', 'label' => 'Branch Name'],
            ['key' => 'opening_date', 'label' => 'Branch Opening date'],
            ['key' => 'fo_count', 'label' => 'FO #', 'format' => 'int'],
            ['key' => 'total_samities', 'label' => 'Total Samities', 'format' => 'int'],
            ['key' => 'total_members', 'label' => 'Total Members', 'format' => 'int'],
            ['key' => 'fy_savings_collection', 'label' => 'Fiscal year Savings Collection', 'fill' => $yellow, 'format' => 'money'],
            ['key' => 'fy_savings_withdrawal', 'label' => 'Fiscal year Savings withdrawal', 'fill' => $yellow, 'format' => 'money'],
            ['key' => 'fy_savings_increase', 'label' => 'Fiscal year Savings Increase', 'fill' => $yellow, 'pink' => true, 'format' => 'money'],
            ['key' => 'savings_balance', 'label' => 'Savings Balance', 'fill' => $grey, 'format' => 'money'],
            ['key' => 'fy_members_admission', 'label' => 'Fiscal year Members Admission', 'fill' => $yellow, 'format' => 'int'],
            ['key' => 'fy_members_dropout', 'label' => 'Fiscal year Members Dropout', 'fill' => $yellow, 'format' => 'int'],
            ['key' => 'fy_members_increase', 'label' => 'Fiscal year Members Increase', 'fill' => $yellow, 'pink' => true, 'format' => 'int'],
            ['key' => 'fy_disbursement_borrowers', 'label' => 'Fiscal year Disbursement Borrowers', 'fill' => $yellow, 'format' => 'int'],
            ['key' => 'fy_fully_repayment_borrowers', 'label' => 'Fiscal year Fully Repayment Borrowers', 'fill' => $yellow, 'format' => 'int'],
            ['key' => 'fy_borrowers_increase', 'label' => 'Fiscal year Borrowers Increase', 'fill' => $yellow, 'pink' => true, 'format' => 'int'],
            ['key' => 'fy_disbursement_amount', 'label' => 'Fiscal year Disbursement Amount', 'fill' => $yellow, 'format' => 'money'],
            ['key' => 'fy_loan_recovery', 'label' => 'Fiscal year Loan Recovery', 'fill' => $yellow, 'format' => 'money'],
            ['key' => 'fy_loan_outstanding_increase', 'label' => 'Fiscal year Loan Outstanding Increase', 'fill' => $yellow, 'pink' => true, 'format' => 'money'],
            ['key' => 'total_borrowers', 'label' => 'Total Borrowers', 'fill' => $grey, 'format' => 'int'],
            ['key' => 'loan_outstanding', 'label' => 'Loan Outstanding', 'fill' => $grey, 'format' => 'money'],
            ['key' => 'recoverable', 'label' => 'Recoverable', 'fill' => $grey, 'format' => 'money'],
            ['key' => 'current_recovery', 'label' => 'Current Recovery', 'fill' => $grey, 'format' => 'money'],
            ['key' => 'due_recovery', 'label' => 'Due Recovery', 'fill' => $grey, 'format' => 'money'],
            ['key' => 'total_od_borrowers', 'label' => 'Total OD Borrowers', 'format' => 'int'],
            ['key' => 'total_od_taka', 'label' => 'Total OD Taka', 'format' => 'money'],
            ['key' => 'due_loanee_loan_outstanding', 'label' => 'Due Loanee Loan Outstanding', 'format' => 'money'],
            ['key' => 'own_fund_until_prior_june', 'label' => 'Own Fund Until '.$priorJune, 'format' => 'money'],
            ['key' => 'surplus_deficit_fy', 'label' => 'Surplus/Deficit (FY '.$fy->label.')', 'format' => 'money'],
            ['key' => 'total_surplus_deficit', 'label' => 'Total Surplus/Deficit '.$endJun, 'pink' => true, 'format' => 'money'],
            ['key' => 'new_due', 'label' => 'New Due', 'pink' => true, 'format' => 'money'],
            ['key' => 'due_increase_this_month', 'label' => 'Due Increase This Month', 'pink' => true, 'format' => 'money'],
            ['key' => 'otr', 'label' => 'OTR', 'format' => 'pct'],
            ['key' => 'dr_borrowers', 'label' => 'DR (Borrowers)', 'pink' => true, 'format' => 'pct'],
            ['key' => 'dr_taka', 'label' => 'DR (Taka)', 'pink' => true, 'format' => 'pct'],
            ['key' => 'par', 'label' => 'PAR', 'pink' => true, 'format' => 'pct'],
            ['key' => 'overdue_growth_vs_outstanding', 'label' => 'Overdue Growth Rate vs. Outstanding Loans', 'format' => 'pct'],
            ['key' => 'due_recovery_pct', 'label' => 'Due Recovery %', 'format' => 'pct'],
            ['key' => 'member_loanee', 'label' => 'Member : Loanee', 'format' => 'pct'],
            ['key' => 'savings_loan', 'label' => 'Savings : Loan Outstanding', 'format' => 'pct'],
            ['key' => 'dropout_pct', 'label' => 'Dropout %', 'format' => 'pct'],
            ['key' => 'savings_withdrawal_pct', 'label' => 'Savings Withdrawal %', 'format' => 'pct'],
            ['key' => 'samities_member', 'label' => 'Samities : Member', 'format' => 'ratio'],
            ['key' => 'samities_borrowers', 'label' => 'Samities : Borrowers', 'format' => 'ratio'],
            ['key' => 'fo_member', 'label' => 'FO : Member', 'format' => 'ratio'],
            ['key' => 'fo_borrowers', 'label' => 'FO : Borrowers', 'format' => 'ratio'],
            ['key' => 'fo_savings', 'label' => 'FO : Savings Balance', 'format' => 'money'],
            ['key' => 'fo_loan', 'label' => 'FO : Loan Outstanding', 'format' => 'money'],
            ['key' => 'member_savings', 'label' => 'Member : Savings Balance', 'format' => 'money'],
            ['key' => 'borrowers_loan', 'label' => 'Borrowers : Loan Outstanding', 'format' => 'money'],
            ['key' => 'today_date', 'label' => 'Today date'],
            ['key' => 'opening_year', 'label' => 'Year', 'format' => 'int'],
            ['key' => 'opening_month', 'label' => 'Month', 'format' => 'int'],
            ['key' => 'opening_day', 'label' => 'Day', 'format' => 'int'],
            ['key' => 'focal_person_name', 'label' => 'Focal Person Name'],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    protected function mergeAreaColumn($sheet, array $rows, int $startRow): void
    {
        $n = count($rows);
        if ($n < 2) {
            return;
        }

        $from = $startRow;
        $prev = $rows[0]['area_name'] ?? '';

        for ($i = 1; $i <= $n; $i++) {
            $current = $i < $n ? ($rows[$i]['area_name'] ?? '') : null;
            if ($i === $n || $current !== $prev) {
                $to = $startRow + $i - 1;
                if ($to > $from) {
                    $sheet->mergeCells([3, $from, 3, $to]);
                    $sheet->getStyle([3, $from])->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                }
                $from = $startRow + $i;
                $prev = $current;
            }
        }
    }
}
