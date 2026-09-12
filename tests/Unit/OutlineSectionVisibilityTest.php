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
}
