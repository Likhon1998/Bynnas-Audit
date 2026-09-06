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
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_superadmin' => true,
            'is_active' => true,
        ]);
        $this->seedCatalog();

        $this->actingAs($user)
            ->get(route('audit-findings.index', ['month' => 8, 'year' => 2026]))
            ->assertOk()
            ->assertSee('Findings Matrix')
            ->assertSee('Download August 2026');
    }

    public function test_month_excel_export_includes_indicators_and_shakhas(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_superadmin' => true,
            'is_active' => true,
        ]);
        $area = Area::query()->create(['name' => 'Metro', 'division' => 'Dhaka']);
        $branchA = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Banani Shakha', 'code' => 'BYN-004', 'status' => 'active']);
        $branchB = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Gulshan Shakha', 'code' => 'BYN-005', 'status' => 'active']);
        $indicator = $this->seedCatalog();

        AuditFinding::query()->create([
            'shakha_id' => $branchA->id,
            'audit_indicator_id' => $indicator->id,
            'audit_month' => 8,
            'audit_year' => 2026,
            'amount' => 2500,
            'sample_size_checked' => 8,
            'irregularity_count' => 2,
            'observation' => 'Cash over limit',
        ]);
        AuditFinding::query()->create([
            'shakha_id' => $branchB->id,
            'audit_indicator_id' => $indicator->id,
            'audit_month' => 8,
            'audit_year' => 2026,
            'amount' => 900,
            'sample_size_checked' => 4,
            'irregularity_count' => 1,
        ]);

        $response = $this->actingAs($user)
            ->get(route('audit-findings.export', ['month' => 8, 'year' => 2026]));

        $response->assertOk();
        $this->assertStringContainsString('findings-matrix-2026-08-aug.xlsx', (string) $response->headers->get('content-disposition'));

        $path = tempnam(sys_get_temp_dir(), 'findings-month');
        file_put_contents($path, $response->streamedContent());

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $names = $spreadsheet->getSheetNames();
        $this->assertSame(['Irregularities', 'Amount', 'Branch detail'], $names);

        $sheet = $spreadsheet->getSheetByName('Irregularities');
        $this->assertNotNull($sheet);
        $this->assertStringContainsString('August 2026', (string) $sheet->getCell('A1')->getValue());

        // Left summary columns + shakha headers
        $this->assertSame('Category', (string) $sheet->getCell('A4')->getValue());
        $headerRow = [];
        for ($c = 1; $c <= 20; $c++) {
            $val = trim((string) $sheet->getCell(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c).'4')->getValue());
            if ($val !== '') {
                $headerRow[] = $val;
            }
        }
        $this->assertTrue(collect($headerRow)->contains(fn ($h) => str_contains($h, 'Banani')));
        $this->assertTrue(collect($headerRow)->contains(fn ($h) => str_contains($h, 'Gulshan')));

        $foundIndicator = false;
        for ($r = 5; $r <= $sheet->getHighestDataRow(); $r++) {
            if ((string) $sheet->getCell('C'.$r)->getValue() === '২০০০-১') {
                $this->assertSame(3400.0, (float) $sheet->getCell('F'.$r)->getValue());
                $this->assertSame(3, (int) $sheet->getCell('H'.$r)->getValue());
                $this->assertSame(2, (int) $sheet->getCell('I'.$r)->getValue());
                // Shakha columns start at J
                $this->assertSame(2, (int) $sheet->getCell('J'.$r)->getValue());
                $this->assertSame(1, (int) $sheet->getCell('K'.$r)->getValue());
                $foundIndicator = true;
                break;
            }
        }
        $this->assertTrue($foundIndicator);

        $detail = $spreadsheet->getSheetByName('Branch detail');
        $this->assertNotNull($detail);
        $this->assertSame('Banani Shakha', (string) $detail->getCell('A4')->getValue());

        @unlink($path);
    }

    public function test_authority_month_summary_page_and_download(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_superadmin' => true,
            'is_active' => true,
        ]);
        $area = Area::query()->create(['name' => 'Metro', 'division' => 'Dhaka']);
        $branch = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Banani Shakha', 'code' => 'BYN-004', 'status' => 'active']);
        $indicator = $this->seedCatalog();

        AuditFinding::query()->create([
            'shakha_id' => $branch->id,
            'audit_indicator_id' => $indicator->id,
            'audit_month' => 8,
            'audit_year' => 2026,
            'amount' => 1500,
            'sample_size_checked' => 10,
            'irregularity_count' => 4,
        ]);

        $this->actingAs($user)
            ->get(route('audit-findings.summary', ['month' => 8, 'year' => 2026]))
            ->assertOk()
            ->assertSee('Monthly irregularities summary')
            ->assertSee('Banani Shakha')
            ->assertSee('Download August 2026');

        $response = $this->actingAs($user)
            ->get(route('audit-findings.summary.export', ['month' => 8, 'year' => 2026]));

        $response->assertOk();
        $this->assertStringContainsString('findings-summary-2026-08.xlsx', (string) $response->headers->get('content-disposition'));

        $path = tempnam(sys_get_temp_dir(), 'auth-sum');
        file_put_contents($path, $response->streamedContent());
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $this->assertSame(['Overview', 'Branches', 'Top issues'], $spreadsheet->getSheetNames());
        $this->assertStringContainsString('August 2026', (string) $spreadsheet->getSheetByName('Overview')->getCell('A1')->getValue());
        $this->assertSame('Banani Shakha', (string) $spreadsheet->getSheetByName('Branches')->getCell('B4')->getValue());
        @unlink($path);
    }

    public function test_year_excel_export_has_monthly_matrix_tabs(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_superadmin' => true,
            'is_active' => true,
        ]);
        $this->seedCatalog();

        $response = $this->actingAs($user)
            ->get(route('audit-findings.export', ['year' => 2026, 'scope' => 'year']));

        $response->assertOk();
        $path = tempnam(sys_get_temp_dir(), 'findings-year');
        file_put_contents($path, $response->streamedContent());
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $names = $spreadsheet->getSheetNames();
        $this->assertContains('Year Summary', $names);
        $this->assertContains('08-Aug', $names);
        $this->assertCount(13, $names);
        @unlink($path);
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
