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

    private function makeShakha(): Shakha
    {
        $area = Area::query()->create([
            'name' => 'Test Area',
            'division' => 'Test Division',
        ]);

        return Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Test Branch 1',
            'code' => 'TST-001',
            'status' => 'active',
        ]);
    }

    public function test_audits_page_loads_with_start_controls(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->makeShakha();

        $this->actingAs($user)
            ->get(route('audits.index'))
            ->assertOk()
            ->assertSeeLivewire(MakeAuditReport::class)
            ->assertSee('Audit Report Dashboard')
            ->assertSee('নতুন রিপোর্ট শুরু করুন')
            ->assertSee('Start new');
    }

    public function test_start_report_without_shakha_fails_validation(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        Livewire::actingAs($user)
            ->test(MakeAuditReport::class)
            ->call('startReport')
            ->assertHasErrors(['shakha_id'])
            ->assertSet('step', 'select');
    }

    public function test_start_report_with_shakha_opens_wizard_and_creates_draft(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
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
        $user = User::factory()->create(['email_verified_at' => now()]);
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
        $user = User::factory()->create(['email_verified_at' => now()]);
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
        $user = User::factory()->create(['email_verified_at' => now()]);
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

    public function test_pdf_download_streams_a_pdf_file(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $shakha = $this->makeShakha();

        Livewire::actingAs($user)
            ->test(MakeAuditReport::class)
            ->call('startReport', $shakha->id)
            ->call('downloadPdf')
            ->assertFileDownloaded();
    }

    public function test_doc_download_streams_a_word_file(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $shakha = $this->makeShakha();

        Livewire::actingAs($user)
            ->test(MakeAuditReport::class)
            ->call('startReport', $shakha->id)
            ->call('downloadDoc')
            ->assertFileDownloaded();
    }
}
