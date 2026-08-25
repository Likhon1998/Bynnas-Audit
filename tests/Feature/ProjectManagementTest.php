<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_project_with_locations(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->post(route('projects.store'), [
                'name' => 'Coastal Resilience',
                'donor' => 'Donor C',
                'status' => 'active',
                'is_pksf' => false,
                'is_maternity' => false,
                'has_project_audit' => true,
                'has_project_monitoring' => true,
                'locations' => [
                    ['name' => 'Cox\'s Bazar', 'division' => 'Chattogram', 'status' => 'active'],
                    ['name' => 'Patuakhali', 'division' => 'Barishal', 'status' => 'active'],
                ],
            ])
            ->assertRedirect();

        $project = Project::query()->where('name', 'Coastal Resilience')->first();
        $this->assertNotNull($project);
        $this->assertTrue($project->has_project_audit);
        $this->assertCount(2, $project->locations);
    }

    public function test_projects_index_loads(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Projects');
    }
}
