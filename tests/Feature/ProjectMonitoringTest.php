<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_monitoring_tab_shows_excel_style_layout(): void
    {
        $this->seed(\Database\Seeders\AnnualAuditSeeder::class);

        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('annual-audit.index', ['tab' => 'project_monitoring']))
            ->assertOk()
            ->assertSee('Project Monitoring Work Plan')
            ->assertSee('Name of the Projects / Donor')
            ->assertSee('Location of the Projects')
            ->assertSee('DSK-WASH Water Aid Project');
    }

    public function test_project_audit_tab_matches_excel_style(): void
    {
        $this->seed(\Database\Seeders\AnnualAuditSeeder::class);

        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('annual-audit.index', ['tab' => 'project_audit']))
            ->assertOk()
            ->assertSee('Project Audit Work Plan')
            ->assertSee('Name of the Projects / Donor')
            ->assertSee('DSK Public Toilet project')
            ->assertSee('Mirpur-10, Dhaka');
    }

    public function test_admin_can_add_project_from_monitoring_tab(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->post(route('annual-audit.projects.store'), [
                'name' => 'New DSK Monitoring Project',
                'donor' => 'Test Donor',
                'status' => 'active',
                'has_project_monitoring' => true,
                'locations' => [
                    ['name' => 'Dhaka', 'division' => 'Dhaka'],
                ],
            ])
            ->assertRedirect(route('annual-audit.index', ['fy' => '2026-2027', 'tab' => 'project_monitoring']));

        $project = Project::query()->where('name', 'New DSK Monitoring Project')->first();
        $this->assertNotNull($project);
        $this->assertTrue($project->has_project_monitoring);
        $this->assertCount(1, $project->locations);
    }

    public function test_admin_can_export_project_audit_excel(): void
    {
        $this->seed(\Database\Seeders\AnnualAuditSeeder::class);

        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)
            ->get(route('annual-audit.export', ['mode' => 'audit']));

        $response->assertOk();
        $this->assertStringContainsString(
            'spreadsheetml.sheet',
            (string) $response->headers->get('content-type')
        );
        $this->assertStringContainsString(
            'attachment',
            (string) $response->headers->get('content-disposition')
        );
    }

    public function test_exported_project_audit_total_is_not_in_june_column(): void
    {
        $this->seed(\Database\Seeders\AnnualAuditSeeder::class);

        $plan = \App\Models\AuditPlan::query()->where('fy_label', '2026-2027')->firstOrFail();
        $path = storage_path('framework/testing-project-audit.xlsx');

        $response = app(\App\Services\ProjectWorkPlanExcelExporter::class)->download($plan, 'audit');
        ob_start();
        $response->sendContent();
        file_put_contents($path, ob_get_clean());

        $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path)->getActiveSheet();

        $this->assertSame("June'27", (string) $sheet->getCell('O4')->getValue());
        $this->assertSame('Total', (string) $sheet->getCell('P4')->getValue());

        $total = $sheet->getCell('P5')->getCalculatedValue();
        $this->assertIsNumeric($total);
        $this->assertGreaterThan(0, (int) $total);

        // June may be blank or 1 — never the row total (e.g. 4)
        $june = $sheet->getCell('O5')->getCalculatedValue();
        $juneInt = (int) ($june ?: 0);
        $this->assertTrue($juneInt === 0 || $juneInt === 1);
        $this->assertNotSame((int) $total, $juneInt);

        @unlink($path);
    }

    public function test_admin_can_remove_location_and_project(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->post(route('annual-audit.projects.store'), [
                'name' => 'Temp Monitoring Project',
                'donor' => 'Temp',
                'status' => 'active',
                'has_project_monitoring' => true,
                'locations' => [
                    ['name' => 'Dhaka', 'division' => 'Dhaka'],
                    ['name' => 'Khulna', 'division' => 'Khulna'],
                ],
            ])
            ->assertRedirect();

        $project = Project::query()->where('name', 'Temp Monitoring Project')->firstOrFail();
        $location = $project->locations()->where('name', 'Khulna')->firstOrFail();

        $this->actingAs($user)
            ->delete(route('annual-audit.projects.locations.destroy', [$project, $location]))
            ->assertRedirect(route('annual-audit.index', ['fy' => '2026-2027', 'tab' => 'project_monitoring']));

        $this->assertDatabaseMissing('project_locations', ['id' => $location->id]);
        $this->assertCount(1, $project->fresh()->locations);

        $this->actingAs($user)
            ->delete(route('annual-audit.projects.destroy', $project))
            ->assertRedirect(route('annual-audit.index', ['fy' => '2026-2027', 'tab' => 'project_monitoring']));

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
}
