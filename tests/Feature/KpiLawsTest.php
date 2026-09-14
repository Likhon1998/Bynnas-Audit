<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\KpiLaw;
use App\Models\Shakha;
use App\Models\ShakhaAnnualKpi;
use App\Models\User;
use App\Services\KpiReportService;
use App\Support\FinancialYear;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KpiLawsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('kpis.manage');
        $role = Role::findOrCreate('audit_manager');
        $role->givePermissionTo('kpis.manage');
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

    public function test_kpi_index_shows_laws_button_and_laws_page_lists_defaults(): void
    {
        $user = $this->manager();

        $this->actingAs($user)
            ->get(route('kpis.index'))
            ->assertOk()
            ->assertSee('KPI laws')
            ->assertSee(route('kpis.laws'), false);

        $this->actingAs($user)
            ->get(route('kpis.laws'))
            ->assertOk()
            ->assertSee('KPI laws')
            ->assertSee('At a glance')
            ->assertSee('OTR')
            ->assertSee('Current Recovery ÷ Recoverable');

        $this->assertGreaterThan(10, KpiLaw::query()->count());
    }

    public function test_manager_can_change_a_law_and_export_math_follows_it(): void
    {
        $user = $this->manager();
        KpiLaw::ensureDefaults();

        $otr = KpiLaw::query()->where('key', 'otr')->firstOrFail();
        $payload = [];
        foreach (KpiLaw::ordered() as $law) {
            $payload[$law->id] = [
                'id' => $law->id,
                'label' => $law->key === 'otr' ? 'Custom OTR Label' : $law->label,
                'operation' => $law->key === 'otr' ? KpiLaw::OPERATION_DIVIDE : $law->operation,
                'left_operand' => $law->key === 'otr' ? 'due_recovery' : $law->left_operand,
                'right_operand' => $law->key === 'otr' ? 'total_od_taka' : $law->right_operand,
                'description' => $law->description,
                'format' => $law->format,
                'is_active' => $law->is_active ? 1 : 0,
                'sort_order' => $law->sort_order,
            ];
        }

        $this->actingAs($user)
            ->put(route('kpis.laws.update'), ['laws' => $payload])
            ->assertRedirect(route('kpis.laws'));

        $otr->refresh();
        $this->assertSame('Custom OTR Label', $otr->label);
        $this->assertSame('due_recovery', $otr->left_operand);
        $this->assertSame('total_od_taka', $otr->right_operand);
        $this->assertSame('Due Recovery ÷ Total OD Taka', $otr->formula_display);

        $area = Area::query()->create(['name' => 'KPI Area', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'KPI Branch',
            'code' => 'KPI-1',
            'status' => 'active',
        ]);
        $fy = FinancialYear::current()->label;
        ShakhaAnnualKpi::query()->create([
            'shakha_id' => $shakha->id,
            'fy_label' => $fy,
            'current_recovery' => 50,
            'recoverable' => 100,
            'due_recovery' => 40,
            'total_od_taka' => 80,
            'loan_outstanding' => 200,
        ]);

        $row = app(KpiReportService::class)->compileRow(
            $shakha->fresh('area'),
            ShakhaAnnualKpi::query()->where('shakha_id', $shakha->id)->first(),
            $fy,
            1,
            Carbon::now('Asia/Dhaka')
        );

        $this->assertEqualsWithDelta(0.5, $row['otr'], 0.0001);
    }

    public function test_inactive_law_is_skipped_in_calculations(): void
    {
        KpiLaw::ensureDefaults();
        KpiLaw::query()->where('key', 'otr')->update(['is_active' => false]);

        $area = Area::query()->create(['name' => 'KPI Area', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'KPI Branch',
            'code' => 'KPI-2',
            'status' => 'active',
        ]);
        $fy = FinancialYear::current()->label;
        $kpi = ShakhaAnnualKpi::query()->create([
            'shakha_id' => $shakha->id,
            'fy_label' => $fy,
            'current_recovery' => 50,
            'recoverable' => 100,
        ]);

        $row = app(KpiReportService::class)->compileRow(
            $shakha->fresh('area'),
            $kpi,
            $fy,
            1,
            Carbon::now('Asia/Dhaka')
        );

        $this->assertArrayNotHasKey('otr', $row);
        $this->assertArrayHasKey('par', $row);
    }

    public function test_reset_restores_default_laws(): void
    {
        $user = $this->manager();
        KpiLaw::ensureDefaults();
        KpiLaw::query()->where('key', 'otr')->update([
            'label' => 'Changed',
            'left_operand' => 'due_recovery',
            'right_operand' => 'total_od_taka',
            'formula_display' => 'changed',
        ]);

        $this->actingAs($user)
            ->post(route('kpis.laws.reset'))
            ->assertRedirect(route('kpis.laws'));

        $otr = KpiLaw::query()->where('key', 'otr')->firstOrFail();
        $this->assertSame('OTR', $otr->label);
        $this->assertSame('current_recovery', $otr->left_operand);
        $this->assertSame('recoverable', $otr->right_operand);
    }

    public function test_user_without_kpi_permission_cannot_open_laws(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('kpis.laws'))
            ->assertForbidden();
    }
}
