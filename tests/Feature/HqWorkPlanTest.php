<?php

namespace Tests\Feature;

use App\Models\HqDepartment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HqWorkPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_hq_tab_matches_excel_layout(): void
    {
        $this->seed(\Database\Seeders\AnnualAuditSeeder::class);

        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('annual-audit.index', ['tab' => 'hq']))
            ->assertOk()
            ->assertSee('Headquarters (HQ) Work Plan')
            ->assertSee('1st Quarter')
            ->assertSee('Monthly total')
            ->assertSee('Quarter total')
            ->assertSee('HR and Admin Department')
            ->assertSee('DSK - Mart')
            ->assertSee('Training Department');
    }

    public function test_admin_can_add_and_delete_hq_department(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->post(route('annual-audit.hq.store'), [
                'name' => 'IT & MIS Department',
            ])
            ->assertRedirect(route('annual-audit.index', ['fy' => '2026-2027', 'tab' => 'hq']));

        $department = HqDepartment::query()->where('name', 'IT & MIS Department')->first();
        $this->assertNotNull($department);

        $this->actingAs($user)
            ->delete(route('annual-audit.hq.destroy', $department))
            ->assertRedirect(route('annual-audit.index', ['fy' => '2026-2027', 'tab' => 'hq']));

        $this->assertDatabaseMissing('hq_departments', ['id' => $department->id]);
    }

    public function test_admin_can_export_hq_excel(): void
    {
        $this->seed(\Database\Seeders\AnnualAuditSeeder::class);
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)
            ->get(route('annual-audit.export', ['mode' => 'hq']));

        $response->assertOk();
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $response->headers->get('content-type'));
    }
}
