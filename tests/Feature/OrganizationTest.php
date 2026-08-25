<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_view_areas_and_shakhas(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('areas.index'))
            ->assertOk()
            ->assertSee('All Areas');

        $this->actingAs($user)
            ->get(route('shakhas.index'))
            ->assertOk()
            ->assertSee('All Shakha');
    }

    public function test_authenticated_users_can_create_an_area_and_shakha(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('areas.store'), [
                'name' => 'Gazipur',
                'division' => 'Dhaka',
                'status' => 'active',
            ])
            ->assertRedirect(route('areas.index'));

        $area = Area::query()->where('name', 'Gazipur')->firstOrFail();

        $this->actingAs($user)
            ->post(route('shakhas.store'), [
                'name' => 'Joydebpur Shakha',
                'area_id' => $area->id,
                'code' => 'DHA-100',
                'status' => 'active',
            ])
            ->assertRedirect(route('shakhas.index'));

        $this->assertDatabaseHas('shakhas', [
            'name' => 'Joydebpur Shakha',
            'area_id' => $area->id,
            'code' => 'DHA-100',
        ]);
    }
}
