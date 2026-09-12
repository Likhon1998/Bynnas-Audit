<?php

namespace Tests\Feature;

use App\Livewire\MakeAuditReport;
use App\Models\Area;
use App\Models\Shakha;
use App\Models\ShakhaEmployee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ObservationAccusedPeopleTest extends TestCase
{
    use RefreshDatabase;

    public function test_ovijukto_button_opens_and_survives_rerender(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_superadmin' => true,
            'is_active' => true,
        ]);
        $area = Area::query()->create(['name' => 'Area A', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Branch A',
            'code' => 'BA-1',
            'status' => 'active',
        ]);
        ShakhaEmployee::query()->create([
            'shakha_id' => $shakha->id,
            'employee_code' => 'K-1',
            'name' => 'Karim Mia',
            'designation' => 'FO',
            'status' => 'active',
        ]);

        $component = Livewire::actingAs($user)
            ->test(MakeAuditReport::class)
            ->set('report_month', 9)
            ->set('report_year', 2026)
            ->call('startReport', $shakha->id)
            ->set('activeTab', 'page4');

        $blocks = $component->get('reportBlocks');
        $obsIndex = null;
        foreach ($blocks as $i => $block) {
            if (($block['type'] ?? '') === 'observation'
                && (str_contains(mb_strtolower((string) ($block['label'] ?? '')), 'পর্যবেক্ষণ')
                    || str_contains(mb_strtolower((string) ($block['label'] ?? '')), 'observation'))) {
                $obsIndex = (int) $i;
                break;
            }
        }

        $this->assertNotNull($obsIndex, 'Expected a পর্যবেক্ষণ observation block on page 4');

        $component
            ->assertSee('অভিযুক্ত আছে?')
            ->call('openObservationPeople', $obsIndex)
            ->assertSet("reportBlocks.{$obsIndex}.show_matrix_people", true)
            ->assertSee('অভিযুক্ত কর্মী (একাধিক)')
            ->assertSee('Employee ID / নাম খুঁজুন')
            // Simulate typing in observation body (triggers normalize on re-render).
            ->set("reportBlocks.{$obsIndex}.body", 'Some observation text')
            ->assertSet("reportBlocks.{$obsIndex}.show_matrix_people", true)
            ->assertSee('অভিযুক্ত কর্মী (একাধিক)')
            ->call('hideObservationPeople', $obsIndex)
            ->assertSet("reportBlocks.{$obsIndex}.show_matrix_people", false)
            ->assertSee('অভিযুক্ত আছে?');
    }

    public function test_add_nam_creates_another_empty_person_slot(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_superadmin' => true,
            'is_active' => true,
        ]);
        $area = Area::query()->create(['name' => 'Area B', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Branch B',
            'code' => 'BB-1',
            'status' => 'active',
        ]);

        $component = Livewire::actingAs($user)
            ->test(MakeAuditReport::class)
            ->set('report_month', 9)
            ->set('report_year', 2026)
            ->call('startReport', $shakha->id)
            ->set('activeTab', 'page4');

        $obsIndex = null;
        foreach ($component->get('reportBlocks') as $i => $block) {
            if (($block['type'] ?? '') === 'observation'
                && (str_contains((string) ($block['label'] ?? ''), 'পর্যবেক্ষণ')
                    || str_contains(mb_strtolower((string) ($block['label'] ?? '')), 'observation'))) {
                $obsIndex = (int) $i;
                break;
            }
        }
        $this->assertNotNull($obsIndex);

        $component
            ->call('openObservationPeople', $obsIndex)
            ->call('addObservationPerson', $obsIndex)
            ->assertCount("reportBlocks.{$obsIndex}.matrix_people", 2)
            ->call('addObservationPerson', $obsIndex)
            ->assertCount("reportBlocks.{$obsIndex}.matrix_people", 3)
            ->call('applyObservationPerson', $obsIndex, 0, 11, 'KRM-0001-01', 'Karim Mia')
            ->assertSet("reportBlocks.{$obsIndex}.matrix_people.0.code", 'KRM-0001-01')
            ->assertSet("reportBlocks.{$obsIndex}.matrix_people.0.name", 'Karim Mia')
            ->assertSee('KRM-0001-01')
            ->assertSee('Karim Mia');
    }
}

