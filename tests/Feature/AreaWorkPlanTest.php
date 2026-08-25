<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AreaWorkPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_area_tab_matches_excel_layout(): void
    {
        $this->seed(\Database\Seeders\OrganizationSeeder::class);
        $this->seed(\Database\Seeders\ShakhaSeeder::class);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user)->post(route('annual-audit.generate'))->assertRedirect();

        $this->actingAs($user)
            ->get(route('annual-audit.index', ['tab' => 'area']))
            ->assertOk()
            ->assertSee('Area Office Work Plan', false)
            ->assertSee('1st Quarter', false)
            ->assertSee('2nd Quarter', false)
            ->assertSee('Area Name', false)
            ->assertSee('Export Excel', false)
            ->assertSee('Monthly total', false);
    }

    public function test_admin_can_export_area_excel(): void
    {
        $this->seed(\Database\Seeders\OrganizationSeeder::class);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user)->post(route('annual-audit.generate'))->assertRedirect();

        $response = $this->actingAs($user)
            ->get(route('annual-audit.export', ['mode' => 'area', 'fy' => '2026-2027']));

        $response->assertOk();
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('area-office-work-plan', (string) $response->headers->get('content-disposition'));
    }
}
