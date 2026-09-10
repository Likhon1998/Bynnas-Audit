<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Shakha;
use App\Models\ShakhaEmployee;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShakhaEmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_manager_can_view_shakha_employee_index(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        [$area, $shakha] = $this->makeBranch();

        ShakhaEmployee::query()->create([
            'shakha_id' => $shakha->id,
            'employee_code' => 'E-101',
            'name' => 'Karim Hossain',
            'designation' => 'Branch Manager',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('shakha-employees.index', ['area_id' => $area->id]))
            ->assertOk()
            ->assertSee('Shakha Employees')
            ->assertSee('Select a shakha')
            ->assertDontSee('Karim Hossain');

        $this->actingAs($admin)
            ->get(route('shakha-employees.index', [
                'division' => 'Dhaka',
                'area_id' => $area->id,
                'shakha_id' => $shakha->id,
            ]))
            ->assertOk()
            ->assertSee('Karim Hossain')
            ->assertSee('E-101')
            ->assertSee('Branch Manager');
    }

    public function test_employee_index_scopes_shakhas_to_selected_division(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $dhaka = Area::query()->create(['name' => 'Dhaka North', 'division' => 'Dhaka', 'status' => 'active']);
        $sylhet = Area::query()->create(['name' => 'Sylhet East', 'division' => 'Sylhet', 'status' => 'active']);
        $dhakaShakha = Shakha::query()->create(['area_id' => $dhaka->id, 'name' => 'Mirpur Staff Branch', 'code' => 'DHK-S1', 'status' => 'active']);
        $sylhetShakha = Shakha::query()->create(['area_id' => $sylhet->id, 'name' => 'Sylhet Staff Branch', 'code' => 'SYL-S1', 'status' => 'active']);

        $this->actingAs($admin)
            ->get(route('shakha-employees.index', ['division' => 'Dhaka']))
            ->assertOk()
            ->assertSee('Mirpur Staff Branch')
            ->assertDontSee('Sylhet Staff Branch');

        $this->assertDatabaseHas('shakhas', ['id' => $dhakaShakha->id]);
        $this->assertDatabaseHas('shakhas', ['id' => $sylhetShakha->id]);
    }

    public function test_shakhas_are_ranked_by_employee_count_highest_first(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $area = Area::query()->create(['name' => 'Rank Area', 'division' => 'Dhaka', 'status' => 'active']);
        $low = Shakha::query()->create(['area_id' => $area->id, 'name' => 'Low Staff Shakha', 'code' => 'LOW-1', 'status' => 'active']);
        $high = Shakha::query()->create(['area_id' => $area->id, 'name' => 'High Staff Shakha', 'code' => 'HIGH-1', 'status' => 'active']);

        ShakhaEmployee::query()->create([
            'shakha_id' => $low->id,
            'employee_code' => 'L-1',
            'name' => 'One Person',
            'designation' => 'FO',
            'status' => 'active',
        ]);
        foreach (['A', 'B', 'C'] as $i => $code) {
            ShakhaEmployee::query()->create([
                'shakha_id' => $high->id,
                'employee_code' => 'H-'.$code,
                'name' => 'Staff '.$code,
                'designation' => 'FO',
                'status' => 'active',
            ]);
        }

        $response = $this->actingAs($admin)
            ->get(route('shakha-employees.index', ['area_id' => $area->id]))
            ->assertOk();

        $highPos = strpos($response->getContent(), 'High Staff Shakha');
        $lowPos = strpos($response->getContent(), 'Low Staff Shakha');
        $this->assertNotFalse($highPos);
        $this->assertNotFalse($lowPos);
        $this->assertLessThan($lowPos, $highPos);
    }

    public function test_manager_can_add_employee_under_shakha(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        [, $shakha] = $this->makeBranch();

        $this->actingAs($admin)
            ->post(route('shakha-employees.store', $shakha), [
                'employee_code' => 'E-202',
                'name' => 'Nusrat Jahan',
                'designation' => 'Cashier',
                'phone' => '01700000000',
                'status' => 'active',
                'joined_shakha_at' => '2024-01-15',
            ])
            ->assertRedirect(route('shakha-employees.manage', $shakha));

        $this->assertDatabaseHas('shakha_employees', [
            'shakha_id' => $shakha->id,
            'employee_code' => 'E-202',
            'name' => 'Nusrat Jahan',
            'designation' => 'Cashier',
        ]);
    }

    public function test_employee_code_must_be_unique_within_shakha(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        [, $shakha] = $this->makeBranch();

        ShakhaEmployee::query()->create([
            'shakha_id' => $shakha->id,
            'employee_code' => 'DUP-1',
            'name' => 'First',
            'designation' => 'Officer',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->from(route('shakha-employees.manage', $shakha))
            ->post(route('shakha-employees.store', $shakha), [
                'employee_code' => 'DUP-1',
                'name' => 'Second',
                'designation' => 'Officer',
                'status' => 'active',
            ])
            ->assertRedirect(route('shakha-employees.manage', $shakha))
            ->assertSessionHasErrors('employee_code');
    }

    public function test_manager_can_update_and_remove_employee(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        [, $shakha] = $this->makeBranch();

        $employee = ShakhaEmployee::query()->create([
            'shakha_id' => $shakha->id,
            'employee_code' => 'E-303',
            'name' => 'Old Name',
            'designation' => 'Clerk',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->put(route('shakha-employees.update', $employee), [
                'employee_code' => 'E-303',
                'name' => 'Updated Name',
                'designation' => 'Senior Clerk',
                'status' => 'inactive',
            ])
            ->assertRedirect(route('shakha-employees.manage', $shakha));

        $this->assertDatabaseHas('shakha_employees', [
            'id' => $employee->id,
            'name' => 'Updated Name',
            'designation' => 'Senior Clerk',
            'status' => 'inactive',
        ]);

        $this->actingAs($admin)
            ->delete(route('shakha-employees.destroy', $employee))
            ->assertRedirect(route('shakha-employees.manage', $shakha));

        $this->assertDatabaseMissing('shakha_employees', ['id' => $employee->id]);
    }

    public function test_officer_without_manage_cannot_add_employee(): void
    {
        $officer = User::factory()->create();
        $officer->assignRole('audit_officer');
        [, $shakha] = $this->makeBranch();

        $this->actingAs($officer)
            ->post(route('shakha-employees.store', $shakha), [
                'employee_code' => 'E-404',
                'name' => 'Blocked',
                'designation' => 'Staff',
                'status' => 'active',
            ])
            ->assertForbidden();
    }

    public function test_manager_can_upload_and_replace_employee_photo(): void
    {
        Storage::fake('public');

        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        [, $shakha] = $this->makeBranch();

        $this->actingAs($admin)
            ->post(route('shakha-employees.store', $shakha), [
                'employee_code' => 'E-505',
                'name' => 'Photo Person',
                'designation' => 'Officer',
                'status' => 'active',
                'photo' => UploadedFile::fake()->image('staff.jpg', 200, 200),
            ])
            ->assertRedirect(route('shakha-employees.manage', $shakha));

        $employee = ShakhaEmployee::query()->where('employee_code', 'E-505')->firstOrFail();
        $this->assertNotNull($employee->photo_path);
        Storage::disk('public')->assertExists($employee->photo_path);

        $oldPath = $employee->photo_path;

        $this->actingAs($admin)
            ->put(route('shakha-employees.update', $employee), [
                'employee_code' => 'E-505',
                'name' => 'Photo Person',
                'designation' => 'Officer',
                'status' => 'active',
                'photo' => UploadedFile::fake()->image('staff-new.png', 180, 180),
            ])
            ->assertRedirect(route('shakha-employees.manage', $shakha));

        $employee->refresh();
        $this->assertNotSame($oldPath, $employee->photo_path);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($employee->photo_path);

        $this->actingAs($admin)
            ->get(route('shakha-employees.manage', $shakha))
            ->assertOk()
            ->assertSee('Photo Person');
    }

    /**
     * @return array{0: Area, 1: Shakha}
     */
    private function makeBranch(): array
    {
        $area = Area::query()->create([
            'name' => 'North Area',
            'division' => 'Dhaka',
            'status' => 'active',
        ]);

        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Mirpur Shakha',
            'code' => 'MIR-1',
            'status' => 'active',
        ]);

        return [$area, $shakha];
    }
}
