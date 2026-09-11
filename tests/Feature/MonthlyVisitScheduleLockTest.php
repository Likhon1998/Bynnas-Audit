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

class MonthlyVisitScheduleLockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_locked_visit_cannot_be_changed_by_other_manager(): void
    {
        $locker = User::factory()->create(['is_active' => true, 'name' => 'Locker Admin']);
        $locker->assignRole('audit_manager');
        $other = User::factory()->create(['is_active' => true, 'name' => 'Other Manager']);
        $other->assignRole('audit_manager');

        [$item, $employee, $today] = $this->makeAssignableVisit();

        app(MonthlyWorklistService::class)->assign($item, [
            'employee_ids' => [$employee->id],
            'start_date' => $today,
            'end_date' => $today,
            'visit_date' => $today,
            'lock_schedule' => true,
        ], $locker->id);

        $assignment = $item->fresh()->assignment;
        $this->assertTrue((bool) $assignment->is_locked);
        $this->assertSame($locker->id, (int) $assignment->locked_by);

        $this->actingAs($other)
            ->post(route('monthly-visits.assign.store', $item), [
                'employee_ids' => [$employee->id],
                'start_date' => $today,
                'end_date' => $today,
                'lock_schedule' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('assign');

        $this->actingAs($other)
            ->get(route('monthly-visits.reschedule', $assignment))
            ->assertForbidden();

        $this->actingAs($other)
            ->post(route('monthly-visits.unlock', $assignment))
            ->assertRedirect()
            ->assertSessionHasErrors('lock');

        $this->actingAs($locker)
            ->post(route('monthly-visits.unlock', $assignment))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertFalse((bool) $assignment->fresh()->is_locked);
    }

    public function test_superadmin_can_change_locked_visit(): void
    {
        $locker = User::factory()->create(['is_active' => true]);
        $locker->assignRole('audit_manager');
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();

        [$item, $employee, $today] = $this->makeAssignableVisit();

        app(MonthlyWorklistService::class)->assign($item, [
            'employee_ids' => [$employee->id],
            'start_date' => $today,
            'end_date' => $today,
            'lock_schedule' => true,
        ], $locker->id);

        $assignment = $item->fresh()->assignment;

        $this->actingAs($admin)
            ->post(route('monthly-visits.unlock', $assignment))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertFalse((bool) $assignment->fresh()->is_locked);
    }

    /**
     * @return array{0: MonthlyWorkItem, 1: Employee, 2: string}
     */
    protected function makeAssignableVisit(): array
    {
        $position = Position::query()->create([
            'serial' => 7,
            'title' => 'Audit Officer',
            'slug' => 'ao-lock-'.uniqid(),
            'color' => '#667085',
        ]);
        $employee = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Lock Test Officer',
            'email' => 'lock.'.uniqid().'@bynnasaudit.com',
            'sort_order' => 1,
        ]);

        $area = Area::query()->create(['name' => 'Lock Area', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Locked Visit Shakha',
            'code' => 'LCK-'.substr(uniqid(), -4),
            'status' => 'active',
        ]);

        $fy = FinancialYear::current(now('Asia/Dhaka'));
        $monthIndex = $fy->monthIndexForDate(now('Asia/Dhaka')) ?? 0;
        $today = now('Asia/Dhaka')->toDateString();

        $activity = ActivityType::query()->create([
            'name' => 'Audit',
            'slug' => 'audit-lock-'.uniqid(),
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

        return [$item, $employee, $today];
    }
}
