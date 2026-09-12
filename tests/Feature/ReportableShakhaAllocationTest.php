<?php

namespace Tests\Feature;

use App\Livewire\MakeAuditReport;
use App\Models\ActivityType;
use App\Models\Area;
use App\Models\AuditPlan;
use App\Models\Employee;
use App\Models\MonthlyWorkItem;
use App\Models\Position;
use App\Models\Shakha;
use App\Models\User;
use App\Services\MonthlyWorklistService;
use App\Services\UserAccessService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportableShakhaAllocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_officer_report_picker_only_includes_allocated_shakha_for_month(): void
    {
        $position = Position::query()->create([
            'serial' => 7,
            'title' => 'Audit Officer',
            'slug' => 'ao-report-alloc',
            'color' => '#667085',
        ]);
        $employee = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Report Officer',
            'email' => 'report.officer@bynnasaudit.com',
            'sort_order' => 1,
        ]);
        $officer = User::factory()->create([
            'name' => 'Report Officer',
            'email' => 'report.officer@bynnasaudit.com',
            'employee_id' => $employee->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $officer->assignRole('audit_officer');

        $area = Area::query()->create(['name' => 'R Area', 'division' => 'Dhaka', 'status' => 'active']);
        $allocated = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Allocated Report Shakha',
            'code' => 'ARS-1',
            'status' => 'active',
        ]);
        $other = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Other Report Shakha',
            'code' => 'ORS-1',
            'status' => 'active',
        ]);

        $today = now('Asia/Dhaka');
        $month = (int) $today->month;
        $year = (int) $today->year;
        $date = $today->toDateString();

        $this->allocateVisit($allocated, $employee, $date, $officer->id);

        $access = app(UserAccessService::class);
        $ids = $access->reportableShakhaIds($officer, $month, $year);

        $this->assertSame([(int) $allocated->id], $ids);
        $this->assertTrue($access->canStartReportForShakha($officer, (int) $allocated->id, $month, $year));
        $this->assertFalse($access->canStartReportForShakha($officer, (int) $other->id, $month, $year));

        Livewire::actingAs($officer)
            ->test(MakeAuditReport::class)
            ->set('report_month', $month)
            ->set('report_year', $year)
            ->assertSee('Allocated Report Shakha')
            ->assertDontSee('Other Report Shakha')
            ->set('shakha_id', $other->id)
            ->call('startReport')
            ->assertHasErrors('shakha_id');
    }

    public function test_senior_officer_with_view_all_still_limited_to_allocations_for_reports(): void
    {
        $position = Position::query()->create([
            'serial' => 6,
            'title' => 'Senior Officer Audit',
            'slug' => 'senior-officer-audit',
            'color' => '#667085',
        ]);
        $employee = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Senior Reporter',
            'email' => 'senior.reporter@bynnasaudit.com',
            'sort_order' => 1,
        ]);
        $senior = User::factory()->create([
            'name' => 'Senior Reporter',
            'email' => 'senior.reporter@bynnasaudit.com',
            'employee_id' => $employee->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $senior->assignRole('senior_officer');

        $area = Area::query()->create(['name' => 'S Area', 'division' => 'Dhaka', 'status' => 'active']);
        $mine = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Senior Allocated Shakha',
            'code' => 'SAS-1',
            'status' => 'active',
        ]);
        $other = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Not Allocated To Senior',
            'code' => 'NAS-1',
            'status' => 'active',
        ]);

        $today = now('Asia/Dhaka');
        $this->allocateVisit($mine, $employee, $today->toDateString(), $senior->id);

        $access = app(UserAccessService::class);
        $this->assertTrue($access->canAccessAllShakhas($senior));
        $this->assertFalse($access->canBrowseAllShakhasForReports($senior));
        $this->assertTrue($access->canStartReportForShakha($senior, (int) $mine->id, (int) $today->month, (int) $today->year));
        $this->assertFalse($access->canStartReportForShakha($senior, (int) $other->id, (int) $today->month, (int) $today->year));
    }

    public function test_audit_manager_with_employee_only_sees_allocated_shakhas_for_reports(): void
    {
        $position = Position::query()->create([
            'serial' => 4,
            'title' => 'Assistant Director Audit',
            'slug' => 'assistant-director-audit',
            'color' => '#667085',
        ]);
        $employee = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Ayesha Manager',
            'email' => 'ayesha.reports@bynnasaudit.com',
            'sort_order' => 1,
        ]);
        $manager = User::factory()->create([
            'name' => 'Ayesha Manager',
            'email' => 'ayesha.reports@bynnasaudit.com',
            'employee_id' => $employee->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $manager->assignRole('audit_manager');

        $area = Area::query()->create(['name' => 'Mgr Report Area', 'division' => 'Dhaka', 'status' => 'active']);
        $mine = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Manager Allocated Branch',
            'code' => 'MAB-1',
            'status' => 'active',
        ]);
        $other = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Not Hers Branch',
            'code' => 'NHB-1',
            'status' => 'active',
        ]);

        $today = now('Asia/Dhaka');
        $this->allocateVisit($mine, $employee, $today->toDateString(), $manager->id);

        $access = app(UserAccessService::class);
        $this->assertTrue($manager->can('audits.manage'));
        $this->assertFalse($access->canBrowseAllShakhasForReports($manager));
        $this->assertTrue($access->canStartReportForShakha($manager, (int) $mine->id, (int) $today->month, (int) $today->year));
        $this->assertFalse($access->canStartReportForShakha($manager, (int) $other->id, (int) $today->month, (int) $today->year));

        Livewire::actingAs($manager)
            ->test(MakeAuditReport::class)
            ->set('report_month', (int) $today->month)
            ->set('report_year', (int) $today->year)
            ->assertSee('Manager Allocated Branch')
            ->assertDontSee('Not Hers Branch');
    }

    public function test_project_location_visit_appears_in_report_picker_and_can_start(): void
    {
        $position = Position::query()->create([
            'serial' => 7,
            'title' => 'Audit Officer',
            'slug' => 'ao-project-alloc',
            'color' => '#667085',
        ]);
        $employee = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Project Officer',
            'email' => 'project.officer@bynnasaudit.com',
            'sort_order' => 1,
        ]);
        $officer = User::factory()->create([
            'name' => 'Project Officer',
            'email' => 'project.officer@bynnasaudit.com',
            'employee_id' => $employee->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $officer->assignRole('audit_officer');

        $project = \App\Models\Project::query()->create([
            'name' => 'DSK-Hospital Dhaka',
            'status' => 'active',
        ]);
        $location = \App\Models\ProjectLocation::query()->create([
            'project_id' => $project->id,
            'name' => 'Shyamoli, Dhaka',
            'division' => 'Dhaka',
            'status' => 'active',
        ]);

        $today = now('Asia/Dhaka');
        $month = (int) $today->month;
        $year = (int) $today->year;
        $date = $today->toDateString();

        $fyStartYear = $month >= 7 ? $year : $year - 1;
        $fyLabel = $fyStartYear.'-'.($fyStartYear + 1);
        $monthIndex = ($month + 5) % 12;

        $activity = ActivityType::query()->create([
            'name' => 'Project Visit',
            'slug' => 'project-visit-'.uniqid(),
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $plan = AuditPlan::query()->firstOrCreate(
            ['fy_label' => $fyLabel],
            [
                'name' => $fyLabel,
                'start_date' => $fyStartYear.'-07-01',
                'end_date' => ($fyStartYear + 1).'-06-30',
                'status' => 'active',
                'generated_at' => now(),
            ]
        );
        $item = MonthlyWorkItem::query()->create([
            'audit_plan_id' => $plan->id,
            'fy_label' => $fyLabel,
            'month_index' => $monthIndex,
            'category' => 'project_visit',
            'activity_type_id' => $activity->id,
            'schedulable_type' => \App\Models\ProjectLocation::class,
            'schedulable_id' => $location->id,
            'source' => MonthlyWorkItem::SOURCE_YEARLY,
            'status' => MonthlyWorkItem::STATUS_UNASSIGNED,
            'entity_label' => 'DSK-Hospital Dhaka — Shyamoli, Dhaka',
        ]);

        app(MonthlyWorklistService::class)->assign($item, [
            'employee_ids' => [$employee->id],
            'start_date' => $date,
            'end_date' => $date,
            'visit_date' => $date,
            'lock_schedule' => false,
        ], $officer->id);

        $access = app(UserAccessService::class);
        $this->assertSame([], $access->reportableShakhaIds($officer, $month, $year));
        $this->assertSame([(int) $location->id], $access->reportableProjectLocationIds($officer, $month, $year));
        $this->assertTrue($access->canStartReportForProjectLocation($officer, (int) $location->id, $month, $year));

        Livewire::actingAs($officer)
            ->test(MakeAuditReport::class)
            ->set('report_month', $month)
            ->set('report_year', $year)
            ->assertDontSee('No allocated shakha or project visit for this month')
            ->call('selectReportEntity', 'location:'.$location->id)
            ->call('startReport')
            ->assertSet('step', 'wizard')
            ->assertSet('project_location_id', (int) $location->id)
            ->assertSet('shakha_id', null);

        $report = \App\Models\AuditReport::query()->where('project_location_id', $location->id)->first();
        $this->assertNotNull($report);
        $this->assertNull($report->shakha_id);
        $this->assertStringContainsString('DSK-Hospital', (string) $report->shakha_display_name);
    }

    protected function allocateVisit(Shakha $shakha, Employee $employee, string $date, int $actorId): void
    {
        $fyStartYear = (int) now('Asia/Dhaka')->month >= 7
            ? (int) now('Asia/Dhaka')->year
            : (int) now('Asia/Dhaka')->year - 1;
        $fyLabel = $fyStartYear.'-'.($fyStartYear + 1);
        $monthIndex = ((int) now('Asia/Dhaka')->month + 5) % 12;

        $activity = ActivityType::query()->create([
            'name' => 'Audit',
            'slug' => 'audit-report-alloc-'.uniqid(),
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $plan = AuditPlan::query()->firstOrCreate(
            ['fy_label' => $fyLabel],
            [
                'name' => $fyLabel,
                'start_date' => $fyStartYear.'-07-01',
                'end_date' => ($fyStartYear + 1).'-06-30',
                'status' => 'active',
                'generated_at' => now(),
            ]
        );
        $item = MonthlyWorkItem::query()->create([
            'audit_plan_id' => $plan->id,
            'fy_label' => $fyLabel,
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
            'start_date' => $date,
            'end_date' => $date,
            'visit_date' => $date,
            'lock_schedule' => false,
        ], $actorId);
    }
}
