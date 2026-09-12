<?php

namespace Tests\Feature;

use App\Livewire\MakeAuditReport;
use App\Models\ActivityType;
use App\Models\Area;
use App\Models\AuditChecklistFormat;
use App\Models\AuditChecklistSubmission;
use App\Models\AuditPlan;
use App\Models\AuditReport;
use App\Models\Employee;
use App\Models\MonthlyAssignment;
use App\Models\MonthlyWorkItem;
use App\Models\Position;
use App\Models\Shakha;
use App\Models\User;
use App\Services\ChecklistReportInfluenceService;
use App\Services\VisitAuditWorkService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChecklistFirstVisitReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_start_work_creates_draft_and_opens_checklist(): void
    {
        [$user, $assignment, $shakha] = $this->makeAssignedVisit();

        $this->actingAs($user)
            ->get(route('monthly-visits.start-work', ['assignment' => $assignment, 'target' => 'checklist']))
            ->assertRedirect();

        $report = AuditReport::query()->where('shakha_id', $shakha->id)->first();
        $this->assertNotNull($report);
        $this->assertSame($assignment->id, (int) $report->monthly_assignment_id);
        $this->assertTrue($report->checklistReady());
    }

    public function test_report_target_opens_report_without_checklist_gate(): void
    {
        [$user, $assignment, $shakha] = $this->makeAssignedVisit();

        $response = $this->actingAs($user)
            ->get(route('monthly-visits.start-work', ['assignment' => $assignment, 'target' => 'report']));

        $report = AuditReport::query()->where('shakha_id', $shakha->id)->firstOrFail();
        $response->assertRedirect(route('audits.index', ['report' => $report->id]));
    }

    public function test_project_location_start_work_creates_draft(): void
    {
        [$user, $assignment, $location] = $this->makeAssignedProjectVisit();

        $response = $this->actingAs($user)
            ->get(route('monthly-visits.start-work', ['assignment' => $assignment, 'target' => 'report']));

        $report = AuditReport::query()->where('project_location_id', $location->id)->firstOrFail();
        $this->assertNull($report->shakha_id);
        $this->assertSame($assignment->id, (int) $report->monthly_assignment_id);
        $response->assertRedirect(route('audits.index', ['report' => $report->id]));
    }

    public function test_findings_editable_without_checklist_evidence(): void
    {
        $user = $this->makeAuditUser();
        $shakha = $this->makeShakha();
        $report = AuditReport::query()->create([
            'user_id' => $user->id,
            'shakha_id' => $shakha->id,
            'report_month' => 9,
            'report_year' => 2026,
            'status' => AuditReport::STATUS_DRAFT,
            'last_saved_at' => now(),
            'pages_data' => ['page4' => ['reportBlocks' => []]],
        ]);

        Livewire::actingAs($user)
            ->test(MakeAuditReport::class)
            ->call('resumeReport', $report->id)
            ->assertSet('checklistReady', true)
            ->assertSet('step', 'wizard')
            ->set('activeTab', 'page4')
            ->assertSet('activeTab', 'page4');
    }

    public function test_evidence_does_not_auto_seed_and_summary_adds_finding_pack(): void
    {
        $user = $this->makeAuditUser();
        $shakha = $this->makeShakha();
        $report = AuditReport::query()->create([
            'user_id' => $user->id,
            'shakha_id' => $shakha->id,
            'report_month' => 9,
            'report_year' => 2026,
            'status' => AuditReport::STATUS_DRAFT,
            'last_saved_at' => now(),
            'pages_data' => ['page4' => ['reportBlocks' => []]],
        ]);

        $visitWork = app(VisitAuditWorkService::class);
        $visitWork->ensureFormatsExist();
        $formats = AuditChecklistFormat::query()->whereIn('code', ['format-1', 'format-2'])->orderBy('format_number')->get();
        $this->assertCount(2, $formats);
        $report->checklistFormats()->sync($formats->pluck('id')->all());

        $format1 = $formats->firstWhere('code', 'format-1');
        $submission = AuditChecklistSubmission::query()->create([
            'user_id' => $user->id,
            'audit_report_id' => $report->id,
            'audit_checklist_format_id' => $format1->id,
            'heading' => $format1->heading,
            'payload' => [
                'section_summaries' => [
                    'formation' => 'গঠন প্রক্রিয়ায় অনিয়ম পাওয়া গেছে।',
                    'closure' => 'বন্ধকরণে অনুমোদন নেই।',
                ],
            ],
            'status' => 'evidence',
            'saved_at' => now(),
        ]);

        $this->assertSame(0, app(ChecklistReportInfluenceService::class)->seedFromSubmission($submission->load('format')));
        $this->assertSame([], $report->fresh()->pages_data['page4']['reportBlocks'] ?? []);

        $service = app(ChecklistReportInfluenceService::class);
        $service->addSummaryToReport(
            $report->fresh(),
            $format1,
            'গঠন প্রক্রিয়ায় অনিয়ম পাওয়া গেছে।',
            ChecklistReportInfluenceService::sectionSummarySeedKey('format-1', 'formation'),
            'সমিতি গঠন',
            false,
        );
        $service->addSummaryToReport(
            $report->fresh(),
            $format1,
            'বন্ধকরণে অনুমোদন নেই।',
            ChecklistReportInfluenceService::sectionSummarySeedKey('format-1', 'closure'),
            'সমিতি বন্ধ /একত্রিকরণ',
            false,
        );

        $format2 = $formats->firstWhere('code', 'format-2');
        $service->addSummaryToReport(
            $report->fresh(),
            $format2,
            'সদস্য ভর্তিতে ঘাটতি।',
            ChecklistReportInfluenceService::formatSummarySeedKey('format-2'),
            (string) $format2->heading,
            false,
        );

        $blocks = $report->fresh()->pages_data['page4']['reportBlocks'] ?? [];
        $sections = collect($blocks)->where('type', 'section')->values();
        $findings = collect($blocks)->where('type', 'finding')->values();
        $observations = collect($blocks)->where('type', 'observation')
            ->filter(fn ($b) => str_contains((string) ($b['label'] ?? ''), 'পর্যবেক্ষণ'))
            ->values();

        $this->assertCount(2, $sections); // one বিভাগ per checklist format
        $this->assertCount(3, $findings);
        $this->assertSame('১.১', $findings[0]['serial']);
        $this->assertSame('১.২', $findings[1]['serial']);
        $this->assertSame('২.১', $findings[2]['serial']);
        $this->assertSame('গঠন প্রক্রিয়ায় অনিয়ম পাওয়া গেছে।', $observations[0]['body']);
        $this->assertNotEmpty($observations[0]['checklist_source_detail'] ?? null);
        $this->assertTrue((bool) ($findings[0]['checklist_pack'] ?? false));

        // Source meta must survive normalizeReportBlocks (Livewire hydrate path).
        $component = Livewire::actingAs($user)
            ->test(MakeAuditReport::class)
            ->call('resumeReport', $report->id);
        $liveBlocks = $component->get('reportBlocks');
        $liveObs = collect($liveBlocks)->first(function ($b) {
            return ($b['type'] ?? '') === 'observation'
                && str_contains((string) ($b['label'] ?? ''), 'পর্যবেক্ষণ')
                && str_contains((string) ($b['body'] ?? ''), 'গঠন প্রক্রিয়ায়');
        });
        $this->assertNotNull($liveObs);
        $this->assertNotEmpty($liveObs['checklist_source_detail'] ?? null);

        // Criteria / risk / recommendation empty without AI
        $risk = collect($blocks)->first(fn ($b) => str_contains((string) ($b['label'] ?? ''), 'ঝুঁকি'));
        $this->assertSame('', (string) ($risk['body'] ?? 'x'));
    }


    /**
     * @return array{0: User, 1: MonthlyAssignment, 2: Shakha}
     */
    private function makeAssignedVisit(): array
    {
        $position = Position::query()->create([
            'serial' => 91,
            'title' => 'Audit Officer',
            'slug' => 'ao-checklist-first',
            'color' => '#4C6FFF',
        ]);
        $employee = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Visit Auditor',
            'sort_order' => 1,
        ]);
        $user = User::factory()->create([
            'name' => 'Visit Auditor',
            'employee_id' => $employee->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $user->givePermissionTo(['audits.create', 'monthly_visits.execute', 'monthly_visits.manage']);

        $area = Area::query()->create(['name' => 'CL Area', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'CL Shakha',
            'code' => 'CL-1',
            'status' => 'active',
        ]);

        $activity = ActivityType::query()->create([
            'name' => 'Audit',
            'slug' => 'audit-checklist-first',
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

        // Sep 2025 => month_index 2 in FY 2025-2026
        $item = MonthlyWorkItem::query()->create([
            'audit_plan_id' => $plan->id,
            'fy_label' => '2025-2026',
            'month_index' => 2,
            'activity_type_id' => $activity->id,
            'category' => 'regular',
            'schedulable_type' => Shakha::class,
            'schedulable_id' => $shakha->id,
            'entity_label' => $shakha->name,
            'status' => MonthlyWorkItem::STATUS_ASSIGNED,
        ]);

        $assignment = MonthlyAssignment::query()->create([
            'monthly_work_item_id' => $item->id,
            'employee_id' => $employee->id,
            'start_date' => '2025-09-01',
            'end_date' => '2025-09-05',
            'duration_days' => 5,
            'duration_mode' => 'working',
            'assigned_by' => $user->id,
        ]);
        $assignment->visitors()->sync([$employee->id => ['sort_order' => 0]]);

        return [$user, $assignment, $shakha];
    }

    /**
     * @return array{0:User,1:MonthlyAssignment,2:\App\Models\ProjectLocation}
     */
    private function makeAssignedProjectVisit(): array
    {
        $position = Position::query()->create([
            'serial' => 8,
            'title' => 'Project Auditor',
            'slug' => 'project-auditor-cl',
            'color' => '#667085',
        ]);
        $employee = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Project Visit Auditor',
            'email' => 'project.visit@bynnasaudit.com',
            'sort_order' => 1,
        ]);
        $user = User::factory()->create([
            'name' => 'Project Visit Auditor',
            'employee_id' => $employee->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $user->givePermissionTo(['audits.create', 'monthly_visits.execute', 'monthly_visits.manage']);

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

        $activity = ActivityType::query()->create([
            'name' => 'Project Audit',
            'slug' => 'project-audit-cl-'.uniqid(),
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $plan = AuditPlan::query()->firstOrCreate(
            ['fy_label' => '2025-2026'],
            [
                'name' => 'FY 2025-2026',
                'start_date' => '2025-07-01',
                'end_date' => '2026-06-30',
                'status' => 'active',
                'generated_at' => now(),
            ]
        );

        $item = MonthlyWorkItem::query()->create([
            'audit_plan_id' => $plan->id,
            'fy_label' => '2025-2026',
            'month_index' => 2,
            'activity_type_id' => $activity->id,
            'category' => 'project_visit',
            'schedulable_type' => \App\Models\ProjectLocation::class,
            'schedulable_id' => $location->id,
            'entity_label' => 'DSK-Hospital Dhaka — Shyamoli, Dhaka',
            'status' => MonthlyWorkItem::STATUS_ASSIGNED,
        ]);

        $assignment = MonthlyAssignment::query()->create([
            'monthly_work_item_id' => $item->id,
            'employee_id' => $employee->id,
            'start_date' => '2025-09-01',
            'end_date' => '2025-09-05',
            'duration_days' => 5,
            'duration_mode' => 'working',
            'assigned_by' => $user->id,
        ]);
        $assignment->visitors()->sync([$employee->id => ['sort_order' => 0]]);

        return [$user, $assignment, $location];
    }

    private function makeAuditUser(): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_superadmin' => true,
            'is_active' => true,
        ]);
        $user->givePermissionTo(['audits.create', 'audits.manage']);

        return $user;
    }

    private function makeShakha(): Shakha
    {
        $area = Area::query()->create([
            'name' => 'Gate Area',
            'division' => 'Dhaka',
            'status' => 'active',
        ]);

        return Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Gate Shakha',
            'code' => 'GATE-1',
            'status' => 'active',
        ]);
    }
}
