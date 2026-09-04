<?php

namespace Tests\Unit;

use App\Support\AuditReportPaginator;
use PHPUnit\Framework\TestCase;

class AuditReportPaginatorTest extends TestCase
{
    public function test_build_sheets_keeps_full_toc_in_overview_flow(): void
    {
        $rows = array_fill(0, 25, ['type' => 'item', 'serial' => '১.১', 'finding' => 'Test', 'page_no' => '']);

        $sheets = AuditReportPaginator::buildSheets($rows);

        $types = array_column($sheets, 'type');

        $this->assertSame([
            'cover',
            'overview',
            'signatures_classification',
            'financial',
        ], $types);
        $this->assertCount(25, $sheets[1]['rows']);
        $this->assertSame('৪', $sheets[1]['rows'][0]['page_no'] ?? '');
    }

    public function test_short_toc_does_not_add_extra_sheets(): void
    {
        $rows = [
            ['type' => 'item', 'serial' => '৩.১', 'finding' => 'One leftover', 'page_no' => ''],
        ];

        $sheets = AuditReportPaginator::buildSheets($rows);

        $this->assertSame([
            'cover',
            'overview',
            'signatures_classification',
            'financial',
        ], array_column($sheets, 'type'));
        $this->assertCount(1, $sheets[1]['rows']);
        // Without page5–21 sheets, later serials fall back to the financial page.
        $this->assertSame('৪', $sheets[1]['rows'][0]['page_no']);
    }

    public function test_financial_findings_point_to_correct_pages(): void
    {
        $rows = [
            ['type' => 'section', 'serial' => '১.০', 'finding' => 'Accounts', 'page_no' => ''],
            ['type' => 'item', 'serial' => '১.১', 'finding' => 'VAT', 'page_no' => ''],
            ['type' => 'item', 'serial' => '১.৩', 'finding' => 'Deposit delay', 'page_no' => ''],
            ['type' => 'item', 'serial' => '১.৪', 'finding' => 'Rent receipt', 'page_no' => ''],
            ['type' => 'item', 'serial' => '১.৬', 'finding' => 'Budget overspend', 'page_no' => ''],
            ['type' => 'item', 'serial' => '১.৮', 'finding' => 'Cost of fund', 'page_no' => ''],
            ['type' => 'item', 'serial' => '১.৯', 'finding' => 'Excess cash', 'page_no' => ''],
            ['type' => 'item', 'serial' => '২.১', 'finding' => 'Fixed asset', 'page_no' => ''],
            ['type' => 'item', 'serial' => '২.২', 'finding' => 'Depreciation', 'page_no' => ''],
            ['type' => 'item', 'serial' => '৩.১', 'finding' => 'Stock', 'page_no' => ''],
            ['type' => 'item', 'serial' => '৪.১', 'finding' => 'Samity', 'page_no' => ''],
            ['type' => 'item', 'serial' => '৪.৩', 'finding' => 'Passbook', 'page_no' => ''],
            ['type' => 'item', 'serial' => '৪.৫', 'finding' => 'Arrears', 'page_no' => ''],
            ['type' => 'item', 'serial' => '৪.৭', 'finding' => 'Absent', 'page_no' => ''],
            ['type' => 'item', 'serial' => '৪.৯', 'finding' => 'Adjust', 'page_no' => ''],
            ['type' => 'item', 'serial' => '৪.১০', 'finding' => 'Dropout', 'page_no' => ''],
            ['type' => 'section', 'serial' => '৫.০০', 'finding' => 'Compliance', 'page_no' => ''],
            ['type' => 'section', 'serial' => '৬.০০', 'finding' => 'IT', 'page_no' => ''],
            ['type' => 'section', 'serial' => '৭.০০', 'finding' => 'External', 'page_no' => ''],
        ];

        $sheets = AuditReportPaginator::buildSheets($rows);
        $bySerial = [];
        foreach ($sheets[1]['rows'] as $row) {
            if (($row['type'] ?? 'item') === 'section') {
                continue;
            }
            $bySerial[$row['serial']] = $row['page_no'];
        }

        // All findings map to financial page when later sheets are disabled.
        foreach ($bySerial as $pageNo) {
            $this->assertSame('৪', $pageNo);
        }
    }

    public function test_legacy_full_sheet_flags_still_work(): void
    {
        $sheets = AuditReportPaginator::buildSheets([], true, true, true, true, true, true, true, true, true, true, true, true, true, true, true, true, true, true);

        $this->assertContains('financial_detail', array_column($sheets, 'type'));
        $this->assertContains('financial_page21', array_column($sheets, 'type'));
    }
}
