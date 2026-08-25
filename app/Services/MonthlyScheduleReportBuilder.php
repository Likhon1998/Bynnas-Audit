<?php

namespace App\Services;

use App\Models\AuditPlan;
use App\Models\MonthlyWorkItem;
use App\Support\FinancialYear;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MonthlyScheduleReportBuilder
{
    public function __construct(
        private MonthlyWorklistService $worklist,
    ) {}

    /**
     * All month work items (assigned + unassigned) for the official schedule printout.
     *
     * @return array{
     *   plan: AuditPlan,
     *   fy: FinancialYear,
     *   monthIndex: int,
     *   monthLabel: string,
     *   monthLabelBn: string,
     *   fyLabelBn: string,
     *   rows: list<array<string, mixed>>,
     *   groups: list<array{purpose:string,purpose_bn:string,count:int,start:int}>
     * }
     */
    public function build(AuditPlan $plan, int $monthIndex): array
    {
        $fy = FinancialYear::fromLabel($plan->fy_label);
        $monthMeta = $fy->months()[$monthIndex];
        $monthLabel = $monthMeta['label'].'-'.$monthMeta['year'];
        $monthLabelBn = $this->monthBn((int) $monthMeta['month']).'-'.$monthMeta['year'];

        $items = $this->worklist->workItemsForMonth($plan, $monthIndex)
            ->sortBy(fn (MonthlyWorkItem $item) => [
                $this->purposeSortKey($item),
                $item->entity_label,
            ])
            ->values();

        $rows = [];
        foreach ($items as $i => $item) {
            $assignment = $item->assignment;
            $lastUpto = $assignment?->last_audit_upto
                ?? $this->worklist->computeLastAuditUpto($item);
            $purpose = $assignment?->purpose
                ?: ($item->activityType?->name ?: $this->categoryPurpose($item->category));

            $rows[] = [
                'sl' => str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                'visitors' => $assignment?->visitorNames("\n") ?: '—',
                'visitors_inline' => $assignment?->visitorNames(', ') ?: '—',
                'last_audit_upto' => $lastUpto ? $lastUpto->format('F-Y') : '—',
                'last_audit_upto_bn' => $lastUpto ? $this->monthBn((int) $lastUpto->month).'-'.$lastUpto->year : '—',
                'entity' => $item->entity_label,
                'visit_dates' => $assignment?->visitDateRangeLabel() ?: 'Not allocated',
                'days' => $assignment?->duration_days
                    ? str_pad((string) $assignment->duration_days, 2, '0', STR_PAD_LEFT).' days'
                    : '—',
                'purpose' => $purpose,
                'purpose_bn' => $this->purposeBn($purpose),
                'status' => $item->status,
                'is_special' => $item->isSpecial(),
            ];
        }

        return [
            'plan' => $plan,
            'fy' => $fy,
            'monthIndex' => $monthIndex,
            'monthLabel' => $monthLabel,
            'monthLabelBn' => $monthLabelBn,
            'fyLabelBn' => str_replace('-', '-', $plan->fy_label),
            'rows' => $rows,
            'groups' => $this->purposeGroups($rows),
        ];
    }

    public function downloadExcel(AuditPlan $plan, int $monthIndex): StreamedResponse
    {
        $data = $this->build($plan, $monthIndex);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Monthly Schedule');

        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'Dushtha Shasthya Kendra (DSK)');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', 'Monthly Schedule for Monitoring and Audit');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:G3');
        $sheet->setCellValue('A3', 'Financial Year '.$plan->fy_label.'  |  Month: '.$data['monthLabel']);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = ['SL', 'Visitor Name', 'Last Audit Upto', 'Branch / Entity', 'Visit Date & Month', 'Days', 'Remarks'];
        foreach ($headers as $i => $header) {
            $col = chr(ord('A') + $i);
            $sheet->setCellValue($col.'5', $header);
        }
        $sheet->getStyle('A5:G5')->getFont()->setBold(true);
        $sheet->getStyle('A5:G5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowNum = 6;
        foreach ($data['rows'] as $row) {
            $sheet->setCellValue("A{$rowNum}", $row['sl']);
            $sheet->setCellValue("B{$rowNum}", $row['visitors_inline']);
            $sheet->setCellValue("C{$rowNum}", $row['last_audit_upto']);
            $sheet->setCellValue("D{$rowNum}", $row['entity']);
            $sheet->setCellValue("E{$rowNum}", $row['visit_dates']);
            $sheet->setCellValue("F{$rowNum}", $row['days']);
            $sheet->setCellValue("G{$rowNum}", $row['purpose']);
            $rowNum++;
        }

        $last = max(5, $rowNum - 1);
        $sheet->getStyle("A5:G{$last}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'monthly-schedule-'.$plan->fy_label.'-'.$data['monthLabel'].'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Word-openable HTML document (.doc).
     */
    public function downloadDoc(AuditPlan $plan, int $monthIndex): \Illuminate\Http\Response
    {
        $data = $this->build($plan, $monthIndex);
        $html = view('monthly-visits.print-schedule', $data + ['forDoc' => true])->render();

        $filename = 'monthly-schedule-'.$plan->fy_label.'-'.$data['monthLabel'].'.doc';

        return response($html, 200, [
            'Content-Type' => 'application/msword; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array{purpose:string,purpose_bn:string,count:int,start:int}>
     */
    protected function purposeGroups(array $rows): array
    {
        $groups = [];
        $index = 0;
        while ($index < count($rows)) {
            $purpose = $rows[$index]['purpose'];
            $count = 1;
            while ($index + $count < count($rows) && $rows[$index + $count]['purpose'] === $purpose) {
                $count++;
            }
            $groups[] = [
                'purpose' => $purpose,
                'purpose_bn' => $rows[$index]['purpose_bn'],
                'count' => $count,
                'start' => $index,
            ];
            $index += $count;
        }

        return $groups;
    }

    protected function purposeSortKey(MonthlyWorkItem $item): string
    {
        $purpose = $item->assignment?->purpose
            ?: ($item->activityType?->name ?: $this->categoryPurpose($item->category));

        return match (true) {
            str_contains(strtolower($purpose), 'joint') => '3-'.$purpose,
            str_contains(strtolower($purpose), 'monitor') => '2-'.$purpose,
            default => '1-'.$purpose,
        };
    }

    protected function categoryPurpose(string $category): string
    {
        return match ($category) {
            'shakha_audit' => 'Audit',
            'project_monitoring', 'monitoring' => 'Monitoring',
            'project_audit' => 'Project Audit',
            'pksf' => 'PKSF & Maternity',
            'area' => 'Area Office',
            'hq' => 'HQ Concern',
            default => str_replace('_', ' ', ucfirst($category)),
        };
    }

    protected function purposeBn(string $purpose): string
    {
        $p = strtolower($purpose);

        return match (true) {
            str_contains($p, 'joint') => 'যৌথ ফরমেট',
            str_contains($p, 'monitor') && str_contains($p, 'audit') => 'পরিবীক্ষণ ও নিরীক্ষা',
            str_contains($p, 'monitor') => 'পরিবীক্ষণ',
            str_contains($p, 'audit') => 'নিরীক্ষা',
            str_contains($p, 'pksf') => 'পিকেএসএফ ও মেটারনিটি',
            str_contains($p, 'area') => 'এলাকা অফিস',
            str_contains($p, 'hq') => 'প্রধান কার্যালয়',
            default => $purpose,
        };
    }

    protected function monthBn(int $month): string
    {
        return match ($month) {
            1 => 'জানুয়ারি',
            2 => 'ফেব্রুয়ারি',
            3 => 'মার্চ',
            4 => 'এপ্রিল',
            5 => 'মে',
            6 => 'জুন',
            7 => 'জুলাই',
            8 => 'আগস্ট',
            9 => 'সেপ্টেম্বর',
            10 => 'অক্টোবর',
            11 => 'নভেম্বর',
            12 => 'ডিসেম্বর',
            default => (string) $month,
        };
    }
}
