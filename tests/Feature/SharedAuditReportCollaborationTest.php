<?php

namespace Tests\Feature;

use App\Livewire\MakeAuditReport;
use App\Models\ActivityType;
use App\Models\Area;
use App\Models\AuditPlan;
use App\Models\AuditReport;
use App\Models\Employee;
use App\Models\MonthlyWorkItem;
use App\Models\Position;
use App\Models\Shakha;
use App\Models\User;
use App\Services\MonthlyWorklistService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SharedAuditReportCollaborationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_two_visit_auditors_share_one_report_draft(): void
    {
        [$shakha, $userA, $userB] = $this->makeJointVisitTeam();

        Livewire::actingAs($userA)
            ->test(MakeAuditReport::class)
            ->set('report_month', 9)
            ->set('report_year', 2025)
            ->call('startReport', $shakha->id)
            ->assertHasNoErrors()
            ->assertSet('step', 'wizard');

        $this->assertSame(1, AuditReport::query()->count());
        $report = AuditReport::query()->firstOrFail();
        $this->assertTrue($report->collaborators()->where('users.id', $userB->id)->exists());
        $this->assertStringContainsString($userA->name, (string) $report->auditor_name);
        $this->assertStringContainsString($userB->name, (string) $report->auditor_name);

        Livewire::actingAs($userB)
            ->test(MakeAuditReport::class)
            ->set('report_month', 9)
            ->set('report_year', 2025)
            ->call('startReport', $shakha->id)
            ->assertHasNoErrors()
            ->assertSet('step', 'wizard')
            ->assertSet('reportId', $report->id);

        $this->assertSame(1, AuditReport::query()->count());

        Livewire::actingAs($userB)
            ->test(MakeAuditReport::class)
            ->call('resumeReport', $report->id)
            ->set('auditor_designation', 'সহকারী নিরীক্ষা কর্মকর্তা')
            ->call('autoSaveDraft')
            ->assertHasNoErrors();

        $report->refresh();
        $this->assertSame('সহকারী নিরীক্ষা কর্মকর্তা', $report->auditor_designation);
    }

    public function test_collaborator_cannot_delete_shared_draft(): void
    {
        [$shakha, $userA, $userB] = $this->makeJointVisitTeam();

        Livewire::actingAs($userA)
            ->test(MakeAuditReport::class)
            ->set('report_month', 9)
            ->set('report_year', 2025)
            ->call('startReport', $shakha->id);

        $report = AuditReport::query()->firstOrFail();

        try {
            Livewire::actingAs($userB)
                ->test(MakeAuditReport::class)
                ->call('deleteDraft', $report->id);
            $this->fail('Collaborator should not be able to delete the shared draft.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            // Owner-only delete scope hides the draft from collaborators.
        }

        $this->assertDatabaseHas('audit_reports', ['id' => $report->id]);
    }

    /**
     * @return array{0: Shakha, 1: User, 2: User}
     */
    private function makeJointVisitTeam(): array
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();

        $position = Position::query()->create([
            'serial' => 90,
            'title' => 'Audit Officer',
            'slug' => 'ao-shared-report',
            'color' => '#4C6FFF',
        ]);

        $employeeA = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Auditor Alpha',
            'sort_order' => 1,
        ]);
        $employeeB = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Auditor Beta',
            'sort_order' => 2,
        ]);

        $userA = User::factory()->create([
            'name' => 'Auditor Alpha',
            'email' => 'alpha.shared@bynnasaudit.com',
            'employee_id' => $employeeA->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $userA->givePermissionTo(['audits.create', 'audits.manage']);

        $userB = User::factory()->create([
            'name' => 'Auditor Beta',
            'email' => 'beta.shared@bynnasaudit.com',
            'employee_id' => $employeeB->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $userB->givePermissionTo(['audits.create', 'audits.manage']);

        $area = Area::query()->create(['name' => 'Shared Area', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Shared Shakha',
            'code' => 'SHR-1',
            'status' => 'active',
        ]);

        $activity = ActivityType::query()->create([
            'name' => 'Audit',
            'slug' => 'audit-shared-report',
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
            'fy_label' => '2025-2026',
            'month_index' => 2, // Sep 2025
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
            'start_date' => '2025-09-10',
            'end_date' => '2025-09-12',
            'visit_date' => '2025-09-10',
        ], $admin->id);

        return [$shakha, $userA, $userB];
    }
}
