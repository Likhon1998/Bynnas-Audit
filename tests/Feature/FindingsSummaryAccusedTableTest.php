<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AuditFinding;
use App\Models\AuditIndicator;
use App\Models\Shakha;
use App\Models\ShakhaEmployee;
use App\Services\AuditSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindingsSummaryAccusedTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_includes_tabular_shakha_accused_kormi_rows(): void
    {
        $area = Area::query()->create(['name' => 'Metro', 'division' => 'Dhaka', 'status' => 'active']);
        $shakhaA = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Branch Alpha', 'code' => 'A-1', 'status' => 'active']);
        $shakhaB = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Branch Beta', 'code' => 'B-1', 'status' => 'active']);
        $indicator = AuditIndicator::query()->create([
            'category' => 'Financial',
            'sub_category' => null,
            'indicator_code' => 'SUM-1',
            'title' => 'VAT issue',
            'risk_rating' => 'Major',
            'is_active' => true,
        ]);

        ShakhaEmployee::query()->create([
            'shakha_id' => $shakhaA->id,
            'employee_code' => 'KRM-A-01',
            'name' => 'Karim Mia',
            'designation' => 'FO',
            'status' => 'active',
        ]);
        ShakhaEmployee::query()->create([
            'shakha_id' => $shakhaA->id,
            'employee_code' => 'KRM-A-02',
            'name' => 'Salma Begum',
            'designation' => 'FO',
            'status' => 'active',
        ]);
        ShakhaEmployee::query()->create([
            'shakha_id' => $shakhaB->id,
            'employee_code' => 'KRM-B-01',
            'name' => 'Rafiq Islam',
            'designation' => 'FO',
            'status' => 'active',
        ]);

        AuditFinding::query()->create([
            'shakha_id' => $shakhaA->id,
            'audit_indicator_id' => $indicator->id,
            'audit_month' => 9,
            'audit_year' => 2026,
            'amount' => 1000,
            'responsible_staff_name' => 'Karim Mia, Salma Begum',
        ]);
        AuditFinding::query()->create([
            'shakha_id' => $shakhaB->id,
            'audit_indicator_id' => $indicator->id,
            'audit_month' => 9,
            'audit_year' => 2026,
            'amount' => 500,
            'responsible_staff_name' => 'Rafiq Islam (KRM-B-01)',
        ]);

        $groups = app(AuditSummaryService::class)->getMonthUnderheadingSummary(9, 2026);
        $this->assertNotEmpty($groups);
        $row = $groups[0]['rows'][0];
        $this->assertSame(2, $row['branch_count']);
        $this->assertCount(2, $row['branch_rows']);
        $this->assertSame('Branch Alpha (A-1)', $row['branch_rows'][0]['label']);
        $this->assertSame('Karim Mia (KRM-A-01), Salma Begum (KRM-A-02)', $row['branch_rows'][0]['accused_kormi']);
        $this->assertSame('Branch Beta (B-1)', $row['branch_rows'][1]['label']);
        $this->assertSame('Rafiq Islam (KRM-B-01)', $row['branch_rows'][1]['accused_kormi']);
    }
}
