<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Position;
use App\Models\Area;
use App\Models\Shakha;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAccessManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_superadmin_can_create_employee_login_and_role(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $position = Position::query()->create([
            'serial' => 1,
            'title' => 'Audit Officer',
            'slug' => 'audit-officer-test',
            'color' => '#4C6FFF',
        ]);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Field Officer',
            'email' => 'officer@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'audit_officer',
            'create_employee' => '1',
            'position_id' => $position->id,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('users.index'));

        $user = User::query()->where('email', 'officer@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('audit_officer'));
        $this->assertNotNull($user->employee_id);
        $this->assertDatabaseHas('employees', [
            'email' => 'officer@example.com',
            'position_id' => $position->id,
        ]);
    }

    public function test_superadmin_can_link_existing_employee_and_shakhas(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $position = Position::query()->create([
            'serial' => 2,
            'title' => 'Senior Officer',
            'slug' => 'senior-officer-test',
            'color' => '#4C6FFF',
        ]);
        $employee = Employee::query()->create([
            'position_id' => $position->id,
            'name' => 'Existing Officer',
            'email' => 'existing@example.com',
            'sort_order' => 1,
        ]);
        $area = Area::query()->create([
            'name' => 'Test Area',
            'division' => 'Test Division',
        ]);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Test Branch',
            'code' => 'TB-1',
            'status' => 'active',
        ]);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Existing Officer',
            'email' => 'login@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'audit_officer',
            'employee_id' => $employee->id,
            'is_active' => '1',
            'shakha_ids' => [$shakha->id],
        ])->assertRedirect(route('users.index'));

        $user = User::query()->where('email', 'login@example.com')->firstOrFail();
        $this->assertSame($employee->id, $user->employee_id);
        $this->assertTrue($user->assignedShakhas->contains('id', $shakha->id));
    }

    public function test_officer_cannot_open_users_management(): void
    {
        $officer = User::factory()->create(['is_active' => true]);
        $officer->assignRole('audit_officer');

        $this->actingAs($officer)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_officer_dashboard_shows_my_work_not_ops_pulse(): void
    {
        $officer = User::factory()->create(['is_active' => true, 'name' => 'Officer One']);
        $officer->assignRole('audit_officer');

        $this->actingAs($officer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('My field work')
            ->assertSee('Officer One')
            ->assertSee('Allocation year')
            ->assertDontSee('Operations pulse');
    }

    public function test_manager_sees_ops_dashboard_and_not_users_menu_route(): void
    {
        $manager = User::factory()->create(['is_active' => true]);
        $manager->assignRole('audit_manager');

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Operations pulse');

        $this->actingAs($manager)
            ->get(route('users.index'))
            ->assertForbidden();
    }
}
