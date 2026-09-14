<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Shakha;
use App\Models\ShakhaAnnualKpi;
use App\Models\User;
use App\Services\RiskAssessmentService;
use App\Support\FinancialYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShakhaRiskReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('risk.manage');
        Permission::findOrCreate('shakhas.view_all');
        $role = Role::findOrCreate('audit_manager');
        $role->givePermissionTo(['risk.manage', 'shakhas.view_all']);
    }

    public function test_empty_kpi_row_does_not_unlock_risk_on_shakha_list(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('audit_manager');

        $area = Area::query()->create(['name' => 'Ready Area', 'division' => 'Dhaka', 'status' => 'active']);
        $ready = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Ready Branch', 'code' => 'RDY-1', 'status' => 'active']);
        $stub = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Stub Branch', 'code' => 'STB-1', 'status' => 'active']);
        $fy = FinancialYear::current()->label;

        ShakhaAnnualKpi::query()->create([
            'shakha_id' => $ready->id,
            'fy_label' => $fy,
            'total_members' => 100,
            'loan_outstanding' => 500000,
            'recoverable' => 200000,
            'current_recovery' => 180000,
        ]);
        ShakhaAnnualKpi::query()->create([
            'shakha_id' => $stub->id,
            'fy_label' => $fy,
            'total_members' => 0,
            'loan_outstanding' => 0,
            'recoverable' => 0,
            'current_recovery' => 0,
        ]);

        $this->actingAs($user)
            ->get(route('shakhas.index'))
            ->assertOk()
            ->assertSee('Ready Branch')
            ->assertSee('Stub Branch');

        $readyKpi = ShakhaAnnualKpi::query()->where('shakha_id', $ready->id)->first();
        $stubKpi = ShakhaAnnualKpi::query()->where('shakha_id', $stub->id)->first();
        $this->assertTrue($readyKpi->isReadyForRisk());
        $this->assertFalse($stubKpi->isReadyForRisk());
    }

    public function test_risk_score_comes_from_kpi_laws_not_placeholder(): void
    {
        $area = Area::query()->create(['name' => 'Calc Area', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Calc Branch', 'code' => 'CLC-1', 'status' => 'active']);
        $fy = FinancialYear::current()->label;

        ShakhaAnnualKpi::query()->create([
            'shakha_id' => $shakha->id,
            'fy_label' => $fy,
            'total_members' => 200,
            'total_borrowers' => 120,
            'loan_outstanding' => 1000000,
            'recoverable' => 500000,
            'current_recovery' => 490000, // OTR 98% → 0 pts
            'total_od_taka' => 10000, // 1% → low DR/NPLR
            'fy_loan_recovery' => 400000,
            'surplus_deficit_fy' => 25000,
        ]);

        $assessment = app(RiskAssessmentService::class)->calculateRiskScore(
            $shakha,
            (int) now('Asia/Dhaka')->month,
            (int) now('Asia/Dhaka')->year,
            [
                'total_income' => 600000,
                'total_expenditure' => 400000, // OSS 1.5 → 0
                'write_off_principal_amount' => 0,
                'savings_adjustment_amount' => 0,
                'distance_from_area_office_km' => false,
                'has_both_bm_and_abm' => true,
                'special_audit_last_two_years' => true,
            ]
        );

        $this->assertLessThanOrEqual(25, $assessment->total_weighted_score);
        $this->assertSame('Low Risk', $assessment->risk_category);
    }

    public function test_incomplete_kpi_cannot_be_scored(): void
    {
        $area = Area::query()->create(['name' => 'Bad Area', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Bad Branch', 'code' => 'BAD-1', 'status' => 'active']);
        $fy = FinancialYear::current()->label;
        ShakhaAnnualKpi::query()->create([
            'shakha_id' => $shakha->id,
            'fy_label' => $fy,
            'total_members' => 0,
            'loan_outstanding' => 0,
            'recoverable' => 0,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('incomplete for risk scoring');

        app(RiskAssessmentService::class)->calculateRiskScore(
            $shakha,
            (int) now('Asia/Dhaka')->month,
            (int) now('Asia/Dhaka')->year,
            [
                'total_income' => 1,
                'total_expenditure' => 1,
                'write_off_principal_amount' => 0,
                'savings_adjustment_amount' => 0,
                'distance_from_area_office_km' => false,
                'has_both_bm_and_abm' => true,
                'special_audit_last_two_years' => true,
            ]
        );
    }
}
