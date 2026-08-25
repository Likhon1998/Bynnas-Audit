<?php

namespace Tests\Feature;

use App\Models\AuditPlan;
use App\Models\AuditPolicy;
use App\Models\PlanSchedule;
use App\Models\Shakha;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnualAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_annual_audit_page_loads_for_authenticated_user(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('annual-audit.index'))
            ->assertOk()
            ->assertSee('Annual Audit', false)
            ->assertSee('2026-2027');
    }

    public function test_generate_creates_shakha_and_area_schedules(): void
    {
        $this->seed(\Database\Seeders\OrganizationSeeder::class);
        $this->seed(\Database\Seeders\ShakhaSeeder::class);

        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->post(route('annual-audit.generate'))
            ->assertRedirect();

        $plan = AuditPlan::query()->where('fy_label', '2026-2027')->first();
        $this->assertNotNull($plan);

        $activeShakhas = Shakha::query()->where('status', 'active')->count();
        $this->assertSame($activeShakhas * 3, PlanSchedule::query()
            ->where('audit_plan_id', $plan->id)
            ->where('category', AuditPolicy::CATEGORY_SHAKHA)
            ->count());

        $this->assertGreaterThan(0, PlanSchedule::query()
            ->where('audit_plan_id', $plan->id)
            ->where('category', AuditPolicy::CATEGORY_AREA)
            ->count());
    }

    public function test_shakha_tab_renders_schedule_rows(): void
    {
        $this->seed(\Database\Seeders\OrganizationSeeder::class);
        $this->seed(\Database\Seeders\ShakhaSeeder::class);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user)->post(route('annual-audit.generate'))->assertRedirect();

        $shakha = Shakha::query()->with('area')->where('status', 'active')->orderBy('name')->firstOrFail();

        $this->actingAs($user)
            ->get(route('annual-audit.index', ['tab' => 'shakha']))
            ->assertOk()
            ->assertSee('Shakha Work Plan', false)
            ->assertSee('Export Excel', false)
            ->assertSee('Area', false)
            ->assertSee('Branch', false)
            ->assertSee($shakha->name, false)
            ->assertSee($shakha->area?->name ?? '', false)
            ->assertSee('rowspan=', false);
    }

    public function test_admin_can_export_shakha_excel(): void
    {
        $this->seed(\Database\Seeders\OrganizationSeeder::class);
        $this->seed(\Database\Seeders\ShakhaSeeder::class);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user)->post(route('annual-audit.generate'))->assertRedirect();

        $response = $this->actingAs($user)
            ->get(route('annual-audit.export', ['mode' => 'shakha', 'fy' => '2026-2027']));

        $response->assertOk();
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('shakha-work-plan', (string) $response->headers->get('content-disposition'));
    }

    public function test_admin_can_export_full_annual_report_workbook(): void
    {
        $this->seed(\Database\Seeders\AnnualAuditSeeder::class);

        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)
            ->get(route('annual-audit.export', ['mode' => 'all', 'fy' => '2026-2027']));

        $response->assertOk();
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('annual-audit-full-report', (string) $response->headers->get('content-disposition'));

        $path = tempnam(sys_get_temp_dir(), 'full-xlsx');
        file_put_contents($path, $response->streamedContent());
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $names = $spreadsheet->getSheetNames();
        $this->assertSame([
            'Total',
            'Shakha Audit',
            'Area Office',
            'PKSF Maternity',
            'HQ',
            'Project Audit',
            'Project Monitoring',
        ], $names);
        @unlink($path);
    }

    public function test_admin_can_change_shakha_frequency_and_toggle_month(): void
    {
        $this->seed(\Database\Seeders\OrganizationSeeder::class);
        $this->seed(\Database\Seeders\ShakhaSeeder::class);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user)->post(route('annual-audit.generate'))->assertRedirect();

        $plan = AuditPlan::query()->where('fy_label', '2026-2027')->firstOrFail();
        $policy = $plan->policies()->where('category', AuditPolicy::CATEGORY_SHAKHA)->firstOrFail();

        $this->actingAs($user)
            ->post(route('annual-audit.policies'), [
                'policies' => [
                    $policy->id => [
                        'frequency_per_year' => 4,
                        'interval_months' => 3,
                        'pattern' => 'rotated_interval',
                        'custom_month_indexes' => '',
                        'notes' => 'Admin chose 4/year',
                    ],
                ],
                'regenerate' => 1,
            ])
            ->assertRedirect();

        $this->assertSame(4, (int) $policy->fresh()->frequency_per_year);

        $activeShakhas = Shakha::query()->where('status', 'active')->count();
        $this->assertSame($activeShakhas * 4, PlanSchedule::query()
            ->where('audit_plan_id', $plan->id)
            ->where('category', AuditPolicy::CATEGORY_SHAKHA)
            ->count());

        $shakha = Shakha::query()->where('status', 'active')->firstOrFail();

        $this->actingAs($user)
            ->post(route('annual-audit.toggle-month'), [
                'category' => AuditPolicy::CATEGORY_SHAKHA,
                'schedulable_type' => Shakha::class,
                'schedulable_id' => $shakha->id,
                'month_index' => 2,
                'tab' => 'shakha',
            ])
            ->assertRedirect();

        $this->assertTrue(
            PlanSchedule::query()
                ->where('audit_plan_id', $plan->id)
                ->where('category', AuditPolicy::CATEGORY_SHAKHA)
                ->where('schedulable_id', $shakha->id)
                ->where('month_index', 2)
                ->where('is_manual', true)
                ->exists()
        );

        $this->actingAs($user)
            ->postJson(route('annual-audit.toggle-month'), [
                'category' => AuditPolicy::CATEGORY_SHAKHA,
                'schedulable_type' => Shakha::class,
                'schedulable_id' => $shakha->id,
                'month_index' => 2,
                'tab' => 'shakha',
            ])
            ->assertOk()
            ->assertJson(['result' => 'removed', 'active' => false]);
    }

    public function test_admin_can_create_next_financial_year_plan(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('annual-audit.index'))
            ->assertOk()
            ->assertSee('Create FY 2027-2028', false);

        $this->actingAs($user)
            ->post(route('annual-audit.years.store'), ['fy' => '2026-2027'])
            ->assertRedirect(route('annual-audit.index', ['fy' => '2027-2028', 'tab' => 'total']));

        $next = AuditPlan::query()->where('fy_label', '2027-2028')->first();
        $this->assertNotNull($next);
        $this->assertSame('draft', $next->status);
        $this->assertGreaterThan(0, $next->policies()->count());

        $this->actingAs($user)
            ->post(route('annual-audit.generate'), ['fy' => '2027-2028'])
            ->assertRedirect();

        $this->assertNotNull($next->fresh()->generated_at);

        $this->actingAs($user)
            ->get(route('annual-audit.index', ['fy' => '2027-2028']))
            ->assertOk()
            ->assertSee('2027-2028', false)
            ->assertDontSee('Create FY 2027-2028', false)
            ->assertSee('Create FY 2028-2029', false);
    }

    public function test_only_superadmin_can_delete_financial_year_plan(): void
    {
        $regular = User::factory()->create([
            'email_verified_at' => now(),
            'is_superadmin' => false,
        ]);
        $super = User::factory()->create([
            'email_verified_at' => now(),
            'is_superadmin' => true,
        ]);

        $this->actingAs($super)
            ->post(route('annual-audit.years.store'), ['fy' => '2026-2027'])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_plans', ['fy_label' => '2027-2028']);

        $this->actingAs($regular)
            ->delete(route('annual-audit.years.destroy'), ['fy' => '2027-2028'])
            ->assertForbidden();

        $this->assertDatabaseHas('audit_plans', ['fy_label' => '2027-2028']);

        $this->actingAs($super)
            ->get(route('annual-audit.index', ['fy' => '2027-2028']))
            ->assertOk()
            ->assertSee('Delete FY 2027-2028', false);

        $this->actingAs($super)
            ->delete(route('annual-audit.years.destroy'), ['fy' => '2027-2028'])
            ->assertRedirect();

        $this->assertDatabaseMissing('audit_plans', ['fy_label' => '2027-2028']);
        $this->assertDatabaseHas('audit_plans', ['fy_label' => '2026-2027']);
    }
}
