<?php

namespace App\Services;

use App\Support\AuditChecklistCatalog;
use RuntimeException;

/**
 * AI সারসংক্ষেপ for checklist heads — focuses on unusual / failed checks for the audit report.
 */
class ChecklistAiSummaryService
{
    public function __construct(private OpenAiTextService $openai) {}

    public function isConfigured(): bool
    {
        return $this->openai->isConfigured();
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>  $payload
     */
    public function summarizeSection(
        array $definition,
        array $payload,
        string $sectionKey,
        string $shakhaName = '',
        string $auditPeriod = '',
    ): string {
        $section = (array) data_get($definition, 'sections.'.$sectionKey, []);
        if ($section === []) {
            throw new RuntimeException('Unknown checklist section.');
        }

        $facts = $this->buildSectionFacts($definition, $payload, $sectionKey);
        if ($facts['unusual'] === [] && $facts['rows_filled'] === 0) {
            throw new RuntimeException('Fill checklist marks first, then generate সারসংক্ষেপ.');
        }

        return $this->askAi(
            heading: (string) ($definition['heading'] ?? 'Checklist'),
            sectionLabel: (string) ($section['label'] ?? $sectionKey),
            shakhaName: $shakhaName,
            auditPeriod: $auditPeriod,
            facts: $facts,
        );
    }

    /**
     * Whole-format summary (formats without multi-head sections).
     *
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>  $payload
     */
    public function summarizeFormat(
        array $definition,
        array $payload,
        string $shakhaName = '',
        string $auditPeriod = '',
    ): string {
        $facts = $this->buildFormatFacts($definition, $payload);
        if ($facts['unusual'] === [] && $facts['ok_count'] === 0 && $facts['rows_filled'] === 0) {
            throw new RuntimeException('Fill checklist marks first, then generate সারসংক্ষেপ.');
        }

        return $this->askAi(
            heading: (string) ($definition['heading'] ?? 'Checklist'),
            sectionLabel: (string) ($definition['heading'] ?? 'Checklist'),
            shakhaName: $shakhaName,
            auditPeriod: $auditPeriod,
            facts: $facts,
        );
    }

    /**
     * @param  array<string, mixed>  $facts
     */
    protected function askAi(
        string $heading,
        string $sectionLabel,
        string $shakhaName,
        string $auditPeriod,
        array $facts,
    ): string {
        $system = <<<'PROMPT'
You are an internal audit assistant for a Bangladeshi microfinance NGO (DSK / Bynnas Audit).
Write a concise Bangla সারসংক্ষেপ for one checklist head/section.

Rules:
- Write in formal Bangla suitable for an audit report.
- Emphasize unusual / non-compliant points (✗, না, no). These must feed the audit report.
- Mention society/member/staff names when present with failed marks.
- If everything is compliant (✓ / হ্যাঁ), say so briefly — do not invent problems.
- Do not invent facts not in the data.
- 3–6 short sentences (or short bullets). No markdown headings. No English except codes/IDs if present.
- Do not repeat the full question list; summarize findings only.
PROMPT;

        $payload = [
            'checklist' => $heading,
            'section' => $sectionLabel,
            'shakha' => $shakhaName,
            'period' => $auditPeriod,
            'stats' => [
                'rows_filled' => $facts['rows_filled'] ?? 0,
                'ok_count' => $facts['ok_count'] ?? 0,
                'fail_count' => count($facts['unusual'] ?? []),
                'na_count' => $facts['na_count'] ?? 0,
            ],
            'unusual_findings' => $facts['unusual'] ?? [],
            'ok_samples' => array_slice($facts['ok'] ?? [], 0, 8),
        ];

        $user = "DATA (JSON):\n".json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n\nWrite the সারসংক্ষেপ now.";

        return $this->openai->complete($system, $user, [
            'temperature' => 0.25,
            'maxOutputTokens' => 700,
        ]);
    }

    /**
     * @return array{risk:string,recommendation:string}
     */
    public function generateRiskAndRecommendation(string $observation, string $context = ''): array
    {
        $observation = trim($observation);
        if ($observation === '') {
            throw new RuntimeException('Observation text is empty.');
        }

        $system = <<<'PROMPT'
You are an internal audit assistant for a Bangladeshi microfinance NGO (DSK / Bynnas Audit).
Given one পর্যবেক্ষণ (observation), write:
1) ঝুঁকি/প্রভাব (Risk/Implication)
2) সুপারিশ (Recommendation)

Rules:
- Formal Bangla for an audit report.
- Do not invent facts beyond the observation; you may state reasonable audit risks/implications of what is stated.
- Each field: 2–4 short sentences. No markdown. No English except codes/IDs.
- Reply ONLY valid JSON: {"risk":"...","recommendation":"..."}
PROMPT;

        $user = "CONTEXT: {$context}\n\nপর্যবেক্ষণ:\n{$observation}\n\nReturn JSON now.";
        $raw = $this->openai->complete($system, $user, [
            'temperature' => 0.3,
            'maxOutputTokens' => 800,
        ]);

        $json = $raw;
        if (preg_match('/\{.*\}/s', $raw, $m)) {
            $json = $m[0];
        }

        try {
            /** @var array<string,mixed> $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new RuntimeException('AI returned an invalid risk/recommendation response.', previous: $e);
        }

        $risk = trim((string) ($decoded['risk'] ?? ''));
        $recommendation = trim((string) ($decoded['recommendation'] ?? ''));
        if ($risk === '' || $recommendation === '') {
            throw new RuntimeException('AI did not return both risk and recommendation.');
        }

        return [
            'risk' => $risk,
            'recommendation' => $recommendation,
        ];
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>  $payload
     * @return array{unusual:list<string>,ok:list<string>,rows_filled:int,ok_count:int,na_count:int}
     */
    public function buildSectionFacts(array $definition, array $payload, string $sectionKey): array
    {
        $section = (array) data_get($definition, 'sections.'.$sectionKey, []);
        $questions = array_values((array) ($section['questions'] ?? []));
        $rows = (array) data_get($payload, 'sections.'.$sectionKey, []);

        $unusual = [];
        $ok = [];
        $okCount = 0;
        $naCount = 0;
        $rowsFilled = 0;

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $society = trim((string) ($row['society_name'] ?? ''));
            $worker = trim((string) ($row['field_worker'] ?? ''));
            $checks = (array) ($row['checks'] ?? []);
            $hasAny = $society !== '' || $worker !== '' || collect($checks)->contains(fn ($v) => trim((string) $v) !== '');
            if (! $hasAny) {
                continue;
            }
            $rowsFilled++;

            foreach ($checks as $ci => $val) {
                $mark = trim((string) $val);
                if ($mark === '') {
                    continue;
                }
                $q = (string) ($questions[$ci] ?? ('Check #'.((int) $ci + 1)));
                $who = trim(implode(' · ', array_filter([$society, $worker !== '' ? 'মাঠকর্মী: '.$worker : ''])));
                $line = ($who !== '' ? $who.' — ' : '').$q.' → '.$mark;

                if ($this->isFail($mark)) {
                    $unusual[] = $line;
                } elseif ($this->isNa($mark)) {
                    $naCount++;
                } elseif ($this->isOk($mark)) {
                    $okCount++;
                    $ok[] = $line;
                }
            }
        }

        return [
            'unusual' => $unusual,
            'ok' => $ok,
            'rows_filled' => $rowsFilled,
            'ok_count' => $okCount,
            'na_count' => $naCount,
        ];
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>  $payload
     * @return array{unusual:list<string>,ok:list<string>,rows_filled:int,ok_count:int,na_count:int}
     */
    public function buildFormatFacts(array $definition, array $payload): array
    {
        $layout = (string) ($definition['layout'] ?? '');
        $unusual = [];
        $ok = [];
        $okCount = 0;
        $naCount = 0;
        $rowsFilled = 0;

        if ($layout === AuditChecklistCatalog::LAYOUT_SOCIETY_MANAGEMENT
            || isset($payload['items'])) {
            $questions = array_values((array) ($definition['questions'] ?? []));
            foreach ((array) ($payload['items'] ?? []) as $qi => $item) {
                if (! is_array($item)) {
                    continue;
                }
                $comp = strtolower(trim((string) ($item['compliance'] ?? '')));
                if ($comp === '') {
                    continue;
                }
                $rowsFilled++;
                $q = (string) ($questions[$qi] ?? ('Check #'.((int) $qi + 1)));
                $extra = trim((string) ($item['incident_count'] ?? ''));
                $line = $q.' → '.($comp === 'no' ? 'না' : ($comp === 'yes' ? 'হ্যাঁ' : $comp))
                    .($extra !== '' ? ' (ঘটনা: '.$extra.')' : '');
                if ($comp === 'no') {
                    $unusual[] = $line;
                } else {
                    $okCount++;
                    $ok[] = $line;
                }
            }

            return [
                'unusual' => $unusual,
                'ok' => $ok,
                'rows_filled' => $rowsFilled,
                'ok_count' => $okCount,
                'na_count' => $naCount,
            ];
        }

        $questions = array_values((array) ($definition['questions'] ?? []));
        foreach ((array) ($payload['rows'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $society = trim((string) ($row['society_name'] ?? ''));
            $member = trim((string) ($row['member_name'] ?? ''));
            $checks = (array) ($row['checks'] ?? []);
            $hasAny = $society !== '' || $member !== '' || collect($checks)->contains(fn ($v) => trim((string) $v) !== '');
            if (! $hasAny) {
                continue;
            }
            $rowsFilled++;
            $who = trim(implode(' · ', array_filter([$society, $member])));
            foreach ($checks as $ci => $val) {
                $mark = trim((string) $val);
                if ($mark === '') {
                    continue;
                }
                $q = (string) ($questions[$ci] ?? ('Check #'.((int) $ci + 1)));
                $line = ($who !== '' ? $who.' — ' : '').$q.' → '.$mark;
                if ($this->isFail($mark)) {
                    $unusual[] = $line;
                } elseif ($this->isNa($mark)) {
                    $naCount++;
                } elseif ($this->isOk($mark)) {
                    $okCount++;
                    $ok[] = $line;
                }
            }
        }

        return [
            'unusual' => $unusual,
            'ok' => $ok,
            'rows_filled' => $rowsFilled,
            'ok_count' => $okCount,
            'na_count' => $naCount,
        ];
    }

    protected function isFail(string $mark): bool
    {
        return in_array($mark, ['✗', 'x', 'X', 'no', 'No', 'NO', 'না'], true);
    }

    protected function isOk(string $mark): bool
    {
        return in_array($mark, ['✓', 'yes', 'Yes', 'YES', 'হ্যাঁ'], true);
    }

    protected function isNa(string $mark): bool
    {
        return in_array(strtoupper($mark), ['N/A', 'NA'], true);
    }
}
