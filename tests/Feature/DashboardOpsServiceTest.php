<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AuditPlan;
use App\Models\PlanSchedule;
use App\Models\Shakha;
use App\Services\DashboardOpsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardOpsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_monthly_plan_shakhas_comes_from_distinct_annual_plan_schedules(): void
    {
        $area = Area::query()->create(['name' => 'Test Area', 'division' => 'Dhaka']);
        $first = Shakha::query()->create(['area_id' => $area->id, 'name' => 'First', 'code' => 'S-1', 'status' => 'active']);
        $second = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Second', 'code' => 'S-2', 'status' => 'active']);
        $plan = AuditPlan::query()->create([
            'name' => 'FY 2026-2027',
            'fy_label' => '2026-2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'status' => 'generated',
        ]);

        foreach ([$first, $first, $second] as $index => $shakha) {
            PlanSchedule::query()->create([
                'audit_plan_id' => $plan->id,
                'category' => 'shakha',
                'schedulable_type' => Shakha::class,
                'schedulable_id' => $shakha->id,
                'month_index' => 4,
                'planned_date' => '2026-11-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'occurrence' => $index + 1,
                'status' => 'planned',
            ]);
        }

        $dashboard = app(DashboardOpsService::class)->build('2026-2027', 4);

        $this->assertSame(2, $dashboard['sights']['monthly_plan_shakhas']);
    }
}
