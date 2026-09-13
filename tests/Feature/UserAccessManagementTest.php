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

    public function test_superadmin_can_deactivate_login_and_block_sign_in(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $officer = User::factory()->create([
            'email' => 'blocked@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $officer->assignRole('audit_officer');

        $this->actingAs($admin)
            ->patch(route('users.toggle-active', $officer))
            ->assertRedirect(route('users.index'));

        $this->assertFalse($officer->fresh()->is_active);

        auth()->logout();

        $this->post('/login', [
            'email' => 'blocked@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_superadmin_can_delete_employee_login(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $officer = User::factory()->create(['is_active' => true]);
        $officer->assignRole('audit_officer');
        $id = $officer->id;

        $this->actingAs($admin)
            ->delete(route('users.destroy', $officer))
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $id]);
    }

    public function test_officer_dashboard_shows_my_work_not_ops_pulse(): void
    {
        $officer = User::factory()->create(['is_active' => true, 'name' => 'Officer One']);
        $officer->assignRole('audit_officer');

        $this->actingAs($officer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Hello, Officer')
            ->assertSee('Where to go today')
            ->assertSee('My monthly visits')
            ->assertDontSee('Unassigned visits')
            ->assertDontSee('Active projects');
    }

    public function test_superadmin_assigns_role_only_not_personal_permissions(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('users.create'))
            ->assertOk()
            ->assertSee('Grant access')
            ->assertSee('Assign one role')
            ->assertSee('Manage roles')
            ->assertSee('Extra shakha access')
            ->assertDontSee('Select access');

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Role Only Officer',
            'email' => 'role.only@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'audit_officer',
            'permissions' => [
                'findings.view_all', // ignored — role-based only
            ],
            'is_active' => '1',
        ])->assertRedirect(route('users.index'));

        $user = User::query()->where('email', 'role.only@example.com')->firstOrFail();
        $this->assertSame('audit_officer', $user->access_profile);
        $this->assertTrue($user->hasRole('audit_officer'));
        $this->assertSame('Audit Officer (Auditor)', $user->roleLabel());
        $this->assertTrue($user->can('audits.create'));
        $this->assertFalse($user->can('findings.view_all'));
        $this->assertFalse($user->hasRole(\App\Support\RoleAccess::personalAccessRoleName((int) $user->id)));
    }

    public function test_custom_role_permissions_apply_when_assigned(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();

        $this->actingAs($admin)->post(route('roles.store'), [
            'label' => 'Branch Reviewer',
            'name' => 'branch_reviewer',
            'permissions' => [
                'audits.create',
                'audits.review',
                'dashboard.officer',
                'map.view',
            ],
        ])->assertRedirect(route('roles.index'));

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Branch Reviewer User',
            'email' => 'branch.reviewer@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'branch_reviewer',
            'is_active' => '1',
        ])->assertRedirect(route('users.index'));

        $user = User::query()->where('email', 'branch.reviewer@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('branch_reviewer'));
        $this->assertTrue($user->can('audits.review'));
        $this->assertTrue($user->can('audits.create'));
        $this->assertFalse($user->can('users.manage'));
    }

    public function test_manager_sees_ops_dashboard_and_not_users_menu_route(): void
    {
        $manager = User::factory()->create(['is_active' => true]);
        $manager->assignRole('audit_manager');

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Total shakha')
            ->assertSee('Significant')
            ->assertSee('Annual plan shakhas')
            ->assertSee('Monthly plan shakhas')
            ->assertDontSee('Annual target achieved')
            ->assertSee('Key Performance Indicator (KPI) entered')
            ->assertDontSee('Where to go today');

        $this->actingAs($manager)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_superadmin_can_sync_correct_auditor_access_package(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();

        $auditor = User::factory()->create([
            'email' => 'sync.auditor@example.com',
            'is_active' => true,
            'access_profile' => 'audit_officer',
        ]);
        $personal = \Spatie\Permission\Models\Role::findOrCreate(
            \App\Support\RoleAccess::personalAccessRoleName((int) $auditor->id),
            'web'
        );
        $personal->syncPermissions([
            'audits.create',
            'findings.enter',
            'monthly_visits.execute',
            'dashboard.officer',
            'map.view',
            'findings.view_all',
            'users.manage',
        ]);
        $auditor->syncRoles([$personal->name]);

        $reviewerAuditor = User::factory()->create([
            'email' => 'sync.reviewer.auditor@example.com',
            'is_active' => true,
            'access_profile' => 'audit_officer',
        ]);
        $reviewerAuditor->assignRole('audit_officer');
        \App\Models\AuditReviewerAssignment::query()->create([
            'auditor_user_id' => $auditor->id,
            'reviewer_user_id' => $reviewerAuditor->id,
            'assigned_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('users.sync-auditors'))
            ->assertRedirect(route('users.index'));

        $auditor->refresh();
        $reviewerAuditor->refresh();

        $this->assertTrue($auditor->hasRole('audit_officer'));
        $this->assertTrue($auditor->can('audits.create'));
        $this->assertTrue($auditor->can('findings.enter'));
        $this->assertFalse($auditor->can('findings.view_all'));
        $this->assertFalse($auditor->can('users.manage'));

        $this->assertTrue($reviewerAuditor->hasRole('auditor_reviewer'));
        $this->assertTrue($reviewerAuditor->can('audits.create'));
        $this->assertTrue($reviewerAuditor->can('audits.review'));
        $this->assertFalse($reviewerAuditor->can('audits.review_assign'));
    }
}
