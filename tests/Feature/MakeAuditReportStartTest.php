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
            ->assertSee('Find branch')
            ->assertSee('Start');
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
            'report_month' => 8,
            'report_year' => 2026,
            'status' => 'draft',
        ]);

        $this->assertSame(1, AuditReport::query()->where('shakha_id', $shakha->id)->count());
    }
}
