<?php

namespace App\Console\Commands;

use App\Models\Rule;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ImportRuleBookCommand extends Command
{
    protected $signature = 'rulebook:import
        {--file= : JSON file made by rulebook:export (relative to the project root)}
        {--exact : Also delete rules that are not in the file, so this server matches it exactly}
        {--dry-run : Show what would change without saving}';

    protected $description = 'Add or update rule book entries from a rulebook:export file (matched by rule text, never duplicated)';

    public function handle(): int
    {
        $file = base_path($this->option('file') ?: ExportRuleBookCommand::DEFAULT_FILE);
        if (! is_file($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        $payload = json_decode((string) file_get_contents($file), true);
        if (! is_array($payload)) {
            $this->error('The file is not valid JSON: '.json_last_error_msg());

            return self::FAILURE;
        }
        $incoming = collect($payload['rules'] ?? [])->filter(fn ($row) => is_array($row) && trim((string) ($row['statement'] ?? '')) !== '');
        if ($incoming->isEmpty()) {
            $this->error('The file has no rules.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $existing = Rule::query()->orderBy('serial')->orderBy('id')->get();
        $byKey = $existing->keyBy(fn (Rule $rule) => $this->key($rule->statement));

        $created = 0;
        $updated = 0;
        $unchanged = 0;
        $matchedIds = [];

        DB::transaction(function () use ($incoming, $byKey, $dryRun, &$created, &$updated, &$unchanged, &$matchedIds) {
            foreach ($incoming as $row) {
                $statement = trim((string) $row['statement']);
                $title = trim((string) ($row['title'] ?? ''));
                $values = [
                    'serial' => (int) ($row['serial'] ?? 0),
                    'title' => $title !== '' && mb_check_encoding($title, 'UTF-8') ? mb_substr($title, 0, 255) : Rule::titleFromStatement($statement),
                    'statement' => $statement,
                    'article' => trim((string) ($row['article'] ?? '')),
                    'reference_where' => (string) ($row['reference_where'] ?? ''),
                    'reference_when' => (string) ($row['reference_when'] ?? ''),
                    'reference_who' => (string) ($row['reference_who'] ?? ''),
                    'source_name' => trim((string) ($row['source_name'] ?? '')),
                ];

                $rule = $byKey->get($this->key($values['statement']));

                if ($rule instanceof Rule) {
                    $matchedIds[] = $rule->id;
                    $rule->fill($values);
                    if (! $rule->isDirty()) {
                        $unchanged++;

                        continue;
                    }
                    $updated++;
                    $this->line('  update  #'.$rule->id.' '.mb_substr($values['statement'], 0, 70));
                    if (! $dryRun) {
                        $rule->save();
                    }

                    continue;
                }

                $created++;
                $this->line('  add     '.mb_substr($values['statement'], 0, 70));
                if (! $dryRun) {
                    $new = new Rule($values + ['created_by' => null]);
                    if (! empty($row['created_at'])) {
                        $new->created_at = Carbon::parse($row['created_at']);
                    }
                    $new->save();
                    $matchedIds[] = $new->id;
                }
            }
        });

        $extras = $existing->reject(fn (Rule $rule) => in_array($rule->id, $matchedIds, true));
        $deleted = 0;

        if ($extras->isNotEmpty()) {
            if ($this->option('exact')) {
                foreach ($extras as $rule) {
                    $this->line('  delete  #'.$rule->id.' '.mb_substr((string) $rule->statement, 0, 70));
                }
                if (! $dryRun) {
                    Rule::query()->whereIn('id', $extras->pluck('id'))->delete();
                }
                $deleted = $extras->count();
            } elseif (! $dryRun) {
                $next = (int) Rule::query()->max('serial');
                foreach ($extras as $rule) {
                    if ($incoming->contains(fn ($row) => (int) ($row['serial'] ?? 0) === (int) $rule->serial)) {
                        $rule->update(['serial' => ++$next]);
                    }
                }
            }
        }

        $prefix = $dryRun ? '[dry run] ' : '';
        $this->newLine();
        $this->info("{$prefix}Rule book: {$created} added, {$updated} updated, {$unchanged} already up to date"
            .($this->option('exact') ? ", {$deleted} deleted" : '').'.');
        if ($extras->isNotEmpty() && ! $this->option('exact')) {
            $this->warn("Kept {$extras->count()} rule(s) that exist only on this server (use --exact to remove them).");
        }
        if (! $dryRun) {
            $this->info('Total rules now: '.Rule::query()->count());
        }

        return self::SUCCESS;
    }

    private function key(?string $statement): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim((string) $statement)) ?? '');
    }
}
