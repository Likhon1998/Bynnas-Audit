<?php

namespace Tests\Feature;

use App\Models\ActivityType;
use App\Models\Area;
use App\Models\AuditPlan;
use App\Models\Employee;
use App\Models\MonthlyAssignment;
use App\Models\MonthlyWorkItem;
use App\Models\Position;
use App\Models\Shakha;
use App\Models\User;
use App\Services\UserAccessService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficerShakhaAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_officer_only_sees_shakhas_from_their_monthly_visit_allocation(): void
    {
        $position = Position::query()->create([
            'serial' => 1,
            'title' => 'Audit Officer',
            'slug' => 'ao-access',
            'color' => '#4C6FFF',
        ]);
        $employee = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Visitor One',
            'email' => 'v1@example.com',
            'sort_order' => 1,
        ]);
        $otherEmployee = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Visitor Two',
            'email' => 'v2@example.com',
            'sort_order' => 2,
        ]);

        $activity = ActivityType::query()->create([
            'name' => 'Shakha Audit',
            'slug' => 'shakha-audit',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $area = Area::query()->create(['name' => 'Area A', 'division' => 'D1']);
        $mine = Shakha::query()->create(['area_id' => $area->id, 'name' => 'My Branch', 'code' => 'MB-1', 'status' => 'active']);
        $theirs = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Other Branch', 'code' => 'OB-1', 'status' => 'active']);

        $plan = AuditPlan::query()->create([
            'name' => 'FY 2025-2026',
            'fy_label' => '2025-2026',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'status' => 'active',
            'generated_at' => now(),
        ]);

        $myItem = MonthlyWorkItem::query()->create([
            'audit_plan_id' => $plan->id,
            'fy_label' => $plan->fy_label,
            'month_index' => 0,
            'category' => 'shakha_audit',
            'activity_type_id' => $activity->id,
            'schedulable_type' => Shakha::class,
            'schedulable_id' => $mine->id,
            'source' => MonthlyWorkItem::SOURCE_YEARLY,
            'status' => MonthlyWorkItem::STATUS_ASSIGNED,
            'entity_label' => $mine->name,
        ]);
        $theirItem = MonthlyWorkItem::query()->create([
            'audit_plan_id' => $plan->id,
            'fy_label' => $plan->fy_label,
            'month_index' => 0,
            'category' => 'shakha_audit',
            'activity_type_id' => $activity->id,
            'schedulable_type' => Shakha::class,
            'schedulable_id' => $theirs->id,
            'source' => MonthlyWorkItem::SOURCE_YEARLY,
            'status' => MonthlyWorkItem::STATUS_ASSIGNED,
            'entity_label' => $theirs->name,
        ]);

        $myAssignment = MonthlyAssignment::query()->create([
            'monthly_work_item_id' => $myItem->id,
            'employee_id' => $employee->id,
            'start_date' => '2025-07-01',
            'end_date' => '2025-07-03',
            'duration_days' => 3,
        ]);
        $myAssignment->visitors()->sync([$employee->id => ['sort_order' => 0]]);

        $theirAssignment = MonthlyAssignment::query()->create([
            'monthly_work_item_id' => $theirItem->id,
            'employee_id' => $otherEmployee->id,
            'start_date' => '2025-07-01',
            'end_date' => '2025-07-03',
            'duration_days' => 3,
        ]);
        $theirAssignment->visitors()->sync([$otherEmployee->id => ['sort_order' => 0]]);

        $officer = User::factory()->create([
            'is_active' => true,
            'employee_id' => $employee->id,
        ]);
        $officer->assignRole('audit_officer');
        // Explicit extra must ADD access (not replace monthly auto-access).
        $officer->assignedShakhas()->sync([$theirs->id]);

        $access = app(UserAccessService::class);
        $ids = $access->accessibleShakhaIds($officer);

        $this->assertEqualsCanonicalizing([$mine->id, $theirs->id], $ids);
        $this->assertTrue($access->canAccessShakha($officer, $mine->id));
        $this->assertTrue($access->canAccessShakha($officer, $theirs->id));

        $this->actingAs($officer)
            ->get(route('monthly-visits.index', ['fy' => $plan->fy_label, 'month' => 0]))
            ->assertOk()
            ->assertSee('My Branch')
            ->assertDontSee('Other Branch')
            ->assertSee('My allocated shakhas');
    }

    public function test_officer_without_visit_gets_only_extra_admin_shakhas(): void
    {
        $position = Position::query()->create([
            'serial' => 1,
            'title' => 'Audit Officer',
            'slug' => 'ao-extra',
            'color' => '#4C6FFF',
        ]);
        $employee = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Extra Only',
            'email' => 'extra@example.com',
            'sort_order' => 1,
        ]);
        $area = Area::query()->create(['name' => 'Area C', 'division' => 'D1']);
        $extra = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Extra Branch', 'code' => 'EX-1', 'status' => 'active']);
        $hidden = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Hidden Branch', 'code' => 'HD-1', 'status' => 'active']);

        $officer = User::factory()->create([
            'is_active' => true,
            'employee_id' => $employee->id,
        ]);
        $officer->assignRole('audit_officer');
        $officer->assignedShakhas()->sync([$extra->id]);

        $access = app(UserAccessService::class);
        $this->assertSame([$extra->id], $access->accessibleShakhaIds($officer));
        $this->assertTrue($access->canAccessShakha($officer, $extra->id));
        $this->assertFalse($access->canAccessShakha($officer, $hidden->id));
    }

    public function test_officer_cannot_open_someone_elses_execution(): void
    {
        $position = Position::query()->create([
            'serial' => 1,
            'title' => 'Audit Officer',
            'slug' => 'ao-exec',
            'color' => '#4C6FFF',
        ]);
        $employee = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Me',
            'sort_order' => 1,
        ]);
        $other = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Them',
            'sort_order' => 2,
        ]);
        $area = Area::query()->create(['name' => 'Area B', 'division' => 'D1']);
        $shakha = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Branch X', 'code' => 'BX', 'status' => 'active']);
        $activity = ActivityType::query()->create([
            'name' => 'Shakha Audit',
            'slug' => 'shakha-audit-2',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $plan = AuditPlan::query()->create([
            'name' => 'FY 2025-2026',
            'fy_label' => '2025-2026',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'status' => 'active',
            'generated_at' => now(),
        ]);
        $item = MonthlyWorkItem::query()->create([
            'audit_plan_id' => $plan->id,
            'fy_label' => $plan->fy_label,
            'month_index' => 0,
            'category' => 'shakha_audit',
            'activity_type_id' => $activity->id,
            'schedulable_type' => Shakha::class,
            'schedulable_id' => $shakha->id,
            'source' => MonthlyWorkItem::SOURCE_YEARLY,
            'status' => MonthlyWorkItem::STATUS_ASSIGNED,
            'entity_label' => $shakha->name,
        ]);
        $assignment = MonthlyAssignment::query()->create([
            'monthly_work_item_id' => $item->id,
            'employee_id' => $other->id,
            'start_date' => '2025-07-01',
            'end_date' => '2025-07-02',
            'duration_days' => 2,
        ]);
        $assignment->visitors()->sync([$other->id => ['sort_order' => 0]]);

        $officer = User::factory()->create(['is_active' => true, 'employee_id' => $employee->id]);
        $officer->assignRole('audit_officer');

        $this->actingAs($officer)
            ->get(route('monthly-visits.execution', $assignment))
            ->assertForbidden();
    }
}
