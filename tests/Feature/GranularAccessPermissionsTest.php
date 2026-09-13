<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GranularAccessPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_calendar_view_only_can_open_but_not_manage(): void
    {
        $user = User::factory()->create(['is_active' => true, 'email_verified_at' => now()]);
        $role = Role::findOrCreate('cal_viewer', 'web');
        $role->syncPermissions(['calendar.view', 'dashboard.officer']);
        $user->syncRoles(['cal_viewer']);

        $this->actingAs($user)
            ->get(route('calendar.index'))
            ->assertOk();

        $this->actingAs($user)
            ->post(route('calendar.store'), [
                'holiday_date' => now('Asia/Dhaka')->toDateString(),
                'name' => 'Test Off',
                'type' => 'ngo',
            ])
            ->assertForbidden();
    }

    public function test_projects_view_only_can_browse_but_not_create(): void
    {
        $user = User::factory()->create(['is_active' => true, 'email_verified_at' => now()]);
        $role = Role::findOrCreate('proj_viewer', 'web');
        $role->syncPermissions(['projects.view', 'dashboard.officer']);
        $user->syncRoles(['proj_viewer']);

        $this->actingAs($user)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertDontSee('Add Project');

        $this->actingAs($user)
            ->get(route('projects.create'))
            ->assertForbidden();
    }

    public function test_findings_summary_permissions_are_split(): void
    {
        $viewer = User::factory()->create(['is_active' => true, 'email_verified_at' => now()]);
        $viewerRole = Role::findOrCreate('sum_viewer', 'web');
        $viewerRole->syncPermissions(['findings.summary.view', 'dashboard.officer']);
        $viewer->syncRoles(['sum_viewer']);

        $this->actingAs($viewer)
            ->get(route('audit-findings.summary'))
            ->assertOk()
            ->assertDontSee('Download PPT');

        $this->actingAs($viewer)
            ->get(route('audit-findings.summary.export-ppt'))
            ->assertForbidden();

        $this->actingAs($viewer)
            ->get(route('audit-findings.index'))
            ->assertForbidden();

        $ppt = User::factory()->create(['is_active' => true, 'email_verified_at' => now()]);
        $pptRole = Role::findOrCreate('sum_ppt', 'web');
        $pptRole->syncPermissions([
            'findings.summary.view',
            'findings.summary.export_ppt',
            'dashboard.officer',
        ]);
        $ppt->syncRoles(['sum_ppt']);

        $this->actingAs($ppt)
            ->get(route('audit-findings.summary'))
            ->assertOk()
            ->assertSee('Download PPT');

        $this->actingAs($ppt)
            ->get(route('audit-findings.summary.export-ppt'))
            ->assertOk();
    }

    public function test_new_permissions_appear_on_roles_form(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('roles.create'))
            ->assertOk()
            ->assertSee('Working Calendar (view only)')
            ->assertSee('Projects (view only)')
            ->assertSee('Findings Matrix (full access)')
            ->assertSee('Findings Summary (view)')
            ->assertSee('Findings Summary — download PPT')
            ->assertSee('Findings Summary (edit)');
    }
}
