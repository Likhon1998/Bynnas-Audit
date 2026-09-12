<?php

namespace App\Services;

use App\Models\AuditChecklistFormat;
use App\Models\AuditChecklistSubmission;
use App\Models\AuditReport;
use App\Support\AuditChecklistCatalog;
use App\Support\BanglaNumerals;
use RuntimeException;

/**
 * Opt-in: add a checklist সারসংক্ষেপ into the report as a finding-format pack.
 * Does not auto-seed fails/summaries on save evidence.
 */
class ChecklistReportInfluenceService
{
    public function __construct(private ChecklistAiSummaryService $aiSummaries) {}

    /**
     * Remove legacy auto-seeded bare checklist findings (চি.N / সূত্র: চেকলিস্ট…).
     * Keeps packs created via explicit "Add to report".
     */
    public function purgeLegacyAutoSeededFindings(AuditReport $report): int
    {
        $pages = (array) $report->pages_data;
        $page4 = (array) ($pages['page4'] ?? []);
        $blocks = array_values((array) ($page4['reportBlocks'] ?? []));
        if ($blocks === []) {
            return 0;
        }

        $kept = [];
        $removed = 0;
        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }
            if ($this->isLegacyAutoSeededFinding($block)) {
                $removed++;
                continue;
            }
            $kept[] = $block;
        }

        if ($removed === 0) {
            return 0;
        }

        $kept = $this->renumberBlocks($kept);
        $page4['reportBlocks'] = $kept;

        if (isset($page4['reportSections']) && is_array($page4['reportSections'])) {
            foreach ($page4['reportSections'] as $si => $section) {
                if (! is_array($section)) {
                    continue;
                }
                $findings = array_values((array) ($section['findings'] ?? []));
                $page4['reportSections'][$si]['findings'] = array_values(array_filter(
                    $findings,
                    fn ($f) => is_array($f) && ! $this->isLegacyAutoSeededFinding(array_merge($f, ['type' => 'finding']))
                ));
            }
        }

        $pages['page4'] = $page4;
        $report->forceFill(['pages_data' => $pages, 'last_saved_at' => now()])->save();

        return $removed;
    }

    /**
     * @param  array<string, mixed>  $block
     */
    public function isLegacyAutoSeededFinding(array $block): bool
    {
        $type = (string) ($block['type'] ?? 'finding');
        if ($type !== 'finding') {
            return false;
        }

        $isPack = ! empty($block['checklist_pack']);
        if ($isPack) {
            return false;
        }

        $serial = trim((string) ($block['serial'] ?? ''));
        $title = (string) ($block['title'] ?? '');
        $body = (string) ($block['body'] ?? '');
        $fromChecklist = ! empty($block['from_checklist']) || ($block['source'] ?? '') === 'checklist';

        if (preg_match('/^চি[\.\s]?[০-৯0-9]+/u', $serial)) {
            return true;
        }
        if (str_contains($title, 'সূত্র: চেকলিস্ট') || str_contains($body, 'সূত্র: চেকলিস্ট')) {
            return true;
        }
        if ($fromChecklist && empty($block['checklist_pack'])) {
            return true;
        }

        return false;
    }

    /**
     * @param  list<array<string,mixed>>  $blocks
     * @return list<array<string,mixed>>
     */
    public function stripLegacyAutoSeededFindings(array $blocks): array
    {
        return array_values(array_filter(
            $blocks,
            fn ($b) => ! is_array($b) || ! $this->isLegacyAutoSeededFinding($b)
        ));
    }

    /**
     * Re-attach source meta on পর্যবেক্ষণ boxes that lost it during older normalize passes.
     * Copies from the nearest preceding checklist finding in the same pack.
     *
     * @param  list<array<string,mixed>>  $blocks
     * @return list<array<string,mixed>>
     */
    public function restoreObservationSourceMeta(array $blocks): array
    {
        $lastLabel = '';
        $lastDetail = '';
        $lastFormat = '';
        $lastSeed = '';

        foreach ($blocks as $i => $block) {
            if (! is_array($block)) {
                continue;
            }
            $type = (string) ($block['type'] ?? '');

            if ($type === 'finding' && ! empty($block['checklist_pack'])) {
                $lastLabel = trim((string) ($block['checklist_source_label'] ?? ''));
                $lastFormat = trim((string) ($block['checklist_format_code'] ?? ''));
                $lastSeed = trim((string) ($block['checklist_seed_key'] ?? ''));
                $heading = $lastFormat !== '' ? $this->formatHeadingForCode($lastFormat) : 'Checklist';
                $lastDetail = trim((string) ($block['checklist_source_detail'] ?? ''));
                if ($lastDetail === '') {
                    $lastDetail = 'সূত্র: চেকলিস্ট — '.$heading
                        .($lastLabel !== '' ? ' · '.$lastLabel : '');
                }
                continue;
            }

            if ($type !== 'observation') {
                continue;
            }

            $label = (string) ($block['label'] ?? '');
            if (! str_contains($label, 'পর্যবেক্ষণ') && ! str_contains(strtolower($label), 'observation')) {
                continue;
            }

            if ($lastDetail === '' && $lastLabel === '') {
                continue;
            }

            if (empty($block['checklist_source_detail'])) {
                $blocks[$i]['checklist_source_detail'] = $lastDetail;
            }
            if (empty($block['checklist_source_label']) && $lastLabel !== '') {
                $blocks[$i]['checklist_source_label'] = $lastLabel;
            }
            if (empty($block['checklist_format_code']) && $lastFormat !== '') {
                $blocks[$i]['checklist_format_code'] = $lastFormat;
            }
            if (empty($block['checklist_seed_key']) && $lastSeed !== '') {
                $blocks[$i]['checklist_seed_key'] = $lastSeed.':observation';
            }
            $blocks[$i]['from_checklist'] = true;
            $blocks[$i]['checklist_pack'] = true;
            $blocks[$i]['source'] = 'checklist';
        }

        return array_values($blocks);
    }

    protected function formatHeadingForCode(string $code): string
    {
        $def = AuditChecklistCatalog::findByCode($code);
        if ($def) {
            return (string) ($def['heading'] ?? $code);
        }

        $format = AuditChecklistFormat::query()->where('code', $code)->first();

        return $format ? (string) $format->heading : $code;
    }

    /**
     * @deprecated Auto-seed disabled — use addSummaryToReport().
     */
    public function seedFromSubmission(AuditChecklistSubmission $submission): int
    {
        return 0;
    }

    /**
     * Add one সারসংক্ষেপ as a full finding template pack.
     *
     * @return array{inserted:int,seed_key:string,with_ai:bool}
     */
    public function addSummaryToReport(
        AuditReport $report,
        AuditChecklistFormat $format,
        string $observationText,
        string $seedKey,
        string $sourceLabel,
        bool $withAi = false,
    ): array {
        $observationText = trim($observationText);
        if ($observationText === '') {
            throw new RuntimeException('সারসংক্ষেপ খালি — আগে লিখুন বা AI দিয়ে তৈরি করুন।');
        }

        $formatCode = (string) ($format->code ?: 'format-'.$format->format_number);
        $formatHeading = (string) ($format->heading ?: 'Checklist');

        $pages = (array) $report->pages_data;
        $page4 = (array) ($pages['page4'] ?? []);
        $blocks = array_values((array) ($page4['reportBlocks'] ?? []));

        if ($this->seedKeyExists($blocks, $seedKey)) {
            throw new RuntimeException('এই সারসংক্ষেপ ইতিমধ্যে রিপোর্টে যোগ করা আছে।');
        }

        $risk = '';
        $recommendation = '';
        if ($withAi) {
            if (! $this->aiSummaries->isConfigured()) {
                throw new RuntimeException('OpenAI is not configured. Add OPENAI_API_KEY or use Without AI.');
            }
            $generated = $this->aiSummaries->generateRiskAndRecommendation(
                $observationText,
                $formatHeading.' — '.$sourceLabel
            );
            $risk = $generated['risk'];
            $recommendation = $generated['recommendation'];
        }

        $sectionIndex = $this->findSectionIndexForFormat($blocks, $formatCode);
        $insertAt = count($blocks);
        $includeSection = true;

        if ($sectionIndex !== null) {
            $includeSection = false;
            $insertAt = $this->endOfSectionGroup($blocks, $sectionIndex);
        }

        $pack = $this->buildFindingPack(
            blocks: $blocks,
            includeSection: $includeSection,
            sectionIndexHint: $sectionIndex,
            observation: $observationText,
            risk: $risk,
            recommendation: $recommendation,
            seedKey: $seedKey,
            formatCode: $formatCode,
            sourceLabel: $sourceLabel,
            formatHeading: $formatHeading,
        );

        array_splice($blocks, $insertAt, 0, $pack);
        $blocks = $this->renumberBlocks($blocks);
        $blocks = $this->relinkStatsToNearestFindings($blocks);

        $page4['reportBlocks'] = $blocks;
        $pages['page4'] = $page4;
        $report->forceFill(['pages_data' => $pages, 'last_saved_at' => now()])->save();

        return [
            'inserted' => count($pack),
            'seed_key' => $seedKey,
            'with_ai' => $withAi,
        ];
    }

    /**
     * @param  list<array<string,mixed>>  $blocks
     * @return list<string>
     */
    public function existingSeedKeys(array $blocks): array
    {
        $keys = [];
        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }
            $key = trim((string) ($block['checklist_seed_key'] ?? ''));
            if ($key !== '') {
                $keys[$key] = true;
            }
        }

        return array_keys($keys);
    }

    /**
     * @param  list<array<string,mixed>>  $blocks
     * @return list<array<string,mixed>>
     */
    protected function buildFindingPack(
        array $blocks,
        bool $includeSection,
        ?int $sectionIndexHint,
        string $observation,
        string $risk,
        string $recommendation,
        string $seedKey,
        string $formatCode,
        string $sourceLabel,
        string $formatHeading,
    ): array {
        $pack = [];

        if ($includeSection) {
            $sectionSerial = $this->nextSectionSerial($blocks);
            $pack[] = [
                'type' => 'section',
                'serial' => $sectionSerial,
                'title' => '', // বিভাগ — user fills
                'checklist_pack' => true,
                'from_checklist' => true,
                'checklist_format_code' => $formatCode,
                'checklist_seed_key' => $formatCode.':section',
            ];
            $findingSerial = $this->nextFindingSerialUnderSection($blocks, $sectionSerial, true);
        } else {
            $sectionSerial = (string) ($blocks[$sectionIndexHint]['serial'] ?? $this->nextSectionSerial($blocks));
            $findingSerial = $this->nextFindingSerialUnderSection($blocks, $sectionSerial, false);
        }

        $finding = [
            'type' => 'finding',
            'serial' => $findingSerial,
            'title' => '', // শিরোনাম — user fills
            'body' => '',
            'rating' => '',
            'amount' => '',
            'indicator_id' => null,
            'indicator_code' => null,
            'source' => 'checklist',
            'from_checklist' => true,
            'checklist_pack' => true,
            'checklist_seed_key' => $seedKey,
            'checklist_format_code' => $formatCode,
            'checklist_source_label' => $sourceLabel,
        ];
        $pack[] = $finding;

        $pack[] = [
            'type' => 'criteria',
            'label' => 'প্রচলিত নিয়ম (Criteria):',
            'body' => '', // user fills
            'checklist_pack' => true,
            'checklist_seed_key' => $seedKey.':criteria',
            'checklist_format_code' => $formatCode,
        ];

        $pack[] = [
            'type' => 'observation',
            'label' => 'পর্যবেক্ষণ (Observation) :',
            'body' => $observation,
            'from_checklist' => true,
            'checklist_pack' => true,
            'checklist_seed_key' => $seedKey.':observation',
            'checklist_format_code' => $formatCode,
            'checklist_source_label' => $sourceLabel,
            'checklist_source_detail' => 'সূত্র: চেকলিস্ট — '.$formatHeading
                .($sourceLabel !== '' ? ' · '.$sourceLabel : ''),
        ];

        $pack[] = [
            'type' => 'stats',
            'heading' => 'Report Rating Box:',
            'rows' => [[
                'total_population' => '',
                'sample_size' => '',
                'instances_found' => '',
                'percentage' => '',
            ]],
            'linked_indicator_id' => null,
            'linked_indicator_code' => null,
            'linked_finding_serial' => $findingSerial,
            'linked_finding_title' => '',
            'link_manual' => false,
            'checklist_pack' => true,
            'checklist_format_code' => $formatCode,
        ];

        $pack[] = [
            'type' => 'observation',
            'label' => 'ঝুঁকি/প্রভাব (Risk/Implication) :',
            'body' => $risk,
            'checklist_pack' => true,
            'checklist_format_code' => $formatCode,
        ];

        $pack[] = [
            'type' => 'observation',
            'label' => 'মূল কারণ (Root Cause):',
            'body' => '', // user fills
            'checklist_pack' => true,
            'checklist_format_code' => $formatCode,
        ];

        $pack[] = [
            'type' => 'observation',
            'label' => 'সুপারিশ (Recommendation) :',
            'body' => $recommendation,
            'checklist_pack' => true,
            'checklist_format_code' => $formatCode,
        ];

        $pack[] = [
            'type' => 'jobab_table',
            'rows' => [
                ['cells' => ['শাখা ব্যবস্থাপকের জবাব', '']],
                ['cells' => ['সমস্যা সমাধানের ক্ষেত্রে দায়িত্বপ্রাপ্ত কর্মীর নাম/আইডি ও গৃহীত পদক্ষেপ', '']],
                ['cells' => ['সমাধানের প্রকৃত সময়কাল/সম্ভাব্য সময়কাল (তারিখ)', '']],
            ],
            'checklist_pack' => true,
            'checklist_format_code' => $formatCode,
        ];

        return $pack;
    }

    /**
     * @param  list<array<string,mixed>>  $blocks
     */
    protected function seedKeyExists(array $blocks, string $seedKey): bool
    {
        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }
            if ((string) ($block['checklist_seed_key'] ?? '') === $seedKey) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<array<string,mixed>>  $blocks
     */
    protected function findSectionIndexForFormat(array $blocks, string $formatCode): ?int
    {
        foreach ($blocks as $i => $block) {
            if (! is_array($block)) {
                continue;
            }
            if (($block['type'] ?? '') !== 'section') {
                continue;
            }
            if ((string) ($block['checklist_format_code'] ?? '') === $formatCode) {
                return $i;
            }
        }

        return null;
    }

    /**
     * @param  list<array<string,mixed>>  $blocks
     */
    protected function endOfSectionGroup(array $blocks, int $sectionIndex): int
    {
        $sectionLike = ['section', 'compliance_table', 'it_checklist', 'external_audit', 'audit_score'];
        $end = count($blocks);
        for ($i = $sectionIndex + 1; $i < count($blocks); $i++) {
            $type = (string) ($blocks[$i]['type'] ?? '');
            if (in_array($type, $sectionLike, true)) {
                return $i;
            }
        }

        return $end;
    }

    /**
     * @param  list<array<string,mixed>>  $blocks
     */
    protected function nextSectionSerial(array $blocks): string
    {
        $major = 0;
        foreach ($blocks as $block) {
            if (($block['type'] ?? '') === 'section') {
                $major++;
            }
        }

        return BanglaNumerals::fromInt($major + 1).'.০';
    }

    /**
     * @param  list<array<string,mixed>>  $blocks
     */
    protected function nextFindingSerialUnderSection(array $blocks, string $sectionSerial, bool $sectionBeingAdded): string
    {
        $major = BanglaNumerals::toInt(explode('.', str_replace('٫', '.', $sectionSerial))[0] ?? null) ?? 1;
        $minor = 0;

        if (! $sectionBeingAdded) {
            $inSection = false;
            $sectionLike = ['section', 'compliance_table', 'it_checklist', 'external_audit', 'audit_score'];
            foreach ($blocks as $block) {
                $type = (string) ($block['type'] ?? '');
                if ($type === 'section' && (string) ($block['serial'] ?? '') === $sectionSerial) {
                    $inSection = true;
                    $minor = 0;
                    continue;
                }
                if ($inSection && in_array($type, $sectionLike, true)) {
                    break;
                }
                if ($inSection && $type === 'finding') {
                    $minor++;
                }
            }
        }

        return BanglaNumerals::fromInt($major).'.'.BanglaNumerals::fromInt($minor + 1);
    }

    /**
     * @param  list<array<string,mixed>>  $blocks
     * @return list<array<string,mixed>>
     */
    public function renumberBlocks(array $blocks): array
    {
        $sectionLike = ['section', 'compliance_table', 'it_checklist', 'external_audit', 'audit_score'];
        $sectionMajor = 0;
        $findingParentMajor = 0;
        $findingMinor = 0;
        $serialMap = [];

        foreach ($blocks as $i => $block) {
            if (! is_array($block)) {
                continue;
            }
            $type = (string) ($block['type'] ?? '');
            if (in_array($type, $sectionLike, true)) {
                $sectionMajor++;
                $newSerial = BanglaNumerals::fromInt($sectionMajor).'.০';
                $oldSerial = trim((string) ($block['serial'] ?? ''));
                if ($oldSerial !== '' && $oldSerial !== $newSerial) {
                    $serialMap[$oldSerial] = $newSerial;
                }
                $blocks[$i]['serial'] = $newSerial;
                if ($type === 'section') {
                    $title = trim((string) ($block['title'] ?? ''));
                    // Keep user-empty বিভাগ empty (only store serial separately).
                    if ($title === '' || $title === $oldSerial) {
                        $blocks[$i]['title'] = '';
                    } elseif ($oldSerial !== '' && str_starts_with($title, $oldSerial)) {
                        $rest = trim(mb_substr($title, mb_strlen($oldSerial)));
                        $blocks[$i]['title'] = $rest !== '' ? $newSerial.' '.$rest : '';
                    }
                    $findingParentMajor = $sectionMajor;
                    $findingMinor = 0;
                }
            } elseif ($type === 'finding') {
                if ($findingParentMajor < 1) {
                    $findingParentMajor = max(1, $sectionMajor);
                }
                $findingMinor++;
                $newSerial = BanglaNumerals::fromInt($findingParentMajor).'.'.BanglaNumerals::fromInt($findingMinor);
                $oldSerial = trim((string) ($block['serial'] ?? ''));
                if ($oldSerial !== '' && $oldSerial !== $newSerial) {
                    $serialMap[$oldSerial] = $newSerial;
                }
                $blocks[$i]['serial'] = $newSerial;
            }
        }

        if ($serialMap !== []) {
            foreach ($blocks as $i => $block) {
                if (($block['type'] ?? '') !== 'stats') {
                    continue;
                }
                $linked = trim((string) ($block['linked_finding_serial'] ?? ''));
                if ($linked !== '' && isset($serialMap[$linked])) {
                    $blocks[$i]['linked_finding_serial'] = $serialMap[$linked];
                }
            }
        }

        return array_values($blocks);
    }

    /**
     * @param  list<array<string,mixed>>  $blocks
     * @return list<array<string,mixed>>
     */
    protected function relinkStatsToNearestFindings(array $blocks): array
    {
        $lastFindingSerial = null;
        $lastFindingTitle = null;
        foreach ($blocks as $i => $block) {
            $type = (string) ($block['type'] ?? '');
            if ($type === 'finding') {
                $lastFindingSerial = (string) ($block['serial'] ?? '');
                $lastFindingTitle = (string) ($block['title'] ?? '');
            } elseif ($type === 'stats' && empty($block['link_manual'])) {
                $blocks[$i]['linked_finding_serial'] = $lastFindingSerial;
                $blocks[$i]['linked_finding_title'] = $lastFindingTitle;
            }
        }

        return $blocks;
    }

    public static function sectionSummarySeedKey(string $formatCode, string $sectionKey): string
    {
        return $formatCode.':summary:'.$sectionKey;
    }

    public static function formatSummarySeedKey(string $formatCode): string
    {
        return $formatCode.':summary:main';
    }
}
