<?php

namespace Tests\Feature;

use App\Livewire\MakeAuditReport;
use App\Models\Area;
use App\Models\AuditChecklistSubmission;
use App\Models\AuditFinding;
use App\Models\AuditIndicator;
use App\Models\AuditReport;
use App\Models\Shakha;
use App\Models\User;
use App\Services\VisitAuditWorkService;
use App\Support\AuditIrregularityCatalog;
use App\Support\AuditScoreSheet;
use Database\Seeders\Demo\OctoberDemoReportContent;
use Database\Seeders\OctoberDemoReportsSeeder;
use Database\Seeders\ShakhaEmployeeRosterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Tests\TestCase;

class OctoberDemoReportsSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (AuditIrregularityCatalog::all() as $row) {
            AuditIndicator::query()->create([
                'indicator_code' => $row['indicator_code'],
                'category' => $row['category'],
                'sub_category' => $row['sub_category'],
                'title' => $row['title'],
                'risk_rating' => $row['risk_rating'],
                'is_active' => true,
            ]);
        }
        $this->artisan('rulebook:import')->assertSuccessful();

        $area = Area::query()->create(['name' => 'Demo Area', 'division' => 'Dhaka']);
        foreach (['মিরপুর', 'সাভার', 'টঙ্গী', 'গাজীপুর'] as $i => $name) {
            Shakha::query()->create([
                'area_id' => $area->id,
                'name' => $name,
                'code' => 'DMO-00'.($i + 1),
                'status' => 'active',
            ]);
        }
        $this->seed(ShakhaEmployeeRosterSeeder::class);

        User::factory()->create([
            'email_verified_at' => now(),
            'is_superadmin' => true,
            'is_active' => true,
        ]);
    }

    /** @return Collection<int, AuditReport> */
    private function demoReports()
    {
        return AuditReport::query()->where('report_month', 10)->where('report_year', 2026)->orderBy('id')->get()
            ->filter(fn (AuditReport $r) => ($r->pages_data['meta']['demo_seed'] ?? null) === OctoberDemoReportsSeeder::DEMO_TAG)
            ->values();
    }

    /** @return list<int> */
    private function octoberListIdsFor(User $user): array
    {
        return Livewire::actingAs($user)
            ->test(MakeAuditReport::class)
            ->set('listFilterMonth', 10)
            ->set('listFilterYear', 2026)
            ->viewData('completedReports')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();
    }

    public function test_reports_show_in_october_list_for_owner_and_super_admins(): void
    {
        $superAdmin = User::query()->where('is_superadmin', true)->firstOrFail();
        $officer = User::factory()->create(['email_verified_at' => now(), 'is_superadmin' => false, 'is_active' => true]);
        $guest = User::factory()->create(['email_verified_at' => now(), 'is_superadmin' => false, 'is_active' => true]);
        $shared = User::factory()->create(['email_verified_at' => now(), 'is_superadmin' => false, 'is_active' => true]);

        $this->artisan('demo:october-reports', ['--owner' => $officer->email, '--share' => [$shared->email]])
            ->assertSuccessful();

        $ids = $this->demoReports()->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $this->assertCount(3, $ids);
        $this->assertTrue($this->demoReports()->every(fn (AuditReport $r) => (int) $r->user_id === (int) $officer->id));

        $this->assertSame($ids, $this->octoberListIdsFor($officer));
        $this->assertSame($ids, $this->octoberListIdsFor($superAdmin));
        $this->assertSame($ids, $this->octoberListIdsFor($shared));
        $this->assertSame([], $this->octoberListIdsFor($guest));
    }

    public function test_seeds_three_complete_reports_with_matrix_staff_and_checklists(): void
    {
        $this->seed(OctoberDemoReportsSeeder::class);

        $reports = $this->demoReports();
        $this->assertCount(3, $reports);

        foreach ($reports as $report) {
            $this->assertSame(AuditReport::STATUS_COMPLETED, $report->status);
            $expected = $this->expectedFindings()[$report->pages_data['meta']['demo_key']];

            $findings = AuditFinding::query()
                ->where('shakha_id', $report->shakha_id)
                ->where('audit_month', 10)
                ->where('audit_year', 2026)
                ->get();
            $this->assertCount($expected, $findings, $report->memo_no);
            $this->assertTrue($findings->every(fn (AuditFinding $f) => ! empty($f->responsible_staff_ids)), 'অভিযুক্ত কর্মী missing');

            $progress = app(VisitAuditWorkService::class)->checklistProgress($report);
            $this->assertSame(5, $progress['done'] ?? null);
            $this->assertSame(5, AuditChecklistSubmission::query()->where('audit_report_id', $report->id)->count());

            $blocks = collect($report->pages_data['page4']['reportBlocks'] ?? []);
            $this->assertSame($expected, $blocks->where('type', 'finding')->count());
            $this->assertTrue($blocks->contains('type', 'audit_score'));
            $this->assertTrue($blocks->contains('type', 'it_checklist'));

            $score = $blocks->firstWhere('type', 'audit_score');
            $summary = AuditScoreSheet::summarize($score['rows'], $score['adjustments'] ?? [], $score['subsequent'] ?? []);

            $pdf = MakeAuditReport::pdfBinaryFor($report->fresh());
            $pages = preg_match_all('/\/Type\s*\/Page[^s]/', $pdf);
            fwrite(STDERR, sprintf("\n%s [%s] => %d PDF pages, score %s (%s)",
                $report->memo_no, $report->control_rating, $pages, $summary['audit_score_display'], $summary['grade']));
            $this->assertGreaterThanOrEqual(14, $pages);
        }
    }

    public function test_rerun_replaces_demo_reports_and_leaves_real_reports_alone(): void
    {
        $owner = User::query()->first();
        $real = AuditReport::query()->create([
            'shakha_id' => Shakha::query()->orderBy('id')->value('id'),
            'user_id' => $owner->id,
            'status' => AuditReport::STATUS_DRAFT,
            'report_month' => 10,
            'report_year' => 2026,
            'memo_no' => 'REAL-1',
            'pages_data' => [],
        ]);

        $this->seed(OctoberDemoReportsSeeder::class);
        $first = $this->demoReports()->pluck('shakha_id')->all();
        $this->seed(OctoberDemoReportsSeeder::class);

        $this->assertCount(3, $this->demoReports());
        $this->assertSame($first, $this->demoReports()->pluck('shakha_id')->all());
        $this->assertNotContains($real->shakha_id, $first);
        $this->assertSame('REAL-1', $real->fresh()->memo_no);
        $this->assertSame(
            array_sum($this->expectedFindings()),
            AuditFinding::query()->where('audit_month', 10)->where('audit_year', 2026)->count()
        );
    }

    /** @return array<string, int> */
    private function expectedFindings(): array
    {
        $counts = [];
        foreach (OctoberDemoReportContent::reports() as $bp) {
            $counts[$bp['key']] = collect($bp['sections'])->sum(fn (array $s) => count($s['findings']));
        }

        return $counts;
    }
}
