<?php

namespace Tests\Unit;

use App\Support\RoleAccess;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessDefinitionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_every_seeded_permission_has_label_and_help(): void
    {
        $defined = RoleAccess::allPermissionNames();
        $this->assertNotEmpty($defined);

        foreach ($defined as $permission) {
            $this->assertNotSame($permission, RoleAccess::permissionLabel($permission));
            $this->assertNotSame('', RoleAccess::permissionHelp($permission));
            $this->assertArrayHasKey($permission, RoleAccess::permissionMenuMap());
        }
    }

    public function test_auditor_and_reviewer_capabilities_are_distinct_in_menus(): void
    {
        $makerMenus = RoleAccess::menusFromPermissions(['audits.create']);
        $this->assertContains('Audit Reports', $makerMenus);
        $this->assertContains('Checklists', $makerMenus);
        $this->assertContains('Review Panel (as maker)', $makerMenus);

        $reviewerMenus = RoleAccess::menusFromPermissions(['audits.review']);
        $this->assertContains('Review Panel (as reviewer)', $reviewerMenus);

        $assignMenus = RoleAccess::menusFromPermissions(['audits.review_assign']);
        $this->assertContains('Assign reviewers', $assignMenus);
        $this->assertContains('Auditors log', $assignMenus);
    }

    public function test_catalog_documents_auditor_can_become_reviewer(): void
    {
        $officer = RoleAccess::catalog()['audit_officer'];
        $this->assertStringContainsString('reviewer', mb_strtolower($officer['notes']));
        $this->assertStringContainsString('Auditor', $officer['label']);

        $senior = RoleAccess::catalog()['senior_officer'];
        $this->assertStringContainsString('review', mb_strtolower($senior['notes']));
    }

    public function test_access_model_guide_covers_core_paths(): void
    {
        $titles = collect(RoleAccess::accessModelGuide())->pluck('title')->implode(' ');
        $this->assertStringContainsString('Build roles', $titles);
        $this->assertStringContainsString('Assign a role', $titles);
        $this->assertStringContainsString('Auditor', $titles);
    }

    public function test_catalog_includes_auditor_reviewer_role(): void
    {
        $role = RoleAccess::catalog()['auditor_reviewer'];
        $this->assertStringContainsString('Reviewer', $role['label']);
        $this->assertContains('audits.review', RoleAccess::permissionNamesForRole('auditor_reviewer'));
    }
}
