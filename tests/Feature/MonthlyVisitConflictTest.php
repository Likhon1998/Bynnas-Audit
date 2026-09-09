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
use App\Services\MonthlyWorklistService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonthlyVisitConflictTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_assign_blocks_same_person_on_overlapping_dates(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $position = Position::query()->create([
            'serial' => 1,
            'title' => 'Audit Officer',
            'slug' => 'ao-conflict',
            'color' => '#4C6FFF',
        ]);
        $employee = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Busy Officer',
            'sort_order' => 1,
        ]);

        $area = Area::query()->create(['name' => 'Area X', 'division' => 'D1']);
        $a = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Shakha A', 'code' => 'A-1', 'status' => 'active']);
        $b = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Shakha B', 'code' => 'B-1', 'status' => 'active']);
        $activity = ActivityType::query()->create([
            'name' => 'Audit',
            'slug' => 'audit-conflict',
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

        $itemA = MonthlyWorkItem::query()->create([
            'audit_plan_id' => $plan->id,
            'fy_label' => $plan->fy_label,
            'month_index' => 2,
            'category' => 'shakha_audit',
            'activity_type_id' => $activity->id,
            'schedulable_type' => Shakha::class,
            'schedulable_id' => $a->id,
            'source' => MonthlyWorkItem::SOURCE_YEARLY,
            'status' => MonthlyWorkItem::STATUS_UNASSIGNED,
            'entity_label' => $a->name,
        ]);
        $itemB = MonthlyWorkItem::query()->create([
            'audit_plan_id' => $plan->id,
            'fy_label' => $plan->fy_label,
            'month_index' => 2,
            'category' => 'shakha_audit',
            'activity_type_id' => $activity->id,
            'schedulable_type' => Shakha::class,
            'schedulable_id' => $b->id,
            'source' => MonthlyWorkItem::SOURCE_YEARLY,
            'status' => MonthlyWorkItem::STATUS_UNASSIGNED,
            'entity_label' => $b->name,
        ]);

        $worklist = app(MonthlyWorklistService::class);

        $worklist->assign($itemA, [
            'employee_ids' => [$employee->id],
            'start_date' => '2025-09-03',
            'end_date' => '2025-09-05',
        ], $admin->id);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('same person cannot be in two places');

        $worklist->assign($itemB, [
            'employee_ids' => [$employee->id],
            'start_date' => '2025-09-03',
            'end_date' => '2025-09-05',
        ], $admin->id);
    }

    public function test_resolve_overlaps_unassigns_second_booking(): void
    {
        $position = Position::query()->create([
            'serial' => 1,
            'title' => 'Audit Officer',
            'slug' => 'ao-resolve',
            'color' => '#4C6FFF',
        ]);
        $employee = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Double Booked',
            'sort_order' => 1,
        ]);
        $area = Area::query()->create(['name' => 'Area Y', 'division' => 'D1']);
        $a = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Branch A', 'code' => 'BA', 'status' => 'active']);
        $b = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Branch B', 'code' => 'BB', 'status' => 'active']);
        $activity = ActivityType::query()->create([
            'name' => 'Audit',
            'slug' => 'audit-resolve',
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

        foreach ([[$a, '2025-09-03'], [$b, '2025-09-04']] as [$shakha, $start]) {
            $item = MonthlyWorkItem::query()->create([
                'audit_plan_id' => $plan->id,
                'fy_label' => $plan->fy_label,
                'month_index' => 2,
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
                'employee_id' => $employee->id,
                'start_date' => $start,
                'end_date' => '2025-09-05',
                'duration_days' => 3,
            ]);
            $assignment->visitors()->sync([$employee->id => ['sort_order' => 0]]);
        }

        $result = app(MonthlyWorklistService::class)->resolveOverlappingAllocations($plan, 2);

        $this->assertGreaterThan(0, $result['fixed']);
        $stillBooked = MonthlyAssignment::query()
            ->where(function ($q) use ($employee) {
                $q->where('employee_id', $employee->id)
                    ->orWhereHas('visitors', fn ($v) => $v->where('employees.id', $employee->id));
            })
            ->count();
        $this->assertSame(1, $stillBooked);
    }

    public function test_assign_rejects_dates_outside_the_selected_plan_month(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $position = Position::query()->create([
            'serial' => 1,
            'title' => 'Audit Officer',
            'slug' => 'ao-month-bound',
            'color' => '#4C6FFF',
        ]);
        $employee = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Month Bound Officer',
            'sort_order' => 1,
        ]);
        $area = Area::query()->create(['name' => 'Area Z', 'division' => 'D1']);
        $shakha = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Sep Branch', 'code' => 'SEP-1', 'status' => 'active']);
        $activity = ActivityType::query()->create([
            'name' => 'Audit',
            'slug' => 'audit-month-bound',
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
        $item = MonthlyWorkItem::query()->create([
            'audit_plan_id' => $plan->id,
            'fy_label' => $plan->fy_label,
            'month_index' => 2,
            'category' => 'shakha_audit',
            'activity_type_id' => $activity->id,
            'schedulable_type' => Shakha::class,
            'schedulable_id' => $shakha->id,
            'source' => MonthlyWorkItem::SOURCE_YEARLY,
            'status' => MonthlyWorkItem::STATUS_UNASSIGNED,
            'entity_label' => $shakha->name,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Visit dates must fall inside Sep 2026');

        app(MonthlyWorklistService::class)->assign($item, [
            'employee_ids' => [$employee->id],
            'start_date' => '2026-11-06',
            'end_date' => '2026-11-09',
        ], $admin->id);
    }

    public function test_monthly_visits_page_opens_on_current_month(): void
    {
        \Illuminate\Support\Carbon::setTestNow(\Illuminate\Support\Carbon::parse('2026-09-09 12:00:00', 'Asia/Dhaka'));

        try {
            $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
            AuditPlan::query()->create([
                'name' => 'FY 2026-2027',
                'fy_label' => '2026-2027',
                'start_date' => '2026-07-01',
                'end_date' => '2027-06-30',
                'status' => 'active',
                'generated_at' => now(),
            ]);

            $this->actingAs($admin)
                ->get(route('monthly-visits.index'))
                ->assertOk()
                ->assertViewHas('monthIndex', 2)
                ->assertViewHas('monthLabel', 'Sep 2026');
        } finally {
            \Illuminate\Support\Carbon::setTestNow();
        }
    }
}
