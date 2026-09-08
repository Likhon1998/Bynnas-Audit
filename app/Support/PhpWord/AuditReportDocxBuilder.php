<?php

namespace App\Support\PhpWord;

use App\Livewire\MakeAuditReport;
use App\Support\AuditComplianceHeading;
use App\Support\AuditDocumentLayout as Doc;
use App\Support\AuditReportClassification;
use App\Support\AuditScoreSheet;
use App\Support\AuditTableHeaders;
use App\Support\BanglaNumerals;
use App\Support\CustomTableSchema;
use Carbon\Carbon;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class AuditReportDocxBuilder
{
    private const FONT = 'Nirmala UI';

    private PhpWord $word;

    /** @var array<string, mixed> */
    private array $fontBody;

    /** @var array<string, mixed> */
    private array $fontBold;

    /** @var array<string, mixed> */
    private array $fontSmall;

    /** @var array<string, mixed> */
    private array $fontTitle;

    /** @var array<string, mixed> */
    private array $gridTable;

    /** @var array<string, mixed> */
    private array $plainTable;

    /**
     * @param  array<string, mixed>  $data
     */
    public function build(array $data): string
    {
        $this->bootDocument();

        $section = $this->word->addSection($this->sectionSettings());
        $this->buildCover($section, $data);
        $section->addPageBreak();
        $this->buildOverview($section, $data);
        $this->buildSignatures($section, $data);
        $this->buildClassification($section, $data);

        if ($this->hasFinancialSheet($data)) {
            $this->buildFinancial($section, $data);
        }

        if ($this->hasFinancialDetailSheet($data)) {
            $this->buildFinancialDetail($section, $data);
        }

        if ($this->hasFinancialPage6Sheet($data)) {
            $this->buildFinancialPage6($section, $data);
        }

        if ($this->hasFinancialPage7Sheet($data)) {
            $this->buildFinancialPage7($section, $data);
        }

        if ($this->hasFinancialPage8Sheet($data)) {
            $this->buildFinancialPage8($section, $data);
        }

        if ($this->hasFinancialPage9Sheet($data)) {
            $this->buildFinancialPage9($section, $data);
        }

        if ($this->hasFinancialPage10Sheet($data)) {
            $this->buildFinancialPage10($section, $data);
        }

        if ($this->hasFinancialPage11Sheet($data)) {
            $this->buildFinancialPage11($section, $data);
        }

        if ($this->hasFinancialPage12Sheet($data)) {
            $this->buildFinancialPage12($section, $data);
        }

        if ($this->hasFinancialPage13Sheet($data)) {
            $this->buildFinancialPage13($section, $data);
        }

        if ($this->hasFinancialPage14Sheet($data)) {
            $this->buildFinancialPage14($section, $data);
        }

        if ($this->hasFinancialPage15Sheet($data)) {
            $this->buildFinancialPage15($section, $data);
        }

        if ($this->hasFinancialPage16Sheet($data)) {
            $this->buildFinancialPage16($section, $data);
        }

        if ($this->hasFinancialPage17Sheet($data)) {
            $this->buildFinancialPage17($section, $data);
        }

        if ($this->hasFinancialPage18Sheet($data)) {
            $this->buildFinancialPage18($section, $data);
        }

        if ($this->hasFinancialPage19Sheet($data)) {
            $this->buildFinancialPage19($section, $data);
        }

        if ($this->hasFinancialPage20Sheet($data)) {
            $this->buildFinancialPage20($section, $data);
        }

        if ($this->hasFinancialPage21Sheet($data)) {
            $this->buildFinancialPage21($section, $data);
        }

        return $this->toBinary();
    }

    protected function bootDocument(): void
    {
        $this->word = new PhpWord;
        $this->word->setDefaultFontName(self::FONT);
        $this->word->setDefaultFontSize(11);

        $this->fontBody = ['name' => self::FONT, 'size' => 11, 'color' => '111111'];
        $this->fontBold = ['name' => self::FONT, 'size' => 11, 'bold' => true, 'color' => '111111'];
        $this->fontSmall = ['name' => self::FONT, 'size' => 9.5, 'color' => '111111'];
        $this->fontTitle = ['name' => self::FONT, 'size' => 14, 'bold' => true, 'color' => '111111', 'underline' => 'single'];

        $this->gridTable = [
            'borderSize' => 6,
            'borderColor' => '222222',
            'cellMargin' => 60,
            'alignment' => Jc::CENTER,
            'width' => 100 * 50,
            'unit' => 'pct',
        ];

        $this->plainTable = [
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
            'cellMargin' => 40,
            'width' => 100 * 50,
            'unit' => 'pct',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function sectionSettings(): array
    {
        return [
            'marginTop' => Converter::cmToTwip(Doc::MARGIN_TOP / 10),
            'marginBottom' => Converter::cmToTwip(Doc::MARGIN_BOTTOM / 10),
            'marginLeft' => Converter::cmToTwip(Doc::MARGIN_LEFT / 10),
            'marginRight' => Converter::cmToTwip(Doc::MARGIN_RIGHT / 10),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildCover($section, array $data): void
    {
        $table = $section->addTable($this->plainTable);
        $table->addRow();
        $left = $table->addCell($this->pct(68), ['valign' => 'top']);
        $this->addLogoOrOrg($left, $data);

        $right = $table->addCell($this->pct(32), ['valign' => 'top']);
        $this->addCoverRating($right, $data);

        $this->addSpacer($section, 160);
        $this->addLabelValue($section, 'সূত্র নাম্বার:', $data['memo_no'] ?? '');
        $this->addLabelValue($section, 'তারিখ:', $this->fmtDate($data['report_date'] ?? null));

        $this->addSpacer($section, 200);
        foreach ([
            'বরাবর,',
            'যুগ্ম পরিচালক (নিরীক্ষা)',
            'দুঃস্থ স্বাস্থ্য কেন্দ্র (ডিএসকে)',
            'প্রধান কার্যালয়, ঢাকা।',
        ] as $line) {
            $section->addText($line, $this->fontBody);
        }

        $section->addText('অভ্যন্তরীণ নিরীক্ষা প্রতিবেদন', $this->fontTitle, ['alignment' => Jc::CENTER, 'spaceBefore' => 200, 'spaceAfter' => 200]);

        $this->addLabelValue($section, 'শাখার নাম ও নাম্বার:', $data['shakha_display_name'] ?? '');
        $this->addLabelValue($section, 'অঞ্চলের নাম:', $data['area_display_name'] ?? '');
        $this->addLabelValue($section, 'নিরীক্ষাকাল:', $data['audit_period_label'] ?? '');

        $this->addSpacer($section, 160);
        $section->addText('প্রিয় মহোদয়,', $this->fontBold);
        $section->addText(
            sprintf(
                'গত %s হতে %s পর্যন্ত মোট %s কর্ম দিবস %s শাখা হতে %s সময়ের উপর অভ্যন্তরীণ নিরীক্ষা সম্পন্ন করা হয়। শাখার খসড়া প্রতিবেদন %s ইং তারিখে প্রেরণ করা হয় এবং %s তারিখে মতামত পাওয়া যায়। এতদসংক্রান্ত অভ্যন্তরীণ নিরীক্ষা প্রতিবেদন আপনার সদয় অবগতির জন্য পেশ করা হলো।',
                $this->fmtDate($data['audit_start_date'] ?? null),
                $this->fmtDate($data['audit_end_date'] ?? null),
                ($data['working_days'] ?? '') !== '' ? (string) $data['working_days'] : '……',
                $data['shakha_display_name'] ?? '………………',
                $data['period_scope'] ?? '………………',
                $this->fmtDate($data['draft_sent_date'] ?? null),
                $this->fmtDate($data['comments_received_date'] ?? null),
            ),
            $this->fontBody,
            ['alignment' => Jc::BOTH, 'spaceBefore' => 80]
        );

        $this->addSpacer($section, 200);
        $section->addText('আপনার বিশ্বস্ত,', $this->fontBody);
        $this->addSpacer($section, 240);
        $this->addLabelValue($section, 'নাম:', $data['auditor_name'] ?? '');
        $this->addLabelValue($section, 'পদবী:', $data['auditor_designation'] ?? '');

        $this->addSpacer($section, 200);
        $section->addText('অনুলিপি:', $this->fontBold);
        $copyRecipients = \App\Support\AuditCopyRecipients::normalize($data['copy_recipients'] ?? null);
        foreach ($copyRecipients as $index => $item) {
            $section->addText(BanglaNumerals::fromInt($index + 1).'. '.$item, $this->fontBody, ['indentation' => ['left' => 360]]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildOverview($section, array $data): void
    {
        $section->addText(
            'এক নজরে '.($data['shakha_display_name'] ?: '………………').' শাখার তথ্য ('.($data['glance_as_of'] ?: '………………').'):',
            $this->fontBold
        );
        $section->addText(
            'শাখা গঠনের তারিখ: '.$this->fmtDate($data['branch_opening_date'] ?? null).' ইং',
            $this->fontBody,
            ['spaceBefore' => 80]
        );

        $this->addGlanceTable($section, $data['glanceRows'] ?? []);

        $section->addText(
            'শাখার কর্মীর তথ্য : '.$this->fmtDate($data['staff_info_as_of'] ?? null).' ইং',
            $this->fontBold,
            ['spaceBefore' => 200]
        );
        $this->addStaffTable($section, $data['staffColumns'] ?? [], $data['staffRows'] ?? []);

        $section->addText('সূচিপত্র', ['name' => self::FONT, 'size' => 12.5, 'bold' => true, 'underline' => 'single'], ['alignment' => Jc::CENTER, 'spaceBefore' => 240, 'spaceAfter' => 120]);
        $this->addTocTable($section, $this->overviewRows($data), $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildSignatures($section, array $data): void
    {
        $this->addSpacer($section, 240);
        $table = $section->addTable($this->gridTable);
        $table->addRow(Converter::cmToTwip(1.8));
        $dash = '………………';

        $cells = [
            [
                'নিরীক্ষা কর্মকর্তার নাম: '.($data['sign_auditor_name'] ?? $dash),
                'পদবী: '.($data['sign_auditor_designation'] ?? $dash),
                'তারিখ: '.$this->fmtDate($data['sign_auditor_date'] ?? null),
            ],
            [
                'শাখা ব্যবস্থাপকের নাম: '.($data['sign_bm_name'] ?? $dash),
                '',
                'তারিখ: '.$this->fmtDate($data['sign_bm_date'] ?? null),
            ],
            [
                'সহকারী শাখা ব্যবস্থাপকের নাম: '.($data['sign_abm_name'] ?? $dash),
                '',
                'তারিখ: '.$this->fmtDate($data['sign_abm_date'] ?? null),
            ],
        ];

        foreach ($cells as $lines) {
            $cell = $table->addCell($this->pct(33.33), ['valign' => 'top']);
            foreach ($lines as $line) {
                if ($line === '') {
                    $this->addSpacerCell($cell, 160);
                    continue;
                }
                $cell->addText($line, $this->fontSmall, ['spaceAfter' => 80]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildClassification($section, array $data): void
    {
        $this->addSpacer($section, 200);
        $section->addText('প্রতিবেদনের শ্রেণীবিন্যাস', ['name' => self::FONT, 'size' => 12.5, 'bold' => true, 'underline' => 'single'], ['alignment' => Jc::CENTER, 'spaceAfter' => 120]);

        $table = $section->addTable($this->gridTable);
        $table->addRow();
        $importanceHeaders = AuditTableHeaders::get($data['tableHeaders'] ?? [], 'classification_importance');
        foreach ($importanceHeaders as $index => $heading) {
            $widths = [16, 7, 77];
            $table->addCell($this->pct($widths[$index]), ['bgColor' => 'BDD7EE', 'valign' => 'center'])
                ->addText($heading, ['name' => self::FONT, 'size' => 8.5, 'bold' => true], ['alignment' => $index === 2 ? Jc::START : Jc::CENTER]);
        }

        foreach (AuditReportClassification::ratingRows() as $row) {
            $table->addRow();
            if ($row['level'] !== null) {
                $table->addCell($this->pct(16), ['vMerge' => 'restart', 'valign' => 'center'])
                    ->addText($row['level'], ['name' => self::FONT, 'size' => 8.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            } else {
                $table->addCell($this->pct(16), ['vMerge' => 'continue']);
            }

            $table->addCell($this->pct(7), ['bgColor' => ltrim($row['code_bg'], '#'), 'valign' => 'center'])
                ->addText($row['code'], ['name' => self::FONT, 'size' => 8.5, 'bold' => true, 'color' => ltrim($row['code_color'], '#')], ['alignment' => Jc::CENTER]);

            $detail = $table->addCell($this->pct(77), ['valign' => 'top']);
            foreach ($row['items'] as $item) {
                $prefix = $row['bulleted'] ? '• ' : '';
                $detail->addText($prefix.$item, ['name' => self::FONT, 'size' => 8.5], ['alignment' => Jc::BOTH, 'spaceAfter' => 40]);
            }
        }

        $summary = $section->addTable($this->gridTable);
        $summary->addRow();
        $summary->addCell($this->pct(70), ['bgColor' => '2E5090', 'valign' => 'center'])
            ->addText($this->hdr($data, 'classification_eval', 0), ['name' => self::FONT, 'size' => 8.5, 'bold' => true, 'color' => 'FFFFFF'], ['alignment' => Jc::CENTER]);
        $summary->addCell($this->pct(30), ['bgColor' => '2E5090', 'valign' => 'center'])
            ->addText($this->hdr($data, 'classification_eval', 1), ['name' => self::FONT, 'size' => 8.5, 'bold' => true, 'color' => 'FFFFFF'], ['alignment' => Jc::CENTER]);

        foreach (AuditReportClassification::performanceSummaryRows() as $row) {
            $summary->addRow();
            $summary->addCell($this->pct(70))->addText($row['label'], $this->fontSmall);
            $summary->addCell($this->pct(30), ['valign' => 'center'])
                ->addText($row['range'], ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildFinancial($section, array $data): void
    {
        $this->addSpacer($section, 240);

        $blocks = $data['reportBlocks'] ?? [];
        if ($blocks === []) {
            $sections = $data['reportSections'] ?? [];
            if ($sections === []) {
                $sections = [[
                    'serial' => '১.০',
                    'title' => $data['financial_section_title'] ?? '১.০ আর্থিক নিরীক্ষা (Financial Audit) :',
                    'findings' => $data['financialFindings'] ?? [],
                ]];
            }
            foreach ($sections as $reportSection) {
                $blocks[] = [
                    'type' => 'section',
                    'serial' => $reportSection['serial'] ?? '১.০',
                    'title' => $reportSection['title'] ?? '',
                ];
                foreach ($reportSection['findings'] ?? [] as $finding) {
                    $blocks[] = array_merge(['type' => 'finding'], is_array($finding) ? $finding : []);
                }
            }
            $blocks[] = [
                'type' => 'criteria',
                'label' => 'প্রচলিত নিয়ম (Criteria):',
                'body' => $data['financial_criteria'] ?? '',
            ];
            $blocks[] = [
                'type' => 'observation',
                'label' => 'পর্যবেক্ষণ (Observation) :',
                'body' => '',
            ];
            $blocks[] = [
                'type' => 'stats',
                'heading' => 'Report Rating Box:',
                'rows' => $data['vatObservationRows'] ?? [],
            ];
            $blocks[] = [
                'type' => 'stats',
                'heading' => 'Report Rating Box:',
                'rows' => $data['taxObservationRows'] ?? [],
            ];
        }

        $widths = Doc::findingColumnWidths();
        foreach ($blocks as $bIndex => $block) {
            $type = $block['type'] ?? '';
            if ($type === 'section') {
                if ($bIndex > 0) {
                    $this->addSpacer($section, 160);
                }
                $section->addText((string) ($block['title'] ?? $block['serial'] ?? ''), $this->fontBold);
            } elseif ($type === 'finding') {
            $table = $section->addTable($this->gridTable);
            $table->addRow();
            $table->addCell($this->pct($widths[0]), ['valign' => 'center'])
                    ->addText($block['serial'] ?? '', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $table->addCell($this->pct($widths[1]), ['valign' => 'center'])
                    ->addText($block['title'] ?? 'শিরোনাম', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
                $body = (string) ($block['body'] ?? '');
                if (($block['amount'] ?? '') !== '') {
                    $body .= ($body !== '' ? "\n" : '').'টাকার পরিমাণ: '.$block['amount'];
                }
            $table->addCell($this->pct($widths[2]), ['valign' => 'top'])
                    ->addText($body, $this->fontSmall, ['alignment' => Jc::BOTH]);
            $ratingCell = $table->addCell($this->pct($widths[3]), ['valign' => 'center']);
                $this->addRatingBox($ratingCell, $block['rating'] ?? '');
            $this->addSpacer($section, 80);
            } elseif ($type === 'criteria') {
                $section->addText((string) ($block['label'] ?? 'প্রচলিত নিয়ম (Criteria):'), $this->fontBold, ['spaceBefore' => 120]);
                $section->addText((string) ($block['body'] ?? ($data['financial_criteria'] ?? '')), $this->fontBody, ['alignment' => Jc::BOTH]);
            } elseif ($type === 'observation') {
                $obsLabel = (string) ($block['label'] ?? 'পর্যবেক্ষণ (Observation) :');
                if ($obsLabel !== '') {
                    $section->addText($obsLabel, $this->fontBold, ['spaceBefore' => 120]);
                }
                $obsBody = trim((string) ($block['body'] ?? ''));
                $section->addText($obsBody !== '' ? $obsBody : str_repeat('·', 40), $this->fontBody, ['alignment' => Jc::BOTH]);
            } elseif (in_array($type, ['stats', 'vat', 'tax'], true)) {
                $obsHeading = (string) ($block['heading'] ?? 'Report Rating Box:');
                if (in_array($obsHeading, ['ভ্যাট সংক্রান্ত:', 'ট্যাক্স সংক্রান্ত:', 'সারণী:', 'নতুন সারণী:'], true)) {
                    $obsHeading = 'Report Rating Box:';
                }
                $obsRows = array_values((array) ($block['rows'] ?? (
                    $type === 'tax' ? ($data['taxObservationRows'] ?? []) : ($data['vatObservationRows'] ?? [])
                )));
                $this->addObservationTable($section, $obsHeading !== '' ? $obsHeading : 'Report Rating Box:', $obsRows, $data);
            } elseif ($type === 'custom_table') {
                $this->addCustomTable($section, is_array($block) ? $block : []);
            } elseif ($type === 'compliance_table') {
                $this->addComplianceTable($section, is_array($block) ? $block : []);
            } elseif ($type === 'it_checklist') {
                $this->addItChecklistTable($section, is_array($block) ? $block : []);
            } elseif ($type === 'external_audit') {
                $this->addExternalAuditTable($section, is_array($block) ? $block : []);
            } elseif ($type === 'audit_score') {
                $this->addAuditScoreTable($section, is_array($block) ? $block : []);
            } elseif ($type === 'jobab_table') {
                $this->addJobabTable($section, is_array($block) ? $block : []);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $block
     */
    protected function addComplianceTable($section, array $block): void
    {
        $this->addSpacer($section, 120);
        $lines = AuditComplianceHeading::lines($block);
        $center = ['alignment' => Jc::CENTER, 'spaceAfter' => 40];

        $section->addText($lines['bn'], $this->fontBold, $center);
        $section->addText($lines['en'], $this->fontBold, $center);
        $section->addText($lines['period_line'], ['name' => self::FONT, 'size' => 9], ['alignment' => Jc::CENTER, 'spaceAfter' => 40]);
        $section->addText($lines['followup_line'], ['name' => self::FONT, 'size' => 9], ['alignment' => Jc::CENTER, 'spaceAfter' => 120]);

        $headers = array_values((array) ($block['headers'] ?? AuditTableHeaders::defaults()['compliance']));
        if (count($headers) < 6) {
            $headers = array_values(AuditTableHeaders::defaults()['compliance']);
        }
        $coreFields = ['prev_para_no', 'findings', 'first_discovery_period', 'management_reply', 'current_status', 'current_para_no'];
        $extraCount = max(0, count($headers) - count($coreFields));

        $compliance = $section->addTable($this->gridTable);
        $compliance->addRow();
        foreach ($headers as $header) {
            $compliance->addCell(1500)->addText((string) $header, ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
        }
        foreach (array_values((array) ($block['rows'] ?? [])) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $compliance->addRow();
            foreach ($coreFields as $field) {
                $align = in_array($field, ['prev_para_no', 'first_discovery_period', 'current_para_no'], true)
                    ? ['alignment' => Jc::CENTER]
                    : [];
                $compliance->addCell(1500)->addText((string) ($row[$field] ?? ''), ['name' => self::FONT, 'size' => 7], $align);
            }
            for ($ei = 0; $ei < $extraCount; $ei++) {
                $compliance->addCell(1500)->addText((string) ($row['extra'][$ei] ?? ''), ['name' => self::FONT, 'size' => 7]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $block
     */
    protected function addItChecklistTable($section, array $block): void
    {
        $this->addSpacer($section, 120);
        $center = ['alignment' => Jc::CENTER, 'spaceAfter' => 60];
        $tickFont = ['name' => 'Segoe UI Symbol', 'size' => 12, 'bold' => true];
        $cellFont = ['name' => self::FONT, 'size' => 9];
        $headerFont = ['name' => self::FONT, 'size' => 9, 'bold' => true];

        $section->addText((string) ($block['title'] ?? ''), ['name' => self::FONT, 'size' => 12, 'bold' => true], $center);
        foreach (['org_line1', 'org_line2', 'org_line3'] as $key) {
            $line = trim((string) ($block[$key] ?? ''));
            if ($line !== '') {
                $section->addText($line, ['name' => self::FONT, 'size' => 10], $center);
            }
        }
        $section->addText(
            'কর্মসূচীর নাম : '.($block['program'] ?? '').'    শাখার নাম : '.($block['branch'] ?? ''),
            ['name' => self::FONT, 'size' => 10],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 60]
        );
        $section->addText(
            (string) ($block['instruction'] ?? 'প্রযোজ্য ক্ষেত্রে টিক চিহ্ন দিন'),
            ['name' => self::FONT, 'size' => 10, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 140]
        );

        $h1 = array_values((array) ($block['headers_r1'] ?? AuditTableHeaders::defaults()['it_r1']));
        $h2 = array_values((array) ($block['headers_r2'] ?? AuditTableHeaders::defaults()['it_r2']));
        $extraHeaders = array_values((array) ($block['extra_headers'] ?? []));

        $checklist = $section->addTable($this->gridTable);
        $checklist->addRow();
        $checklist->addCell(700, ['vMerge' => 'restart'])->addText((string) ($h1[0] ?? 'ক্রমিক'), $headerFont, ['alignment' => Jc::CENTER]);
        $checklist->addCell(2400, ['vMerge' => 'restart'])->addText((string) ($h1[1] ?? 'বিবরণ'), $headerFont, ['alignment' => Jc::CENTER]);
        $checklist->addCell(1800, ['gridSpan' => 3])->addText((string) ($h1[2] ?? 'Compliance'), $headerFont, ['alignment' => Jc::CENTER]);
        $checklist->addCell(1100, ['vMerge' => 'restart'])->addText((string) ($h1[3] ?? 'Action Owner'), $headerFont, ['alignment' => Jc::CENTER]);
        $checklist->addCell(1300, ['vMerge' => 'restart'])->addText((string) ($h1[4] ?? 'Management Comments'), $headerFont, ['alignment' => Jc::CENTER]);
        $checklist->addCell(1300, ['vMerge' => 'restart'])->addText((string) ($h1[5] ?? 'Recommendation'), $headerFont, ['alignment' => Jc::CENTER]);
        foreach ($extraHeaders as $eh) {
            $checklist->addCell(900, ['vMerge' => 'restart'])->addText((string) $eh, $headerFont, ['alignment' => Jc::CENTER]);
        }

        $checklist->addRow();
        $checklist->addCell(700, ['vMerge' => 'continue']);
        $checklist->addCell(2400, ['vMerge' => 'continue']);
        $checklist->addCell(600)->addText((string) ($h2[0] ?? 'Yes'), $headerFont, ['alignment' => Jc::CENTER]);
        $checklist->addCell(600)->addText((string) ($h2[1] ?? 'No'), $headerFont, ['alignment' => Jc::CENTER]);
        $checklist->addCell(600)->addText((string) ($h2[2] ?? 'N/A'), $headerFont, ['alignment' => Jc::CENTER]);
        $checklist->addCell(1100, ['vMerge' => 'continue']);
        $checklist->addCell(1300, ['vMerge' => 'continue']);
        $checklist->addCell(1300, ['vMerge' => 'continue']);
        foreach ($extraHeaders as $unused) {
            $checklist->addCell(900, ['vMerge' => 'continue']);
        }

        foreach (array_values((array) ($block['rows'] ?? [])) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $compliance = (string) ($row['compliance'] ?? '');
            $checklist->addRow();
            $checklist->addCell(700)->addText((string) ($row['sl_no'] ?? ''), ['name' => self::FONT, 'size' => 9, 'bold' => true], ['alignment' => Jc::CENTER]);
            $checklist->addCell(2400)->addText((string) ($row['description'] ?? ''), $cellFont);
            $checklist->addCell(600)->addText($compliance === 'yes' ? '✓' : '', $tickFont, ['alignment' => Jc::CENTER]);
            $checklist->addCell(600)->addText($compliance === 'no' ? '✓' : '', $tickFont, ['alignment' => Jc::CENTER]);
            $checklist->addCell(600)->addText($compliance === 'na' ? '✓' : '', $tickFont, ['alignment' => Jc::CENTER]);
            $checklist->addCell(1100)->addText((string) ($row['action_owner'] ?? ''), $cellFont);
            $checklist->addCell(1300)->addText((string) ($row['management_comments'] ?? ''), $cellFont);
            $checklist->addCell(1300)->addText((string) ($row['recommendation'] ?? ''), $cellFont);
            for ($ei = 0; $ei < count($extraHeaders); $ei++) {
                $checklist->addCell(900)->addText((string) ($row['extra'][$ei] ?? ''), $cellFont);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $block
     */
    protected function addExternalAuditTable($section, array $block): void
    {
        $this->addSpacer($section, 120);
        $center = ['alignment' => Jc::CENTER, 'spaceAfter' => 60];
        $cellFont = ['name' => self::FONT, 'size' => 9];
        $headerFont = ['name' => self::FONT, 'size' => 9, 'bold' => true];

        $section->addText(
            (string) ($block['title'] ?? ''),
            ['name' => self::FONT, 'size' => 12, 'bold' => true, 'underline' => 'single'],
            $center
        );
        $section->addText(
            trim((string) ($block['branch_label'] ?? 'Name of Branch----').' '.(string) ($block['branch'] ?? '')),
            ['name' => self::FONT, 'size' => 10, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 140]
        );

        $coreFields = ['area_of_observation', 'year_of_reporting', 'external_observation', 'compliance', 'internal_index_no'];
        $headers = array_values((array) ($block['headers'] ?? AuditTableHeaders::defaults()['external_audit']));
        if (count($headers) < 5) {
            $headers = array_values(AuditTableHeaders::defaults()['external_audit']);
        }
        $extraCount = max(0, count($headers) - count($coreFields));
        $widths = [1300, 1000, 3400, 2000, 1300];
        for ($ei = 0; $ei < $extraCount; $ei++) {
            $widths[] = 900;
        }

        $table = $section->addTable($this->gridTable);
        $table->addRow();
        foreach ($headers as $i => $header) {
            $w = $widths[$i] ?? 900;
            $table->addCell($w, ['bgColor' => 'F0E4D4'])->addText((string) $header, $headerFont, ['alignment' => Jc::CENTER]);
        }

        foreach (array_values((array) ($block['rows'] ?? [])) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $table->addRow();
            foreach ($coreFields as $fi => $field) {
                $align = in_array($field, ['area_of_observation', 'year_of_reporting', 'internal_index_no'], true)
                    ? ['alignment' => Jc::CENTER]
                    : [];
                $table->addCell($widths[$fi] ?? 900)->addText((string) ($row[$field] ?? ''), $cellFont, $align);
            }
            for ($ei = 0; $ei < $extraCount; $ei++) {
                $table->addCell($widths[5 + $ei] ?? 900)->addText((string) ($row['extra'][$ei] ?? ''), $cellFont);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $block
     */
    protected function addAuditScoreTable($section, array $block): void
    {
        $this->addSpacer($section, 120);
        $rows = array_values((array) ($block['rows'] ?? []));
        $extraHeaders = array_values((array) ($block['extra_headers'] ?? []));
        $extraCount = count($extraHeaders);
        $adjustments = array_values((array) ($block['adjustments'] ?? []));
        $subsequent = array_values((array) ($block['subsequent'] ?? []));
        $summary = AuditScoreSheet::summarize($rows, $adjustments, $subsequent);
        $cellFont = ['name' => self::FONT, 'size' => 8];
        $boldCell = ['name' => self::FONT, 'size' => 8, 'bold' => true];
        $headerFont = ['name' => self::FONT, 'size' => 8, 'bold' => true, 'color' => 'FFFFFF'];
        $center = ['alignment' => Jc::CENTER];

        $meta = $section->addTable(['borderSize' => 0, 'borderColor' => 'FFFFFF', 'cellMargin' => 40]);
        $meta->addRow();
        $left = $meta->addCell(5200);
        $left->addText('Branch Name & Code: '.($block['branch_name_code'] ?? ''), ['name' => self::FONT, 'size' => 9, 'bold' => true]);
        $left->addText('Branch Category: '.($block['branch_category'] ?? ''), ['name' => self::FONT, 'size' => 9]);
        $left->addText('Audit period: '.($block['audit_period'] ?? ''), ['name' => self::FONT, 'size' => 9]);
        $right = $meta->addCell(3800);
        $box = $right->addTable($this->gridTable);
        $box->addRow();
        $box->addCell(1800, ['bgColor' => 'E7E6E6'])->addText('Audit Score', $boldCell, $center);
        $box->addCell(1800, ['bgColor' => 'C6EFCE'])->addText($summary['audit_score_display'] !== '' ? $summary['audit_score_display'] : '—', ['name' => self::FONT, 'size' => 9, 'bold' => true], $center);
        $box->addRow();
        $box->addCell(1800, ['bgColor' => 'E7E6E6'])->addText('Performance Grade', $boldCell, $center);
        $box->addCell(1800, ['bgColor' => 'F4B183'])->addText($summary['grade'] !== '' ? $summary['grade'] : '—', $boldCell, $center);

        $this->addSpacer($section, 80);
        $widths = [2600, 850, 650, 650, 750, 650, 850, 750, 650];
        for ($ei = 0; $ei < $extraCount; $ei++) {
            $widths[] = 700;
        }
        $colCount = count($widths);
        $leftSpan = $colCount - 1;
        $table = $section->addTable($this->gridTable);

        $table->addRow();
        $table->addCell($widths[0], ['vMerge' => 'restart', 'bgColor' => '1F4E79'])->addText('Observation Title', $headerFont, $center);
        $table->addCell($widths[1], ['vMerge' => 'restart', 'bgColor' => '1F4E79'])->addText('Category', $headerFont, $center);
        $table->addCell($widths[2] + $widths[3] + $widths[4], ['gridSpan' => 3, 'bgColor' => '1F4E79'])->addText('Sample Score', $headerFont, $center);
        $table->addCell($widths[5], ['vMerge' => 'restart', 'bgColor' => '1F4E79'])->addText('Instance (D)', $headerFont, $center);
        $table->addCell($widths[6] + $widths[7], ['gridSpan' => 2, 'bgColor' => '1F4E79'])->addText('Achieved Score', $headerFont, $center);
        $table->addCell($widths[8], ['vMerge' => 'restart', 'bgColor' => '1F4E79'])->addText('G=(F/C)', $headerFont, $center);
        foreach ($extraHeaders as $ei => $eh) {
            $table->addCell($widths[9 + $ei] ?? 700, ['vMerge' => 'restart', 'bgColor' => '1F4E79'])->addText((string) $eh, $headerFont, $center);
        }

        $table->addRow();
        $table->addCell($widths[0], ['vMerge' => 'continue']);
        $table->addCell($widths[1], ['vMerge' => 'continue']);
        $table->addCell($widths[2], ['bgColor' => '2E75B6'])->addText('A', $headerFont, $center);
        $table->addCell($widths[3], ['bgColor' => '2E75B6'])->addText('B', $headerFont, $center);
        $table->addCell($widths[4], ['bgColor' => '2E75B6'])->addText('C=A*B', $headerFont, $center);
        $table->addCell($widths[5], ['vMerge' => 'continue']);
        $table->addCell($widths[6], ['bgColor' => '2E75B6'])->addText('E=A-D', $headerFont, $center);
        $table->addCell($widths[7], ['bgColor' => '2E75B6'])->addText('F=E*B', $headerFont, $center);
        $table->addCell($widths[8], ['vMerge' => 'continue']);
        foreach ($extraHeaders as $ei => $unused) {
            $table->addCell($widths[9 + $ei] ?? 700, ['vMerge' => 'continue']);
        }

        $table->addRow();
        $table->addCell(array_sum($widths), ['gridSpan' => $colCount, 'bgColor' => 'F8CBAD'])->addText((string) ($block['section_label'] ?? 'Sample-based observations'), $boldCell);

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $computed = AuditScoreSheet::computeRow($row);
            $cat = (string) ($computed['category'] ?? '');
            $calcBg = str_replace('#', '', AuditScoreSheet::calcCellBg($cat));
            $gBg = str_replace('#', '', AuditScoreSheet::gCellBg($cat));
            $inBg = str_replace('#', '', AuditScoreSheet::inputCellBg($cat));
            $table->addRow();
            $table->addCell($widths[0])->addText((string) ($computed['title'] ?? ''), $cellFont);
            $table->addCell($widths[1], ['bgColor' => $inBg])->addText((string) ($computed['category'] ?? ''), $cellFont, $center);
            $table->addCell($widths[2], ['bgColor' => $inBg])->addText((string) ($computed['sample_size'] ?? ''), $cellFont, $center);
            $table->addCell($widths[3], ['bgColor' => $inBg])->addText((string) ($computed['risk_weight'] ?? ''), $cellFont, $center);
            $table->addCell($widths[4], ['bgColor' => $calcBg])->addText((string) ($computed['risk_weighted_c'] ?? ''), $boldCell, $center);
            $table->addCell($widths[5], ['bgColor' => $inBg])->addText((string) ($computed['instance_size'] ?? ''), $cellFont, $center);
            $table->addCell($widths[6], ['bgColor' => $calcBg])->addText((string) ($computed['samples_not_reported_e'] ?? ''), $boldCell, $center);
            $table->addCell($widths[7], ['bgColor' => $calcBg])->addText((string) ($computed['risk_weighted_f'] ?? ''), $boldCell, $center);
            $table->addCell($widths[8], ['bgColor' => $gBg])->addText((string) ($computed['audit_score_g'] ?? ''), $boldCell, $center);
            for ($ei = 0; $ei < $extraCount; $ei++) {
                $table->addCell($widths[9 + $ei] ?? 700)->addText((string) ($row['extra'][$ei] ?? ''), $cellFont, $center);
            }
        }

        $blue = ['name' => self::FONT, 'size' => 8, 'bold' => true, 'color' => 'FFFFFF'];
        $table->addRow();
        $table->addCell(array_sum(array_slice($widths, 0, $leftSpan)), ['gridSpan' => $leftSpan, 'bgColor' => '1F4E79'])->addText('Initial Audit Score', $blue);
        $table->addCell($widths[$leftSpan], ['bgColor' => '1F4E79'])->addText(AuditScoreSheet::formatPercent($summary['initial']) ?: '—', $blue, $center);

        $table->addRow();
        $table->addCell(array_sum($widths), ['gridSpan' => $colCount, 'bgColor' => 'F8CBAD'])->addText('Other Considerations:', $boldCell);

        foreach ($adjustments as $adj) {
            if (! is_array($adj)) {
                continue;
            }
            $table->addRow();
            $table->addCell(array_sum(array_slice($widths, 0, $leftSpan)), ['gridSpan' => $leftSpan])->addText((string) ($adj['label'] ?? ''), $cellFont);
            $table->addCell($widths[$leftSpan])->addText(AuditScoreSheet::formatPercent(AuditScoreSheet::parseNumber($adj['value'] ?? null)), $cellFont, $center);
        }

        $table->addRow();
        $table->addCell(array_sum(array_slice($widths, 0, $leftSpan)), ['gridSpan' => $leftSpan, 'bgColor' => '1F4E79'])->addText('Final Audit Score', $blue);
        $table->addCell($widths[$leftSpan], ['bgColor' => '1F4E79'])->addText(AuditScoreSheet::formatPercent($summary['final']) ?: '—', $blue, $center);

        $table->addRow();
        $table->addCell(array_sum($widths), ['gridSpan' => $colCount, 'bgColor' => 'F8CBAD'])->addText('Other Relevant Considerations:', $boldCell);

        foreach ($subsequent as $sub) {
            if (! is_array($sub)) {
                continue;
            }
            $table->addRow();
            $table->addCell(array_sum(array_slice($widths, 0, $leftSpan)), ['gridSpan' => $leftSpan])->addText((string) ($sub['label'] ?? ''), $cellFont);
            $table->addCell($widths[$leftSpan])->addText(AuditScoreSheet::formatPercent(AuditScoreSheet::parseNumber($sub['value'] ?? null)), $cellFont, $center);
        }

        $table->addRow();
        $table->addCell(array_sum(array_slice($widths, 0, $leftSpan)), ['gridSpan' => $leftSpan, 'bgColor' => '1F4E79'])->addText('Adjusted Audit Score', $blue);
        $table->addCell($widths[$leftSpan], ['bgColor' => '1F4E79'])->addText(AuditScoreSheet::formatPercent($summary['adjusted']) ?: '—', $blue, $center);
    }

    /**
     * @param  array<string, mixed>  $block
     */
    protected function addJobabTable($section, array $block): void
    {
        $rows = array_values((array) ($block['rows'] ?? []));
        if ($rows === []) {
            return;
        }
        $colCount = 2;
        foreach ($rows as $row) {
            $colCount = max($colCount, count(array_values((array) ($row['cells'] ?? []))));
        }
        $table = $section->addTable($this->gridTable);
        $widths = [];
        if ($colCount === 2) {
            $widths = [3400, 5600];
        } else {
            $each = (int) floor(9000 / max(1, $colCount));
            $widths = array_fill(0, $colCount, $each);
        }
        foreach ($rows as $row) {
            $cells = array_values((array) ($row['cells'] ?? []));
            while (count($cells) < $colCount) {
                $cells[] = '';
            }
            $table->addRow();
            foreach ($cells as $ci => $text) {
                $font = ['name' => self::FONT, 'size' => 9, 'bold' => $ci === 0];
                $table->addCell($widths[$ci] ?? 1500, ['valign' => 'top'])
                    ->addText((string) $text, $font, ['alignment' => Jc::START]);
            }
        }
        $this->addSpacer($section, 80);
    }

    /**
     * Nested-column custom table (gridSpan / vMerge from schema).
     *
     * @param  array<string, mixed>  $block
     */
    protected function addCustomTable($section, array $block): void
    {
        $tableData = CustomTableSchema::normalize($block);
        $title = trim((string) ($tableData['title'] ?? ''));
        if ($title !== '') {
            $section->addText($title, $this->fontBold, ['spaceBefore' => 120]);
        }

        $columns = $tableData['columns'];
        $rows = $tableData['rows'];
        $leafCount = CustomTableSchema::leafCount($columns);
        if ($leafCount < 1) {
            return;
        }

        $matrix = CustomTableSchema::headerMatrix($columns);
        $widthsPct = CustomTableSchema::leafWidths($columns);
        $paint = CustomTableSchema::bodyPaintPlan($tableData);
        $table = $section->addTable($this->gridTable);
        $totalTwip = 9000;
        $leafTwips = [];
        foreach ($widthsPct as $pct) {
            $leafTwips[] = max(400, (int) round($totalTwip * ($pct / 100)));
        }
        // Fix rounding drift
        $sum = array_sum($leafTwips);
        if ($sum !== $totalTwip && $leafTwips !== []) {
            $leafTwips[count($leafTwips) - 1] += $totalTwip - $sum;
        }

        foreach ($matrix as $hRow) {
            $table->addRow();
            $leafCursor = 0;
            foreach ($hRow as $hCell) {
                $colspan = max(1, (int) ($hCell['colspan'] ?? 1));
                $rowspan = max(1, (int) ($hCell['rowspan'] ?? 1));
                $w = 0;
                for ($i = 0; $i < $colspan && ($leafCursor + $i) < count($leafTwips); $i++) {
                    $w += $leafTwips[$leafCursor + $i];
                }
                $leafCursor += $colspan;
                $cellOpts = [
                    'bgColor' => 'D9D9D9',
                    'valign' => 'center',
                    'gridSpan' => $colspan,
                ];
                if ($rowspan > 1) {
                    $cellOpts['vMerge'] = 'restart';
                }
                $cell = $table->addCell(max(400, $w), $cellOpts);
                $cell->addText(
                    (string) ($hCell['text'] ?? ''),
                    ['name' => self::FONT, 'size' => 8, 'bold' => true],
                    ['alignment' => Jc::CENTER]
                );
            }
        }

        foreach ($rows as $rIndex => $row) {
            $isTotal = (bool) ($row['is_total'] ?? false);
            $font = ['name' => self::FONT, 'size' => 8, 'bold' => $isTotal];
            $table->addRow();
            for ($c = 0; $c < $leafCount; $c++) {
                $cellPlan = $paint[$rIndex][$c] ?? null;
                if (! $cellPlan || ($cellPlan['skip'] ?? false)) {
                    continue;
                }
                $rs = max(1, (int) ($cellPlan['rowspan'] ?? 1));
                $cs = max(1, (int) ($cellPlan['colspan'] ?? 1));
                $w = 0;
                for ($i = 0; $i < $cs && ($c + $i) < count($leafTwips); $i++) {
                    $w += $leafTwips[$c + $i];
                }
                $opts = [
                    'valign' => $rs > 1 ? 'center' : 'top',
                    'gridSpan' => $cs,
                ];
                if ($rs > 1) {
                    $opts['vMerge'] = 'restart';
                }
                $align = ($c === 2 && $cs === 1 && $rs === 1) ? Jc::START : Jc::CENTER;
                $table->addCell(max(400, $w), $opts)
                    ->addText((string) ($cellPlan['text'] ?? ''), $font, ['alignment' => $align]);
            }
        }

        $this->addSpacer($section, 80);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildFinancialDetail($section, array $data): void
    {
        $this->addSpacer($section, 200);
        $section->addText('বিস্তারিত নিম্নে দেওয়া হল:', $this->fontBold);

        $table = $section->addTable($this->gridTable);
        $table->addRow();
        $expenseHeaders = array_merge(
            array_slice(AuditTableHeaders::get($data['tableHeaders'] ?? [], 'expense_r1'), 0, 4),
            AuditTableHeaders::get($data['tableHeaders'] ?? [], 'expense_r2')
        );
        foreach ($expenseHeaders as $header) {
            $table->addCell(900, ['bgColor' => 'D9D9D9', 'valign' => 'center'])
                ->addText($header, ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
        }

        foreach ($data['expenseDetailRows'] ?? [] as $row) {
            $table->addRow();
            foreach (['date_month', 'voucher_no', 'description', 'expense_amount', 'vat_applicable', 'vat_paid', 'vat_diff', 'tax_applicable', 'tax_paid', 'tax_diff'] as $field) {
                $table->addCell(900, ['valign' => 'center'])
                    ->addText((string) ($row[$field] ?? ''), ['name' => self::FONT, 'size' => 7.5], ['alignment' => Jc::CENTER]);
            }
        }

        $section->addText('ঝুঁকি/প্রভাব (Risk/Implication):', $this->fontBold, ['spaceBefore' => 120]);
        $section->addText($data['expense_detail_risk'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
        $section->addText('মূল কারণ (Root Cause):', $this->fontBold, ['spaceBefore' => 80]);
        $section->addText($data['expense_detail_root_cause'] ?: str_repeat('·', 40), $this->fontBody);
        $section->addText('সুপারিশ (Recommendation):', $this->fontBold, ['spaceBefore' => 80]);
        $section->addText($data['expense_detail_recommendation'] ?: str_repeat('·', 40), $this->fontBody);

        $response = $section->addTable($this->gridTable);
        foreach ([
            ['শাখা ব্যবস্থাপকের জবাব', $data['expense_detail_bm_reply'] ?? ''],
            ['দায়িত্বপ্রাপ্ত কর্মী/পদক্ষেপ', $data['expense_detail_responsible'] ?? ''],
            ['সমাধানের সময়কাল (তারিখ)', $data['expense_detail_resolution_date'] ?? ''],
        ] as [$label, $value]) {
            $response->addRow();
            $response->addCell(3500)->addText($label, $this->fontBold);
            $response->addCell(5500)->addText((string) $value, $this->fontSmall);
        }

        $this->addSpacer($section, 160);
        $findingTable = $section->addTable($this->gridTable);
        $findingTable->addRow();
        $widths = Doc::findingColumnWidths();
        $findingTable->addCell($this->pct($widths[0]), ['valign' => 'center'])
            ->addText($data['finding13_serial'] ?? '১.৩', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
        $findingTable->addCell($this->pct($widths[1]), ['valign' => 'center'])
            ->addText($data['finding13_title'] ?? 'শিরোনাম', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
        $body = (string) ($data['finding13_body'] ?? '');
        if (($data['finding13_amount'] ?? '') !== '') {
            $body .= "\nটাকার পরিমাণ: ".$data['finding13_amount'];
        }
        $findingTable->addCell($this->pct($widths[2]), ['valign' => 'top'])
            ->addText($body, $this->fontSmall, ['alignment' => Jc::BOTH]);
        $ratingCell = $findingTable->addCell($this->pct($widths[3]), ['valign' => 'center']);
        $this->addRatingBox($ratingCell, $data['finding13_rating'] ?? '');

        $section->addText('প্রচলিত নিয়ম (Criteria):', $this->fontBold, ['spaceBefore' => 120]);
        $section->addText($data['finding13_criteria'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
        $section->addText('পর্যবেক্ষণ (Observation) :', $this->fontBold, ['spaceBefore' => 80]);
        $section->addText($data['finding13_observation'] ?: str_repeat('·', 40), $this->fontBody);

        $this->addObservationTable($section, '', $data['finding13_statsRows'] ?? [], $data);

        $deposit = $section->addTable($this->gridTable);
        $deposit->addRow();
        foreach (AuditTableHeaders::get($data['tableHeaders'] ?? [], 'deposit') as $header) {
            $deposit->addCell(1500, ['bgColor' => 'D9D9D9'])->addText($header, ['name' => self::FONT, 'size' => 8, 'bold' => true], ['alignment' => Jc::CENTER]);
        }
        foreach ($data['finding13_depositRows'] ?? [] as $row) {
            $deposit->addRow();
            foreach (['description', 'month_name', 'withdrawal_date', 'deposit_date', 'amount', 'holding_period'] as $field) {
                $deposit->addCell(1500)->addText((string) ($row[$field] ?? ''), ['name' => self::FONT, 'size' => 8], ['alignment' => Jc::CENTER]);
            }
        }

        $section->addText('ঝুঁকি/প্রভাব (Risk/Implication):', $this->fontBold, ['spaceBefore' => 120]);
        $section->addText($data['finding13_risk'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
        $section->addText('মূল কারণ (Root Cause):', $this->fontBold, ['spaceBefore' => 80]);
        $section->addText($data['finding13_root_cause'] ?: str_repeat('·', 40), $this->fontBody);
        $section->addText('সুপারিশ (Recommendation):', $this->fontBold, ['spaceBefore' => 80]);
        $section->addText($data['finding13_recommendation'] ?: str_repeat('·', 40), $this->fontBody);

        $mgmt = $section->addTable($this->gridTable);
        foreach ([
            ['শাখা ব্যবস্থাপকের জবাব', $data['finding13_bm_reply'] ?? ''],
            ['দায়িত্বপ্রাপ্ত কর্মী/পদক্ষেপ', $data['finding13_responsible'] ?? ''],
            ['সমাধানের সময়কাল (তারিখ)', $data['finding13_resolution_date'] ?? ''],
        ] as [$label, $value]) {
            $mgmt->addRow();
            $mgmt->addCell(3500)->addText($label, $this->fontBold);
            $mgmt->addCell(5500)->addText((string) $value, $this->fontSmall);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    protected function addGlanceTable($section, array $rows): void
    {
        $table = $section->addTable($this->gridTable);
        $widths = Doc::glanceColumnWidths();
        $dash = '………………';

        foreach ($rows as $row) {
            $table->addRow();
            $table->addCell($this->pct($widths[0]))->addText($row['left_label'] ?? '—', $this->fontSmall);
            $table->addCell($this->pct($widths[1]), ['valign' => 'center'])
                ->addText($row['left_value'] !== '' ? $row['left_value'] : $dash, ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $table->addCell($this->pct($widths[2]))->addText($row['right_label'] ?? '—', $this->fontSmall);
            $table->addCell($this->pct($widths[3]), ['valign' => 'center'])
                ->addText($row['right_value'] !== '' ? $row['right_value'] : $dash, ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
        }
    }

    /**
     * @param  list<string>  $columns
     * @param  list<array{cells:list<string>}>  $rows
     */
    protected function addStaffTable($section, array $columns, array $rows): void
    {
        $widths = Doc::staffColumnWidths($columns);
        $table = $section->addTable($this->gridTable);
        $table->addRow();
        $table->addCell($this->pct($widths[0]), ['bgColor' => 'D9D9D9', 'valign' => 'center'])
            ->addText('ক্রমিক নং', ['name' => self::FONT, 'size' => 8.5, 'bold' => true], ['alignment' => Jc::CENTER]);
        foreach ($columns as $index => $column) {
            $table->addCell($this->pct($widths[$index + 1]), ['bgColor' => 'D9D9D9', 'valign' => 'center'])
                ->addText($column !== '' ? $column : '—', ['name' => self::FONT, 'size' => 8.5, 'bold' => true], ['alignment' => Jc::CENTER]);
        }

        foreach ($rows as $idx => $row) {
            $table->addRow();
            $table->addCell($this->pct($widths[0]), ['valign' => 'center'])
                ->addText(BanglaNumerals::fromInt($idx + 1), ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            foreach ($columns as $cIdx => $column) {
                $align = Doc::staffColumnAlign($column);
                $jc = match ($align) {
                    'right' => Jc::END,
                    'center' => Jc::CENTER,
                    default => Jc::START,
                };
                $cell = trim((string) ($row['cells'][$cIdx] ?? ''));
                $table->addCell($this->pct($widths[$cIdx + 1]), ['valign' => 'center'])
                    ->addText($cell !== '' ? $cell : ' ', $this->fontSmall, ['alignment' => $jc]);
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  array<string, mixed>  $data
     */
    protected function addTocTable($section, array $rows, array $data = []): void
    {
        $widths = Doc::tocColumnWidths();
        $table = $section->addTable($this->gridTable);
        $headers = AuditTableHeaders::get($data['tableHeaders'] ?? [], 'toc');
        $table->addRow();
        foreach ($headers as $index => $heading) {
            $table->addCell($this->pct($widths[$index]), ['bgColor' => 'D9D9D9', 'valign' => 'center'])
                ->addText($heading, ['name' => self::FONT, 'size' => 8.5, 'bold' => true], ['alignment' => $index === 1 ? Jc::START : Jc::CENTER]);
        }

        foreach ($rows as $row) {
            $isSection = ($row['type'] ?? 'item') === 'section';
            $table->addRow();
            if ($isSection) {
                $table->addCell($this->pct($widths[0]), ['bgColor' => 'EFEFEF', 'valign' => 'center'])
                    ->addText($row['serial'] ?: '—', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
                $table->addCell($this->pct($widths[1] + $widths[2] + $widths[3] + $widths[4] + $widths[5]), ['gridSpan' => 5, 'bgColor' => 'EFEFEF', 'valign' => 'center'])
                    ->addText($row['finding'] ?: '—', ['name' => self::FONT, 'size' => 9.5, 'bold' => true]);
                continue;
            }

            $table->addCell($this->pct($widths[0]), ['valign' => 'center'])
                ->addText($row['serial'] ?: '—', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $table->addCell($this->pct($widths[1]), ['valign' => 'top'])
                ->addText($row['finding'] ?: '—', $this->fontSmall, ['alignment' => Jc::START]);
            $table->addCell($this->pct($widths[2]), ['valign' => 'center'])
                ->addText($row['amount'] ?? ' ', $this->fontSmall, ['alignment' => Jc::END]);
            $ratingCell = $table->addCell($this->pct($widths[3]), ['valign' => 'center']);
            $this->addRatingBox($ratingCell, $row['rating'] ?? '');
            $table->addCell($this->pct($widths[4]), ['valign' => 'center'])
                ->addText($row['status'] ?? ' ', $this->fontSmall, ['alignment' => Jc::CENTER]);
            $table->addCell($this->pct($widths[5]), ['valign' => 'center'])
                ->addText($row['page_no'] ?? ' ', ['name' => self::FONT, 'size' => 9.5, 'bold' => true, 'color' => '1D4ED8'], ['alignment' => Jc::CENTER]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  array<string, mixed>  $data
     */
    protected function addObservationTable($section, string $label, array $rows, array $data = []): void
    {
        $section->addText($label, $this->fontBold, ['spaceBefore' => 120, 'spaceAfter' => 60]);
        $table = $section->addTable($this->gridTable);
        $headers = AuditTableHeaders::get($data['tableHeaders'] ?? [], 'stats');
        $table->addRow();
        foreach ($headers as $heading) {
            $table->addCell($this->pct(25), ['bgColor' => '2E5090', 'valign' => 'center'])
                ->addText($heading, ['name' => self::FONT, 'size' => 8.5, 'bold' => true, 'color' => 'FFFFFF'], ['alignment' => Jc::CENTER]);
        }
        foreach ($rows as $row) {
            $table->addRow();
            foreach (['total_population', 'sample_size', 'instances_found', 'percentage'] as $key) {
                $table->addCell($this->pct(25), ['valign' => 'center'])
                    ->addText((string) ($row[$key] ?? ''), $this->fontSmall, ['alignment' => Jc::CENTER]);
            }
        }
    }

    protected function addRatingBox($cell, ?string $rating): void
    {
        if (! $rating) {
            $cell->addText(' ', $this->fontSmall);

            return;
        }

        $parts = MakeAuditReport::findingRatingParts($rating);
        $style = MakeAuditReport::findingRatingStyle($rating);
        $bg = ltrim((string) ($style['bg'] ?? '#FCE4D6'), '#');
        $fg = ltrim((string) ($style['color'] ?? '#111111'), '#');
        $inner = $cell->addTable([
            'borderSize' => 6,
            'borderColor' => '111111',
            'cellMargin' => 40,
            'width' => 100 * 50,
            'unit' => 'pct',
        ]);
        $inner->addRow();
        $inner->addCell($this->pct(100), ['gridSpan' => 2, 'bgColor' => '4472C4', 'valign' => 'center'])
            ->addText('রেটিং (Rating)', ['name' => self::FONT, 'size' => 8, 'bold' => true, 'color' => 'FFFFFF'], ['alignment' => Jc::CENTER]);
        $inner->addRow();
        $inner->addCell($this->pct(50), ['bgColor' => $bg, 'valign' => 'center'])
            ->addText($parts['label'] ?: '—', ['name' => self::FONT, 'size' => 9, 'bold' => true, 'color' => $fg], ['alignment' => Jc::CENTER]);
        $inner->addCell($this->pct(50), ['bgColor' => $bg, 'valign' => 'center'])
            ->addText($parts['code'] ?: '—', ['name' => self::FONT, 'size' => 9, 'bold' => true, 'color' => $fg], ['alignment' => Jc::CENTER]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function addCoverRating($cell, array $data): void
    {
        $ratingTable = $cell->addTable([
            'borderSize' => 6,
            'borderColor' => '222222',
            'cellMargin' => 40,
            'alignment' => Jc::CENTER,
            'width' => 100 * 50,
            'unit' => 'pct',
        ]);
        $ratingTable->addRow();
        $ratingTable->addCell($this->pct(48), ['bgColor' => 'E7E6E6', 'valign' => 'center'])
            ->addText('Audit Score', ['name' => self::FONT, 'size' => 7.5, 'bold' => true], ['alignment' => Jc::START]);
        $ratingTable->addCell($this->pct(52), ['bgColor' => 'C6EFCE', 'valign' => 'center'])
            ->addText((string) ($data['audit_score_display'] ?? '—'), ['name' => self::FONT, 'size' => 8, 'bold' => true], ['alignment' => Jc::CENTER]);
        $ratingTable->addRow();
        $ratingTable->addCell($this->pct(48), ['bgColor' => 'E7E6E6', 'valign' => 'center'])
            ->addText('Performance Grade', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::START]);
        $ratingTable->addCell($this->pct(52), ['bgColor' => 'F4B183', 'valign' => 'center'])
            ->addText((string) ($data['performance_grade'] ?? '—'), ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function addLogoOrOrg($cell, array $data): void
    {
        $logoPath = $data['logoPath'] ?? null;
        if (is_string($logoPath) && $logoPath !== '' && is_file($logoPath)) {
            $cell->addImage($logoPath, [
                'width' => Converter::cmToPixel(6.2),
                'height' => Converter::cmToPixel(1.6),
                'alignment' => Jc::START,
            ]);

            return;
        }

        $cell->addText('DSK', ['name' => self::FONT, 'size' => 16, 'bold' => true]);
        $cell->addText('দুঃস্থ স্বাস্থ্য কেন্দ্র', ['name' => self::FONT, 'size' => 11.5, 'bold' => true]);
        $cell->addText('Dushtha Shasthya Kendra', ['name' => self::FONT, 'size' => 8.5, 'bold' => true]);
    }

    protected function addLabelValue($section, string $label, ?string $value): void
    {
        $text = $label.' '.($value !== null && $value !== '' ? $value : '………………………………');
        $section->addText($text, $this->fontBody, ['spaceAfter' => 60]);
    }

    protected function addSpacer($section, int $twips): void
    {
        $section->addText('', $this->fontBody, ['spaceAfter' => $twips]);
    }

    protected function addSpacerCell($cell, int $twips): void
    {
        $cell->addText('', $this->fontBody, ['spaceAfter' => $twips]);
    }

    protected function pct(float $percent): int
    {
        return (int) round($percent * 100);
    }

    protected function fmtDate(?string $date): string
    {
        if (! $date) {
            return '……………………';
        }

        try {
            return Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable) {
            return $date;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<array<string, mixed>>
     */
    protected function overviewRows(array $data): array
    {
        foreach ($data['documentSheets'] ?? [] as $sheet) {
            if (($sheet['type'] ?? '') === 'overview') {
                return $sheet['rows'] ?? [];
            }
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function hasFinancialSheet(array $data): bool
    {
        foreach ($data['documentSheets'] ?? [] as $sheet) {
            if (($sheet['type'] ?? '') === 'financial') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function hasFinancialDetailSheet(array $data): bool
    {
        foreach ($data['documentSheets'] ?? [] as $sheet) {
            if (($sheet['type'] ?? '') === 'financial_detail') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function hasFinancialPage6Sheet(array $data): bool
    {
        foreach ($data['documentSheets'] ?? [] as $sheet) {
            if (($sheet['type'] ?? '') === 'financial_page6') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function hasFinancialPage7Sheet(array $data): bool
    {
        foreach ($data['documentSheets'] ?? [] as $sheet) {
            if (($sheet['type'] ?? '') === 'financial_page7') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildFinancialPage6($section, array $data): void
    {
        foreach ($data['page6Findings'] ?? [] as $finding) {
            $this->addSpacer($section, 160);
            $findingTable = $section->addTable($this->gridTable);
            $findingTable->addRow();
            $widths = Doc::findingColumnWidths();
            $findingTable->addCell($this->pct($widths[0]), ['valign' => 'center'])
                ->addText($finding['serial'] ?? '', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $findingTable->addCell($this->pct($widths[1]), ['valign' => 'center'])
                ->addText($finding['title'] ?? 'শিরোনাম', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $body = (string) ($finding['body'] ?? '');
            if (($finding['amount'] ?? '') !== '') {
                $body .= "\nটাকার পরিমাণ: ".$finding['amount'];
            }
            $findingTable->addCell($this->pct($widths[2]), ['valign' => 'top'])
                ->addText($body, $this->fontSmall, ['alignment' => Jc::BOTH]);
            $ratingCell = $findingTable->addCell($this->pct($widths[3]), ['valign' => 'center']);
            $this->addRatingBox($ratingCell, $finding['rating'] ?? '');

            $section->addText('প্রচলিত নিয়ম (Criteria):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['criteria'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('পর্যবেক্ষণ (Observation) :', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['observation'] ?: str_repeat('·', 40), $this->fontBody);

            $this->addObservationTable($section, '', $finding['statsRows'] ?? [], $data);

            $section->addText($finding['detail_intro'] ?? 'বিস্তারিত নিম্নে দেওয়া হলো:', $this->fontBold, ['spaceBefore' => 80]);
            $vouchers = $section->addTable($this->gridTable);
            $vouchers->addRow();
            foreach (AuditTableHeaders::get($data['tableHeaders'] ?? [], 'voucher') as $header) {
                $vouchers->addCell(1800, ['bgColor' => 'D9D9D9'])->addText($header, ['name' => self::FONT, 'size' => 8, 'bold' => true], ['alignment' => Jc::CENTER]);
            }
            foreach ($finding['voucherRows'] ?? [] as $row) {
                $vouchers->addRow();
                foreach (['date', 'voucher_type_no', 'description', 'amount', 'remarks'] as $field) {
                    $vouchers->addCell(1800)->addText((string) ($row[$field] ?? ''), ['name' => self::FONT, 'size' => 8], ['alignment' => Jc::CENTER]);
                }
            }

            $section->addText('ঝুঁকি/প্রভাব (Risk/Implication):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['risk'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('মূল কারণ (Root Cause):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['root_cause'] ?: str_repeat('·', 40), $this->fontBody);
            $section->addText('সুপারিশ (Recommendation):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['recommendation'] ?: str_repeat('·', 40), $this->fontBody);

            $mgmt = $section->addTable($this->gridTable);
            foreach ([
                ['শাখা ব্যবস্থাপকের জবাব', $finding['bm_reply'] ?? ''],
                ['দায়িত্বপ্রাপ্ত কর্মী/পদক্ষেপ', $finding['responsible'] ?? ''],
                ['সমাধানের সময়কাল (তারিখ)', $finding['resolution_date'] ?? ''],
            ] as [$label, $value]) {
                $mgmt->addRow();
                $mgmt->addCell(3500)->addText($label, $this->fontBold);
                $mgmt->addCell(5500)->addText((string) $value, $this->fontSmall);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildFinancialPage7($section, array $data): void
    {
        foreach ($data['page7Findings'] ?? [] as $finding) {
            $this->addSpacer($section, 160);
            $findingTable = $section->addTable($this->gridTable);
            $findingTable->addRow();
            $widths = Doc::findingColumnWidths();
            $findingTable->addCell($this->pct($widths[0]), ['valign' => 'center'])
                ->addText($finding['serial'] ?? '', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $findingTable->addCell($this->pct($widths[1]), ['valign' => 'center'])
                ->addText($finding['title'] ?? 'শিরোনাম', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $body = (string) ($finding['body'] ?? '');
            if (($finding['amount'] ?? '') !== '') {
                $body .= "\nটাকার পরিমাণ: ".$finding['amount'];
            }
            $findingTable->addCell($this->pct($widths[2]), ['valign' => 'top'])
                ->addText($body, $this->fontSmall, ['alignment' => Jc::BOTH]);
            $ratingCell = $findingTable->addCell($this->pct($widths[3]), ['valign' => 'center']);
            $this->addRatingBox($ratingCell, $finding['rating'] ?? '');

            $section->addText('প্রচলিত নিয়ম (Criteria):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['criteria'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('পর্যবেক্ষণ (Observation) :', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['observation'] ?: str_repeat('·', 40), $this->fontBody);

            $this->addObservationTable($section, '', $finding['statsRows'] ?? [], $data);

            if (($finding['detail_type'] ?? 'none') === 'budget') {
                $year = (string) ($finding['budget_year'] ?? '২০২২-২০২৩');
                $section->addText($finding['detail_intro'] ?? 'নিম্নে বিস্তারিত দেওয়া হলো:', $this->fontBold, ['spaceBefore' => 80]);
                $budget = $section->addTable($this->gridTable);
                $budget->addRow();
                $budgetHeaders = [
                    $this->hdr($data, 'budget_r1', 0),
                    trim($this->hdr($data, 'budget_r1', 1).' '.$year.' ('.$this->hdr($data, 'budget_r2', 0).')'),
                    $this->hdr($data, 'budget_r2', 1),
                    $this->hdr($data, 'budget_r1', 2),
                    $this->hdr($data, 'budget_r1', 3),
                ];
                foreach ($budgetHeaders as $header) {
                    $budget->addCell(1800, ['bgColor' => 'D9D9D9'])->addText($header, ['name' => self::FONT, 'size' => 8, 'bold' => true], ['alignment' => Jc::CENTER]);
                }
                foreach ($finding['budgetRows'] ?? [] as $row) {
                    $budget->addRow();
                    foreach (['budget_head', 'budget_annual', 'budget_upto_june', 'actual_expense', 'difference'] as $field) {
                        $budget->addCell(1800)->addText((string) ($row[$field] ?? ''), ['name' => self::FONT, 'size' => 8], ['alignment' => Jc::CENTER]);
                    }
                }
            }

            if (($finding['detail_type'] ?? 'none') === 'bonus') {
                $section->addText($finding['detail_intro'] ?? 'নিম্নে বিস্তারিত দেওয়া হলো:', $this->fontBold, ['spaceBefore' => 80]);
                $bonus = $section->addTable($this->gridTable);
                $bonus->addRow();
                foreach (AuditTableHeaders::get($data['tableHeaders'] ?? [], 'bonus') as $header) {
                    $bonus->addCell(2250, ['bgColor' => 'D9D9D9'])->addText($header, ['name' => self::FONT, 'size' => 8, 'bold' => true], ['alignment' => Jc::CENTER]);
                }
                foreach ($finding['bonusRows'] ?? [] as $row) {
                    $bonus->addRow();
                    foreach (['joining_date', 'bonus_date_voucher', 'service_age', 'bonus_amount'] as $field) {
                        $bonus->addCell(2250)->addText((string) ($row[$field] ?? ''), ['name' => self::FONT, 'size' => 8], ['alignment' => Jc::CENTER]);
                    }
                }
            }

            $section->addText('ঝুঁকি/প্রভাব (Risk/Implication):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['risk'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('মূল কারণ (Root Cause):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['root_cause'] ?: str_repeat('·', 40), $this->fontBody);
            $section->addText('সুপারিশ (Recommendation):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['recommendation'] ?: str_repeat('·', 40), $this->fontBody);

            $mgmt = $section->addTable($this->gridTable);
            foreach ([
                ['শাখা ব্যবস্থাপকের জবাব', $finding['bm_reply'] ?? ''],
                ['দায়িত্বপ্রাপ্ত কর্মী/পদক্ষেপ', $finding['responsible'] ?? ''],
                ['সমাধানের সময়কাল (তারিখ)', $finding['resolution_date'] ?? ''],
            ] as [$label, $value]) {
                $mgmt->addRow();
                $mgmt->addCell(3500)->addText($label, $this->fontBold);
                $mgmt->addCell(5500)->addText((string) $value, $this->fontSmall);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function hasFinancialPage8Sheet(array $data): bool
    {
        foreach ($data['documentSheets'] ?? [] as $sheet) {
            if (($sheet['type'] ?? '') === 'financial_page8') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildFinancialPage8($section, array $data): void
    {
        foreach ($data['page8Findings'] ?? [] as $finding) {
            $this->addSpacer($section, 160);
            $findingTable = $section->addTable($this->gridTable);
            $findingTable->addRow();
            $widths = Doc::findingColumnWidths();
            $findingTable->addCell($this->pct($widths[0]), ['valign' => 'center'])
                ->addText($finding['serial'] ?? '', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $findingTable->addCell($this->pct($widths[1]), ['valign' => 'center'])
                ->addText($finding['title'] ?? 'শিরোনাম', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $body = (string) ($finding['body'] ?? '');
            if (($finding['amount'] ?? '') !== '') {
                $body .= "\nটাকার পরিমাণ: ".$finding['amount'];
            }
            $findingTable->addCell($this->pct($widths[2]), ['valign' => 'top'])
                ->addText($body, $this->fontSmall, ['alignment' => Jc::BOTH]);
            $ratingCell = $findingTable->addCell($this->pct($widths[3]), ['valign' => 'center']);
            $this->addRatingBox($ratingCell, $finding['rating'] ?? '');

            $section->addText('প্রচলিত নিয়ম (Criteria):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['criteria'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('পর্যবেক্ষণ (Observation) :', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['observation'] ?: str_repeat('·', 40), $this->fontBody);

            $this->addObservationTable($section, '', $finding['statsRows'] ?? [], $data);

            if (($finding['detail_type'] ?? 'cost_of_fund') === 'cost_of_fund') {
                $cof = $section->addTable($this->gridTable);
                $cof->addRow();
                foreach (AuditTableHeaders::get($data['tableHeaders'] ?? [], 'cof') as $header) {
                    $cof->addCell(1000, ['bgColor' => 'D9D9D9'])->addText($header, ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                }
                foreach ($finding['cofRows'] ?? [] as $row) {
                    $cof->addRow();
                    foreach (['month_name', 'opening_balance', 'closing_balance', 'total_balance', 'avg_balance', 'profit_rate_10', 'monthly_profit', 'branch_charged', 'variance'] as $field) {
                        $cof->addCell(1000)->addText((string) ($row[$field] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
                    }
                }
            }

            $section->addText('ঝুঁকি/প্রভাব (Risk/Implication):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['risk'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('মূল কারণ (Root Cause):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['root_cause'] ?: str_repeat('·', 40), $this->fontBody);
            $section->addText('সুপারিশ (Recommendation):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['recommendation'] ?: str_repeat('·', 40), $this->fontBody);

            $mgmt = $section->addTable($this->gridTable);
            foreach ([
                ['শাখা ব্যবস্থাপকের জবাব', $finding['bm_reply'] ?? ''],
                ['দায়িত্বপ্রাপ্ত কর্মী/পদক্ষেপ', $finding['responsible'] ?? ''],
                ['সমাধানের সময়কাল (তারিখ)', $finding['resolution_date'] ?? ''],
            ] as [$label, $value]) {
                $mgmt->addRow();
                $mgmt->addCell(3500)->addText($label, $this->fontBold);
                $mgmt->addCell(5500)->addText((string) $value, $this->fontSmall);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function hasFinancialPage9Sheet(array $data): bool
    {
        foreach ($data['documentSheets'] ?? [] as $sheet) {
            if (($sheet['type'] ?? '') === 'financial_page9') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildFinancialPage9($section, array $data): void
    {
        foreach ($data['page9Findings'] ?? [] as $finding) {
            $this->addSpacer($section, 160);
            $findingTable = $section->addTable($this->gridTable);
            $findingTable->addRow();
            $widths = Doc::findingColumnWidths();
            $findingTable->addCell($this->pct($widths[0]), ['valign' => 'center'])
                ->addText($finding['serial'] ?? '', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $findingTable->addCell($this->pct($widths[1]), ['valign' => 'center'])
                ->addText($finding['title'] ?? 'শিরোনাম', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $body = (string) ($finding['body'] ?? '');
            if (($finding['amount'] ?? '') !== '') {
                $body .= "\nটাকার পরিমাণ: ".$finding['amount'];
            }
            $findingTable->addCell($this->pct($widths[2]), ['valign' => 'top'])
                ->addText($body, $this->fontSmall, ['alignment' => Jc::BOTH]);
            $ratingCell = $findingTable->addCell($this->pct($widths[3]), ['valign' => 'center']);
            $this->addRatingBox($ratingCell, $finding['rating'] ?? '');

            $section->addText('প্রচলিত নিয়ম (Criteria):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['criteria'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('পর্যবেক্ষণ (Observation) :', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['observation'] ?: str_repeat('·', 40), $this->fontBody);

            $this->addObservationTable($section, '', $finding['statsRows'] ?? [], $data);

            if (($finding['detail_type'] ?? '') === 'cash') {
                $section->addText($finding['detail_intro'] ?? 'নিম্নে বিস্তারিত দেওয়া হলো:', $this->fontBold, ['spaceBefore' => 80]);
                $cash = $section->addTable($this->gridTable);
                $cash->addRow();
                foreach (AuditTableHeaders::get($data['tableHeaders'] ?? [], 'cash') as $header) {
                    $cash->addCell(1500, ['bgColor' => 'D9D9D9'])->addText($header, ['name' => self::FONT, 'size' => 8, 'bold' => true], ['alignment' => Jc::CENTER]);
                }
                foreach ($finding['cashRows'] ?? [] as $row) {
                    $cash->addRow();
                    foreach (['date_1', 'cash_1', 'date_2', 'cash_2', 'date_3', 'cash_3'] as $field) {
                        $cash->addCell(1500)->addText((string) ($row[$field] ?? ''), ['name' => self::FONT, 'size' => 8], ['alignment' => Jc::CENTER]);
                    }
                }
            }

            if (($finding['detail_type'] ?? '') === 'stamp') {
                $section->addText($finding['detail_intro'] ?? 'নিম্নে বিস্তারিত দেওয়া হলো:', $this->fontBold, ['spaceBefore' => 80]);
                $stamp = $section->addTable($this->gridTable);
                $stamp->addRow();
                foreach (AuditTableHeaders::get($data['tableHeaders'] ?? [], 'stamp') as $header) {
                    $stamp->addCell(2250, ['bgColor' => 'D9D9D9'])->addText($header, ['name' => self::FONT, 'size' => 8, 'bold' => true], ['alignment' => Jc::CENTER]);
                }
                foreach ($finding['stampRows'] ?? [] as $row) {
                    $stamp->addRow();
                    foreach (['date', 'voucher_no', 'amount', 'description'] as $field) {
                        $stamp->addCell(2250)->addText((string) ($row[$field] ?? ''), ['name' => self::FONT, 'size' => 8], ['alignment' => Jc::CENTER]);
                    }
                }
            }

            $section->addText('ঝুঁকি/প্রভাব (Risk/Implication):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['risk'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('মূল কারণ (Root Cause):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['root_cause'] ?: str_repeat('·', 40), $this->fontBody);
            $section->addText('সুপারিশ (Recommendation):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['recommendation'] ?: str_repeat('·', 40), $this->fontBody);

            $mgmt = $section->addTable($this->gridTable);
            foreach ([
                ['শাখা ব্যবস্থাপকের জবাব', $finding['bm_reply'] ?? ''],
                ['দায়িত্বপ্রাপ্ত কর্মী/পদক্ষেপ', $finding['responsible'] ?? ''],
                ['সমাধানের সময়কাল (তারিখ)', $finding['resolution_date'] ?? ''],
            ] as [$label, $value]) {
                $mgmt->addRow();
                $mgmt->addCell(3500)->addText($label, $this->fontBold);
                $mgmt->addCell(5500)->addText((string) $value, $this->fontSmall);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function hasFinancialPage10Sheet(array $data): bool
    {
        foreach ($data['documentSheets'] ?? [] as $sheet) {
            if (($sheet['type'] ?? '') === 'financial_page10') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildFinancialPage10($section, array $data): void
    {
        $title = (string) ($data['page10_section_title'] ?? '');
        if ($title !== '') {
            $section->addText($title, ['name' => self::FONT, 'size' => 12, 'bold' => true, 'underline' => 'single'], ['alignment' => Jc::CENTER, 'spaceBefore' => 200, 'spaceAfter' => 160]);
        }

        foreach ($data['page10Findings'] ?? [] as $finding) {
            $this->addSpacer($section, 120);
            $findingTable = $section->addTable($this->gridTable);
            $findingTable->addRow();
            $widths = Doc::findingColumnWidths();
            $findingTable->addCell($this->pct($widths[0]), ['valign' => 'center'])
                ->addText($finding['serial'] ?? '', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $findingTable->addCell($this->pct($widths[1]), ['valign' => 'center'])
                ->addText($finding['title'] ?? 'শিরোনাম', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $body = (string) ($finding['body'] ?? '');
            if (($finding['amount'] ?? '') !== '') {
                $body .= "\nটাকার পরিমাণ: ".$finding['amount'];
            }
            $findingTable->addCell($this->pct($widths[2]), ['valign' => 'top'])
                ->addText($body, $this->fontSmall, ['alignment' => Jc::BOTH]);
            $ratingCell = $findingTable->addCell($this->pct($widths[3]), ['valign' => 'center']);
            $this->addRatingBox($ratingCell, $finding['rating'] ?? '');

            $section->addText('প্রচলিত নিয়ম (Criteria):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['criteria'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('পর্যবেক্ষণ (Observation) :', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['observation'] ?: str_repeat('·', 40), $this->fontBody);

            $this->addObservationTable($section, '', $finding['statsRows'] ?? [], $data);

            if (($finding['detail_type'] ?? 'asset') === 'asset') {
                $section->addText($finding['detail_intro'] ?? 'নিম্নে বিস্তারিত দেওয়া হলো:', $this->fontBold, ['spaceBefore' => 80]);
                $assets = $section->addTable($this->gridTable);
                $assets->addRow();
                foreach (AuditTableHeaders::get($data['tableHeaders'] ?? [], 'asset') as $header) {
                    $assets->addCell(1500, ['bgColor' => 'D9D9D9'])->addText($header, ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                }
                foreach ($finding['assetRows'] ?? [] as $row) {
                    $assets->addRow();
                    foreach (['purchase_date', 'voucher_no', 'asset_name', 'purchase_price', 'previous_head', 'current_location'] as $field) {
                        $assets->addCell(1500)->addText((string) ($row[$field] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
                    }
                }
            }

            $section->addText('ঝুঁকি/প্রভাব (Risk/Implication):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['risk'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('মূল কারণ (Root Cause):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['root_cause'] ?: str_repeat('·', 40), $this->fontBody);
            $section->addText('সুপারিশ (Recommendation):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['recommendation'] ?: str_repeat('·', 40), $this->fontBody);

            $mgmt = $section->addTable($this->gridTable);
            foreach ([
                ['শাখা ব্যবস্থাপকের জবাব', $finding['bm_reply'] ?? ''],
                ['দায়িত্বপ্রাপ্ত কর্মী/পদক্ষেপ', $finding['responsible'] ?? ''],
                ['সমাধানের সময়কাল (তারিখ)', $finding['resolution_date'] ?? ''],
            ] as [$label, $value]) {
                $mgmt->addRow();
                $mgmt->addCell(3500)->addText($label, $this->fontBold);
                $mgmt->addCell(5500)->addText((string) $value, $this->fontSmall);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function hasFinancialPage11Sheet(array $data): bool
    {
        foreach ($data['documentSheets'] ?? [] as $sheet) {
            if (($sheet['type'] ?? '') === 'financial_page11') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildFinancialPage11($section, array $data): void
    {
        foreach ($data['page11Findings'] ?? [] as $finding) {
            $this->addSpacer($section, 160);
            $findingTable = $section->addTable($this->gridTable);
            $findingTable->addRow();
            $widths = Doc::findingColumnWidths();
            $findingTable->addCell($this->pct($widths[0]), ['valign' => 'center'])
                ->addText($finding['serial'] ?? '', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $findingTable->addCell($this->pct($widths[1]), ['valign' => 'center'])
                ->addText($finding['title'] ?? 'শিরোনাম', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $body = (string) ($finding['body'] ?? '');
            if (($finding['amount'] ?? '') !== '') {
                $body .= "\nটাকার পরিমাণ: ".$finding['amount'];
            }
            $findingTable->addCell($this->pct($widths[2]), ['valign' => 'top'])
                ->addText($body, $this->fontSmall, ['alignment' => Jc::BOTH]);
            $ratingCell = $findingTable->addCell($this->pct($widths[3]), ['valign' => 'center']);
            $this->addRatingBox($ratingCell, $finding['rating'] ?? '');

            $section->addText('প্রচলিত নিয়ম (Criteria):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['criteria'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('পর্যবেক্ষণ (Observation) :', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['observation'] ?: str_repeat('·', 40), $this->fontBody);

            $this->addObservationTable($section, '', $finding['statsRows'] ?? [], $data);

            if (($finding['detail_type'] ?? '') === 'dep_compare') {
                $section->addText($finding['detail_intro'] ?? 'নিম্নে বিস্তারিত দেওয়া হলো:', $this->fontBold, ['spaceBefore' => 80]);
                $dep = $section->addTable($this->gridTable);
                $dep->addRow();
                $depHeaders = array_merge(
                    [$this->hdr($data, 'dep_r1', 0)],
                    AuditTableHeaders::get($data['tableHeaders'] ?? [], 'dep_r2')
                );
                foreach ($depHeaders as $header) {
                    $dep->addCell(1285, ['bgColor' => 'D9D9D9'])->addText($header, ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                }
                foreach ($finding['depRows'] ?? [] as $row) {
                    $dep->addRow();
                    foreach (['asset_group', 'value_report', 'value_register', 'value_diff', 'dep_report', 'dep_register', 'dep_diff'] as $field) {
                        $dep->addCell(1285)->addText((string) ($row[$field] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
                    }
                }
            } elseif (($finding['detail_type'] ?? '') === 'quote') {
                $section->addText($finding['detail_intro'] ?? 'বিস্তারিত নিম্নে দেওয়া হলো:', $this->fontBold, ['spaceBefore' => 80]);
                $quotes = $section->addTable($this->gridTable);
                $quotes->addRow();
                foreach (AuditTableHeaders::get($data['tableHeaders'] ?? [], 'quote') as $header) {
                    $quotes->addCell(1500, ['bgColor' => 'D9D9D9'])->addText($header, ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                }
                foreach ($finding['quoteRows'] ?? [] as $row) {
                    $quotes->addRow();
                    foreach (['product_name', 'product_group', 'purchase_date', 'voucher_no', 'amount', 'quote_status'] as $field) {
                        $quotes->addCell(1500)->addText((string) ($row[$field] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
                    }
                }
            } elseif (($finding['detail_intro'] ?? '') !== '') {
                $section->addText($finding['detail_intro'], $this->fontBold, ['spaceBefore' => 80]);
            }

            $section->addText('ঝুঁকি/প্রভাব (Risk/Implication):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['risk'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('মূল কারণ (Root Cause):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['root_cause'] ?: str_repeat('·', 40), $this->fontBody);
            $section->addText('সুপারিশ (Recommendation):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['recommendation'] ?: str_repeat('·', 40), $this->fontBody);

            $mgmt = $section->addTable($this->gridTable);
            foreach ([
                ['শাখা ব্যবস্থাপকের জবাব', $finding['bm_reply'] ?? ''],
                ['দায়িত্বপ্রাপ্ত কর্মী/পদক্ষেপ', $finding['responsible'] ?? ''],
                ['সমাধানের সময়কাল (তারিখ)', $finding['resolution_date'] ?? ''],
            ] as [$label, $value]) {
                $mgmt->addRow();
                $mgmt->addCell(3500)->addText($label, $this->fontBold);
                $mgmt->addCell(5500)->addText((string) $value, $this->fontSmall);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function hasFinancialPage12Sheet(array $data): bool
    {
        foreach ($data['documentSheets'] ?? [] as $sheet) {
            if (($sheet['type'] ?? '') === 'financial_page12') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildFinancialPage12($section, array $data): void
    {
        $title = (string) ($data['page12_section_title'] ?? '');
        if ($title !== '') {
            $section->addText($title, ['name' => self::FONT, 'size' => 12, 'bold' => true, 'underline' => 'single'], ['alignment' => Jc::CENTER, 'spaceBefore' => 200, 'spaceAfter' => 160]);
        }

        foreach ($data['page12Findings'] ?? [] as $finding) {
            $this->addSpacer($section, 120);
            $findingTable = $section->addTable($this->gridTable);
            $findingTable->addRow();
            $widths = Doc::findingColumnWidths();
            $findingTable->addCell($this->pct($widths[0]), ['valign' => 'center'])
                ->addText($finding['serial'] ?? '', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $findingTable->addCell($this->pct($widths[1]), ['valign' => 'center'])
                ->addText($finding['title'] ?? 'শিরোনাম', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $body = (string) ($finding['body'] ?? '');
            if (($finding['amount'] ?? '') !== '') {
                $body .= "\nটাকার পরিমাণ: ".$finding['amount'];
            }
            $findingTable->addCell($this->pct($widths[2]), ['valign' => 'top'])
                ->addText($body, $this->fontSmall, ['alignment' => Jc::BOTH]);
            $ratingCell = $findingTable->addCell($this->pct($widths[3]), ['valign' => 'center']);
            $this->addRatingBox($ratingCell, $finding['rating'] ?? '');

            $section->addText('প্রচলিত নিয়ম (Criteria):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['criteria'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('পর্যবেক্ষণ (Observation) :', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['observation'] ?: str_repeat('·', 40), $this->fontBody);

            $this->addObservationTable($section, '', $finding['statsRows'] ?? [], $data);

            if (($finding['detail_type'] ?? 'stock') === 'stock') {
                $section->addText($finding['detail_intro'] ?? 'নিম্নে বিস্তারিত দেওয়া হলো:', $this->fontBold, ['spaceBefore' => 80]);
                $stocks = $section->addTable($this->gridTable);
                $stocks->addRow();
                foreach (AuditTableHeaders::get($data['tableHeaders'] ?? [], 'stock') as $header) {
                    $stocks->addCell(2250, ['bgColor' => 'D9D9D9'])->addText($header, ['name' => self::FONT, 'size' => 8, 'bold' => true], ['alignment' => Jc::CENTER]);
                }
                foreach ($finding['stockRows'] ?? [] as $row) {
                    $stocks->addRow();
                    foreach (['product_name', 'purchase_date_voucher', 'purchase_price', 'register_status'] as $field) {
                        $stocks->addCell(2250)->addText((string) ($row[$field] ?? ''), ['name' => self::FONT, 'size' => 8], ['alignment' => Jc::CENTER]);
                    }
                }
            }

            $section->addText('ঝুঁকি (Risk) :', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['risk'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('মূল কারণ (Root Cause):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['root_cause'] ?: str_repeat('·', 40), $this->fontBody);
            $section->addText('সুপারিশ (Recommendation):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['recommendation'] ?: str_repeat('·', 40), $this->fontBody);

            $mgmt = $section->addTable($this->gridTable);
            foreach ([
                ['শাখা ব্যবস্থাপকের জবাব', $finding['bm_reply'] ?? ''],
                ['দায়িত্বপ্রাপ্ত কর্মী/পদক্ষেপ', $finding['responsible'] ?? ''],
                ['সমাধানের সময়কাল (তারিখ)', $finding['resolution_date'] ?? ''],
            ] as [$label, $value]) {
                $mgmt->addRow();
                $mgmt->addCell(3500)->addText($label, $this->fontBold);
                $mgmt->addCell(5500)->addText((string) $value, $this->fontSmall);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function hasFinancialPage13Sheet(array $data): bool
    {
        foreach ($data['documentSheets'] ?? [] as $sheet) {
            if (($sheet['type'] ?? '') === 'financial_page13') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildFinancialPage13($section, array $data): void
    {
        $title = (string) ($data['page13_section_title'] ?? '');
        if ($title !== '') {
            $section->addText($title, ['name' => self::FONT, 'size' => 12, 'bold' => true, 'underline' => 'single'], ['alignment' => Jc::CENTER, 'spaceBefore' => 200, 'spaceAfter' => 160]);
        }

        foreach ($data['page13Findings'] ?? [] as $finding) {
            $this->addSpacer($section, 120);
            $findingTable = $section->addTable($this->gridTable);
            $findingTable->addRow();
            $widths = Doc::findingColumnWidths();
            $findingTable->addCell($this->pct($widths[0]), ['valign' => 'center'])
                ->addText($finding['serial'] ?? '', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $findingTable->addCell($this->pct($widths[1]), ['valign' => 'center'])
                ->addText($finding['title'] ?? 'শিরোনাম', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $body = (string) ($finding['body'] ?? '');
            if (($finding['amount'] ?? '') !== '') {
                $body .= "\nটাকার পরিমাণ: ".$finding['amount'];
            }
            $findingTable->addCell($this->pct($widths[2]), ['valign' => 'top'])
                ->addText($body, $this->fontSmall, ['alignment' => Jc::BOTH]);
            $ratingCell = $findingTable->addCell($this->pct($widths[3]), ['valign' => 'center']);
            $this->addRatingBox($ratingCell, $finding['rating'] ?? '');

            $section->addText('প্রচলিত নিয়ম (Criteria):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['criteria'] ?: str_repeat('·', 40), $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('পর্যবেক্ষণ (Observation) :', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['observation'] ?: str_repeat('·', 40), $this->fontBody);

            $this->addObservationTable($section, '', $finding['statsRows'] ?? [], $data);

            if (($finding['detail_type'] ?? '') === 'samity_collection') {
                $section->addText($finding['detail_intro'] ?? 'বিস্তারিত নিম্নে দেওয়া হলো:', $this->fontBold, ['spaceBefore' => 80]);
                $samity = $section->addTable($this->gridTable);
                $samity->addRow();
                $samityHeaders = [
                    $this->hdr($data, 'samity_r1', 0),
                    $this->hdr($data, 'samity_r1', 1),
                    $this->hdr($data, 'samity_r1', 2),
                    $this->hdr($data, 'samity_r2', 0),
                    $this->hdr($data, 'samity_r2', 1),
                    $this->hdr($data, 'samity_r2', 2),
                    $this->hdr($data, 'samity_r2', 3),
                    $this->hdr($data, 'samity_r2', 4),
                    $this->hdr($data, 'samity_r2', 5),
                    $this->hdr($data, 'samity_r2', 6),
                    $this->hdr($data, 'samity_r1', 5),
                    $this->hdr($data, 'samity_r1', 6),
                ];
                foreach ($samityHeaders as $header) {
                    $samity->addCell(750, ['bgColor' => 'D9D9D9'])->addText($header, ['name' => self::FONT, 'size' => 6, 'bold' => true], ['alignment' => Jc::CENTER]);
                }
                foreach ($finding['samityRows'] ?? [] as $row) {
                    $samity->addRow();
                    foreach (['samity_no', 'member_name_id', 'date', 'savings', 'voluntary', 'term', 'installment', 'total_collection', 'deposit_date', 'deposit_amount', 'difference', 'staff_name_id'] as $field) {
                        $samity->addCell(750)->addText((string) ($row[$field] ?? ''), ['name' => self::FONT, 'size' => 6], ['alignment' => Jc::CENTER]);
                    }
                }
            }

            $section->addText('ঝুঁকি:-', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['risk'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('মূল কারণ (Root Cause):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['root_cause'] ?: str_repeat('·', 40), $this->fontBody);
            $section->addText('সুপারিশ (Recommendation) :', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['recommendation'] ?: str_repeat('·', 40), $this->fontBody);

            $mgmt = $section->addTable($this->gridTable);
            foreach ([
                ['শাখা ব্যবস্থাপকের জবাব', $finding['bm_reply'] ?? ''],
                ['দায়িত্বপ্রাপ্ত কর্মী/পদক্ষেপ', $finding['responsible'] ?? ''],
                ['সমাধানের সময়কাল (তারিখ)', $finding['resolution_date'] ?? ''],
            ] as [$label, $value]) {
                $mgmt->addRow();
                $mgmt->addCell(3500)->addText($label, $this->fontBold);
                $mgmt->addCell(5500)->addText((string) $value, $this->fontSmall);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function hasFinancialPage14Sheet(array $data): bool
    {
        foreach ($data['documentSheets'] ?? [] as $sheet) {
            if (($sheet['type'] ?? '') === 'financial_page14') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildFinancialPage14($section, array $data): void
    {
        foreach ($data['page14Findings'] ?? [] as $finding) {
            $this->addSpacer($section, 120);
            $findingTable = $section->addTable($this->gridTable);
            $findingTable->addRow();
            $widths = Doc::findingColumnWidths();
            $findingTable->addCell($this->pct($widths[0]), ['valign' => 'center'])
                ->addText($finding['serial'] ?? '', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $findingTable->addCell($this->pct($widths[1]), ['valign' => 'center'])
                ->addText($finding['title'] ?? 'শিরোনাম', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $body = (string) ($finding['body'] ?? '');
            if (($finding['amount'] ?? '') !== '') {
                $body .= "\nটাকার পরিমাণ: ".$finding['amount'];
            }
            $findingTable->addCell($this->pct($widths[2]), ['valign' => 'top'])
                ->addText($body, $this->fontSmall, ['alignment' => Jc::BOTH]);
            $ratingCell = $findingTable->addCell($this->pct($widths[3]), ['valign' => 'center']);
            $this->addRatingBox($ratingCell, $finding['rating'] ?? '');

            $section->addText('প্রচলিত নিয়ম (Criteria):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['criteria'] ?: str_repeat('·', 40), $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('পর্যবেক্ষণ (Observation) :', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['observation'] ?: str_repeat('·', 40), $this->fontBody);

            $this->addObservationTable($section, '', $finding['statsRows'] ?? [], $data);

            if (($finding['detail_type'] ?? '') === 'passbook_installment') {
                $section->addText($finding['detail_intro'] ?? 'নিম্নে বিস্তারিত দেওয়া হলো:', $this->fontBold, ['spaceBefore' => 80]);
                $rows = $section->addTable($this->gridTable);
                $rows->addRow();
                foreach (AuditTableHeaders::get($data['tableHeaders'] ?? [], 'passbook') as $header) {
                    $rows->addCell(1500, ['bgColor' => 'D9D9D9'])->addText($header, ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                }
                foreach ($finding['passbookRows'] ?? [] as $row) {
                    $rows->addRow();
                    foreach (['samity_no', 'member_name_id', 'date', 'savings_amount', 'installment_amount', 'savings_adjustment'] as $field) {
                        $rows->addCell(1500)->addText((string) ($row[$field] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
                    }
                }
            } elseif (($finding['detail_type'] ?? '') === 'sufolon_term') {
                $section->addText($finding['detail_intro'] ?? 'নিম্নে বিস্তারিত দেওয়া হলো:', $this->fontBold, ['spaceBefore' => 80]);
                $rows = $section->addTable($this->gridTable);
                $rows->addRow();
                foreach (AuditTableHeaders::get($data['tableHeaders'] ?? [], 'sufolon') as $header) {
                    $rows->addCell(900, ['bgColor' => 'D9D9D9'])->addText($header, ['name' => self::FONT, 'size' => 6, 'bold' => true], ['alignment' => Jc::CENTER]);
                }
                foreach ($finding['sufolonRows'] ?? [] as $row) {
                    $rows->addRow();
                    foreach (['sl_no', 'samity_member_id', 'member_name', 'disbursement_sector', 'disbursement_date', 'actual_term', 'software_last_date', 'software_term', 'disbursed_amount', 'excess_service_charge'] as $field) {
                        $rows->addCell(900)->addText((string) ($row[$field] ?? ''), ['name' => self::FONT, 'size' => 6], ['alignment' => Jc::CENTER]);
                    }
                }
            }

            $section->addText('ঝুঁকি (Risk):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['risk'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('মূল কারণ (Root Cause):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['root_cause'] ?: str_repeat('·', 40), $this->fontBody);
            $section->addText('সুপারিশ (Recommendation):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['recommendation'] ?: str_repeat('·', 40), $this->fontBody);

            $mgmt = $section->addTable($this->gridTable);
            foreach ([
                ['শাখা ব্যবস্থাপকের জবাব', $finding['bm_reply'] ?? ''],
                ['দায়িত্বপ্রাপ্ত কর্মী/পদক্ষেপ', $finding['responsible'] ?? ''],
                ['সমাধানের সময়কাল (তারিখ)', $finding['resolution_date'] ?? ''],
            ] as [$label, $value]) {
                $mgmt->addRow();
                $mgmt->addCell(3500)->addText($label, $this->fontBold);
                $mgmt->addCell(5500)->addText((string) $value, $this->fontSmall);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function hasFinancialPage15Sheet(array $data): bool
    {
        foreach ($data['documentSheets'] ?? [] as $sheet) {
            if (($sheet['type'] ?? '') === 'financial_page15') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildFinancialPage15($section, array $data): void
    {
        foreach ($data['page15Findings'] ?? [] as $finding) {
            $this->addSpacer($section, 120);
            $findingTable = $section->addTable($this->gridTable);
            $findingTable->addRow();
            $widths = Doc::findingColumnWidths();
            $findingTable->addCell($this->pct($widths[0]), ['valign' => 'center'])
                ->addText($finding['serial'] ?? '', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $findingTable->addCell($this->pct($widths[1]), ['valign' => 'center'])
                ->addText($finding['title'] ?? 'শিরোনাম', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $body = (string) ($finding['body'] ?? '');
            if (($finding['amount'] ?? '') !== '') {
                $body .= "\nটাকার পরিমাণ: ".$finding['amount'];
            }
            $findingTable->addCell($this->pct($widths[2]), ['valign' => 'top'])
                ->addText($body, $this->fontSmall, ['alignment' => Jc::BOTH]);
            $ratingCell = $findingTable->addCell($this->pct($widths[3]), ['valign' => 'center']);
            $this->addRatingBox($ratingCell, $finding['rating'] ?? '');

            $section->addText('প্রচলিত নিয়ম (Criteria):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['criteria'] ?: str_repeat('·', 40), $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('পর্যবেক্ষণ (Observation) :', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['observation'] ?: str_repeat('·', 40), $this->fontBody);

            $this->addObservationTable($section, '', $finding['statsRows'] ?? [], $data);

            if (($finding['detail_type'] ?? 'none') === 'arrears_compare') {
                $section->addText($finding['detail_intro'] ?: 'নিম্নে বিস্তারিত দেওয়া হলো:', $this->fontBold, ['spaceBefore' => 80]);
                $arrears = $section->addTable($this->gridTable);
                $arrears->addRow();
                foreach (AuditTableHeaders::get($data['tableHeaders'] ?? [], 'arrears') as $header) {
                    $arrears->addCell(1000)->addText($header, ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                }
                foreach ($finding['arrearsRows'] ?? [] as $row) {
                    $arrears->addRow();
                    foreach ([
                        'samity_no', 'member_name_id', 'disbursement_date', 'loan_amount',
                        'actual_due_date', 'software_due_date', 'installment_date',
                        'actual_arrears', 'software_arrears',
                    ] as $field) {
                        $arrears->addCell(1000)->addText((string) ($row[$field] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
                    }
                }
            }

            $section->addText('ঝুঁকি (Risk):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['risk'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('মূল কারণ (Root Cause):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['root_cause'] ?: str_repeat('·', 40), $this->fontBody);
            $section->addText('সুপারিশ (Recommendation):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['recommendation'] ?: str_repeat('·', 40), $this->fontBody);

            $mgmt = $section->addTable($this->gridTable);
            foreach ([
                ['শাখা ব্যবস্থাপকের জবাব', $finding['bm_reply'] ?? ''],
                ['দায়িত্বপ্রাপ্ত কর্মী/পদক্ষেপ', $finding['responsible'] ?? ''],
                ['সমাধানের সময়কাল (তারিখ)', $finding['resolution_date'] ?? ''],
            ] as [$label, $value]) {
                $mgmt->addRow();
                $mgmt->addCell(3500)->addText($label, $this->fontBold);
                $mgmt->addCell(5500)->addText((string) $value, $this->fontSmall);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function hasFinancialPage16Sheet(array $data): bool
    {
        foreach ($data['documentSheets'] ?? [] as $sheet) {
            if (($sheet['type'] ?? '') === 'financial_page16') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildFinancialPage16($section, array $data): void
    {
        foreach ($data['page16Findings'] ?? [] as $finding) {
            $this->addSpacer($section, 120);
            $findingTable = $section->addTable($this->gridTable);
            $findingTable->addRow();
            $widths = Doc::findingColumnWidths();
            $findingTable->addCell($this->pct($widths[0]), ['valign' => 'center'])
                ->addText($finding['serial'] ?? '', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $findingTable->addCell($this->pct($widths[1]), ['valign' => 'center'])
                ->addText($finding['title'] ?? 'শিরোনাম', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $body = (string) ($finding['body'] ?? '');
            if (($finding['amount'] ?? '') !== '') {
                $body .= "\nটাকার পরিমাণ: ".$finding['amount'];
            }
            $findingTable->addCell($this->pct($widths[2]), ['valign' => 'top'])
                ->addText($body, $this->fontSmall, ['alignment' => Jc::BOTH]);
            $ratingCell = $findingTable->addCell($this->pct($widths[3]), ['valign' => 'center']);
            $this->addRatingBox($ratingCell, $finding['rating'] ?? '');

            $section->addText('প্রচলিত নিয়ম (Criteria):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['criteria'] ?: str_repeat('·', 40), $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('পর্যবেক্ষণ (Observation) :', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['observation'] ?: str_repeat('·', 40), $this->fontBody);

            $this->addObservationTable($section, '', $finding['statsRows'] ?? [], $data);

            if (($finding['detail_type'] ?? 'none') === 'passbook_absent') {
                $passbook = $section->addTable($this->gridTable);
                $passbook->addRow();
                foreach (AuditTableHeaders::get($data['tableHeaders'] ?? [], 'passbook_absent') as $header) {
                    $passbook->addCell(1200)->addText($header, ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                }
                foreach ($finding['passbookAbsentRows'] ?? [] as $row) {
                    $passbook->addRow();
                    foreach ([
                        'staff_name', 'samity_no', 'total_members',
                        'passbooks_received', 'passbooks_absent', 'officer_comment',
                    ] as $field) {
                        $passbook->addCell(1200)->addText((string) ($row[$field] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
                    }
                }
            }

            $section->addText('ঝুঁকি (Risk):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['risk'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('মূল কারণ (Root Cause):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['root_cause'] ?: str_repeat('·', 40), $this->fontBody);
            $section->addText('সুপারিশ (Recommendation):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['recommendation'] ?: str_repeat('·', 40), $this->fontBody);

            $mgmt = $section->addTable($this->gridTable);
            foreach ([
                ['শাখা ব্যবস্থাপকের জবাব', $finding['bm_reply'] ?? ''],
                ['দায়িত্বপ্রাপ্ত কর্মী/পদক্ষেপ', $finding['responsible'] ?? ''],
                ['সমাধানের সময়কাল (তারিখ)', $finding['resolution_date'] ?? ''],
            ] as [$label, $value]) {
                $mgmt->addRow();
                $mgmt->addCell(3500)->addText($label, $this->fontBold);
                $mgmt->addCell(5500)->addText((string) $value, $this->fontSmall);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function hasFinancialPage17Sheet(array $data): bool
    {
        foreach ($data['documentSheets'] ?? [] as $sheet) {
            if (($sheet['type'] ?? '') === 'financial_page17') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildFinancialPage17($section, array $data): void
    {
        foreach ($data['page17Findings'] ?? [] as $finding) {
            $this->addSpacer($section, 120);
            $findingTable = $section->addTable($this->gridTable);
            $findingTable->addRow();
            $widths = Doc::findingColumnWidths();
            $findingTable->addCell($this->pct($widths[0]), ['valign' => 'center'])
                ->addText($finding['serial'] ?? '', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $findingTable->addCell($this->pct($widths[1]), ['valign' => 'center'])
                ->addText($finding['title'] ?? 'শিরোনাম', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $body = (string) ($finding['body'] ?? '');
            if (($finding['amount'] ?? '') !== '') {
                $body .= "\nটাকার পরিমাণ: ".$finding['amount'];
            }
            $findingTable->addCell($this->pct($widths[2]), ['valign' => 'top'])
                ->addText($body, $this->fontSmall, ['alignment' => Jc::BOTH]);
            $ratingCell = $findingTable->addCell($this->pct($widths[3]), ['valign' => 'center']);
            $this->addRatingBox($ratingCell, $finding['rating'] ?? '');

            $section->addText('প্রচলিত নিয়ম (Criteria):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['criteria'] ?: str_repeat('·', 40), $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('পর্যবেক্ষণ (Observation) :', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['observation'] ?: str_repeat('·', 40), $this->fontBody);

            $this->addObservationTable($section, '', $finding['statsRows'] ?? [], $data);

            if (($finding['detail_type'] ?? 'none') === 'savings_partial_adjust') {
                $section->addText($finding['detail_intro'] ?: 'বিস্তারিত নিম্নে দেওয়া হল:', $this->fontBold, ['spaceBefore' => 80]);
                $adjust = $section->addTable($this->gridTable);
                $adjust->addRow();
                $adjust->addCell(1200, ['vMerge' => 'restart'])->addText($this->hdr($data, 'savings_adjust_r1', 0), ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                $adjust->addCell(1800, ['vMerge' => 'restart'])->addText($this->hdr($data, 'savings_adjust_r1', 1), ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                $adjust->addCell(2400, ['gridSpan' => 2])->addText($this->hdr($data, 'savings_adjust_r1', 2), ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                $adjust->addRow();
                $adjust->addCell(1200, ['vMerge' => 'continue']);
                $adjust->addCell(1800, ['vMerge' => 'continue']);
                $adjust->addCell(1200)->addText($this->hdr($data, 'savings_adjust_r2', 0), ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                $adjust->addCell(1200)->addText($this->hdr($data, 'savings_adjust_r2', 1), ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                foreach ($finding['savingsAdjustRows'] ?? [] as $row) {
                    $adjust->addRow();
                    $adjust->addCell(1200)->addText((string) ($row['samity_no'] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
                    $adjust->addCell(1800)->addText((string) ($row['member_name_id'] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
                    $adjust->addCell(1200)->addText((string) ($row['adjust_date'] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
                    $adjust->addCell(1200)->addText((string) ($row['adjust_amount'] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
                }
            }

            $section->addText('ঝুঁকি (Risk):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['risk'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('মূল কারণ (Root Cause):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['root_cause'] ?: str_repeat('·', 40), $this->fontBody);
            $section->addText('সুপারিশ (Recommendation):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['recommendation'] ?: str_repeat('·', 40), $this->fontBody);

            $mgmt = $section->addTable($this->gridTable);
            foreach ([
                ['শাখা ব্যবস্থাপকের জবাব', $finding['bm_reply'] ?? ''],
                ['দায়িত্বপ্রাপ্ত কর্মী/পদক্ষেপ', $finding['responsible'] ?? ''],
                ['সমাধানের সময়কাল (তারিখ)', $finding['resolution_date'] ?? ''],
            ] as [$label, $value]) {
                $mgmt->addRow();
                $mgmt->addCell(3500)->addText($label, $this->fontBold);
                $mgmt->addCell(5500)->addText((string) $value, $this->fontSmall);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function hasFinancialPage18Sheet(array $data): bool
    {
        foreach ($data['documentSheets'] ?? [] as $sheet) {
            if (($sheet['type'] ?? '') === 'financial_page18') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildFinancialPage18($section, array $data): void
    {
        foreach ($data['page18Findings'] ?? [] as $finding) {
            $this->addSpacer($section, 120);
            $findingTable = $section->addTable($this->gridTable);
            $findingTable->addRow();
            $widths = Doc::findingColumnWidths();
            $findingTable->addCell($this->pct($widths[0]), ['valign' => 'center'])
                ->addText($finding['serial'] ?? '', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $findingTable->addCell($this->pct($widths[1]), ['valign' => 'center'])
                ->addText($finding['title'] ?? 'শিরোনাম', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $body = (string) ($finding['body'] ?? '');
            if (($finding['amount'] ?? '') !== '') {
                $body .= "\nটাকার পরিমাণ: ".$finding['amount'];
            }
            $findingTable->addCell($this->pct($widths[2]), ['valign' => 'top'])
                ->addText($body, $this->fontSmall, ['alignment' => Jc::BOTH]);
            $ratingCell = $findingTable->addCell($this->pct($widths[3]), ['valign' => 'center']);
            $this->addRatingBox($ratingCell, $finding['rating'] ?? '');

            $section->addText('প্রচলিত নিয়ম (Criteria):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['criteria'] ?: str_repeat('·', 40), $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('পর্যবেক্ষণ (Observation) :', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['observation'] ?: str_repeat('·', 40), $this->fontBody);

            $this->addObservationTable($section, '', $finding['statsRows'] ?? [], $data);

            if (($finding['detail_type'] ?? 'none') === 'dropout_savings_refund') {
                $section->addText($finding['detail_intro'] ?: 'বিস্তারিত নিম্নে দেওয়া হল:', $this->fontBold, ['spaceBefore' => 80]);
                $refund = $section->addTable($this->gridTable);
                $refund->addRow();
                $refund->addCell(1500)->addText($this->hdr($data, 'dropout_refund', 0), ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                $refund->addCell(1800)->addText($this->hdr($data, 'dropout_refund', 1), ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                $refund->addCell(2400)->addText($this->hdr($data, 'dropout_refund', 2), ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                $refund->addCell(1800)->addText($this->hdr($data, 'dropout_refund', 3), ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                foreach ($finding['dropoutRefundRows'] ?? [] as $row) {
                    $refund->addRow();
                    $refund->addCell(1500)->addText((string) ($row['date'] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
                    $refund->addCell(1800)->addText((string) ($row['samity_member_no'] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
                    $refund->addCell(2400)->addText((string) ($row['member_name'] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
                    $refund->addCell(1800)->addText((string) ($row['refund_amount'] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
                }
            } elseif (($finding['detail_type'] ?? 'none') === 'savings_adjust_compare') {
                $section->addText($finding['detail_intro'] ?: 'বিস্তারিত নিম্নে দেওয়া হল:', $this->fontBold, ['spaceBefore' => 80]);
                $compare = $section->addTable($this->gridTable);
                $compare->addRow();
                $compare->addCell(1800)->addText($this->hdr($data, 'savings_compare', 0), ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                $compare->addCell(2200)->addText($this->hdr($data, 'savings_compare', 1), ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                $compare->addCell(2200)->addText($this->hdr($data, 'savings_compare', 2), ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                $compare->addCell(1800)->addText($this->hdr($data, 'savings_compare', 3), ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                foreach ($finding['savingsAdjustCompareRows'] ?? [] as $row) {
                    $compare->addRow();
                    $compare->addCell(1800)->addText((string) ($row['month_name'] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
                    $compare->addCell(2200)->addText((string) ($row['manual_adjust'] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
                    $compare->addCell(2200)->addText((string) ($row['software_adjust'] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
                    $compare->addCell(1800)->addText((string) ($row['difference'] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
                }
            } elseif (($finding['detail_intro'] ?? '') !== '') {
                $section->addText($finding['detail_intro'], $this->fontBold, ['spaceBefore' => 80]);
            }

            $section->addText('ঝুঁকি (Risk):', $this->fontBold, ['spaceBefore' => 120]);
            $section->addText($finding['risk'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
            $section->addText('মূল কারণ (Root Cause):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['root_cause'] ?: str_repeat('·', 40), $this->fontBody);
            $section->addText('সুপারিশ (Recommendation):', $this->fontBold, ['spaceBefore' => 80]);
            $section->addText($finding['recommendation'] ?: str_repeat('·', 40), $this->fontBody);

            $mgmt = $section->addTable($this->gridTable);
            foreach ([
                ['শাখা ব্যবস্থাপকের জবাব', $finding['bm_reply'] ?? ''],
                ['দায়িত্বপ্রাপ্ত কর্মী/পদক্ষেপ', $finding['responsible'] ?? ''],
                ['সমাধানের সময়কাল (তারিখ)', $finding['resolution_date'] ?? ''],
            ] as [$label, $value]) {
                $mgmt->addRow();
                $mgmt->addCell(3500)->addText($label, $this->fontBold);
                $mgmt->addCell(5500)->addText((string) $value, $this->fontSmall);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function hasFinancialPage19Sheet(array $data): bool
    {
        foreach ($data['documentSheets'] ?? [] as $sheet) {
            if (($sheet['type'] ?? '') === 'financial_page19') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildFinancialPage19($section, array $data): void
    {
        $this->addComplianceTable($section, [
            'title' => (string) ($data['page19_compliance_title'] ?? ''),
            'title_en' => '',
            'period' => (string) ($data['page19_compliance_period'] ?? ''),
            'followup_date' => (string) ($data['page19_compliance_followup_date'] ?? ''),
            'headers' => array_values(AuditTableHeaders::get($data['tableHeaders'] ?? [], 'compliance')),
            'rows' => array_values((array) ($data['page19ComplianceRows'] ?? [])),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function hasFinancialPage20Sheet(array $data): bool
    {
        foreach ($data['documentSheets'] ?? [] as $sheet) {
            if (($sheet['type'] ?? '') === 'financial_page20') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildFinancialPage20($section, array $data): void
    {
        $this->addItChecklistTable($section, [
            'title' => (string) ($data['page20_it_title'] ?? ''),
            'org_line1' => (string) ($data['page20_it_org_line1'] ?? ''),
            'org_line2' => (string) ($data['page20_it_org_line2'] ?? ''),
            'org_line3' => (string) ($data['page20_it_org_line3'] ?? ''),
            'program' => (string) ($data['page20_it_program'] ?? ''),
            'branch' => (string) ($data['page20_it_branch'] ?? ''),
            'instruction' => (string) ($data['page20_it_instruction'] ?? 'প্রযোজ্য ক্ষেত্রে টিক চিহ্ন দিন'),
            'headers_r1' => AuditTableHeaders::get($data['tableHeaders'] ?? [], 'it_r1'),
            'headers_r2' => AuditTableHeaders::get($data['tableHeaders'] ?? [], 'it_r2'),
            'extra_headers' => [],
            'rows' => array_values((array) ($data['page20ItChecklistRows'] ?? [])),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function hasFinancialPage21Sheet(array $data): bool
    {
        foreach ($data['documentSheets'] ?? [] as $sheet) {
            if (($sheet['type'] ?? '') === 'financial_page21') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildFinancialPage21($section, array $data): void
    {
        $this->addSpacer($section, 120);
        $section->addText($data['page21_section_title'] ?? '', $this->fontBold, ['spaceAfter' => 80]);
        $section->addText(
            'Year of reporting: '.($data['page21_year_of_reporting'] ?? '').'    Name of Branch: '.($data['page21_branch_name'] ?? ''),
            ['name' => self::FONT, 'size' => 8],
            ['spaceAfter' => 80]
        );

        $peachHeader = ['bgColor' => 'FCE5CD'];
        $peachHeaderAlt = ['bgColor' => 'F5D5B8'];
        $headers = AuditTableHeaders::get($data['tableHeaders'] ?? [], 'external');
        $fields = ['area_of_observation', 'compliance_area', 'year_of_reporting', 'external_observation', 'compliance', 'internal_index_no'];

        $external = $section->addTable($this->gridTable);
        $external->addRow();
        foreach ($headers as $index => $header) {
            $external->addCell(1500, $index % 2 === 0 ? $peachHeader : $peachHeaderAlt)
                ->addText($header, ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
        }
        foreach ($data['page21ExternalAuditRows'] ?? [] as $row) {
            $external->addRow();
            foreach ($fields as $field) {
                $external->addCell(1500)->addText((string) ($row[$field] ?? ''), ['name' => self::FONT, 'size' => 7]);
            }
        }

        $this->addSpacer($section, 160);
        $section->addText($data['page21_sign_label'] ?? 'নিরীক্ষা কর্মকর্তার স্বাক্ষরঃ', $this->fontBold, ['spaceAfter' => 120]);
        $section->addText((string) ($data['page21_sign_name'] ?? ''), ['name' => self::FONT, 'size' => 8], ['spaceAfter' => 40]);
        $section->addText((string) ($data['page21_sign_designation'] ?? ''), ['name' => self::FONT, 'size' => 8]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function hdr(array $data, string $key, int $i): string
    {
        $headers = AuditTableHeaders::get($data['tableHeaders'] ?? [], $key);

        return (string) ($headers[$i] ?? '');
    }

    protected function toBinary(): string
    {
        $temp = tempnam(sys_get_temp_dir(), 'audit-docx-');
        if ($temp === false) {
            throw new \RuntimeException('Unable to create temporary DOCX file.');
        }

        try {
            $writer = IOFactory::createWriter($this->word, 'Word2007');
            $writer->save($temp);
            $binary = file_get_contents($temp);
            if ($binary === false) {
                throw new \RuntimeException('Unable to read generated DOCX file.');
            }

            return $binary;
        } finally {
            @unlink($temp);
        }
    }
}
