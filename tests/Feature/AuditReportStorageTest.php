<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AuditReport;
use App\Models\Shakha;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditReportStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_storage_lists_only_reports_marked_done_at_100_percent(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_superadmin' => true,
            'is_active' => true,
        ]);
        $area = Area::query()->create(['name' => 'Store Area', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Stored Branch',
            'code' => 'ST-1',
            'status' => 'active',
        ]);
        $draft = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Draft Branch',
            'code' => 'ST-2',
            'status' => 'active',
        ]);

        AuditReport::query()->create([
            'user_id' => $user->id,
            'shakha_id' => $shakha->id,
            'shakha_display_name' => 'Stored Branch',
            'report_month' => 9,
            'report_year' => 2026,
            'status' => AuditReport::STATUS_REVIEWED,
            'review_perfect' => true,
            'maker_done_at' => now(),
            'maker_done_by' => $user->id,
        ]);
        AuditReport::query()->create([
            'user_id' => $user->id,
            'shakha_id' => $draft->id,
            'shakha_display_name' => 'Draft Branch',
            'report_month' => 9,
            'report_year' => 2026,
            'status' => AuditReport::STATUS_COMPLETED,
            'progress_pct' => 80,
        ]);

        $this->actingAs($user)
            ->get(route('audits.storage'))
            ->assertOk()
            ->assertSee('Stored Branch')
            ->assertDontSee('Draft Branch')
            ->assertSee('September 2026');
    }
}
