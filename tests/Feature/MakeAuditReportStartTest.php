<?php

namespace Tests\Feature;

use App\Livewire\MakeAuditReport;
use App\Models\Area;
use App\Models\AuditReport;
use App\Models\Shakha;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MakeAuditReportStartTest extends TestCase
{
    use RefreshDatabase;

    private function makeShakha(?string $name = 'Test Branch 1', ?string $code = 'TST-001'): Shakha
    {
        $area = Area::query()->create([
            'name' => 'Test Area '.$code,
            'division' => 'Test Division',
        ]);

        return Shakha::query()->create([
            'area_id' => $area->id,
            'name' => $name,
            'code' => $code,
            'status' => 'active',
        ]);
    }

    /** User who can open /audits and start reports for any shakha. */
    private function makeAuditUser(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
            'is_superadmin' => true,
            'is_active' => true,
        ]);
    }

    public function test_audits_page_loads_with_start_controls(): void
    {
        $user = $this->makeAuditUser();
        $this->makeShakha();

        $this->actingAs($user)
            ->get(route('audits.index'))
            ->assertOk()
            ->assertSeeLivewire(MakeAuditReport::class)
            ->assertSee('Audit Reports')
            ->assertSee('Start new')
            ->assertSee('Start');
    }

    public function test_start_report_without_shakha_fails_validation(): void
    {
        $user = $this->makeAuditUser();

        Livewire::actingAs($user)
            ->test(MakeAuditReport::class)
            ->call('startReport')
            ->assertHasErrors(['shakha_id'])
            ->assertSet('step', 'select');
    }

    public function test_start_report_with_shakha_opens_wizard_and_creates_draft(): void
    {
        $user = $this->makeAuditUser();
        $shakha = $this->makeShakha();

        $component = Livewire::actingAs($user)
            ->test(MakeAuditReport::class)
            ->set('report_month', 8)
            ->set('report_year', 2026)
            ->call('startReport', $shakha->id)
            ->assertHasNoErrors()
            ->assertSet('step', 'wizard')
            ->assertSet('activeTab', 'cover')
            ->assertSee('অভ্যন্তরীণ নিরীক্ষা প্রতিবেদন');

        $reportId = $component->get('reportId');
        $this->assertNotNull($reportId);

        $this->assertDatabaseHas('audit_reports', [
            'id' => $reportId,
            'shakha_id' => $shakha->id,
            'user_id' => $user->id,
            'report_month' => 8,
            'report_year' => 2026,
            'status' => 'draft',
        ]);

        $this->assertSame(1, AuditReport::query()->where('shakha_id', $shakha->id)->count());
    }

    public function test_user_cannot_start_more_than_three_concurrent_drafts(): void
    {
        $user = $this->makeAuditUser();
        $area = Area::query()->create(['name' => 'Area', 'division' => 'Div']);

        for ($i = 1; $i <= 3; $i++) {
            $shakha = Shakha::query()->create([
                'area_id' => $area->id,
                'name' => "Branch $i",
                'code' => "B-$i",
                'status' => 'active',
            ]);

            Livewire::actingAs($user)
                ->test(MakeAuditReport::class)
                ->call('startReport', $shakha->id)
                ->assertHasNoErrors()
                ->assertSet('step', 'wizard')
                ->call('backToSelect');
        }

        $this->assertSame(3, AuditReport::query()->where('user_id', $user->id)->where('status', 'draft')->count());

        $extra = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Branch 4',
            'code' => 'B-4',
            'status' => 'active',
        ]);

        Livewire::actingAs($user)
            ->test(MakeAuditReport::class)
            ->call('startReport', $extra->id)
            ->assertHasErrors(['shakha_id'])
            ->assertSet('step', 'select');

        $this->assertSame(3, AuditReport::query()->where('user_id', $user->id)->where('status', 'draft')->count());
    }

    public function test_auto_save_and_resume_persists_draft(): void
    {
        $user = $this->makeAuditUser();
        $shakha = $this->makeShakha();

        $component = Livewire::actingAs($user)
            ->test(MakeAuditReport::class)
            ->call('startReport', $shakha->id)
            ->set('memo_no', 'TEST-MEMO-99')
            ->call('autoSaveDraft');

        $reportId = $component->get('reportId');
        $this->assertNotNull($reportId);
        $this->assertDatabaseHas('audit_reports', [
            'id' => $reportId,
            'memo_no' => 'TEST-MEMO-99',
            'status' => 'draft',
        ]);

        Livewire::actingAs($user)
            ->test(MakeAuditReport::class)
            ->call('resumeReport', $reportId)
            ->assertSet('step', 'wizard')
            ->assertSet('memo_no', 'TEST-MEMO-99')
            ->assertSet('reportId', $reportId);
    }

    public function test_complete_report_marks_completed_and_frees_slot(): void
    {
        $user = $this->makeAuditUser();
        $shakha = $this->makeShakha();

        Livewire::actingAs($user)
            ->test(MakeAuditReport::class)
            ->call('startReport', $shakha->id)
            ->call('completeReport')
            ->assertSet('step', 'select');

        $this->assertDatabaseHas('audit_reports', [
            'shakha_id' => $shakha->id,
            'user_id' => $user->id,
            'status' => 'completed',
            'progress_pct' => 100,
        ]);
    }

    public function test_toolbar_save_uses_current_tab_not_always_cover(): void
    {
        $user = $this->makeAuditUser();
        $shakha = $this->makeShakha();

        Livewire::actingAs($user)
            ->test(MakeAuditReport::class)
            ->call('startReport', $shakha->id)
            ->set('activeTab', 'page4')
            ->call('saveCurrentTab')
            ->assertHasNoErrors()
            ->assertSet('activeTab', 'page4');
    }

    public function test_pdf_download_streams_a_pdf_file(): void
    {
        $user = $this->makeAuditUser();
        $shakha = $this->makeShakha();

        Livewire::actingAs($user)
            ->test(MakeAuditReport::class)
            ->call('startReport', $shakha->id)
            ->call('downloadPdf')
            ->assertFileDownloaded();
    }

    public function test_undo_stays_available_after_save_for_ten_minutes(): void
    {
        $user = $this->makeAuditUser();
        $shakha = $this->makeShakha();

        $component = Livewire::actingAs($user)
            ->test(MakeAuditReport::class)
            ->call('startReport', $shakha->id)
            ->assertSet('step', 'wizard');

        $reportId = (int) $component->get('reportId');
        $before = $component->get('reportBlocks');
        $this->assertNotEmpty($before);

        // Edit body then Save — Undo must light up and restore pre-save state.
        $edited = $before;
        $edited[0]['title'] = ($edited[0]['title'] ?? '').' [edited]';
        $component
            ->set('reportBlocks', $edited)
            ->call('autoSaveDraft');

        $this->assertNotEmpty($component->get('undoStack'));
        $this->assertGreaterThan(0, $component->instance()->undoSecondsRemaining());
        $this->assertNotEquals($before, $component->get('reportBlocks'));

        // Resume within 10 minutes still has Undo.
        Livewire::actingAs($user)
            ->test(MakeAuditReport::class)
            ->call('resumeReport', $reportId)
            ->assertSet('step', 'wizard')
            ->tap(function ($c) {
                $this->assertNotEmpty($c->get('undoStack'));
            })
            ->call('undoLastChange')
            ->assertSet('reportBlocks', $before);
    }

    public function test_undo_works_after_block_delete_and_save(): void
    {
        $user = $this->makeAuditUser();
        $shakha = $this->makeShakha();

        $component = Livewire::actingAs($user)
            ->test(MakeAuditReport::class)
            ->call('startReport', $shakha->id);

        $before = $component->get('reportBlocks');
        $this->assertNotEmpty($before);

        $component
            ->call('removeBlock', 0)
            ->call('autoSaveDraft')
            ->call('undoLastChange');

        $this->assertSame($before, $component->get('reportBlocks'));
    }

    public function test_expired_undo_entries_are_pruned(): void
    {
        $user = $this->makeAuditUser();
        $shakha = $this->makeShakha();

        $component = Livewire::actingAs($user)
            ->test(MakeAuditReport::class)
            ->call('startReport', $shakha->id);

        $component->set('undoStack', [[
            'id' => 'expired-snap',
            'label' => 'পুরনো',
            'at' => time() - MakeAuditReport::UNDO_TTL_SECONDS - 5,
        ]]);

        $component->call('refreshUndoWindow');
        $this->assertSame([], $component->get('undoStack'));
    }
}
