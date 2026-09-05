<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AuditFinding;
use App\Models\AuditIndicator;
use App\Models\AuditReport;
use App\Models\Shakha;
use App\Models\User;
use App\Services\AuditSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditReportMatrixSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_rating_box_syncs_to_findings_matrix_under_linked_indicator(): void
    {
        $area = Area::query()->create(['name' => 'Metro', 'division' => 'Dhaka']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Branch Sync',
            'code' => 'SYN-1',
            'status' => 'active',
        ]);
        $indicator = AuditIndicator::query()->create([
            'category' => 'Financial',
            'sub_category' => null,
            'indicator_code' => 'SYNC-1',
            'title' => 'VAT not deducted',
            'risk_rating' => 'Major',
            'is_active' => true,
        ]);
        $user = User::factory()->create(['email_verified_at' => now()]);

        $report = AuditReport::query()->create([
            'user_id' => $user->id,
            'shakha_id' => $shakha->id,
            'report_month' => 9,
            'report_year' => 2026,
            'status' => AuditReport::STATUS_DRAFT,
            'pages_data' => [
                'page4' => [
                    'reportBlocks' => [
                        [
                            'type' => 'finding',
                            'serial' => '১.১',
                            'title' => 'শিরোনাম',
                            'body' => 'VAT not deducted',
                            'rating' => 'Major (B)',
                            'amount' => '১২০০',
                            'indicator_id' => $indicator->id,
                            'indicator_code' => $indicator->indicator_code,
                        ],
                        [
                            'type' => 'stats',
                            'heading' => 'Report Rating Box:',
                            'linked_indicator_id' => $indicator->id,
                            'linked_indicator_code' => $indicator->indicator_code,
                            'linked_finding_serial' => '১.১',
                            'linked_finding_title' => 'VAT not deducted',
                            'rows' => [
                                [
                                    'total_population' => '100',
                                    'sample_size' => '২০',
                                    'instances_found' => '৩',
                                    'percentage' => '15',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $touched = app(AuditSummaryService::class)->syncFromReport($report);
        $this->assertSame(1, $touched);

        $cell = AuditFinding::query()
            ->where('shakha_id', $shakha->id)
            ->where('audit_indicator_id', $indicator->id)
            ->where('audit_month', 9)
            ->where('audit_year', 2026)
            ->first();

        $this->assertNotNull($cell);
        $this->assertSame(1200.0, (float) $cell->amount);
        $this->assertSame(20, (int) $cell->sample_size_checked);
        $this->assertSame(3, (int) $cell->irregularity_count);

        $totals = app(AuditSummaryService::class)->getOrganizationTotals(9, 2026);
        $row = $totals->firstWhere('indicator_id', $indicator->id);
        $this->assertNotNull($row);
        $this->assertSame(1200.0, (float) $row->total_amount);
        $this->assertSame(20, (int) $row->total_samples_checked);
        $this->assertSame(3, (int) $row->total_irregularities);
        $this->assertSame(1, (int) $row->objected_branch_count);
    }

    public function test_fifo_pairs_rating_box_to_first_heading_when_boxes_follow_all_headings(): void
    {
        $service = app(AuditSummaryService::class);

        $rows = $service->extractMatrixRowsFromBlocks([
            [
                'type' => 'finding',
                'indicator_id' => 10,
                'body' => 'First indicator',
                'amount' => '',
            ],
            [
                'type' => 'finding',
                'indicator_id' => 20,
                'body' => 'Second indicator',
                'amount' => '',
            ],
            [
                'type' => 'stats',
                'rows' => [[
                    'total_population' => '20',
                    'sample_size' => '20',
                    'instances_found' => '15',
                    'percentage' => '10',
                ]],
            ],
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame(10, $rows[0]['indicator_id']);
        $this->assertSame(20, $rows[0]['sample_size_checked']);
        $this->assertSame(15, $rows[0]['irregularity_count']);
        $this->assertSame('First indicator', $rows[0]['observation']);
    }
}
