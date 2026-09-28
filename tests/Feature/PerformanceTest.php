<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AuditReport;
use App\Models\AuditReviewerAssignment;
use App\Models\PerformanceRule;
use App\Models\Shakha;
use App\Models\User;
use App\Services\AuditReportReviewService;
use App\Services\PerformanceService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $star;

    private User $starter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $this->star = User::factory()->create(['name' => 'Star Auditor', 'email_verified_at' => now()]);
        $this->star->assignRole('audit_officer');
        $this->starter = User::factory()->create(['name' => 'Starter Auditor', 'email_verified_at' => now()]);
        $this->starter->assignRole('audit_officer');
        $reviewer = User::factory()->create(['email_verified_at' => now()]);
        $reviewer->assignRole('senior_officer');

        AuditReviewerAssignment::query()->create([
            'auditor_user_id' => $this->star->id,
            'reviewer_user_id' => $reviewer->id,
            'assigned_by' => $this->admin->id,
        ]);

        $area = Area::query()->create(['name' => 'Metro', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Score Branch', 'code' => 'SC-1', 'status' => 'active']);

        $report = AuditReport::query()->create([
            'user_id' => $this->star->id,
            'shakha_id' => $shakha->id,
            'report_month' => (int) now()->month,
            'report_year' => (int) now()->year,
            'status' => AuditReport::STATUS_COMPLETED,
            'progress_pct' => 90,
            'completed_at' => now(),
            'pages_data' => [],
        ]);
        app(AuditReportReviewService::class)->submitForReview($report, $this->star, false);
        $this->actingAs($reviewer)->post(route('audit-review.totally-fixed', $report->fresh()));

        AuditReport::query()->create([
            'user_id' => $this->starter->id,
            'shakha_id' => $shakha->id,
            'report_month' => (int) now()->month,
            'report_year' => (int) now()->year,
            'status' => AuditReport::STATUS_DRAFT,
            'progress_pct' => 10,
            'pages_data' => [],
        ]);
    }

    public function test_default_rules_are_seeded(): void
    {
        $this->assertSame(count(PerformanceRule::defaults()), PerformanceRule::query()->count());
        $this->assertSame(10.0, PerformanceRule::query()->where('key', 'confirmed')->value('points'));
    }

    public function test_leaderboard_ranks_people_and_picks_awards(): void
    {
        $service = app(PerformanceService::class);
        $board = $service->board($service->period('month'));
        $rows = collect($board['rows'])->keyBy('id');

        $this->assertSame(1, $rows[$this->star->id]['rank']);
        $this->assertGreaterThan($rows[$this->starter->id]['score'], $rows[$this->star->id]['score']);
        $this->assertSame(1, $rows[$this->star->id]['lines']['confirmed']['count']);
        $this->assertSame(1, $rows[$this->star->id]['lines']['perfect']['count']);
        $this->assertSame($this->star->id, $board['awards']['employee']['row']['id']);
        $this->assertSame($this->star->id, $board['awards']['quality']['row']['id']);
        $this->assertFalse($rows->has($this->admin->id));

        $this->actingAs($this->admin)
            ->get(route('performance.index'))
            ->assertOk()
            ->assertSee('Best employee')
            ->assertSee('Most progressive')
            ->assertSee('Leaderboard')
            ->assertSee('Star Auditor')
            ->assertSee('Starter Auditor');

        $this->actingAs($this->admin)
            ->get(route('performance.index', ['period' => 'year', 'year' => now()->year]))
            ->assertOk()
            ->assertSee('Performance &amp; awards · '.now()->year, false);
    }

    public function test_admin_can_edit_and_reset_rules(): void
    {
        $confirmed = PerformanceRule::query()->where('key', 'confirmed')->firstOrFail();
        $email = PerformanceRule::query()->where('key', 'email')->firstOrFail();

        $this->actingAs($this->admin)->get(route('performance.rules'))->assertOk()->assertSee('Scoring rules');

        $this->actingAs($this->admin)
            ->post(route('performance.rules.save'), [
                'rules' => [
                    $confirmed->id => ['label' => 'Report locked', 'description' => 'Renamed', 'points' => 25, 'monthly_cap' => 3, 'is_active' => 1],
                    $email->id => ['label' => $email->label, 'points' => 1, 'monthly_cap' => '', 'is_active' => 0],
                ],
            ])
            ->assertRedirect(route('performance.rules'));

        $this->assertDatabaseHas('performance_rules', ['id' => $confirmed->id, 'label' => 'Report locked', 'points' => 25, 'monthly_cap' => 3, 'is_active' => true]);
        $this->assertDatabaseHas('performance_rules', ['id' => $email->id, 'monthly_cap' => null, 'is_active' => false]);

        $this->actingAs($this->admin)->post(route('performance.rules.reset'))->assertRedirect(route('performance.rules'));
        $this->assertDatabaseHas('performance_rules', ['id' => $confirmed->id, 'label' => 'Report confirmed', 'points' => 10, 'monthly_cap' => null]);
        $this->assertDatabaseHas('performance_rules', ['id' => $email->id, 'monthly_cap' => 20, 'is_active' => true]);
    }

    public function test_admin_can_add_and_remove_rules(): void
    {
        $this->actingAs($this->admin)
            ->post(route('performance.rules.store'), [
                'counts' => 'manual',
                'label' => 'Client appreciation',
                'description' => 'Branch or client praised the audit.',
                'points' => 8,
            ])
            ->assertRedirect(route('performance.rules'));

        $custom = PerformanceRule::query()->where('label', 'Client appreciation')->firstOrFail();
        $this->assertSame(PerformanceRule::SOURCE_MANUAL, $custom->source);
        $this->assertStringStartsWith('custom_', $custom->key);

        $email = PerformanceRule::query()->where('key', 'email')->firstOrFail();
        $this->actingAs($this->admin)->delete(route('performance.rules.destroy', $email))->assertRedirect(route('performance.rules'));
        $this->assertDatabaseMissing('performance_rules', ['key' => 'email']);

        $this->actingAs($this->admin)
            ->get(route('performance.rules'))
            ->assertOk()
            ->assertSee('Client appreciation')
            ->assertSee('Given by admin')
            ->assertSee('<option value="email">Report emailed</option>', false);

        $this->actingAs($this->admin)
            ->post(route('performance.rules.store'), ['counts' => 'email', 'label' => 'Emails sent', 'points' => 2])
            ->assertRedirect(route('performance.rules'));
        $this->assertDatabaseHas('performance_rules', ['key' => 'email', 'source' => 'auto', 'label' => 'Emails sent']);

        $this->actingAs($this->admin)
            ->post(route('performance.rules.store'), ['counts' => 'email', 'label' => 'Twice', 'points' => 2])
            ->assertSessionHasErrors('counts');
    }

    public function test_special_marks_change_the_score(): void
    {
        $service = app(PerformanceService::class);
        $scoreOf = function (User $user) use ($service) {
            return collect($service->board($service->period('month'))['rows'])->firstWhere('id', $user->id);
        };
        $before = $scoreOf($this->starter)['score'];

        $this->actingAs($this->admin)
            ->post(route('performance.marks.store'), [
                'user_id' => $this->starter->id,
                'label' => 'Covered extra branch',
                'points' => 15,
                'note' => 'Stepped in when the team was short.',
                'awarded_on' => now()->toDateString(),
            ])
            ->assertSessionHasNoErrors();

        $custom = PerformanceRule::query()->create([
            'key' => 'custom_praise', 'source' => 'manual', 'label' => 'Client praise',
            'points' => 4, 'is_active' => true, 'sort_order' => 99,
        ]);
        $this->actingAs($this->admin)
            ->post(route('performance.marks.store'), [
                'user_id' => $this->starter->id,
                'performance_rule_id' => $custom->id,
                'awarded_on' => now()->toDateString(),
            ])
            ->assertSessionHasNoErrors();

        $row = $scoreOf($this->starter);
        $this->assertEqualsWithDelta($before + 15 + 4, $row['score'], 0.01);
        $this->assertSame(1, $row['lines']['special']['count']);
        $this->assertSame(1, $row['lines']['custom_praise']['count']);

        $this->actingAs($this->admin)
            ->get(route('performance.marks'))
            ->assertOk()
            ->assertSee('Covered extra branch')
            ->assertSee('Stepped in when the team was short.')
            ->assertSee('Client praise');

        $this->actingAs($this->admin)
            ->get(route('performance.show', ['user' => $this->starter, 'year' => now()->year]))
            ->assertOk()
            ->assertSee('Covered extra branch')
            ->assertSee('special marks of appreciation', false);

        $this->actingAs($this->admin)->delete(route('performance.rules.destroy', $custom));
        $this->assertDatabaseHas('performance_marks', ['label' => 'Client praise', 'performance_rule_id' => null, 'points' => 4]);
        $this->assertEqualsWithDelta($before + 19, $scoreOf($this->starter)['score'], 0.01);

        $mark = \App\Models\PerformanceMark::query()->where('label', 'Covered extra branch')->firstOrFail();
        $this->actingAs($this->admin)->delete(route('performance.marks.destroy', $mark))->assertRedirect();
        $this->assertEqualsWithDelta($before + 4, $scoreOf($this->starter)['score'], 0.01);

        $this->actingAs($this->admin)
            ->post(route('performance.marks.store'), ['user_id' => $this->starter->id, 'awarded_on' => now()->toDateString()])
            ->assertSessionHasErrors(['label', 'points']);
    }

    public function test_leaderboard_downloads_as_csv(): void
    {
        $response = $this->actingAs($this->admin)->get(route('performance.export', ['period' => 'month']));
        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $csv = $response->streamedContent();
        $this->assertStringContainsString('Rank,Name,Email', $csv);
        $this->assertStringContainsString('Star Auditor', $csv);
    }

    public function test_yearly_scorecard_shows_month_ranks(): void
    {
        $this->actingAs($this->admin)
            ->get(route('performance.show', ['user' => $this->star, 'year' => now()->year]))
            ->assertOk()
            ->assertSee('Scorecard · Star Auditor')
            ->assertSee('Score by month')
            ->assertSee('Promotion summary')
            ->assertSee('Best employee in 1 month.');
    }

    public function test_auditors_cannot_open_performance(): void
    {
        $this->actingAs($this->star)->get(route('performance.index'))->assertForbidden();
        $this->actingAs($this->star)->get(route('performance.rules'))->assertForbidden();
        $this->actingAs($this->star)->get(route('performance.marks'))->assertForbidden();
        $this->actingAs($this->star)
            ->post(route('performance.marks.store'), ['user_id' => $this->star->id, 'label' => 'Self', 'points' => 100, 'awarded_on' => now()->toDateString()])
            ->assertForbidden();
    }
}
