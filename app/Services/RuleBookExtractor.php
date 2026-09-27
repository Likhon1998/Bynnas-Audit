<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

class RuleBookExtractor
{
    /**
     * @return list<array{title:string,statement:string,article:string,where:string,when:string,who:string}>
     */
    public function extract(string $pdfPath, string $originalName): array
    {
        $script = base_path('scripts/extract_pdf_rules.py');
        $process = new Process(
            [
                $this->pythonBinary(),
                $script,
                $pdfPath,
                $originalName,
            ],
            base_path(),
            $this->pythonEnvironment(),
        );
        $process->setTimeout(120);
        $process->run();

        $output = trim($process->getOutput());
        $decoded = json_decode($output, true);

        if (! $process->isSuccessful() || ! is_array($decoded)) {
            $detail = trim($process->getErrorOutput());
            $hint = is_array($decoded) ? (string) ($decoded['error'] ?? '') : '';
            $message = $hint !== '' ? $hint : ($detail !== '' ? $detail : 'Could not read that PDF.');

            throw new RuntimeException($message);
        }

        $text = trim((string) ($decoded['text'] ?? ''));
        $codeRules = $this->normalizeRows($decoded['rules'] ?? []);
        $pageCount = max(1, (int) ($decoded['page_count'] ?? 1));
        $pageImages = is_array($decoded['pages'] ?? null) ? $decoded['pages'] : [];
        $textCoversPages = $this->readableLetters($text) >= ($pageCount * 350);

        if ($textCoversPages && $codeRules !== []) {
            return $codeRules;
        }

        if ($textCoversPages) {
            $readRules = $this->readRulesFromText($text, $originalName);
            if ($readRules !== []) {
                return $readRules;
            }
        }

        $seenRules = $this->readRulesFromImages($pageImages, $originalName);
        if ($seenRules !== []) {
            return $seenRules;
        }

        if ($codeRules !== []) {
            return $codeRules;
        }

        throw new RuntimeException('No rules were found in that PDF.');
    }

    /**
     * @param  mixed  $rows
     * @return list<array{title:string,statement:string,article:string,where:string,when:string,who:string}>
     */
    private function normalizeRows(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        $rules = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $statement = $row['statement'] ?? '';
            if (is_array($statement)) {
                $statement = implode(' ', array_filter($statement, 'is_string'));
            }
            $statement = trim((string) $statement);
            if ($statement === '') {
                continue;
            }
            $article = $row['article'] ?? '';
            if (is_array($article)) {
                $article = implode('', array_filter($article, 'is_scalar'));
            }
            $rules[] = [
                'title' => trim((string) (is_array($row['title'] ?? null) ? '' : ($row['title'] ?? ''))) ?: 'Rule',
                'statement' => $statement,
                'article' => trim((string) $article),
                'where' => trim((string) ($row['where'] ?? '')),
                'when' => trim((string) ($row['when'] ?? '')) ?: 'উল্লেখ নেই',
                'who' => trim((string) ($row['who'] ?? '')) ?: 'উল্লেখ নেই',
            ];
        }

        return $rules;
    }

    private function readableLetters(string $text): int
    {
        preg_match_all('/[A-Za-z\x{0980}-\x{09FF}]/u', $text, $matches);

        return count($matches[0]);
    }

    /**
     * Read the extracted page text and split it into rules. A quoted rule is kept only when
     * those exact words are in the PDF, so the reader cannot invent text.
     *
     * @return list<array{title:string,statement:string,article:string,where:string,when:string,who:string}>
     */
    private function readRulesFromText(string $text, string $originalName): array
    {
        if ($text === '' || ! app(OpenAiTextService::class)->isConfigured()) {
            return [];
        }

        $source = trim(preg_replace('/\.pdf$/i', '', $originalName) ?? $originalName);
        $prompt = <<<TXT
Read this policy text and list every separate rule, whether or not it has a number.
Copy each rule word for word from the text. Do not rewrite, translate, or add anything that is not written.
One rule per obligation or paragraph. Keep the original language.
where: "{$source}"
when and who: only if printed, otherwise "উল্লেখ নেই".
article: the printed number if there is one, otherwise leave empty.
Return only JSON: {"rules":[{"title":"","statement":"","article":"","where":"","when":"","who":""}]}

TEXT:
{$text}
TXT;

        try {
            $raw = app(OpenAiTextService::class)->complete(
                'You split a policy into rules by quoting the source. You never invent a sentence.',
                $prompt,
                ['temperature' => 0, 'maxOutputTokens' => 8000],
            );
        } catch (\Throwable) {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        $kept = [];
        foreach ($this->normalizeRows($decoded['rules'] ?? []) as $rule) {
            if (! $this->quotedFrom($rule['statement'], $text)) {
                continue;
            }
            if ($rule['where'] === '') {
                $rule['where'] = $source;
            }
            $kept[] = $rule;
        }

        return $kept;
    }

    /**
     * When the PDF stores the page as a picture, read every page from top to bottom.
     *
     * @param  list<mixed>  $pages
     * @return list<array{title:string,statement:string,article:string,where:string,when:string,who:string}>
     */
    private function readRulesFromImages(array $pages, string $originalName): array
    {
        $sheets = [];
        foreach ($pages as $page) {
            if (is_string($page) && $page !== '') {
                $sheets[] = [$page];
                continue;
            }
            if (! is_array($page)) {
                continue;
            }
            $slices = array_values(array_filter($page, fn ($image) => is_string($image) && $image !== ''));
            if ($slices !== []) {
                $sheets[] = $slices;
            }
        }

        if ($sheets === [] || ! app(OpenAiTextService::class)->isConfigured()) {
            return [];
        }

        $source = trim(preg_replace('/\.pdf$/i', '', $originalName) ?? $originalName);
        $total = count($sheets);
        $kept = [];
        foreach ($sheets as $index => $slices) {
            $pageNo = $index + 1;
            $prompt = <<<TXT
These images are page {$pageNo} of {$total} of one policy. The first image is the top of the page. The next image, if present, is the bottom of the same page. Read both, including the last line.
Copy every rule exactly as printed, in the original language. A rule may have a number or only be a paragraph.
Do not summarize, shorten, translate, or invent. Include clauses at the bottom, not only the first few.
Skip the title, date, and signature when they are not rules.
where: "{$source}"
when and who: only words printed on this page, otherwise "উল্লেখ নেই".
article: the printed number if the rule has one, otherwise "".
Return JSON: {"rules":[{"title":"short label","statement":"full printed rule","article":"","where":"","when":"","who":""}]}
TXT;

            $raw = app(OpenAiTextService::class)->completeVision(
                'You copy every printed rule from a policy page, from the top through the bottom. You never summarize and you never invent a sentence.',
                $prompt,
                $slices,
                ['maxOutputTokens' => 8000, 'model' => 'gpt-4o'],
            );

            $decoded = json_decode($raw, true);
            if (! is_array($decoded)) {
                continue;
            }
            foreach ($this->normalizeRows($decoded['rules'] ?? []) as $rule) {
                if ($rule['where'] === '') {
                    $rule['where'] = $source;
                }
                $kept[] = $rule;
            }
        }

        return $this->uniqueRules($kept);
    }

    /**
     * @param  list<array{title:string,statement:string,article:string,where:string,when:string,who:string}>  $rules
     * @return list<array{title:string,statement:string,article:string,where:string,when:string,who:string}>
     */
    private function uniqueRules(array $rules): array
    {
        $seen = [];
        $kept = [];
        foreach ($rules as $rule) {
            $key = preg_replace('/\s+/u', '', $rule['statement']) ?? '';
            $key = mb_substr($key, 0, 180);
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $kept[] = $rule;
        }

        return $kept;
    }

    private function quotedFrom(string $statement, string $source): bool
    {
        $needle = preg_replace('/\s+/u', '', $statement) ?? '';
        $haystack = preg_replace('/\s+/u', '', $source) ?? '';
        if ($needle === '' || $haystack === '') {
            return false;
        }

        return str_contains($haystack, mb_substr($needle, 0, min(80, mb_strlen($needle))));
    }

    private function pythonBinary(): string
    {
        $candidates = [
            (string) (getenv('RULE_PYTHON') ?: ''),
            'C:\\laragon\\bin\\python\\python-3.10\\python.exe',
            'C:\\Python313\\python.exe',
        ];

        foreach ($candidates as $binary) {
            if ($binary !== '' && is_file($binary)) {
                return $binary;
            }
        }

        return 'C:\\laragon\\bin\\python\\python-3.10\\python.exe';
    }

    /**
     * Python on Windows fails to start if SystemRoot is missing or PYTHONHASHSEED is empty.
     *
     * @return array<string, string>
     */
    private function pythonEnvironment(): array
    {
        $env = [];
        foreach (array_merge($_SERVER, $_ENV) as $key => $value) {
            if (is_string($key) && is_string($value) && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
                $env[$key] = $value;
            }
        }

        $root = getenv('SYSTEMROOT') ?: getenv('SystemRoot') ?: 'C:\\Windows';
        $env['SYSTEMROOT'] = $root;
        $env['SystemRoot'] = $root;
        $env['WINDIR'] = getenv('WINDIR') ?: $root;
        $env['COMSPEC'] = getenv('COMSPEC') ?: $root.'\\System32\\cmd.exe';
        $env['PATH'] = getenv('PATH') ?: ($env['PATH'] ?? '');
        $env['USERPROFILE'] = getenv('USERPROFILE') ?: 'C:\\Users\\eGen';
        $env['HOMEDRIVE'] = getenv('HOMEDRIVE') ?: 'C:';
        $env['HOMEPATH'] = getenv('HOMEPATH') ?: '\\Users\\eGen';
        $env['PYTHONUTF8'] = '1';
        $env['PYTHONIOENCODING'] = 'utf-8';
        unset($env['PYTHONHASHSEED']);

        return $env;
    }
}
