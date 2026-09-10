<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Shakha;
use App\Models\User;
use App\Support\BangladeshGazetteer;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditMapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['features.map' => true]);
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_map_is_hidden_when_feature_flag_is_off(): void
    {
        config(['features.map' => false]);
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();

        $this->actingAs($admin)->get(route('map.index'))->assertNotFound();
        $this->actingAs($admin)->getJson(route('map.live'))->assertNotFound();
    }

    public function test_guest_is_redirected_from_map(): void
    {
        $this->get(route('map.index'))->assertRedirect(route('login'));
    }

    public function test_user_without_permission_cannot_open_map(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->actingAs($user)->get(route('map.index'))->assertForbidden();
    }

    public function test_superadmin_can_open_map_and_receive_live_markers(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->firstOrFail();
        $area = Area::query()->create([
            'name' => 'Dhaka North Map',
            'division' => 'Dhaka',
            'status' => 'active',
        ]);
        Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Mirpur Shakha',
            'code' => 'MAP-001',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('map.index'))
            ->assertOk()
            ->assertSee('Pouroshova', false)
            ->assertSee('All areas', false)
            ->assertSee('Back to dashboard', false)
            ->assertSee('Assigned auditor', false)
            ->assertSee('Compliance score', false)
            ->assertSee('Priority issues', false)
            ->assertSee('open-shakha-drawer', false)
            ->assertSee('turf.voronoi', false)
            ->assertSee('Satellite', false)
            ->assertSee('OpenStreet', false)
            ->assertSee('selectDivision', false)
            ->assertSee('index.json', false)
            ->assertDontSee('MAIN', false);

        $this->assertFileExists(public_path('geo/all.json'));
        $this->assertFileExists(public_path('geo/index.json'));
        $this->assertFileExists(public_path('geo/divisions/dhaka.json'));
        $this->assertFileExists(public_path('geo/divisions/chattogram.json'));

        $this->actingAs($admin)
            ->getJson(route('map.live'))
            ->assertOk()
            ->assertJsonPath('stats.shakhas', 1)
            ->assertJsonPath('markers.0.name', 'Mirpur Shakha')
            ->assertJsonPath('markers.0.upazila', 'Mirpur')
            ->assertJsonPath('markers.0.area', 'Dhaka North Map')
            ->assertJsonPath('markers.0.division', 'Dhaka')
            ->assertJsonPath('markers.0.auditor', 'Unassigned')
            ->assertJsonPath('markers.0.findings_count', 0)
            ->assertJsonPath('markers.0.issues', [])
            ->assertJsonStructure([
                'markers' => [[
                    'auditor',
                    'compliance_score',
                    'findings_count',
                    'issues',
                ]],
            ]);
    }

    public function test_gazetteer_places_mirpur_in_dhaka(): void
    {
        $point = BangladeshGazetteer::locate('Mirpur Shakha', 'Dhaka North', 'Dhaka');

        $this->assertSame('Mirpur', $point['upazila']);
        $this->assertEqualsWithDelta(23.8223, $point['lat'], 0.05);
        $this->assertEqualsWithDelta(90.3654, $point['lng'], 0.05);
    }
}
