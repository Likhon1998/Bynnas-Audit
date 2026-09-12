<?php

namespace Tests\Unit;

use App\Services\ChecklistAiSummaryService;
use App\Services\OpenAiTextService;
use App\Support\AuditChecklistCatalog;
use Mockery;
use Tests\TestCase;

class ChecklistAiSummaryServiceTest extends TestCase
{
    public function test_build_section_facts_prioritizes_fail_marks(): void
    {
        $def = AuditChecklistCatalog::findByCode('format-1');
        $this->assertNotNull($def);

        $payload = [
            'sections' => [
                'formation' => [
                    [
                        'society_name' => 'সমাজ এ',
                        'field_worker' => 'রহিম',
                        'checks' => ['✓', '✗', 'N/A'],
                    ],
                ],
            ],
        ];

        $service = new ChecklistAiSummaryService(Mockery::mock(OpenAiTextService::class));
        $facts = $service->buildSectionFacts($def, $payload, 'formation');

        $this->assertSame(1, $facts['rows_filled']);
        $this->assertSame(1, $facts['ok_count']);
        $this->assertSame(1, $facts['na_count']);
        $this->assertCount(1, $facts['unusual']);
        $this->assertStringContainsString('সমাজ এ', $facts['unusual'][0]);
        $this->assertStringContainsString('✗', $facts['unusual'][0]);
    }

    public function test_summarize_section_asks_openai_with_unusual_focus(): void
    {
        config(['services.openai.key' => 'test-key']);

        $def = AuditChecklistCatalog::findByCode('format-1');
        $payload = [
            'sections' => [
                'formation' => [
                    [
                        'society_name' => 'সমাজ বি',
                        'field_worker' => '',
                        'checks' => ['✗', '', ''],
                    ],
                ],
            ],
        ];

        $openai = Mockery::mock(OpenAiTextService::class);
        $openai->shouldReceive('complete')
            ->once()
            ->andReturn('গঠন প্রক্রিয়ায় সমাজ বি-তে অনিয়ম পাওয়া গেছে।');

        $service = new ChecklistAiSummaryService($openai);
        $text = $service->summarizeSection($def, $payload, 'formation', 'শাখা-১', '২০২৫-২৬');

        $this->assertStringContainsString('অনিয়ম', $text);
    }
}
