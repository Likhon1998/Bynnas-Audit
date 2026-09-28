<?php

namespace Tests\Unit;

use App\Livewire\MakeAuditReport;
use ReflectionMethod;
use Tests\TestCase;

class OutlineSectionVisibilityTest extends TestCase
{
    public function test_serial_only_section_title_is_not_treated_as_duplicate(): void
    {
        $component = new MakeAuditReport;
        $method = new ReflectionMethod(MakeAuditReport::class, 'outlineSectionDuplicatesPageLabel');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($component, '৩.০', ''));
        $this->assertFalse($method->invoke($component, '৩.০', '৩.০'));
        $this->assertFalse($method->invoke($component, '৩.০', '৩.০ নতুন বিভাগ'));
        $this->assertTrue($method->invoke($component, '১.০', '১.০ আর্থিক নিরীক্ষা'));
        $this->assertTrue($method->invoke($component, '১.০', '১.০ আর্থিক নিরীক্ষা (Financial Audit)'));
    }

    public function test_retitle_keeps_empty_section_title_empty(): void
    {
        $component = new MakeAuditReport;
        $method = new ReflectionMethod(MakeAuditReport::class, 'retitleWithSerial');
        $method->setAccessible(true);

        $this->assertSame('', $method->invoke($component, '', '২.০', '৩.০'));
        $this->assertSame('৩.০ ঋণ', $method->invoke($component, '২.০ ঋণ', '২.০', '৩.০'));
    }

    public function test_added_template_section_is_numbered_in_outline(): void
    {
        $component = new MakeAuditReport;
        $component->reportBlocks = [
            ['type' => 'section', 'serial' => '৩.০', 'title' => '৩.০ অর্থ ও হিসাব সংক্রান্ত'],
            ['type' => 'section', 'serial' => '৪.০', 'title' => '', 'start_indicator' => true],
            ['type' => 'section', 'serial' => '৫.০', 'title' => 'ঋণ কার্যক্রম', 'start_indicator' => true],
        ];

        $labels = array_column(array_filter(
            $component->outlineNavItems(),
            fn (array $item) => $item['kind'] === 'section' || $item['kind'] === 'indicator'
        ), 'label');

        $this->assertSame(['৩.০ অর্থ ও হিসাব সংক্রান্ত', '৪.০ নতুন বিভাগ', '৫.০ ঋণ কার্যক্রম'], array_values($labels));
    }

    public function test_untitled_finding_is_listed_in_outline(): void
    {
        $component = new MakeAuditReport;
        $component->reportBlocks = [
            ['type' => 'section', 'serial' => '৪.০', 'title' => 'ami'],
            ['type' => 'finding', 'serial' => '৪.১', 'title' => '', 'body' => '', 'rating' => '', 'amount' => ''],
        ];

        $labels = array_column(array_filter(
            $component->outlineNavItems(),
            fn (array $item) => $item['kind'] === 'finding'
        ), 'label');

        $this->assertSame(['৪.১ নতুন শিরোনাম'], array_values($labels));
    }
}
