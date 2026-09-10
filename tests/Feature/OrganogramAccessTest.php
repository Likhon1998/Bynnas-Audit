<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use App\Support\RoleAccess;
use Database\Seeders\OrganogramLoginSeeder;
use Database\Seeders\OrganogramSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganogramAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganogramSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        $this->seed(OrganogramLoginSeeder::class);
    }

    public function test_every_organogram_employee_gets_a_login(): void
    {
        $employees = Employee::query()->whereNotNull('email')->count();
        $linkedUsers = User::query()->whereNotNull('employee_id')->count();

        $this->assertGreaterThan(0, $employees);
        $this->assertSame($employees, $linkedUsers);
    }

    public function test_position_slugs_map_to_expected_roles(): void
    {
        $this->assertSame('director_audit', RoleAccess::suggestedRoleFromPosition('Director Audit', 'director-audit'));
        $this->assertSame('audit_manager', RoleAccess::suggestedRoleFromPosition('Joint Director Audit', 'joint-director-audit'));
        $this->assertSame('audit_manager', RoleAccess::suggestedRoleFromPosition('Deputy Director Audit', 'deputy-director-audit'));
        $this->assertSame('audit_manager', RoleAccess::suggestedRoleFromPosition('Assistant Director Audit', 'assistant-director-audit'));
        $this->assertSame('senior_officer', RoleAccess::suggestedRoleFromPosition('Senior Officer Audit', 'senior-officer-audit'));
        $this->assertSame('audit_officer', RoleAccess::suggestedRoleFromPosition('Officer Audit', 'officer-audit'));
        $this->assertSame('audit_officer', RoleAccess::suggestedRoleFromPosition('Audit Officer', 'audit-officer'));
    }

    public function test_director_gets_ops_dashboard(): void
    {
        $director = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'director_audit'))
            ->firstOrFail();

        $this->actingAs($director)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Hello,')
            ->assertSee('Director Audit');
    }

    public function test_officer_gets_officer_dashboard_and_findings_entry_nav(): void
    {
        $officer = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'audit_officer'))
            ->firstOrFail();

        $this->actingAs($officer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('My work');

        $this->actingAs($officer)
            ->get(route('audit-findings.entry'))
            ->assertRedirect(route('dashboard'));

        $this->assertTrue($officer->can('findings.enter'));
        $this->assertFalse($officer->can('findings.view_all'));
    }

    public function test_senior_officer_can_view_findings_matrix(): void
    {
        $senior = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'senior_officer'))
            ->firstOrFail();

        $this->actingAs($senior)
            ->get(route('audit-findings.index'))
            ->assertOk();
    }

    public function test_officer_cannot_manage_users(): void
    {
        $officer = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'audit_officer'))
            ->firstOrFail();

        $this->actingAs($officer)
            ->get(route('users.index'))
            ->assertForbidden();
    }
}
