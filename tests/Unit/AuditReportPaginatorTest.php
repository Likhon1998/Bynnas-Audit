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

        $this->assertSame(['cover', 'overview', 'signatures_classification', 'financial'], $types);
        $this->assertCount(25, $sheets[1]['rows']);
        $this->assertSame('৪', $sheets[1]['rows'][0]['page_no'] ?? '');
    }

    public function test_short_toc_does_not_add_extra_sheets(): void
    {
        $rows = [
            ['type' => 'item', 'serial' => '২.১', 'finding' => 'One leftover', 'page_no' => ''],
        ];

        $sheets = AuditReportPaginator::buildSheets($rows);

        $this->assertSame(['cover', 'overview', 'signatures_classification', 'financial'], array_column($sheets, 'type'));
        $this->assertCount(1, $sheets[1]['rows']);
        $this->assertSame('৩', $sheets[1]['rows'][0]['page_no']);
    }

    public function test_financial_findings_point_to_financial_page(): void
    {
        $rows = [
            ['type' => 'section', 'serial' => '১.০', 'finding' => 'Accounts', 'page_no' => ''],
            ['type' => 'item', 'serial' => '১.১', 'finding' => 'VAT', 'page_no' => ''],
            ['type' => 'item', 'serial' => '২.১', 'finding' => 'Asset', 'page_no' => ''],
        ];

        $stamped = AuditReportPaginator::stampRows($rows, 6, 5);

        $this->assertSame('', $stamped[0]['page_no']);
        $this->assertSame('৬', $stamped[1]['page_no']);
        $this->assertSame('৫', $stamped[2]['page_no']);
    }
}
