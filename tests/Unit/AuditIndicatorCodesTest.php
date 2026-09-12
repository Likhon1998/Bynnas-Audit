<?php

namespace Tests\Unit;

use App\Models\AuditIndicator;
use App\Support\AuditIndicatorCodes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditIndicatorCodesTest extends TestCase
{
    use RefreshDatabase;

    public function test_next_custom_report_code_matches_catalog_style(): void
    {
        $first = AuditIndicatorCodes::nextCustomReportCode();
        $this->assertSame('৯০০০-১', $first);

        AuditIndicator::query()->create([
            'category' => 'নিরীক্ষা প্রতিবেদন',
            'sub_category' => null,
            'indicator_code' => $first,
            'title' => 'Demo heading A',
            'risk_rating' => null,
            'is_active' => true,
        ]);

        $second = AuditIndicatorCodes::nextCustomReportCode();
        $this->assertSame('৯০০০-২', $second);
        $this->assertTrue(AuditIndicatorCodes::isCustomReportCode($second));
        $this->assertTrue(AuditIndicatorCodes::isCustomReportCode('রিপোর্ট-260912023147-rg89'));
        $this->assertFalse(AuditIndicatorCodes::isCustomReportCode('১০০০-১'));
    }
}
