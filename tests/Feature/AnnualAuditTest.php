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
        $user = User::factory()->create(['email_verified_at' => now(), 'is_superadmin' => true]);

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

        $user = User::factory()->create(['email_verified_at' => now(), 'is_superadmin' => true]);

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

        $user = User::factory()->create(['email_verified_at' => now(), 'is_superadmin' => true]);
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

        $user = User::factory()->create(['email_verified_at' => now(), 'is_superadmin' => true]);
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

        $user = User::factory()->create(['email_verified_at' => now(), 'is_superadmin' => true]);

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

        $user = User::factory()->create(['email_verified_at' => now(), 'is_superadmin' => true]);
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
        $user = User::factory()->create(['email_verified_at' => now(), 'is_superadmin' => true]);

        $this->actingAs($user)
            ->get(route('annual-audit.index'))
            ->assertOk()
            ->assertSee('Create 2027-2028', false);

        $this->actingAs($user)
            ->post(route('annual-audit.years.store'), ['fy' => '2026-2027'])
            ->assertRedirect(route('annual-audit.index', ['fy' => '2027-2028', 'tab' => 'policies']));

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
            ->assertDontSee('Create 2027-2028', false)
            ->assertSee('Create 2028-2029', false);
    }

    public function test_only_superadmin_can_delete_financial_year_plan(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $regular = User::factory()->create([
            'email_verified_at' => now(),
            'is_superadmin' => false,
            'is_active' => true,
        ]);
        $regular->assignRole('audit_manager');

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
            ->assertSee('Delete FY', false);

        $this->actingAs($super)
            ->delete(route('annual-audit.years.destroy'), ['fy' => '2027-2028'])
            ->assertRedirect();

        $this->assertDatabaseMissing('audit_plans', ['fy_label' => '2027-2028']);
        $this->assertDatabaseHas('audit_plans', ['fy_label' => '2026-2027']);
    }

    public function test_view_only_user_can_open_annual_plan_but_not_generate(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $viewer = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
        $role = \Spatie\Permission\Models\Role::findOrCreate('annual_viewer', 'web');
        $role->syncPermissions(['annual_audit.view', 'dashboard.officer']);
        $viewer->syncRoles(['annual_viewer']);

        $this->actingAs($viewer)
            ->get(route('annual-audit.index'))
            ->assertOk()
            ->assertSee('View only', false)
            ->assertDontSee('Generate Plan', false);

        $this->actingAs($viewer)
            ->post(route('annual-audit.generate'))
            ->assertForbidden();
    }

    public function test_completed_card_counts_a_finished_monthly_visit(): void
    {
        $position = \App\Models\Position::query()->create([
            'serial' => 41,
            'title' => 'Audit Officer',
            'slug' => 'ao-annual-done',
            'color' => '#667085',
        ]);
        $employee = \App\Models\Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Annual Done Officer',
            'sort_order' => 1,
        ]);
        $area = \App\Models\Area::query()->create(['name' => 'Done Area', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Done Shakha',
            'code' => 'DONE-1',
            'status' => 'active',
        ]);
        $activity = \App\Models\ActivityType::query()->create([
            'name' => 'Shakha Audit',
            'slug' => 'shakha-audit-done-kpi',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $plan = AuditPlan::query()->create([
            'name' => 'FY 2026-2027',
            'fy_label' => '2026-2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
            'generated_at' => now(),
        ]);
        $schedule = PlanSchedule::query()->create([
            'audit_plan_id' => $plan->id,
            'category' => AuditPolicy::CATEGORY_SHAKHA,
            'schedulable_type' => Shakha::class,
            'schedulable_id' => $shakha->id,
            'month_index' => 0,
            'planned_date' => '2026-07-15',
            'occurrence' => 1,
            'status' => 'planned',
        ]);
        $item = \App\Models\MonthlyWorkItem::query()->create([
            'audit_plan_id' => $plan->id,
            'fy_label' => '2026-2027',
            'month_index' => 0,
            'category' => AuditPolicy::CATEGORY_SHAKHA,
            'activity_type_id' => $activity->id,
            'schedulable_type' => Shakha::class,
            'schedulable_id' => $shakha->id,
            'plan_schedule_id' => $schedule->id,
            'source' => \App\Models\MonthlyWorkItem::SOURCE_YEARLY,
            'status' => \App\Models\MonthlyWorkItem::STATUS_UNASSIGNED,
            'entity_label' => $shakha->name,
        ]);

        $builder = new \App\Services\AnnualAuditReportBuilder($plan);
        $this->assertSame(0, $builder->kpis()['completed']);
        $this->assertSame(1, $builder->kpis()['pending']);

        $assignment = app(\App\Services\MonthlyWorklistService::class)->assign($item, [
            'employee_ids' => [$employee->id],
            'start_date' => '2026-07-15',
            'end_date' => '2026-07-15',
            'visit_date' => '2026-07-15',
        ]);

        app(\App\Services\MonthlyWorklistService::class)->updateExecution($assignment, [
            'status' => \App\Models\VisitExecution::STATUS_COMPLETED,
            'actual_start_date' => '2026-07-15',
            'actual_end_date' => '2026-07-15',
        ]);

        $schedule->refresh();
        $this->assertSame('completed', $schedule->status);
        $kpis = $builder->kpis();
        $this->assertSame(1, $kpis['completed']);
        $this->assertSame(0, $kpis['pending']);
    }
}
