<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AuditReport;
use App\Models\AuditReviewerAssignment;
use App\Models\Shakha;
use App\Models\User;
use App\Services\AuditReportReviewService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AuditReportReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_save_reviewer_assignment_map(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $auditor = User::factory()->create(['email_verified_at' => now()]);
        $auditor->assignRole('audit_officer');
        $reviewer = User::factory()->create(['email_verified_at' => now()]);
        $reviewer->assignRole('senior_officer');

        $this->actingAs($admin)
            ->post(route('audit-review.assignments.save'), [
                'assignments' => [
                    [
                        'auditor_user_id' => $auditor->id,
                        'reviewer_user_id' => $reviewer->id,
                    ],
                ],
            ])
            ->assertRedirect(route('audit-review.assignments'));

        $this->assertDatabaseHas('audit_reviewer_assignments', [
            'auditor_user_id' => $auditor->id,
            'reviewer_user_id' => $reviewer->id,
        ]);
    }

    public function test_submit_requires_assignment_then_round_trip_and_lock(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $auditor = User::factory()->create(['email_verified_at' => now(), 'name' => 'Maker']);
        $auditor->assignRole('audit_officer');
        $reviewer = User::factory()->create(['email_verified_at' => now(), 'name' => 'Reviewer']);
        $reviewer->assignRole('senior_officer');

        $area = Area::query()->create(['name' => 'Metro', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Review Branch',
            'code' => 'RV-1',
            'status' => 'active',
        ]);

        $report = AuditReport::query()->create([
            'user_id' => $auditor->id,
            'shakha_id' => $shakha->id,
            'report_month' => 9,
            'report_year' => 2026,
            'status' => AuditReport::STATUS_COMPLETED,
            'progress_pct' => 100,
            'completed_at' => now(),
            'pages_data' => [],
        ]);

        $service = app(AuditReportReviewService::class);

        try {
            $service->submitForReview($report, $auditor, true);
            $this->fail('Expected ValidationException without assignment');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('report', $e->errors());
        }

        AuditReviewerAssignment::query()->create([
            'auditor_user_id' => $auditor->id,
            'reviewer_user_id' => $reviewer->id,
            'assigned_by' => $admin->id,
        ]);

        $service->submitForReview($report->fresh(), $auditor, true, 'Please check');
        $report->refresh();
        $this->assertSame(AuditReport::STATUS_IN_REVIEW, $report->status);
        $this->assertSame((int) $reviewer->id, (int) $report->reviewer_user_id);
        $this->assertTrue($report->review_cc_superadmin);
        $this->assertTrue($service->isLocked($report));
        $this->assertFalse($service->isEditableByMaker($report));

        $this->actingAs($reviewer)
            ->post(route('audit-review.request-changes', $report), [
                'body' => 'Fix VAT observation wording.',
            ])
            ->assertRedirect(route('audit-review.index', ['tab' => 'inbox']));

        $report->refresh();
        $this->assertSame(AuditReport::STATUS_CHANGES_REQUESTED, $report->status);
        $this->assertTrue($service->isEditableByMaker($report));

        $service->submitForReview($report, $auditor, false, 'Fixed');
        $report->refresh();
        $this->assertSame(AuditReport::STATUS_IN_REVIEW, $report->status);
        $this->assertSame(2, (int) $report->review_round);

        $this->actingAs($reviewer)
            ->post(route('audit-review.approve', $report), [
                'note' => 'Looks good',
            ])
            ->assertRedirect(route('audit-review.index', ['tab' => 'reviewed']));

        $report->refresh();
        $this->assertSame(AuditReport::STATUS_REVIEWED, $report->status);
        $this->assertNotNull($report->reviewed_at);
        $this->assertTrue($service->isLocked($report));
        $this->assertFalse($service->isEditableByMaker($report));

        $this->actingAs($admin)
            ->get(route('audit-review.show', $report))
            ->assertOk()
            ->assertSee('Reviewed');
    }

    public function test_superadmin_can_act_when_cc_flag_set(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $auditor = User::factory()->create(['email_verified_at' => now()]);
        $auditor->assignRole('audit_officer');
        $reviewer = User::factory()->create(['email_verified_at' => now()]);
        $reviewer->assignRole('senior_officer');

        AuditReviewerAssignment::query()->create([
            'auditor_user_id' => $auditor->id,
            'reviewer_user_id' => $reviewer->id,
            'assigned_by' => $admin->id,
        ]);

        $area = Area::query()->create(['name' => 'Metro', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'CC Branch',
            'code' => 'CC-1',
            'status' => 'active',
        ]);

        $report = AuditReport::query()->create([
            'user_id' => $auditor->id,
            'shakha_id' => $shakha->id,
            'report_month' => 9,
            'report_year' => 2026,
            'status' => AuditReport::STATUS_COMPLETED,
            'progress_pct' => 100,
            'pages_data' => [],
        ]);

        app(AuditReportReviewService::class)->submitForReview($report, $auditor, true);
        $report->refresh();

        $this->actingAs($admin)
            ->post(route('audit-review.approve', $report), ['note' => 'SA approve'])
            ->assertRedirect(route('audit-review.index', ['tab' => 'reviewed']));

        $this->assertSame(AuditReport::STATUS_REVIEWED, $report->fresh()->status);
    }

    public function test_can_send_directly_to_superadmin_without_assignment(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $auditor = User::factory()->create(['email_verified_at' => now(), 'name' => 'Direct Maker']);
        $auditor->assignRole('audit_officer');

        $area = Area::query()->create(['name' => 'SA Area', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'SA Branch',
            'code' => 'SA-1',
            'status' => 'active',
        ]);

        $report = AuditReport::query()->create([
            'user_id' => $auditor->id,
            'shakha_id' => $shakha->id,
            'report_month' => 9,
            'report_year' => 2026,
            'status' => AuditReport::STATUS_COMPLETED,
            'progress_pct' => 100,
            'completed_at' => now(),
            'pages_data' => [],
        ]);

        $this->actingAs($auditor)
            ->post(route('audit-review.submit', $report), [
                'destination' => 'superadmin',
                'note' => 'Please review urgently',
            ])
            ->assertRedirect(route('audit-review.show', $report));

        $report->refresh();
        $this->assertSame(AuditReport::STATUS_IN_REVIEW, $report->status);
        $this->assertSame((int) $admin->id, (int) $report->reviewer_user_id);
        $this->assertTrue($report->review_cc_superadmin);

        $this->actingAs($admin)
            ->post(route('audit-review.approve', $report), ['note' => 'Approved by SA'])
            ->assertRedirect(route('audit-review.index', ['tab' => 'reviewed']));

        $this->assertSame(AuditReport::STATUS_REVIEWED, $report->fresh()->status);
    }

    public function test_reviewer_can_open_full_report_document(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $auditor = User::factory()->create(['email_verified_at' => now(), 'name' => 'Report Maker']);
        $auditor->assignRole('audit_officer');
        $reviewer = User::factory()->create(['email_verified_at' => now(), 'name' => 'Full Reviewer']);
        $reviewer->assignRole('senior_officer');

        AuditReviewerAssignment::query()->create([
            'auditor_user_id' => $auditor->id,
            'reviewer_user_id' => $reviewer->id,
            'assigned_by' => $admin->id,
        ]);

        $area = Area::query()->create(['name' => 'Doc Area', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Document Branch',
            'code' => 'DOC-1',
            'status' => 'active',
        ]);

        $report = AuditReport::query()->create([
            'user_id' => $auditor->id,
            'shakha_id' => $shakha->id,
            'report_month' => 9,
            'report_year' => 2026,
            'status' => AuditReport::STATUS_COMPLETED,
            'progress_pct' => 100,
            'completed_at' => now(),
            'memo_no' => 'অডিট/শাখা - DOC-1/2026',
            'shakha_display_name' => 'Document Branch',
            'auditor_name' => 'Report Maker',
            'pages_data' => [
                'cover' => [],
                'page2' => [],
                'page4' => ['reportBlocks' => []],
            ],
        ]);

        app(AuditReportReviewService::class)->submitForReview($report, $auditor, false);
        $report->refresh();

        $this->actingAs($reviewer)
            ->get(route('audit-review.show', $report))
            ->assertOk()
            ->assertSee('Full report')
            ->assertSee('Document Branch')
            ->assertSee('অডিট/শাখা - DOC-1/2026', false)
            ->assertDontSee('<iframe', false);

        $pdf = $this->actingAs($reviewer)
            ->get(route('audit-review.document', $report));

        $pdf->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $pdf->headers->get('Content-Type'));
        $this->assertNotSame('', $pdf->getContent());
    }

    public function test_reviewer_can_mark_text_with_comment(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $auditor = User::factory()->create(['email_verified_at' => now()]);
        $auditor->assignRole('audit_officer');
        $reviewer = User::factory()->create(['email_verified_at' => now(), 'name' => 'Marker']);
        $reviewer->assignRole('senior_officer');

        AuditReviewerAssignment::query()->create([
            'auditor_user_id' => $auditor->id,
            'reviewer_user_id' => $reviewer->id,
            'assigned_by' => $admin->id,
        ]);

        $area = Area::query()->create(['name' => 'Mark Area', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Mark Branch',
            'code' => 'MRK-1',
            'status' => 'active',
        ]);

        $report = AuditReport::query()->create([
            'user_id' => $auditor->id,
            'shakha_id' => $shakha->id,
            'report_month' => 9,
            'report_year' => 2026,
            'status' => AuditReport::STATUS_COMPLETED,
            'progress_pct' => 100,
            'completed_at' => now(),
            'memo_no' => 'অডিট/শাখা - MRK-1/2026',
            'pages_data' => ['cover' => [], 'page4' => ['reportBlocks' => []]],
        ]);

        app(AuditReportReviewService::class)->submitForReview($report, $auditor, false);
        $report->refresh();

        $this->actingAs($reviewer)
            ->get(route('audit-review.show', $report))
            ->assertOk()
            ->assertSee('Draw area')
            ->assertSee('Marks and comments');

        $this->actingAs($reviewer)
            ->postJson(route('audit-review.annotations.store', $report), [
                'type' => 'text',
                'quote' => 'Mark Branch',
                'prefix' => 'name ',
                'suffix' => ' audit',
                'body' => 'Please correct this name',
                'color' => 'rose',
            ])
            ->assertCreated()
            ->assertJsonPath('annotation.color', 'rose')
            ->assertJsonPath('annotation.body', 'Please correct this name');

        $this->assertDatabaseHas('audit_report_review_annotations', [
            'audit_report_id' => $report->id,
            'user_id' => $reviewer->id,
            'quote' => 'Mark Branch',
            'color' => 'rose',
            'type' => 'text',
        ]);

        $this->actingAs($reviewer)
            ->postJson(route('audit-review.annotations.store', $report), [
                'type' => 'area',
                'body' => 'Wrong amount in this section',
                'color' => 'orange',
                'rect_x' => 10.5,
                'rect_y' => 20.25,
                'rect_w' => 40,
                'rect_h' => 15.5,
            ])
            ->assertCreated()
            ->assertJsonPath('annotation.type', 'area')
            ->assertJsonPath('annotation.body', 'Wrong amount in this section');

        $this->assertDatabaseHas('audit_report_review_annotations', [
            'audit_report_id' => $report->id,
            'type' => 'area',
            'body' => 'Wrong amount in this section',
        ]);

        $annotationId = (int) $report->reviewAnnotations()->where('type', 'text')->value('id');

        $this->actingAs($reviewer)
            ->deleteJson(route('audit-review.annotations.destroy', [$report, $annotationId]))
            ->assertOk();

        $this->assertDatabaseMissing('audit_report_review_annotations', [
            'id' => $annotationId,
        ]);

        $areaId = (int) $report->reviewAnnotations()->where('type', 'area')->value('id');

        $this->actingAs($reviewer)
            ->patchJson(route('audit-review.annotations.update', [$report, $areaId]), [
                'body' => 'Updated area note',
                'color' => 'sky',
            ])
            ->assertOk()
            ->assertJsonPath('annotation.body', 'Updated area note')
            ->assertJsonPath('annotation.color', 'sky');

        $this->assertDatabaseHas('audit_report_review_annotations', [
            'id' => $areaId,
            'body' => 'Updated area note',
            'color' => 'sky',
        ]);
    }

    public function test_review_done_stays_editable_until_sent_to_maker(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $auditor = User::factory()->create([
            'email_verified_at' => now(),
            'name' => 'Maker Auditor',
            'email' => 'maker-auditor@example.com',
        ]);
        $auditor->assignRole('audit_officer');
        $reviewer = User::factory()->create([
            'email_verified_at' => now(),
            'name' => 'Done Reviewer',
            'email' => 'done-reviewer@example.com',
        ]);
        $reviewer->assignRole('senior_officer');

        AuditReviewerAssignment::query()->create([
            'auditor_user_id' => $auditor->id,
            'reviewer_user_id' => $reviewer->id,
            'assigned_by' => $admin->id,
        ]);

        $area = Area::query()->create(['name' => 'Done Area', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Done Branch',
            'code' => 'DN-1',
            'status' => 'active',
        ]);

        $report = AuditReport::query()->create([
            'user_id' => $auditor->id,
            'shakha_id' => $shakha->id,
            'report_month' => 9,
            'report_year' => 2026,
            'status' => AuditReport::STATUS_COMPLETED,
            'progress_pct' => 100,
            'completed_at' => now(),
            'memo_no' => 'অডিট/শাখা - DN-1/2026',
            'pages_data' => ['cover' => [], 'page4' => ['reportBlocks' => []]],
        ]);

        app(AuditReportReviewService::class)->submitForReview($report, $auditor, false);
        $report->refresh();

        $this->actingAs($reviewer)
            ->post(route('audit-review.done', $report))
            ->assertRedirect(route('audit-review.index', ['tab' => 'reviewed']));

        $report->refresh();
        $this->assertSame(AuditReport::STATUS_IN_REVIEW, $report->status);
        $this->assertNotNull($report->review_ready_at);
        \Illuminate\Support\Facades\Mail::assertNothingSent();

        $this->actingAs($reviewer)
            ->get(route('audit-review.index', ['tab' => 'reviewed']))
            ->assertOk()
            ->assertSee('Edit marks')
            ->assertSee('Send to maker')
            ->assertSee('Confirm');

        $this->actingAs($reviewer)
            ->postJson(route('audit-review.annotations.store', $report), [
                'type' => 'text',
                'quote' => 'Still editable',
                'body' => 'Added after review done',
                'color' => 'yellow',
            ])
            ->assertCreated();

        $download = $this->actingAs($reviewer)
            ->get(route('audit-review.download', $report));
        $download->assertOk();
        $this->assertStringContainsString('zip', (string) $download->headers->get('Content-Type'));

        $this->actingAs($reviewer)
            ->post(route('audit-review.send-to-maker', $report))
            ->assertRedirect(route('audit-review.index', ['tab' => 'reviewed']));

        $report->refresh();
        $this->assertSame(AuditReport::STATUS_CHANGES_REQUESTED, $report->status);
        $this->assertNotNull($report->review_sent_to_maker_at);
        $this->assertNull($report->reviewed_at);
        \Illuminate\Support\Facades\Mail::assertNothingSent();

        $this->actingAs($auditor)
            ->get(route('audit-review.show', $report))
            ->assertOk()
            ->assertSee('Added after review done')
            ->assertDontSee('Action needed')
            ->assertDontSee('Resubmit for review');

        $this->actingAs($auditor)
            ->get(route('audits.index', ['report' => $report->id]))
            ->assertOk()
            ->assertSee('Fix & resubmit')
            ->assertSee('Resubmit for review')
            ->assertDontSee('Read-only — waiting for reviewer');

        $this->assertTrue(app(AuditReportReviewService::class)->isEditableByMaker($report->fresh()));

        $this->actingAs($auditor)
            ->post(route('audit-review.submit', $report), [
                'destination' => 'assigned',
                'note' => 'Fixed per comments',
            ])
            ->assertRedirect();

        $report->refresh();
        $this->assertSame(AuditReport::STATUS_IN_REVIEW, $report->status);
        $this->assertNull($report->review_ready_at);
        $this->assertNull($report->review_sent_to_maker_at);

        $this->actingAs($reviewer)
            ->post(route('audit-review.done', $report))
            ->assertRedirect();

        $this->actingAs($reviewer)
            ->post(route('audit-review.approve', $report), ['note' => 'Looks good'])
            ->assertRedirect(route('audit-review.index', ['tab' => 'reviewed']));

        $report->refresh();
        $this->assertSame(AuditReport::STATUS_REVIEWED, $report->status);
        $this->assertNotNull($report->reviewed_at);
    }

    public function test_review_panel_shows_action_notifications(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $auditor = User::factory()->create(['email_verified_at' => now()]);
        $auditor->assignRole('audit_officer');
        $reviewer = User::factory()->create(['email_verified_at' => now(), 'name' => 'Notify Reviewer']);
        $reviewer->assignRole('senior_officer');

        AuditReviewerAssignment::query()->create([
            'auditor_user_id' => $auditor->id,
            'reviewer_user_id' => $reviewer->id,
            'assigned_by' => $admin->id,
        ]);

        $area = Area::query()->create(['name' => 'Notify Area', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Notify Branch',
            'code' => 'NTF-1',
            'status' => 'active',
        ]);

        $report = AuditReport::query()->create([
            'user_id' => $auditor->id,
            'shakha_id' => $shakha->id,
            'report_month' => 9,
            'report_year' => 2026,
            'status' => AuditReport::STATUS_COMPLETED,
            'progress_pct' => 100,
            'completed_at' => now(),
            'pages_data' => ['cover' => []],
        ]);
        app(AuditReportReviewService::class)->submitForReview($report, $auditor, false);

        $this->actingAs($reviewer)
            ->get(route('audit-review.index'))
            ->assertOk()
            ->assertSee('waiting for your review')
            ->assertSee('What to do');
    }
}
