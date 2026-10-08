<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Database\Seeders\OrganogramSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrganogramTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guests_cannot_view_the_organogram(): void
    {
        $this->get(route('organogram'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_the_audit_organogram(): void
    {
        $this->seed(OrganogramSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('organogram.view');

        $this->actingAs($user)
            ->get(route('organogram'))
            ->assertOk()
            ->assertSee('Audit Organogram')
            ->assertSee('Preview')
            ->assertSee('Organogram preview')
            ->assertSee('Director Audit')
            ->assertSee('Joint Director Audit')
            ->assertSee('Deputy Director Audit')
            ->assertSee('Assistant Director Audit')
            ->assertSee('Senior Officer Audit')
            ->assertDontSee('Add position')
            ->assertSee('Officer Audit')
            ->assertSee('Audit Officer')
            ->assertSee('Mahmud Hasan');
    }

    public function test_authenticated_users_can_add_an_officer_to_a_rank(): void
    {
        Storage::fake('public');
        $this->seed(OrganogramSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('audit_manager');
        $position = Position::query()->where('slug', 'audit-officer')->firstOrFail();

        $this->actingAs($user)
            ->post(route('organogram.employees.store'), [
                'name' => 'New Audit Officer',
                'email' => 'new.officer@bynnasaudit.com',
                'position_id' => $position->id,
                'photo' => UploadedFile::fake()->image('auditor.jpg', 200, 200),
            ])
            ->assertRedirect();

        $employee = Employee::query()
            ->where('name', 'New Audit Officer')
            ->where('position_id', $position->id)
            ->first();

        $this->assertNotNull($employee);
        $this->assertNotNull($employee->photo_path);
        Storage::disk('public')->assertExists($employee->photo_path);
    }

    public function test_authenticated_users_can_add_a_position(): void
    {
        $this->seed(OrganogramSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('audit_manager');

        $this->actingAs($user)
            ->post(route('organogram.positions.store'), [
                'title' => 'Chief Audit Coordinator',
                'serial' => 8,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('positions', [
            'title' => 'Chief Audit Coordinator',
            'serial' => 8,
            'color' => '#4C6FFF',
        ]);
    }

    public function test_authenticated_users_can_remove_an_officer(): void
    {
        $this->seed(OrganogramSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('audit_manager');
        $employee = Employee::query()->firstOrFail();

        $this->actingAs($user)
            ->delete(route('organogram.employees.destroy', $employee))
            ->assertRedirect();

        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }

    public function test_manager_can_edit_an_officer_and_linked_login_follows(): void
    {
        Storage::fake('public');
        $this->seed(OrganogramSeeder::class);
        $manager = User::factory()->create();
        $manager->assignRole('audit_manager');

        $employee = Employee::query()->firstOrFail();
        $employee->update(['email' => 'old.officer@bynnasaudit.com', 'photo_path' => 'organogram-employees/'.$employee->id.'/old.jpg']);
        Storage::disk('public')->put($employee->photo_path, 'old');
        $login = User::factory()->create(['email' => 'old.officer@bynnasaudit.com', 'employee_id' => $employee->id]);
        $newPosition = Position::query()->where('slug', 'audit-officer')->firstOrFail();

        $this->actingAs($manager)
            ->get(route('organogram'))
            ->assertOk()
            ->assertSee('Edit officer');

        $this->actingAs($manager)
            ->put(route('organogram.employees.update', $employee), [
                'name' => 'Renamed Officer',
                'email' => 'renamed.officer@bynnasaudit.com',
                'position_id' => $newPosition->id,
                'photo' => UploadedFile::fake()->image('new.jpg', 120, 120),
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $employee->refresh();
        $this->assertSame('Renamed Officer', $employee->name);
        $this->assertSame('renamed.officer@bynnasaudit.com', $employee->email);
        $this->assertSame($newPosition->id, (int) $employee->position_id);
        Storage::disk('public')->assertMissing('organogram-employees/'.$employee->id.'/old.jpg');
        Storage::disk('public')->assertExists($employee->photo_path);

        $login->refresh();
        $this->assertSame('Renamed Officer', $login->name);
        $this->assertSame('renamed.officer@bynnasaudit.com', $login->email);
    }

    public function test_editing_keeps_a_different_login_email_and_blocks_duplicates(): void
    {
        $this->seed(OrganogramSeeder::class);
        $manager = User::factory()->create();
        $manager->assignRole('audit_manager');

        $employee = Employee::query()->firstOrFail();
        $employee->update(['email' => 'officer@bynnasaudit.com']);
        $login = User::factory()->create(['email' => 'personal.login@bynnasaudit.com', 'employee_id' => $employee->id]);
        User::factory()->create(['email' => 'taken@bynnasaudit.com']);

        $this->actingAs($manager)
            ->put(route('organogram.employees.update', $employee), [
                'name' => $employee->name,
                'email' => 'officer.new@bynnasaudit.com',
                'position_id' => $employee->position_id,
            ])
            ->assertRedirect();
        $this->assertSame('personal.login@bynnasaudit.com', $login->fresh()->email);

        $this->actingAs($manager)
            ->put(route('organogram.employees.update', $employee), [
                'name' => $employee->name,
                'email' => 'taken@bynnasaudit.com',
                'position_id' => $employee->position_id,
                'edit_employee_id' => $employee->id,
            ])
            ->assertSessionHasErrors('email', null, 'editEmployee');
        $this->assertSame('officer.new@bynnasaudit.com', $employee->fresh()->email);
    }

    public function test_view_only_users_cannot_edit_officers(): void
    {
        $this->seed(OrganogramSeeder::class);
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('organogram.view');
        $employee = Employee::query()->firstOrFail();

        $this->actingAs($viewer)
            ->put(route('organogram.employees.update', $employee), [
                'name' => 'Hacked',
                'position_id' => $employee->position_id,
            ])
            ->assertForbidden();

        $this->assertNotSame('Hacked', $employee->fresh()->name);
    }

    public function test_authenticated_users_can_view_the_dashboard(): void
    {
        $this->seed(OrganogramSeeder::class);
        $user = User::factory()->create(['name' => 'Dashboard Viewer']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Hello, Dashboard')
            ->assertSee('Where to go today')
            ->assertSee('My monthly visits')
            ->assertDontSee('Audit organogram ranks');
    }
}
