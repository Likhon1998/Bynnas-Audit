<?php

namespace Tests\Feature;

use App\Livewire\MakeAuditReport;
use App\Models\Area;
use App\Models\Shakha;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OutlineActiveHighlightTest extends TestCase
{
    use RefreshDatabase;

    public function test_outline_blue_highlight_moves_to_clicked_finding(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_superadmin' => true,
            'is_active' => true,
        ]);
        $area = Area::query()->create(['name' => 'Area O', 'division' => 'Dhaka', 'status' => 'active']);
        $shakha = Shakha::query()->create([
            'area_id' => $area->id,
            'name' => 'Outline Branch',
            'code' => 'OUT-1',
            'status' => 'active',
        ]);

        $component = Livewire::actingAs($user)
            ->test(MakeAuditReport::class)
            ->set('report_month', 9)
            ->set('report_year', 2026)
            ->call('startReport', $shakha->id)
            ->set('activeTab', 'page4')
            ->set('outlineActiveAnchor', 'audit-page4');

        $findingAnchor = null;
        foreach ($component->get('reportBlocks') as $block) {
            if (($block['type'] ?? '') !== 'finding') {
                continue;
            }
            $findingAnchor = MakeAuditReport::findingAnchorId((string) ($block['serial'] ?? ''));
            if ($findingAnchor !== '') {
                break;
            }
        }

        // Ensure at least one finding exists for the test.
        if ($findingAnchor === null || $findingAnchor === '') {
            $component->call('insertBlockAt', 0, 'finding');
            $blocks = $component->get('reportBlocks');
            foreach ($blocks as $i => $block) {
                if (($block['type'] ?? '') === 'finding') {
                    $blocks[$i]['serial'] = '৪.১';
                    $blocks[$i]['body'] = 'Ortho songkranto issue';
                    $component->set('reportBlocks', $blocks);
                    $findingAnchor = MakeAuditReport::findingAnchorId('৪.১');
                    break;
                }
            }
        }

        $this->assertNotSame('', (string) $findingAnchor);

        $component
            ->call('goToOutlineItem', 'page4', $findingAnchor)
            ->assertSet('activeTab', 'page4')
            ->assertSet('outlineActiveAnchor', $findingAnchor)
            ->assertSeeHtml('bg-[#2b579a] text-white');
    }
}
