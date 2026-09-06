<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_superadmin_can_create_custom_role_with_permissions(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('roles.store'), [
                'label' => 'Area Manager',
                'name' => 'area_manager',
                'permissions' => [
                    'monthly_visits.execute',
                    'audits.create',
                    'dashboard.officer',
                ],
            ])
            ->assertRedirect(route('roles.index'));

        $role = Role::query()->where('name', 'area_manager')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('audits.create'));
        $this->assertFalse($role->hasPermissionTo('users.manage'));
    }

    public function test_superadmin_can_update_custom_role_permissions(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $role = Role::create(['name' => 'viewer', 'guard_name' => 'web']);
        $role->syncPermissions(['dashboard.officer']);

        $this->actingAs($admin)
            ->put(route('roles.update', $role), [
                'label' => 'Viewer',
                'name' => 'viewer',
                'permissions' => [
                    'dashboard.officer',
                    'findings.enter',
                ],
            ])
            ->assertRedirect(route('roles.index'));

        $this->assertTrue($role->fresh()->hasPermissionTo('findings.enter'));
    }

    public function test_cannot_delete_builtin_role(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $officer = Role::query()->where('name', 'audit_officer')->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('roles.destroy', $officer))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseHas('roles', ['name' => 'audit_officer']);
    }

    public function test_cannot_edit_superadmin_role(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $role = Role::query()->where('name', 'superadmin')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('roles.update', $role), [
                'label' => 'Super Admin',
                'name' => 'superadmin',
                'permissions' => ['dashboard.officer'],
            ])
            ->assertSessionHasErrors('role');
    }

    public function test_officer_cannot_manage_roles(): void
    {
        $officer = User::factory()->create(['is_active' => true]);
        $officer->assignRole('audit_officer');

        $this->actingAs($officer)
            ->get(route('roles.index'))
            ->assertForbidden();
    }

    public function test_custom_role_appears_on_user_create_form(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $role = Role::create(['name' => 'area_lead', 'guard_name' => 'web']);
        $role->syncPermissions(['audits.create', 'dashboard.officer']);

        $this->actingAs($admin)
            ->get(route('users.create'))
            ->assertOk()
            ->assertSee('area_lead')
            ->assertSee('Area Lead');
    }
}
