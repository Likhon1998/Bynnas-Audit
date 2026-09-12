<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AuditFinding;
use App\Models\AuditIndicator;
use App\Models\Shakha;
use App\Models\ShakhaEmployee;
use App\Models\User;
use App\Services\AuditSummaryService;
use App\Services\StaffFinancialOccurrenceService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffFinancialIdentityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_lifetime_report_count_survives_transfer_across_years(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $area = Area::query()->create(['name' => 'Metro', 'division' => 'Dhaka', 'status' => 'active']);
        $shakhaA = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Shakha A', 'code' => 'A', 'status' => 'active']);
        $shakhaB = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Shakha B', 'code' => 'B', 'status' => 'active']);
        $shakhaC = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Shakha C', 'code' => 'C', 'status' => 'active']);

        $indicator = AuditIndicator::query()->create([
            'category' => 'Financial',
            'sub_category' => null,
            'indicator_code' => 'FIN-ID-1',
            'title' => 'Cash shortage',
            'risk_rating' => 'Major',
            'is_active' => true,
        ]);

        $employee = ShakhaEmployee::query()->create([
            'shakha_id' => $shakhaA->id,
            'employee_code' => 'FIXED-99',
            'name' => 'Repeat Offender',
            'designation' => 'FO',
            'status' => 'active',
        ]);
        $fixedId = (int) $employee->id;

        AuditFinding::query()->create([
            'shakha_id' => $shakhaA->id,
            'audit_indicator_id' => $indicator->id,
            'audit_month' => 1,
            'audit_year' => 2024,
            'amount' => 100,
            'responsible_staff_name' => 'Repeat Offender (FIXED-99)',
            'responsible_staff_ids' => [$fixedId],
        ]);

        $this->actingAs($admin)
            ->post(route('shakha-employees.transfer', $employee), [
                'target_shakha_id' => $shakhaB->id,
            ])
            ->assertRedirect();

        $employee->refresh();
        $this->assertSame($fixedId, (int) $employee->id);
        $this->assertSame('FIXED-99', $employee->employee_code);
        $this->assertSame($shakhaB->id, (int) $employee->shakha_id);

        AuditFinding::query()->create([
            'shakha_id' => $shakhaB->id,
            'audit_indicator_id' => $indicator->id,
            'audit_month' => 6,
            'audit_year' => 2025,
            'amount' => 200,
            'responsible_staff_name' => 'Repeat Offender (FIXED-99)',
            'responsible_staff_ids' => [$fixedId],
        ]);

        $this->actingAs($admin)
            ->post(route('shakha-employees.transfer', $employee), [
                'target_shakha_id' => $shakhaC->id,
            ])
            ->assertRedirect();

        AuditFinding::query()->create([
            'shakha_id' => $shakhaC->id,
            'audit_indicator_id' => $indicator->id,
            'audit_month' => 9,
            'audit_year' => 2026,
            'amount' => 300,
            'responsible_staff_name' => 'Repeat Offender (FIXED-99)',
            'responsible_staff_ids' => [$fixedId],
        ]);

        $payload = app(StaffFinancialOccurrenceService::class)->dossierPayload($employee->fresh());
        $this->assertSame(3, $payload['report_count']);
        $this->assertSame(3, $payload['shakha_count']);
        $this->assertSame(2, $payload['transfer_count']);

        $this->actingAs($admin)
            ->get(route('shakha-employees.dossier', $employee))
            ->assertOk()
            ->assertSee('আর্থিক রিপোর্ট')
            ->assertSee('FIXED-99')
            ->assertSee('Shakha A')
            ->assertSee('Shakha B')
            ->assertSee('Shakha C');

        $groups = app(AuditSummaryService::class)->getMonthUnderheadingSummary(9, 2026);
        $people = $groups[0]['rows'][0]['branch_rows'][0]['accused_people'] ?? [];
        $this->assertNotEmpty($people);
        $this->assertSame($fixedId, (int) $people[0]['id']);
        $this->assertSame(3, (int) $people[0]['report_count']);
        $this->assertNotEmpty($people[0]['dossier_url']);
    }

    public function test_sync_from_matrix_people_persists_staff_ids(): void
    {
        $area = Area::query()->create(['name' => 'Metro', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Branch', 'code' => 'BR', 'status' => 'active']);
        $employee = ShakhaEmployee::query()->create([
            'shakha_id' => $shakha->id,
            'employee_code' => 'SYNC-1',
            'name' => 'Sync Person',
            'designation' => 'FO',
            'status' => 'active',
        ]);
        $indicator = AuditIndicator::query()->create([
            'category' => 'Financial',
            'sub_category' => null,
            'indicator_code' => 'SYNC-IND',
            'title' => 'Sync indicator',
            'risk_rating' => 'Major',
            'is_active' => true,
        ]);

        $report = \App\Models\AuditReport::query()->create([
            'shakha_id' => $shakha->id,
            'report_month' => 9,
            'report_year' => 2026,
            'status' => 'draft',
            'pages_data' => [
                'page4' => [
                    'reportBlocks' => [
                        [
                            'type' => 'finding',
                            'indicator_id' => $indicator->id,
                            'body' => 'Title',
                            'amount' => '150',
                        ],
                        [
                            'type' => 'observation',
                            'label' => 'পর্যবেক্ষণ',
                            'body' => 'Observed shortage',
                            'matrix_people' => [
                                ['id' => $employee->id, 'code' => 'SYNC-1', 'name' => 'Sync Person'],
                            ],
                        ],
                        [
                            'type' => 'stats',
                            'linked_indicator_id' => $indicator->id,
                            'rows' => [['sample_size' => '5', 'instances_found' => '1']],
                        ],
                    ],
                ],
            ],
        ]);

        app(AuditSummaryService::class)->syncFromReport($report);

        $finding = AuditFinding::query()
            ->where('shakha_id', $shakha->id)
            ->where('audit_indicator_id', $indicator->id)
            ->first();

        $this->assertNotNull($finding);
        $this->assertSame([(int) $employee->id], $finding->responsibleStaffIds());
        $this->assertSame('Sync Person (SYNC-1)', $finding->responsible_staff_name);
    }
}
