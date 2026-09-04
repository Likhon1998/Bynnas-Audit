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
            'financial_detail',
            'financial_page6',
            'financial_page7',
            'financial_page8',
            'financial_page9',
            'financial_page10',
            'financial_page11',
            'financial_page12',
            'financial_page13',
            'financial_page14',
            'financial_page15',
            'financial_page16',
            'financial_page17',
            'financial_page18',
            'financial_page19',
            'financial_page20',
            'financial_page21',
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
            'financial_detail',
            'financial_page6',
            'financial_page7',
            'financial_page8',
            'financial_page9',
            'financial_page10',
            'financial_page11',
            'financial_page12',
            'financial_page13',
            'financial_page14',
            'financial_page15',
            'financial_page16',
            'financial_page17',
            'financial_page18',
            'financial_page19',
            'financial_page20',
            'financial_page21',
        ], array_column($sheets, 'type'));
        $this->assertCount(1, $sheets[1]['rows']);
        $this->assertSame('১২', $sheets[1]['rows'][0]['page_no']);
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
            ['type' => 'item', 'serial' => '২.২', 'finding' => 'Depreciation mismatch', 'page_no' => ''],
            ['type' => 'item', 'serial' => '৩.১', 'finding' => 'Stock', 'page_no' => ''],
            ['type' => 'item', 'serial' => '৪.১', 'finding' => 'Savings deposit', 'page_no' => ''],
            ['type' => 'item', 'serial' => '৪.২', 'finding' => 'Passbook savings', 'page_no' => ''],
            ['type' => 'item', 'serial' => '৪.৩', 'finding' => 'Passbook installment', 'page_no' => ''],
            ['type' => 'item', 'serial' => '৪.৪', 'finding' => 'Sufolon posting', 'page_no' => ''],
            ['type' => 'item', 'serial' => '৪.৫', 'finding' => 'Receivable not shown', 'page_no' => ''],
            ['type' => 'item', 'serial' => '৪.৬', 'finding' => 'Late receivable', 'page_no' => ''],
            ['type' => 'item', 'serial' => '৪.৭', 'finding' => 'Samity visit gaps', 'page_no' => ''],
            ['type' => 'item', 'serial' => '৪.৮', 'finding' => 'Missing passbooks', 'page_no' => ''],
            ['type' => 'item', 'serial' => '৪.৯', 'finding' => 'Savings partial adjust', 'page_no' => ''],
            ['type' => 'item', 'serial' => '৪.১০', 'finding' => 'Dropout savings refund', 'page_no' => ''],
            ['type' => 'item', 'serial' => '৪.১১', 'finding' => 'Savings reconcile mismatch', 'page_no' => ''],
        ];

        $stamped = AuditReportPaginator::stampRows($rows, 4, 3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21);

        $this->assertSame('', $stamped[0]['page_no']);
        $this->assertSame('৪', $stamped[1]['page_no']);
        $this->assertSame('৫', $stamped[2]['page_no']);
        $this->assertSame('৬', $stamped[3]['page_no']);
        $this->assertSame('৭', $stamped[4]['page_no']);
        $this->assertSame('৮', $stamped[5]['page_no']);
        $this->assertSame('৯', $stamped[6]['page_no']);
        $this->assertSame('১০', $stamped[7]['page_no']);
        $this->assertSame('১১', $stamped[8]['page_no']);
        $this->assertSame('১২', $stamped[9]['page_no']);
        $this->assertSame('১৩', $stamped[10]['page_no']);
        $this->assertSame('১৩', $stamped[11]['page_no']);
        $this->assertSame('১৪', $stamped[12]['page_no']);
        $this->assertSame('১৪', $stamped[13]['page_no']);
        $this->assertSame('১৫', $stamped[14]['page_no']);
        $this->assertSame('১৫', $stamped[15]['page_no']);
        $this->assertSame('১৬', $stamped[16]['page_no']);
        $this->assertSame('১৬', $stamped[17]['page_no']);
        $this->assertSame('১৭', $stamped[18]['page_no']);
        $this->assertSame('১৮', $stamped[19]['page_no']);
        $this->assertSame('১৮', $stamped[20]['page_no']);
    }
}
