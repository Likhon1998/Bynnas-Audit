<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\RiskLaw;
use App\Models\Shakha;
use App\Models\ShakhaAnnualKpi;
use App\Models\User;
use App\Services\RiskAssessmentService;
use App\Support\FinancialYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RiskLawsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('risk.manage');
        $role = Role::findOrCreate('audit_manager');
        $role->givePermissionTo('risk.manage');
    }

    protected function manager(): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('audit_manager');

        return $user;
    }

    public function test_risk_laws_page_lists_scoring_matrix(): void
    {
        $user = $this->manager();

        $this->actingAs($user)
            ->get(route('shakhas.risk.laws'))
            ->assertOk()
            ->assertSee('Risk analysis laws')
            ->assertSee('At a glance')
            ->assertSee('OTR');

        $this->assertGreaterThan(5, RiskLaw::query()->count());
    }

    public function test_changing_otr_law_changes_live_score(): void
    {
        RiskLaw::ensureDefaults();
        $otr = RiskLaw::query()->where('key', 'otr')->firstOrFail();
        $otr->update([
            'bands' => [
                ['op' => 'gte', 'value' => 50, 'points' => 0],
                ['op' => 'default', 'points' => 99],
            ],
        ]);

        $area = Area::query()->create(['name' => 'Risk Area', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Risk Branch',
            'code' => 'RISK-1',
            'status' => 'active',
        ]);
        $fy = FinancialYear::current()->label;
        ShakhaAnnualKpi::query()->create([
            'shakha_id' => $shakha->id,
            'fy_label' => $fy,
            'total_members' => 100,
            'current_recovery' => 40,
            'recoverable' => 100,
            'loan_outstanding' => 1000,
            'total_od_taka' => 0,
            'fy_loan_recovery' => 100,
            'surplus_deficit_fy' => 10,
        ]);

        $assessment = app(RiskAssessmentService::class)->calculateRiskScore(
            $shakha,
            (int) now('Asia/Dhaka')->month,
            (int) now('Asia/Dhaka')->year,
            [
                'total_income' => 120,
                'total_expenditure' => 100,
                'write_off_principal_amount' => 0,
                'savings_adjustment_amount' => 0,
                'distance_from_area_office_km' => false,
                'has_both_bm_and_abm' => true,
                'special_audit_last_two_years' => true,
            ]
        );

        // OTR 40% → default band 99 points under the edited law.
        $this->assertSame(99, $assessment->total_weighted_score);
        $this->assertSame('Significant Risk', $assessment->risk_category);
    }

    public function test_manager_can_save_and_reset_risk_laws(): void
    {
        $user = $this->manager();
        RiskLaw::ensureDefaults();
        $otr = RiskLaw::query()->where('key', 'otr')->firstOrFail();

        $payload = [];
        foreach (RiskLaw::ordered() as $law) {
            $row = [
                'id' => $law->id,
                'label' => $law->key === 'otr' ? 'Custom OTR' : $law->label,
                'description' => $law->description,
                'is_active' => 1,
                'sort_order' => $law->sort_order,
            ];
            if ($law->unit === 'boolean') {
                $row['true_points'] = $law->bands['true_points'] ?? 0;
                $row['false_points'] = $law->bands['false_points'] ?? 0;
                $row['true_label'] = $law->bands['true_label'] ?? 'Yes';
                $row['false_label'] = $law->bands['false_label'] ?? 'No';
            } else {
                $row['bands'] = $law->bands;
            }
            $payload[$law->id] = $row;
        }

        $this->actingAs($user)
            ->put(route('shakhas.risk.laws.update'), ['laws' => $payload])
            ->assertRedirect(route('shakhas.risk.laws'));

        $this->assertSame('Custom OTR', $otr->fresh()->label);

        $this->actingAs($user)
            ->post(route('shakhas.risk.laws.reset'))
            ->assertRedirect(route('shakhas.risk.laws'));

        $this->assertSame('OTR (On-time recovery)', RiskLaw::query()->where('key', 'otr')->value('label'));
    }
}
