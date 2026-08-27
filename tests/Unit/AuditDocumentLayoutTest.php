<?php

namespace Tests\Unit;

use App\Support\AuditDocumentLayout;
use PHPUnit\Framework\TestCase;

class AuditDocumentLayoutTest extends TestCase
{
    public function test_a4_content_area_uses_iso_margins(): void
    {
        $this->assertSame(170.0, AuditDocumentLayout::contentWidthMm());
        $this->assertSame(267.0, AuditDocumentLayout::contentHeightMm());
        $this->assertSame('15mm 20mm 15mm 20mm', AuditDocumentLayout::pdfPageMarginCss());
    }

    public function test_staff_column_widths_sum_to_one_hundred(): void
    {
        $columns = [
            'কর্মকর্তার নাম',
            'পরিচিতি নং',
            'পদবী',
            'সংস্থায় যোগদানের তারিখ',
            'শাখায় যোগদানের তারিখ',
        ];

        $widths = AuditDocumentLayout::staffColumnWidths($columns);

        $this->assertCount(6, $widths);
        $this->assertEqualsWithDelta(100.0, array_sum($widths), 0.01);
        $this->assertGreaterThan($widths[2], $widths[1]); // name wider than id
    }

    public function test_toc_and_glance_widths_sum_to_one_hundred(): void
    {
        $this->assertEqualsWithDelta(100.0, array_sum(AuditDocumentLayout::tocColumnWidths()), 0.01);
        $this->assertEqualsWithDelta(100.0, array_sum(AuditDocumentLayout::glanceColumnWidths()), 0.01);
        $this->assertEqualsWithDelta(100.0, array_sum(AuditDocumentLayout::findingColumnWidths()), 0.01);
    }

    public function test_staff_alignment_follows_content_type(): void
    {
        $this->assertSame('left', AuditDocumentLayout::staffColumnAlign('কর্মকর্তার নাম'));
        $this->assertSame('center', AuditDocumentLayout::staffColumnAlign('পরিচিতি নং'));
        $this->assertSame('center', AuditDocumentLayout::staffColumnAlign('সংস্থায় যোগদানের তারিখ'));
        $this->assertSame('right', AuditDocumentLayout::staffColumnAlign('Total Amount'));
    }
}
