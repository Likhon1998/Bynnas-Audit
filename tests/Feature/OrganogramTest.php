<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Database\Seeders\OrganogramSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganogramTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_view_the_organogram(): void
    {
        $this->get(route('organogram'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_the_audit_organogram(): void
    {
        $this->seed(OrganogramSeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('organogram'))
            ->assertOk()
            ->assertSee('Audit Organogram')
            ->assertSee('Show')
            ->assertSee('Full Audit Organogram')
            ->assertSee('Director Audit')
            ->assertSee('Joint Director Audit')
            ->assertSee('Deputy Director Audit')
            ->assertSee('Assistant Director Audit')
            ->assertSee('Senior Officer Audit')
            ->assertSee('Add Position')
            ->assertSee('Officer Audit')
            ->assertSee('Audit Officer')
            ->assertSee('Mahmud Hasan');
    }

    public function test_authenticated_users_can_add_an_officer_to_a_rank(): void
    {
        $this->seed(OrganogramSeeder::class);
        $user = User::factory()->create();
        $position = Position::query()->where('slug', 'audit-officer')->firstOrFail();

        $this->actingAs($user)
            ->post(route('organogram.employees.store'), [
                'name' => 'New Audit Officer',
                'email' => 'new.officer@bynnasaudit.com',
                'position_id' => $position->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('employees', [
            'name' => 'New Audit Officer',
            'position_id' => $position->id,
        ]);
    }

    public function test_authenticated_users_can_add_a_position(): void
    {
        $this->seed(OrganogramSeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('organogram.positions.store'), [
                'title' => 'Chief Audit Coordinator',
                'serial' => 8,
                'color' => '#123456',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('positions', [
            'title' => 'Chief Audit Coordinator',
            'serial' => 8,
            'color' => '#123456',
        ]);
    }

    public function test_authenticated_users_can_remove_an_officer(): void
    {
        $this->seed(OrganogramSeeder::class);
        $user = User::factory()->create();
        $employee = Employee::query()->firstOrFail();

        $this->actingAs($user)
            ->delete(route('organogram.employees.destroy', $employee))
            ->assertRedirect();

        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }

    public function test_authenticated_users_can_view_the_dashboard(): void
    {
        $this->seed(OrganogramSeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Organogram')
            ->assertDontSee('Audit organogram ranks');
    }
}
