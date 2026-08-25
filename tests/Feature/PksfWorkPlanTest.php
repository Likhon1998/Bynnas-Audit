<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PksfWorkPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_pksf_tab_matches_excel_layout(): void
    {
        $this->seed(\Database\Seeders\AnnualAuditSeeder::class);

        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('annual-audit.index', ['tab' => 'pksf']))
            ->assertOk()
            ->assertSee('PKSF and Maternity Work Plan', false)
            ->assertSee('1st Quarter', false)
            ->assertSee('Project Name', false)
            ->assertSee('Project Location', false)
            ->assertSee('RMTP', false)
            ->assertSee('RAISE', false)
            ->assertSee('DSK Maternity Hospital', false)
            ->assertSee('Export Excel', false);
    }

    public function test_admin_can_export_pksf_excel(): void
    {
        $this->seed(\Database\Seeders\AnnualAuditSeeder::class);
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)
            ->get(route('annual-audit.export', ['mode' => 'pksf', 'fy' => '2026-2027']));

        $response->assertOk();
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('pksf-maternity-work-plan', (string) $response->headers->get('content-disposition'));
    }
}
