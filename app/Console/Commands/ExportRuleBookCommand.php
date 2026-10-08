<?php

namespace App\Console\Commands;

use App\Models\Rule;
use Illuminate\Console\Command;

class ExportRuleBookCommand extends Command
{
    public const DEFAULT_FILE = 'database/data/rule-book.json';

    protected $signature = 'rulebook:export {--file= : Output path relative to the project root}';

    protected $description = 'Write every rule book entry to a JSON file that rulebook:import can load on another server';

    public function handle(): int
    {
        $file = base_path($this->option('file') ?: self::DEFAULT_FILE);

        $rules = Rule::query()->orderBy('serial')->orderBy('id')->get()->map(fn (Rule $rule) => [
            'serial' => (int) $rule->serial,
            'title' => mb_check_encoding((string) $rule->title, 'UTF-8') ? (string) $rule->title : Rule::titleFromStatement((string) $rule->statement),
            'statement' => (string) $rule->statement,
            'article' => (string) ($rule->article ?? ''),
            'reference_where' => (string) ($rule->reference_where ?? ''),
            'reference_when' => (string) ($rule->reference_when ?? ''),
            'reference_who' => (string) ($rule->reference_who ?? ''),
            'source_name' => (string) ($rule->source_name ?? ''),
            'created_at' => $rule->created_at?->toDateTimeString(),
        ])->values();

        if (! is_dir(dirname($file))) {
            mkdir(dirname($file), 0755, true);
        }

        file_put_contents($file, json_encode([
            'exported_at' => now()->toDateTimeString(),
            'count' => $rules->count(),
            'rules' => $rules,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");

        $this->info("Exported {$rules->count()} rule(s) to ".str_replace(base_path().DIRECTORY_SEPARATOR, '', $file));

        return self::SUCCESS;
    }
}
