<?php

namespace App\Services;

use App\Models\AuditPlan;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectWorkPlanExcelExporter
{
    public function download(AuditPlan $plan, string $mode): StreamedResponse
    {
        $builder = new AnnualAuditReportBuilder($plan);
        $months = $builder->months();
        $fy = $plan->fy_label;

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        if ($mode === 'hq') {
            $rows = $builder->hqMatrix();
            $totals = $builder->hqTotals($rows);
            $sheet->setTitle('HQ');
            $this->writeHqSheet($sheet, $fy, $months, $rows, $totals);
            $filename = 'hq-work-plan-'.$fy.'.xlsx';
        } elseif ($mode === 'total') {
            $sheet->setTitle('Total');
            $this->writeTotalSheet($sheet, $fy, $months, $builder->totalsByCategory());
            $filename = 'annual-total-work-plan-'.$fy.'.xlsx';
        } elseif ($mode === 'shakha') {
            $groups = $builder->shakhaGroups();
            $rows = $groups->flatMap(fn ($g) => $g['rows']);
            $totals = $builder->shakhaTotals($rows);
            $sheet->setTitle('Shakha Audit');
            $this->writeShakhaSheet($sheet, $fy, $months, $groups, $totals);
            $filename = 'shakha-work-plan-'.$fy.'.xlsx';
        } elseif ($mode === 'area') {
            $rows = $builder->areaMatrix();
            $totals = $builder->areaTotals($rows);
            $sheet->setTitle('Area Office');
            $this->writeAreaSheet($sheet, $fy, $months, $rows, $totals);
            $filename = 'area-office-work-plan-'.$fy.'.xlsx';
        } elseif ($mode === 'pksf') {
            $rows = $builder->pksfMatrix();
            $totals = $builder->pksfTotals($rows);
            $sheet->setTitle('PKSF Maternity');
            $this->writePksfSheet($sheet, $fy, $months, $rows, $totals);
            $filename = 'pksf-maternity-work-plan-'.$fy.'.xlsx';
        } else {
            $isAudit = $mode === 'audit';
            $groups = $isAudit ? $builder->projectAuditGroups() : $builder->projectMonitoringGroups();
            $title = $isAudit ? 'Project Audit Work Plan' : 'Project Monitoring Work Plan';
            $sheetTitle = $isAudit ? 'Project Audit' : 'Project Monitoring';
            $sheet->setTitle($sheetTitle);
            $this->writeSheet($sheet, $title, $fy, $months, $groups);
            $filename = str_replace(' ', '-', strtolower($sheetTitle)).'-'.$fy.'.xlsx';
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * One workbook with sheets: Total → Shakha → Area → PKSF → HQ → Project Audit → Project Monitoring.
     */
    public function downloadAll(AuditPlan $plan): StreamedResponse
    {
        $builder = new AnnualAuditReportBuilder($plan);
        $months = $builder->months();
        $fy = $plan->fy_label;

        $spreadsheet = new Spreadsheet;

        $totalSheet = $spreadsheet->getActiveSheet();
        $totalSheet->setTitle('Total');
        $this->writeTotalSheet($totalSheet, $fy, $months, $builder->totalsByCategory());

        $shakhaSheet = $spreadsheet->createSheet();
        $shakhaSheet->setTitle('Shakha Audit');
        $shakhaGroups = $builder->shakhaGroups();
        $shakhaRows = $shakhaGroups->flatMap(fn ($g) => $g['rows']);
        $this->writeShakhaSheet($shakhaSheet, $fy, $months, $shakhaGroups, $builder->shakhaTotals($shakhaRows));

        $areaSheet = $spreadsheet->createSheet();
        $areaSheet->setTitle('Area Office');
        $areaRows = $builder->areaMatrix();
        $this->writeAreaSheet($areaSheet, $fy, $months, $areaRows, $builder->areaTotals($areaRows));

        $pksfSheet = $spreadsheet->createSheet();
        $pksfSheet->setTitle('PKSF Maternity');
        $pksfRows = $builder->pksfMatrix();
        $this->writePksfSheet($pksfSheet, $fy, $months, $pksfRows, $builder->pksfTotals($pksfRows));

        $hqSheet = $spreadsheet->createSheet();
        $hqSheet->setTitle('HQ');
        $hqRows = $builder->hqMatrix();
        $this->writeHqSheet($hqSheet, $fy, $months, $hqRows, $builder->hqTotals($hqRows));

        $auditSheet = $spreadsheet->createSheet();
        $auditSheet->setTitle('Project Audit');
        $this->writeSheet($auditSheet, 'Project Audit Work Plan', $fy, $months, $builder->projectAuditGroups());

        $monitoringSheet = $spreadsheet->createSheet();
        $monitoringSheet->setTitle('Project Monitoring');
        $this->writeSheet($monitoringSheet, 'Project Monitoring Work Plan', $fy, $months, $builder->projectMonitoringGroups());

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'annual-audit-full-report-'.$fy.'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @param  list<array{index:int,key:string,label:string,year:int,month:int}>  $months
     * @param  array<string, array{label:string,planned:int,by_month:array<int,int>}>  $categoryTotals
     */
    protected function writeTotalSheet(Worksheet $sheet, string $fy, array $months, array $categoryTotals): void
    {
        $fyParts = explode('-', $fy);
        $startYear = substr($fyParts[0] ?? '2026', -2);
        $endYear = substr($fyParts[1] ?? '2027', -2);
        $lastCol = $this->col(2 + count($months)); // A category + 12 months + total = 14 = N

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'Dushtha Shasthya Kendra (DSK)');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '0F172A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', "Annual Audit & Monitoring — Summary Totals · FY {$fy}");
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '334155']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->setCellValue('A4', 'Category');
        foreach ($months as $i => $month) {
            $shortYear = $month['index'] <= 5 ? $startYear : $endYear;
            $monthName = match ($month['month']) {
                7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
                default => $month['label'],
            };
            $sheet->setCellValue($this->col(2 + $i).'4', "{$monthName}'{$shortYear}");
        }
        $sheet->setCellValue($this->col(2 + count($months)).'4', 'Total');
        $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']]],
        ]);

        $row = 5;
        $grandByMonth = array_fill(0, 12, 0);
        $grandTotal = 0;
        foreach ($categoryTotals as $item) {
            $sheet->setCellValue("A{$row}", $item['label']);
            $byMonth = array_values($item['by_month']);
            foreach ($byMonth as $i => $count) {
                $sheet->setCellValue($this->col(2 + $i)."{$row}", $count ?: '—');
                $grandByMonth[$i] += (int) $count;
            }
            $sheet->setCellValue($this->col(2 + count($months))."{$row}", $item['planned']);
            $grandTotal += (int) $item['planned'];
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                'font' => ['size' => 10],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $row++;
        }

        $sheet->setCellValue("A{$row}", 'Grand Total');
        foreach ($grandByMonth as $i => $count) {
            $sheet->setCellValue($this->col(2 + $i)."{$row}", $count);
        }
        $sheet->setCellValue($this->col(2 + count($months))."{$row}", $grandTotal);
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->getColumnDimension('A')->setWidth(22);
        for ($i = 2; $i <= 1 + count($months); $i++) {
            $sheet->getColumnDimension($this->col($i))->setWidth(9);
        }
        $sheet->getColumnDimension($lastCol)->setWidth(10);
    }

    /**
     * @param  list<array{index:int,key:string,label:string,year:int,month:int}>  $months
     * @param  Collection<int, array<string, mixed>>  $rows
     * @param  array{by_month: array<int,int>, by_quarter: array<string,int>, grand: int}  $totals
     */
    protected function writeHqSheet(Worksheet $sheet, string $fy, array $months, Collection $rows, array $totals): void
    {
        $fyParts = explode('-', $fy);
        $startYear = substr($fyParts[0] ?? '2026', -2);
        $endYear = substr($fyParts[1] ?? '2027', -2);
        $lastColIndex = 2 + count($months) + 1; // A #, B dept, C-N months, O total
        $lastCol = $this->col($lastColIndex);

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'Dushtha Shasthya Kendra (DSK)');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '0F172A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', "Headquarters Monitoring & Audit Work Plan: July'{$startYear} to June'{$endYear}");
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '334155']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', 'Headquarters (HQ)');
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Quarter header row
        $sheet->setCellValue('A4', '#');
        $sheet->setCellValue('B4', 'Department / Section');
        $sheet->mergeCells('C4:E4');
        $sheet->setCellValue('C4', '1st Quarter');
        $sheet->mergeCells('F4:H4');
        $sheet->setCellValue('F4', '2nd Quarter');
        $sheet->mergeCells('I4:K4');
        $sheet->setCellValue('I4', '3rd Quarter');
        $sheet->mergeCells('L4:N4');
        $sheet->setCellValue('L4', '4th Quarter');
        $sheet->setCellValue('O4', 'Total');

        $sheet->getStyle('A4:O4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Month labels
        $sheet->setCellValue('A5', '');
        $sheet->setCellValue('B5', '');
        foreach ($months as $i => $month) {
            $shortYear = $month['index'] <= 5 ? $startYear : $endYear;
            $monthName = match ($month['month']) {
                7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
                default => $month['label'],
            };
            $sheet->setCellValue($this->col(3 + $i).'5', "{$monthName}'{$shortYear}");
        }
        $sheet->setCellValue('O5', '');
        $sheet->getStyle('A5:O5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 8],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'ECFDF5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $row = 6;
        foreach ($rows as $item) {
            $sheet->setCellValue("A{$row}", $item['sl']);
            $sheet->setCellValue("B{$row}", $item['name']);
            foreach ($item['months'] as $monthIndex => $active) {
                $cell = $this->col(3 + (int) $monthIndex).$row;
                if ($active) {
                    $sheet->setCellValue($cell, 1);
                    $sheet->getStyle($cell)->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'A7F3D0']],
                        'font' => ['bold' => true, 'color' => ['rgb' => '065F46']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }
            }
            $sheet->setCellValue("O{$row}", (int) $item['total']);
            $sheet->getStyle("A{$row}:O{$row}")->applyFromArray([
                'font' => ['size' => 10],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ]);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("O{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $row++;
        }

        // Monthly totals
        $sheet->setCellValue("A{$row}", '');
        $sheet->setCellValue("B{$row}", 'Monthly total');
        foreach ($totals['by_month'] as $i => $count) {
            $sheet->setCellValue($this->col(3 + $i).$row, $count);
        }
        $sheet->setCellValue("O{$row}", $totals['grand']);
        $sheet->getStyle("A{$row}:O{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']]],
        ]);
        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $row++;

        // Quarter totals
        $sheet->setCellValue("A{$row}", '');
        $sheet->setCellValue("B{$row}", 'Quarter total');
        $sheet->mergeCells("C{$row}:E{$row}");
        $sheet->setCellValue("C{$row}", 'Q1: '.$totals['by_quarter']['q1']);
        $sheet->mergeCells("F{$row}:H{$row}");
        $sheet->setCellValue("F{$row}", 'Q2: '.$totals['by_quarter']['q2']);
        $sheet->mergeCells("I{$row}:K{$row}");
        $sheet->setCellValue("I{$row}", 'Q3: '.$totals['by_quarter']['q3']);
        $sheet->mergeCells("L{$row}:N{$row}");
        $sheet->setCellValue("L{$row}", 'Q4: '.$totals['by_quarter']['q4']);
        $sheet->setCellValue("O{$row}", $totals['grand']);
        $sheet->getStyle("A{$row}:O{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']]],
        ]);
        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(42);
        for ($i = 3; $i <= 14; $i++) {
            $sheet->getColumnDimension($this->col($i))->setWidth(10);
        }
        $sheet->getColumnDimension('O')->setWidth(8);
        $sheet->freezePane('C6');
    }

    /**
     * Excel Shakha sheet: # | Code | Area (merged) | Branch | 12 months | Total
     *
     * @param  list<array{index:int,key:string,label:string,year:int,month:int}>  $months
     * @param  Collection<int, array{area_id:mixed,area:?string,division:?string,rows:Collection}>  $groups
     * @param  array{by_month: array<int,int>, grand: int}  $totals
     */
    protected function writeShakhaSheet(Worksheet $sheet, string $fy, array $months, Collection $groups, array $totals): void
    {
        $fyParts = explode('-', $fy);
        $startYear = substr($fyParts[0] ?? '2026', -2);
        $endYear = substr($fyParts[1] ?? '2027', -2);
        $lastCol = 'Q';

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'Dushtha Shasthya Kendra (DSK)');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '0F172A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', "Monitoring & Audit Work Plan — FY {$fy}");
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '334155']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', 'Branch Office (Shakha)');
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->setCellValue('A4', '');
        $sheet->setCellValue('B4', '');
        $sheet->setCellValue('C4', '');
        $sheet->setCellValue('D4', '');
        foreach ($totals['by_month'] as $i => $count) {
            $sheet->setCellValue($this->col(5 + $i).'4', $count);
        }
        $sheet->setCellValue('Q4', $totals['grand']);
        $sheet->getStyle('A4:Q4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->setCellValue('A5', '#');
        $sheet->setCellValue('B5', 'Code');
        $sheet->setCellValue('C5', 'Area Name');
        $sheet->setCellValue('D5', 'Branch Name');
        $sheet->mergeCells('E5:G5');
        $sheet->setCellValue('E5', '1st Quarter');
        $sheet->mergeCells('H5:J5');
        $sheet->setCellValue('H5', '2nd Quarter');
        $sheet->mergeCells('K5:M5');
        $sheet->setCellValue('K5', '3rd Quarter');
        $sheet->mergeCells('N5:P5');
        $sheet->setCellValue('N5', '4th Quarter');
        $sheet->setCellValue('Q5', 'Total');
        $sheet->mergeCells('A5:A6');
        $sheet->mergeCells('B5:B6');
        $sheet->mergeCells('C5:C6');
        $sheet->mergeCells('D5:D6');
        $sheet->mergeCells('Q5:Q6');
        $sheet->getStyle('A5:Q5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FDE047']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '64748B']]],
        ]);

        foreach ($months as $i => $month) {
            $shortYear = $month['index'] <= 5 ? $startYear : $endYear;
            $monthName = match ($month['month']) {
                7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
                default => $month['label'],
            };
            $sheet->setCellValue($this->col(5 + $i).'6', "{$monthName}-{$shortYear}");
        }
        $sheet->getStyle('E6:P6')->applyFromArray([
            'font' => ['bold' => true, 'size' => 8],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '64748B']]],
        ]);
        $sheet->getStyle('A5:D6')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '64748B']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
        ]);
        $sheet->getStyle('Q5:Q6')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '64748B']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $row = 7;
        foreach ($groups as $group) {
            $rows = $group['rows'];
            $count = $rows->count();
            if ($count === 0) {
                continue;
            }

            $groupStart = $row;
            foreach ($rows as $index => $item) {
                $sheet->setCellValue("A{$row}", $item['sl'] ?? '');
                $sheet->setCellValue("B{$row}", $item['code'] ?: '—');
                if ($index === 0) {
                    $sheet->setCellValue("C{$row}", $group['area'] ?: '—');
                }
                $sheet->setCellValue("D{$row}", $item['name'] ?? '');

                foreach ($item['months'] as $monthIndex => $active) {
                    $cell = $this->col(5 + (int) $monthIndex).$row;
                    if ($active) {
                        $sheet->setCellValue($cell, 1);
                        $sheet->getStyle($cell)->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BBF7D0']],
                            'font' => ['bold' => true, 'color' => ['rgb' => '14532D']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    } else {
                        $sheet->setCellValue($cell, '-');
                        $sheet->getStyle($cell)->applyFromArray([
                            'font' => ['color' => ['rgb' => '94A3B8']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    }
                }

                $sheet->setCellValue("Q{$row}", (int) ($item['total'] ?? 0));
                $sheet->getStyle("A{$row}:Q{$row}")->applyFromArray([
                    'font' => ['size' => 9],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '64748B']]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("Q{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $row++;
            }

            $groupEnd = $row - 1;
            if ($groupEnd > $groupStart) {
                $sheet->mergeCells("C{$groupStart}:C{$groupEnd}");
            }
            $sheet->getStyle("C{$groupStart}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 9],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
            ]);
        }

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(10);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(22);
        for ($i = 5; $i <= 16; $i++) {
            $sheet->getColumnDimension($this->col($i))->setWidth(7);
        }
        $sheet->getColumnDimension('Q')->setWidth(8);
        $sheet->freezePane('E7');
    }

    /**
     * Excel Area Office sheet: # | Area Name | 12 months (4 quarters) | Total
     *
     * @param  list<array{index:int,key:string,label:string,year:int,month:int}>  $months
     * @param  Collection<int, array<string, mixed>>  $rows
     * @param  array{by_month: array<int,int>, by_quarter: array<string,int>, grand: int}  $totals
     */
    protected function writeAreaSheet(Worksheet $sheet, string $fy, array $months, Collection $rows, array $totals): void
    {
        $fyParts = explode('-', $fy);
        $startYear = substr($fyParts[0] ?? '2026', -2);
        $endYear = substr($fyParts[1] ?? '2027', -2);
        $lastCol = 'O';

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'Dushtha Shasthya Kendra (DSK)');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '0F172A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', "Monitoring & Audit Work Plan — FY {$fy}");
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '334155']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', 'Area Office');
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->setCellValue('A4', '');
        $sheet->setCellValue('B4', '');
        foreach ($totals['by_month'] as $i => $count) {
            $sheet->setCellValue($this->col(3 + $i).'4', $count);
        }
        $sheet->setCellValue('O4', $totals['grand']);
        $sheet->getStyle('A4:O4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->setCellValue('A5', '#');
        $sheet->setCellValue('B5', 'Area Name');
        $sheet->mergeCells('C5:E5');
        $sheet->setCellValue('C5', '1st Quarter');
        $sheet->mergeCells('F5:H5');
        $sheet->setCellValue('F5', '2nd Quarter');
        $sheet->mergeCells('I5:K5');
        $sheet->setCellValue('I5', '3rd Quarter');
        $sheet->mergeCells('L5:N5');
        $sheet->setCellValue('L5', '4th Quarter');
        $sheet->setCellValue('O5', 'Total');
        $sheet->mergeCells('A5:A6');
        $sheet->mergeCells('B5:B6');
        $sheet->mergeCells('O5:O6');
        $sheet->getStyle('A5:O5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FDE047']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '64748B']]],
        ]);

        foreach ($months as $i => $month) {
            $shortYear = $month['index'] <= 5 ? $startYear : $endYear;
            $monthName = match ($month['month']) {
                7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
                default => $month['label'],
            };
            $sheet->setCellValue($this->col(3 + $i).'6', "{$monthName}-{$shortYear}");
        }
        $sheet->getStyle('C6:N6')->applyFromArray([
            'font' => ['bold' => true, 'size' => 8],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '64748B']]],
        ]);
        $sheet->getStyle('A5:B6')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '64748B']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('O5:O6')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '64748B']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $row = 7;
        foreach ($rows as $item) {
            $sheet->setCellValue("A{$row}", $item['sl']);
            $sheet->setCellValue("B{$row}", $item['name']);
            foreach ($item['months'] as $monthIndex => $active) {
                $cell = $this->col(3 + (int) $monthIndex).$row;
                if ($active) {
                    $sheet->setCellValue($cell, 1);
                    $sheet->getStyle($cell)->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BBF7D0']],
                        'font' => ['bold' => true, 'color' => ['rgb' => '14532D']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                } else {
                    $sheet->setCellValue($cell, '-');
                    $sheet->getStyle($cell)->applyFromArray([
                        'font' => ['color' => ['rgb' => '94A3B8']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }
            }
            $sheet->setCellValue("O{$row}", (int) $item['total']);
            $sheet->getStyle("A{$row}:O{$row}")->applyFromArray([
                'font' => ['size' => 10],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '64748B']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("O{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(26);
        for ($i = 3; $i <= 14; $i++) {
            $sheet->getColumnDimension($this->col($i))->setWidth(8);
        }
        $sheet->getColumnDimension('O')->setWidth(8);
        $sheet->freezePane('C7');
    }

    /**
     * Excel PKSF & Maternity: # | Project | Location | 12 months | Total
     *
     * @param  list<array{index:int,key:string,label:string,year:int,month:int}>  $months
     * @param  Collection<int, array<string, mixed>>  $rows
     * @param  array{by_month: array<int,int>, by_quarter: array<string,int>, grand: int}  $totals
     */
    protected function writePksfSheet(Worksheet $sheet, string $fy, array $months, Collection $rows, array $totals): void
    {
        $fyParts = explode('-', $fy);
        $startYear = substr($fyParts[0] ?? '2026', -2);
        $endYear = substr($fyParts[1] ?? '2027', -2);
        $lastCol = 'P';

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'Dushtha Shasthya Kendra (DSK)');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '0F172A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', "Monitoring & Audit Work Plan — FY {$fy}");
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '334155']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', 'PKSF Projects and DSK Hospital & Maternity');
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->setCellValue('A4', '');
        $sheet->setCellValue('B4', '');
        $sheet->setCellValue('C4', '');
        foreach ($totals['by_month'] as $i => $count) {
            $sheet->setCellValue($this->col(4 + $i).'4', $count);
        }
        $sheet->setCellValue('P4', $totals['grand']);
        $sheet->getStyle('A4:P4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->setCellValue('A5', '#');
        $sheet->setCellValue('B5', 'Project Name');
        $sheet->setCellValue('C5', 'Project Location');
        $sheet->mergeCells('D5:F5');
        $sheet->setCellValue('D5', '1st Quarter');
        $sheet->mergeCells('G5:I5');
        $sheet->setCellValue('G5', '2nd Quarter');
        $sheet->mergeCells('J5:L5');
        $sheet->setCellValue('J5', '3rd Quarter');
        $sheet->mergeCells('M5:O5');
        $sheet->setCellValue('M5', '4th Quarter');
        $sheet->setCellValue('P5', 'Total');
        $sheet->mergeCells('A5:A6');
        $sheet->mergeCells('B5:B6');
        $sheet->mergeCells('C5:C6');
        $sheet->mergeCells('P5:P6');
        $sheet->getStyle('A5:P5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FDE047']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '64748B']]],
        ]);

        foreach ($months as $i => $month) {
            $shortYear = $month['index'] <= 5 ? $startYear : $endYear;
            $monthName = match ($month['month']) {
                7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
                default => $month['label'],
            };
            $sheet->setCellValue($this->col(4 + $i).'6', "{$monthName}-{$shortYear}");
        }
        $sheet->getStyle('D6:O6')->applyFromArray([
            'font' => ['bold' => true, 'size' => 8],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '64748B']]],
        ]);
        $sheet->getStyle('A5:C6')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '64748B']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
        ]);
        $sheet->getStyle('P5:P6')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '64748B']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $row = 7;
        foreach ($rows as $item) {
            $sheet->setCellValue("A{$row}", $item['sl']);
            $sheet->setCellValue("B{$row}", $item['project']);
            $sheet->setCellValue("C{$row}", $item['location']);
            foreach ($item['months'] as $monthIndex => $active) {
                $cell = $this->col(4 + (int) $monthIndex).$row;
                if ($active) {
                    $sheet->setCellValue($cell, 1);
                    $sheet->getStyle($cell)->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BBF7D0']],
                        'font' => ['bold' => true, 'color' => ['rgb' => '14532D']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                } else {
                    $sheet->setCellValue($cell, '-');
                    $sheet->getStyle($cell)->applyFromArray([
                        'font' => ['color' => ['rgb' => '94A3B8']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }
            }
            $sheet->setCellValue("P{$row}", (int) $item['total']);
            $sheet->getStyle("A{$row}:P{$row}")->applyFromArray([
                'font' => ['size' => 10],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '64748B']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("P{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $row++;
        }

        $sheet->setCellValue("A{$row}", '');
        $sheet->mergeCells("B{$row}:C{$row}");
        $sheet->setCellValue("B{$row}", 'Total');
        foreach ($totals['by_month'] as $i => $count) {
            $sheet->setCellValue($this->col(4 + $i).$row, $count);
        }
        $sheet->setCellValue("P{$row}", $totals['grand']);
        $sheet->getStyle("A{$row}:P{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFEDD5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '64748B']]],
        ]);
        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(24);
        $sheet->getColumnDimension('C')->setWidth(24);
        for ($i = 4; $i <= 15; $i++) {
            $sheet->getColumnDimension($this->col($i))->setWidth(8);
        }
        $sheet->getColumnDimension('P')->setWidth(8);
        $sheet->freezePane('D7');
    }

    /**
     * @param  list<array{index:int,key:string,label:string,year:int,month:int}>  $months
     * @param  Collection<int, array<string, mixed>>  $groups
     */
    protected function writeSheet(Worksheet $sheet, string $title, string $fy, array $months, Collection $groups): void
    {
        $fyParts = explode('-', $fy);
        $startYear = substr($fyParts[0] ?? '2026', -2);
        $endYear = substr($fyParts[1] ?? '2027', -2);

        // A=# B=Project C=Location | D..O = 12 months | P = Total
        $monthStartCol = 4;
        $monthCount = count($months);
        $totalColIndex = 3 + $monthCount + 1; // 16 when 12 months
        $lastMonthColIndex = $monthStartCol + $monthCount - 1; // 15
        $lastCol = $this->col($totalColIndex);
        $lastMonthCol = $this->col($lastMonthColIndex);

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'Dushtha Shasthya Kendra (DSK)');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '0F172A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', "{$title}: July'{$startYear} to June'{$endYear}");
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '334155']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $headers = ['#', 'Name of the Projects / Donor', 'Location of the Projects'];
        foreach ($months as $month) {
            $shortYear = ((int) $month['index']) <= 5 ? $startYear : $endYear;
            $monthName = match ((int) $month['month']) {
                7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
                default => $month['label'],
            };
            $headers[] = "{$monthName}'{$shortYear}";
        }
        $headers[] = 'Total';

        $headerRow = 4;
        foreach ($headers as $i => $header) {
            $sheet->setCellValue($this->col($i + 1).$headerRow, $header);
        }

        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '1E293B']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D1FAE5'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '94A3B8'],
                ],
            ],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(32);

        $row = $headerRow + 1;
        foreach ($groups as $group) {
            $locations = $group['rows'];
            if ($locations->isEmpty()) {
                $sheet->setCellValue("A{$row}", $group['sl']);
                $projectLabel = $group['project'];
                if (! empty($group['donor'])) {
                    $projectLabel .= "\n".$group['donor'];
                }
                $sheet->setCellValue("B{$row}", $projectLabel);
                $sheet->setCellValue("C{$row}", '—');
                $sheet->setCellValue($this->col($totalColIndex).$row, 0);
                $this->styleDataRow($sheet, $row, $lastCol, $lastMonthCol, false);
                $row++;

                continue;
            }

            $startRow = $row;
            $endRow = $row + $locations->count() - 1;

            foreach ($locations as $index => $location) {
                if ($index === 0) {
                    $sheet->setCellValue("A{$row}", $group['sl']);
                    $projectLabel = $group['project'];
                    if (! empty($group['donor'])) {
                        $projectLabel .= "\n".$group['donor'];
                    }
                    $sheet->setCellValue("B{$row}", $projectLabel);
                }

                $sheet->setCellValue("C{$row}", $location['location']);

                $monthsList = array_values($location['months']);
                foreach ($monthsList as $monthIndex => $active) {
                    $cell = $this->col($monthStartCol + (int) $monthIndex).$row;
                    if ($active) {
                        $sheet->setCellValue($cell, 1);
                        $sheet->getStyle($cell)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'A7F3D0'],
                            ],
                            'font' => ['bold' => true, 'color' => ['rgb' => '065F46']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    } else {
                        $sheet->setCellValue($cell, '');
                    }
                }

                $rowTotal = (int) ($location['total'] ?? count(array_filter($monthsList)));
                $sheet->setCellValue($this->col($totalColIndex).$row, $rowTotal);
                $this->styleDataRow($sheet, $row, $lastCol, $lastMonthCol, true);
                $row++;
            }

            if ($endRow > $startRow) {
                $sheet->mergeCells("A{$startRow}:A{$endRow}");
                $sheet->mergeCells("B{$startRow}:B{$endRow}");
                $sheet->getStyle("A{$startRow}:B{$endRow}")->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
            }
        }

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(36);
        $sheet->getColumnDimension('C')->setWidth(28);
        for ($i = $monthStartCol; $i <= $lastMonthColIndex; $i++) {
            $sheet->getColumnDimension($this->col($i))->setWidth(11);
        }
        $sheet->getColumnDimension($lastCol)->setWidth(10);

        $sheet->freezePane('D5');
        if ($row > $headerRow) {
            $sheet->getStyle("A{$headerRow}:{$lastCol}".($row - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '94A3B8'],
                    ],
                ],
            ]);
        }
    }

    protected function styleDataRow(Worksheet $sheet, int $row, string $lastCol, string $lastMonthCol, bool $centerMonths): void
    {
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'font' => ['size' => 10],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ]);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("{$lastCol}{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        if ($centerMonths) {
            $sheet->getStyle("D{$row}:{$lastMonthCol}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
    }

    protected function col(int $index): string
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index);
    }
}
