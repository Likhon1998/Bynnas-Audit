<?php

namespace Tests\Feature;

use App\Models\ActivityType;
use App\Models\Area;
use App\Models\AuditPlan;
use App\Models\Employee;
use App\Models\MonthlyWorkItem;
use App\Models\Position;
use App\Models\Shakha;
use App\Models\User;
use App\Services\MonthlyWorklistService;
use App\Support\FinancialYear;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficerTodayVisitsDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_officer_sees_today_shakha_and_monthly_list_on_dashboard(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $position = Position::query()->create([
            'serial' => 7,
            'title' => 'Audit Officer',
            'slug' => 'ao-today-dash',
            'color' => '#667085',
        ]);
        $employee = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Field Visitor',
            'email' => 'field.visitor@bynnasaudit.com',
            'sort_order' => 1,
        ]);
        $officer = User::factory()->create([
            'name' => 'Field Visitor',
            'email' => 'field.visitor@bynnasaudit.com',
            'employee_id' => $employee->id,
            'is_active' => true,
        ]);
        $officer->assignRole('audit_officer');

        $area = Area::query()->create(['name' => 'Today Area', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Today Mirpur Shakha',
            'code' => 'TOD-1',
            'status' => 'active',
        ]);

        $fy = FinancialYear::current(now('Asia/Dhaka'));
        $monthIndex = $fy->monthIndexForDate(now('Asia/Dhaka')) ?? 0;
        $today = now('Asia/Dhaka')->toDateString();

        $activity = ActivityType::query()->create([
            'name' => 'Audit',
            'slug' => 'audit-today-dash',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $plan = AuditPlan::query()->create([
            'name' => $fy->label,
            'fy_label' => $fy->label,
            'start_date' => $fy->startDate->toDateString(),
            'end_date' => $fy->endDate->toDateString(),
            'status' => 'active',
            'generated_at' => now(),
        ]);
        $item = MonthlyWorkItem::query()->create([
            'audit_plan_id' => $plan->id,
            'fy_label' => $fy->label,
            'month_index' => $monthIndex,
            'category' => 'shakha_audit',
            'activity_type_id' => $activity->id,
            'schedulable_type' => Shakha::class,
            'schedulable_id' => $shakha->id,
            'source' => MonthlyWorkItem::SOURCE_YEARLY,
            'status' => MonthlyWorkItem::STATUS_UNASSIGNED,
            'entity_label' => $shakha->name,
        ]);

        app(MonthlyWorklistService::class)->assign($item, [
            'employee_ids' => [$employee->id],
            'start_date' => $today,
            'end_date' => $today,
            'visit_date' => $today,
        ], $officer->id);

        $this->actingAs($officer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Where to go today')
            ->assertSee('Today Mirpur Shakha')
            ->assertSee('My monthly visits')
            ->assertSee('Go / Open')
            ->assertSee('Solo');
    }

    public function test_officer_sees_companion_names_when_visit_is_shared(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $position = Position::query()->create([
            'serial' => 7,
            'title' => 'Audit Officer',
            'slug' => 'ao-pair-dash',
            'color' => '#667085',
        ]);
        $employeeA = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Pair Officer A',
            'email' => 'pair.a@bynnasaudit.com',
            'sort_order' => 1,
        ]);
        $employeeB = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Pair Officer B',
            'email' => 'pair.b@bynnasaudit.com',
            'sort_order' => 2,
        ]);
        $officer = User::factory()->create([
            'name' => 'Pair Officer A',
            'email' => 'pair.a@bynnasaudit.com',
            'employee_id' => $employeeA->id,
            'is_active' => true,
        ]);
        $officer->assignRole('audit_officer');

        $area = Area::query()->create(['name' => 'Pair Area', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Paired Visit Shakha',
            'code' => 'PAIR-1',
            'status' => 'active',
        ]);

        $fy = FinancialYear::current(now('Asia/Dhaka'));
        $monthIndex = $fy->monthIndexForDate(now('Asia/Dhaka')) ?? 0;
        $today = now('Asia/Dhaka')->toDateString();

        $activity = ActivityType::query()->create([
            'name' => 'Audit',
            'slug' => 'audit-pair-dash',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $plan = AuditPlan::query()->create([
            'name' => $fy->label,
            'fy_label' => $fy->label,
            'start_date' => $fy->startDate->toDateString(),
            'end_date' => $fy->endDate->toDateString(),
            'status' => 'active',
            'generated_at' => now(),
        ]);
        $item = MonthlyWorkItem::query()->create([
            'audit_plan_id' => $plan->id,
            'fy_label' => $fy->label,
            'month_index' => $monthIndex,
            'category' => 'shakha_audit',
            'activity_type_id' => $activity->id,
            'schedulable_type' => Shakha::class,
            'schedulable_id' => $shakha->id,
            'source' => MonthlyWorkItem::SOURCE_YEARLY,
            'status' => MonthlyWorkItem::STATUS_UNASSIGNED,
            'entity_label' => $shakha->name,
        ]);

        app(MonthlyWorklistService::class)->assign($item, [
            'employee_ids' => [$employeeA->id, $employeeB->id],
            'start_date' => $today,
            'end_date' => $today,
            'visit_date' => $today,
        ], $officer->id);

        $this->actingAs($officer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Paired Visit Shakha')
            ->assertSee('With Pair Officer B')
            ->assertDontSee('Solo');
    }

    public function test_linked_audit_manager_sees_personal_visits_on_ops_dashboard(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $position = Position::query()->create([
            'serial' => 4,
            'title' => 'Assistant Director Audit',
            'slug' => 'assistant-director-audit',
            'color' => '#667085',
        ]);
        $employee = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Ayesha Manager',
            'email' => 'ayesha.manager@bynnasaudit.com',
            'sort_order' => 1,
        ]);
        $manager = User::factory()->create([
            'name' => 'Ayesha Manager',
            'email' => 'ayesha.manager@bynnasaudit.com',
            'employee_id' => $employee->id,
            'is_active' => true,
        ]);
        $manager->assignRole('audit_manager');

        $area = Area::query()->create(['name' => 'Mgr Area', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Manager Today Shakha',
            'code' => 'MGR-1',
            'status' => 'active',
        ]);

        $fy = FinancialYear::current(now('Asia/Dhaka'));
        $monthIndex = $fy->monthIndexForDate(now('Asia/Dhaka')) ?? 0;
        $today = now('Asia/Dhaka')->toDateString();

        $activity = ActivityType::query()->create([
            'name' => 'Audit',
            'slug' => 'audit-mgr-today',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $plan = AuditPlan::query()->create([
            'name' => $fy->label,
            'fy_label' => $fy->label,
            'start_date' => $fy->startDate->toDateString(),
            'end_date' => $fy->endDate->toDateString(),
            'status' => 'active',
            'generated_at' => now(),
        ]);
        $item = MonthlyWorkItem::query()->create([
            'audit_plan_id' => $plan->id,
            'fy_label' => $fy->label,
            'month_index' => $monthIndex,
            'category' => 'shakha_audit',
            'activity_type_id' => $activity->id,
            'schedulable_type' => Shakha::class,
            'schedulable_id' => $shakha->id,
            'source' => MonthlyWorkItem::SOURCE_YEARLY,
            'status' => MonthlyWorkItem::STATUS_UNASSIGNED,
            'entity_label' => $shakha->name,
        ]);

        app(MonthlyWorklistService::class)->assign($item, [
            'employee_ids' => [$employee->id],
            'start_date' => $today,
            'end_date' => $today,
            'visit_date' => $today,
        ], $manager->id);

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Total shakha')
            ->assertSee('My field visits')
            ->assertSee('Where to go today')
            ->assertSee('Manager Today Shakha')
            ->assertSee('My monthly visits');
    }
}
