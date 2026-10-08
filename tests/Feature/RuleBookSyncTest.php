<?php

namespace Tests\Feature;

use App\Models\Rule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuleBookSyncTest extends TestCase
{
    use RefreshDatabase;

    private string $file = 'storage/framework/testing/rule-book-test.json';

    protected function tearDown(): void
    {
        @unlink(base_path($this->file));
        parent::tearDown();
    }

    private function writeFile(array $rules): void
    {
        @mkdir(dirname(base_path($this->file)), 0755, true);
        file_put_contents(base_path($this->file), json_encode(['rules' => $rules], JSON_UNESCAPED_UNICODE));
    }

    public function test_title_keeps_bangla_letters_intact(): void
    {
        $title = Rule::titleFromStatement('শাখা ব্যবস্থাপক প্রতিদিন নগদ যাচাই করবেন তত।');

        $this->assertTrue(mb_check_encoding($title, 'UTF-8'));
        $this->assertStringEndsWith('তত', $title);
    }

    public function test_bundled_rule_book_file_is_valid(): void
    {
        $payload = json_decode((string) file_get_contents(base_path('database/data/rule-book.json')), true);

        $this->assertIsArray($payload);
        $this->assertNotEmpty($payload['rules']);
        $this->assertSame(count($payload['rules']), $payload['count']);
    }

    public function test_import_adds_updates_and_never_duplicates(): void
    {
        $matching = Rule::query()->create([
            'serial' => 1, 'title' => 'old', 'statement' => "নগদ   যাচাই\nকরতে হবে", 'article' => '', 'source_name' => '',
        ]);
        $prodOnly = Rule::query()->create([
            'serial' => 2, 'title' => 'prod', 'statement' => 'Only on production', 'article' => '', 'source_name' => '',
        ]);

        $this->writeFile([
            ['serial' => 1, 'title' => 'Cash check', 'statement' => 'নগদ যাচাই করতে হবে', 'article' => '4.1', 'source_name' => 'Policy A'],
            ['serial' => 2, 'title' => 'Loan', 'statement' => 'ঋণ বিতরণ নিয়ম', 'article' => '5', 'source_name' => 'Policy A'],
        ]);

        $this->artisan('rulebook:import', ['--file' => $this->file])->assertSuccessful();

        $this->assertSame(3, Rule::query()->count());
        $this->assertSame('4.1', $matching->fresh()->article);
        $this->assertSame('Policy A', $matching->fresh()->source_name);
        $this->assertNotNull($prodOnly->fresh());
        $this->assertSame(3, (int) $prodOnly->fresh()->serial);

        $this->artisan('rulebook:import', ['--file' => $this->file])->assertSuccessful();
        $this->assertSame(3, Rule::query()->count());

        $this->artisan('rulebook:import', ['--file' => $this->file, '--exact' => true])->assertSuccessful();
        $this->assertSame(2, Rule::query()->count());
        $this->assertNull($prodOnly->fresh());
    }

    public function test_dry_run_changes_nothing(): void
    {
        $this->writeFile([
            ['serial' => 1, 'title' => 'A', 'statement' => 'Rule A', 'article' => '', 'source_name' => ''],
        ]);

        $this->artisan('rulebook:import', ['--file' => $this->file, '--dry-run' => true])->assertSuccessful();

        $this->assertSame(0, Rule::query()->count());
    }

    public function test_export_round_trips(): void
    {
        Rule::query()->create([
            'serial' => 1, 'title' => 'x', 'statement' => 'শাখা নিয়ম', 'article' => '1', 'source_name' => 'Doc',
        ]);

        $this->artisan('rulebook:export', ['--file' => $this->file])->assertSuccessful();
        $payload = json_decode((string) file_get_contents(base_path($this->file)), true);

        $this->assertSame(1, $payload['count']);
        $this->assertSame('শাখা নিয়ম', $payload['rules'][0]['statement']);
    }
}
