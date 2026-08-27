<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AuditFinding;
use App\Models\AuditIndicator;
use App\Models\Shakha;
use App\Models\User;
use App\Services\AuditSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditFindingsConsolidatedTest extends TestCase
{
    use RefreshDatabase;

    private function seedCatalog(): AuditIndicator
    {
        return AuditIndicator::query()->create([
            'category' => 'অর্থ, হিসাব ও প্রশাসন সংক্রান্ত',
            'sub_category' => null,
            'indicator_code' => '২০০০-১',
            'title' => 'দৈনিক আর্থিক চাহিদা রেজিস্টারে প্রদানকৃত চাহিদা অপেক্ষায় প্রকৃত খরচ কম হওয়া',
            'risk_rating' => null,
            'is_active' => true,
        ]);
    }

    public function test_organization_totals_sum_sparse_findings(): void
    {
        $area = Area::query()->create(['name' => 'Metro', 'division' => 'Dhaka']);
        $a = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Branch A', 'code' => 'A-1', 'status' => 'active']);
        $b = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Branch B', 'code' => 'B-1', 'status' => 'active']);
        $indicator = $this->seedCatalog();

        AuditFinding::query()->create([
            'shakha_id' => $a->id,
            'audit_indicator_id' => $indicator->id,
            'audit_month' => 8,
            'audit_year' => 2026,
            'amount' => 1000.50,
            'sample_size_checked' => 10,
            'irregularity_count' => 2,
            'observation' => 'Cash over limit',
        ]);
        AuditFinding::query()->create([
            'shakha_id' => $b->id,
            'audit_indicator_id' => $indicator->id,
            'audit_month' => 8,
            'audit_year' => 2026,
            'amount' => 500,
            'sample_size_checked' => 5,
            'irregularity_count' => 1,
        ]);

        $totals = app(AuditSummaryService::class)->getOrganizationTotals(8, 2026);
        $row = $totals->firstWhere('indicator_id', $indicator->id);

        $this->assertNotNull($row);
        $this->assertSame(1500.5, (float) $row->total_amount);
        $this->assertSame(15, $row->total_samples_checked);
        $this->assertSame(3, $row->total_irregularities);
        $this->assertSame(2, $row->objected_branch_count);
    }

    public function test_findings_index_loads(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->seedCatalog();

        $this->actingAs($user)
            ->get(route('audit-findings.index', ['month' => 8, 'year' => 2026]))
            ->assertOk()
            ->assertSee('Audit Findings Consolidated')
            ->assertViewHas('rows', function ($rows) {
                return collect($rows)->contains(fn ($row) => ($row['code'] ?? null) === '২০০০-১');
            });
    }

    public function test_upsert_clears_empty_finding_row(): void
    {
        $area = Area::query()->create(['name' => 'Metro', 'division' => 'Dhaka']);
        $shakha = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Branch A', 'code' => 'A-1', 'status' => 'active']);
        $indicator = $this->seedCatalog();

        $service = app(AuditSummaryService::class);
        $service->upsertFinding($shakha->id, $indicator->id, 8, 2026, [
            'amount' => 100,
            'irregularity_count' => 1,
        ]);
        $this->assertDatabaseCount('audit_findings', 1);

        $service->upsertFinding($shakha->id, $indicator->id, 8, 2026, []);
        $this->assertDatabaseCount('audit_findings', 0);
    }

    public function test_indicator_model_accepts_bengali_codes(): void
    {
        $indicator = AuditIndicator::query()->create([
            'category' => 'অর্থ, হিসাব ও প্রশাসন সংক্রান্ত',
            'sub_category' => null,
            'indicator_code' => '২০০০-৯৯',
            'title' => 'টেস্ট আপত্তি',
            'risk_rating' => null,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('audit_indicators', [
            'id' => $indicator->id,
            'indicator_code' => '২০০০-৯৯',
        ]);
    }
}
