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

    public function test_finding_with_amount_and_no_rating_box_still_syncs_to_matrix(): void
    {
        $area = Area::query()->create(['name' => 'Metro', 'division' => 'Dhaka']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Branch Amount Only',
            'code' => 'AMT-1',
            'status' => 'active',
        ]);
        $indicator = AuditIndicator::query()->create([
            'category' => 'নিরীক্ষা প্রতিবেদন',
            'sub_category' => null,
            'indicator_code' => 'রিপোর্ট-260911185432-afug',
            'title' => 'Kormi kortik financial irregularities songghotito kora.',
            'risk_rating' => null,
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
                            'serial' => '৬.১',
                            'title' => 'শিরোনাম',
                            'body' => $indicator->title,
                            'rating' => '',
                            'amount' => '1000',
                            'indicator_id' => $indicator->id,
                            'indicator_code' => $indicator->indicator_code,
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
        $this->assertSame(1000.0, (float) $cell->amount);

        $totals = app(AuditSummaryService::class)->getOrganizationTotals(9, 2026);
        $row = $totals->firstWhere('indicator_id', $indicator->id);
        $this->assertNotNull($row);
        $this->assertSame(1000.0, (float) $row->total_amount);
    }

    public function test_observation_matrix_people_sync_to_responsible_staff_by_indicator(): void
    {
        $service = app(AuditSummaryService::class);

        $rows = $service->extractMatrixRowsFromBlocks([
            [
                'type' => 'finding',
                'indicator_id' => 55,
                'body' => 'Indicator title only',
                'amount' => '500',
            ],
            [
                'type' => 'observation',
                'label' => 'পর্যবেক্ষণ (Observation) :',
                'body' => 'Branch did not deduct VAT on contractor bills.',
                'matrix_people' => [
                    ['id' => 1, 'code' => 'E-01', 'name' => 'Karim Mia'],
                    ['id' => null, 'code' => '', 'name' => 'Free Typed Person'],
                    ['id' => 2, 'code' => 'E-02', 'name' => ''],
                ],
            ],
            [
                'type' => 'stats',
                'linked_indicator_id' => 55,
                'rows' => [[
                    'sample_size' => '10',
                    'instances_found' => '2',
                ]],
            ],
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame(55, $rows[0]['indicator_id']);
        $this->assertSame(500.0, (float) $rows[0]['amount']);
        $this->assertSame(10, $rows[0]['sample_size_checked']);
        $this->assertSame(2, $rows[0]['irregularity_count']);
        $this->assertSame('Branch did not deduct VAT on contractor bills.', $rows[0]['observation']);
        $this->assertSame('Karim Mia (E-01), Free Typed Person, E-02', $rows[0]['responsible_staff_name']);
        $this->assertSame([1, 2], $rows[0]['responsible_staff_ids']);
    }

    public function test_multiple_name_boxes_join_without_appearing_as_finding_title(): void
    {
        $service = app(AuditSummaryService::class);

        $rows = $service->extractMatrixRowsFromBlocks([
            [
                'type' => 'finding',
                'indicator_id' => 77,
                'body' => 'শিরোনাম title',
                'amount' => '1000',
            ],
            [
                'type' => 'observation',
                'label' => 'Observation',
                'body' => 'Detailed পর্যবেক্ষণ text already in report.',
                'matrix_people' => [
                    ['id' => 9, 'code' => 'A1', 'name' => 'Person A'],
                    ['id' => 10, 'code' => 'B2', 'name' => 'Person B'],
                ],
            ],
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame('Detailed পর্যবেক্ষণ text already in report.', $rows[0]['observation']);
        $this->assertSame('Person A (A1), Person B (B2)', $rows[0]['responsible_staff_name']);
        $this->assertSame([9, 10], $rows[0]['responsible_staff_ids']);
        $this->assertNotSame('শিরোনাম title', $rows[0]['observation']);
    }
}
