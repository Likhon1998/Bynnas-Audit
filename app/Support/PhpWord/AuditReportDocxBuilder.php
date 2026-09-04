<?php

namespace App\Support\PhpWord;

use App\Livewire\MakeAuditReport;
use App\Support\AuditDocumentLayout as Doc;
use App\Support\AuditReportClassification;
use App\Support\BanglaNumerals;
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
        $this->buildClassification($section);

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
        foreach ([
            'নির্বাহী পরিচালক',
            'উপ-নির্বাহী পরিচালক',
            'পরিচালক ঋণ',
            'উপ-প্রধান ঋণ',
            'যুগ্ম পরিচালক প্রশাসন ও মানব সম্পদ',
            'ফোকাল পার্সন',
            'অঞ্চলিক ব্যবস্থাপক',
            'শাখা ব্যবস্থাপক',
            'অফিস কপি',
        ] as $index => $item) {
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
        $this->addTocTable($section, $this->overviewRows($data));
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

    protected function buildClassification($section): void
    {
        $this->addSpacer($section, 200);
        $section->addText('প্রতিবেদনের শ্রেণীবিন্যাস', ['name' => self::FONT, 'size' => 12.5, 'bold' => true, 'underline' => 'single'], ['alignment' => Jc::CENTER, 'spaceAfter' => 120]);

        $table = $section->addTable($this->gridTable);
        $table->addRow();
        foreach (['পর্যবেক্ষণসমূহের গুরুত্বের মাত্রা', 'কোড', 'রেটিং নির্বাচনের বিষদ, পয়েন্ট ও কারণ'] as $index => $heading) {
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
            ->addText('নিরীক্ষাকার্যে ফলাফল মূল্যায়ন', ['name' => self::FONT, 'size' => 8.5, 'bold' => true, 'color' => 'FFFFFF'], ['alignment' => Jc::CENTER]);
        $summary->addCell($this->pct(30), ['bgColor' => '2E5090', 'valign' => 'center'])
            ->addText('সমষ্টিগত কর্মসম্পাদনের হার', ['name' => self::FONT, 'size' => 8.5, 'bold' => true, 'color' => 'FFFFFF'], ['alignment' => Jc::CENTER]);

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
        $section->addText($data['financial_section_title'] ?? '১.০ আর্থিক নিরীক্ষা (Financial Audit) :', $this->fontBold);

        foreach ($data['financialFindings'] ?? [] as $finding) {
            $table = $section->addTable($this->gridTable);
            $table->addRow();
            $widths = Doc::findingColumnWidths();
            $table->addCell($this->pct($widths[0]), ['valign' => 'center'])
                ->addText($finding['serial'] ?? '', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $table->addCell($this->pct($widths[1]), ['valign' => 'center'])
                ->addText($finding['title'] ?? 'শিরোনাম', ['name' => self::FONT, 'size' => 9.5, 'bold' => true], ['alignment' => Jc::CENTER]);
            $table->addCell($this->pct($widths[2]), ['valign' => 'top'])
                ->addText($finding['body'] ?? '', $this->fontSmall, ['alignment' => Jc::BOTH]);
            $ratingCell = $table->addCell($this->pct($widths[3]), ['valign' => 'center']);
            $this->addRatingBox($ratingCell, $finding['rating'] ?? '');
            $this->addSpacer($section, 80);
        }

        $section->addText('প্রচলিত নিয়ম (Criteria):', $this->fontBold, ['spaceBefore' => 120]);
        $section->addText($data['financial_criteria'] ?? '', $this->fontBody, ['alignment' => Jc::BOTH]);
        $section->addText('পর্যবেক্ষণ (Observation) :', $this->fontBold, ['spaceBefore' => 120]);
        $section->addText(str_repeat('·', 40), $this->fontBody);

        $this->addObservationTable($section, 'ভ্যাট সংক্রান্ত:', $data['vatObservationRows'] ?? []);
        $this->addObservationTable($section, 'ট্যাক্স সংক্রান্ত:', $data['taxObservationRows'] ?? []);
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
        foreach (['তারিখ/মাস', 'ভাউচার', 'বিবরণ', 'খরচ', 'ভ্যাট প্রযোজ্য', 'ভ্যাট প্রদান', 'ভ্যাট কম/বেশি', 'ট্যাক্স প্রযোজ্য', 'ট্যাক্স প্রদান', 'ট্যাক্স কম/বেশি'] as $header) {
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

        $this->addObservationTable($section, '', $data['finding13_statsRows'] ?? []);

        $deposit = $section->addTable($this->gridTable);
        $deposit->addRow();
        foreach (['বিবরণ', 'মাস', 'উত্তোলন', 'জমা', 'টাকা', 'সময়কাল'] as $header) {
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
     */
    protected function addTocTable($section, array $rows): void
    {
        $widths = Doc::tocColumnWidths();
        $table = $section->addTable($this->gridTable);
        $headers = ['ক্রমিক নং', 'নিরীক্ষায় প্রাপ্ত ঘটনা সমূহ', 'টাকা', 'রেটিং', 'বর্তমান অবস্থা', 'পৃষ্ঠা নাম্বার'];
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
     */
    protected function addObservationTable($section, string $label, array $rows): void
    {
        $section->addText($label, $this->fontBold, ['spaceBefore' => 120, 'spaceAfter' => 60]);
        $table = $section->addTable($this->gridTable);
        $headers = ['Total Population', 'Sample Size(Checked)', 'Instantans Found', 'Persentange(%)'];
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
        $inner->addCell($this->pct(50), ['bgColor' => 'F8CBAD', 'valign' => 'center'])
            ->addText($parts['label'] ?: '—', ['name' => self::FONT, 'size' => 9, 'bold' => true], ['alignment' => Jc::CENTER]);
        $inner->addCell($this->pct(50), ['bgColor' => 'F8CBAD', 'valign' => 'center'])
            ->addText($parts['code'] ?: '—', ['name' => self::FONT, 'size' => 9, 'bold' => true], ['alignment' => Jc::CENTER]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function addCoverRating($cell, array $data): void
    {
        $ratingTable = $cell->addTable([
            'borderSize' => 0,
            'cellMargin' => 40,
            'alignment' => Jc::CENTER,
            'width' => 100 * 50,
            'unit' => 'pct',
        ]);
        $ratingTable->addRow();
        $ratingTable->addCell($this->pct(100), ['bgColor' => '1D4ED8', 'valign' => 'center'])
            ->addText("Branch Internal\nControl Rating", ['name' => self::FONT, 'size' => 8, 'bold' => true, 'color' => 'FFFFFF'], ['alignment' => Jc::CENTER]);
        $ratingTable->addRow();
        $ratingTable->addCell($this->pct(100), [
            'borderTopSize' => 12,
            'borderTopColor' => 'F97316',
            'borderBottomSize' => 12,
            'borderBottomColor' => 'F97316',
            'borderLeftSize' => 12,
            'borderLeftColor' => 'F97316',
            'borderRightSize' => 12,
            'borderRightColor' => 'F97316',
            'bgColor' => ltrim((string) ($data['ratingColor'] ?? '16A34A'), '#'),
            'valign' => 'center',
        ])->addText($data['control_rating'] ?: '—', ['name' => self::FONT, 'size' => 10, 'bold' => true, 'color' => 'FFFFFF'], ['alignment' => Jc::CENTER]);
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

            $this->addObservationTable($section, '', $finding['statsRows'] ?? []);

            $section->addText($finding['detail_intro'] ?? 'বিস্তারিত নিম্নে দেওয়া হলো:', $this->fontBold, ['spaceBefore' => 80]);
            $vouchers = $section->addTable($this->gridTable);
            $vouchers->addRow();
            foreach (['তারিখ', 'ভাউচার', 'বিবরণ', 'টাকা', 'মন্তব্য'] as $header) {
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

            $this->addObservationTable($section, '', $finding['statsRows'] ?? []);

            if (($finding['detail_type'] ?? 'none') === 'budget') {
                $year = (string) ($finding['budget_year'] ?? '২০২২-২০২৩');
                $section->addText($finding['detail_intro'] ?? 'নিম্নে বিস্তারিত দেওয়া হলো:', $this->fontBold, ['spaceBefore' => 80]);
                $budget = $section->addTable($this->gridTable);
                $budget->addRow();
                foreach (['বাজেটের খাত', 'বাজেট '.$year.' (বাৎসরিক)', 'জুন পর্যন্ত', 'প্রকৃত খরচ', 'পার্থক্য'] as $header) {
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
                foreach (['যোগদানের তারিখ', 'বোনাস তারিখ ও ভাউচার', 'চাকরির বয়স', 'বোনাসের পরিমাণ'] as $header) {
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

            $this->addObservationTable($section, '', $finding['statsRows'] ?? []);

            if (($finding['detail_type'] ?? 'cost_of_fund') === 'cost_of_fund') {
                $cof = $section->addTable($this->gridTable);
                $cof->addRow();
                foreach (['মাস', 'ওপেনিং', 'ক্লোজিং', 'মোট', 'গড়', '১০%', 'লাভ', 'শাখা', 'পার্থক্য'] as $header) {
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

            $this->addObservationTable($section, '', $finding['statsRows'] ?? []);

            if (($finding['detail_type'] ?? '') === 'cash') {
                $section->addText($finding['detail_intro'] ?? 'নিম্নে বিস্তারিত দেওয়া হলো:', $this->fontBold, ['spaceBefore' => 80]);
                $cash = $section->addTable($this->gridTable);
                $cash->addRow();
                foreach (['তারিখ', 'নগদ', 'তারিখ', 'নগদ', 'তারিখ', 'নগদ'] as $header) {
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
                foreach (['তারিখ', 'ভাউচার', 'পরিমাণ', 'বিবরণ'] as $header) {
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

            $this->addObservationTable($section, '', $finding['statsRows'] ?? []);

            if (($finding['detail_type'] ?? 'asset') === 'asset') {
                $section->addText($finding['detail_intro'] ?? 'নিম্নে বিস্তারিত দেওয়া হলো:', $this->fontBold, ['spaceBefore' => 80]);
                $assets = $section->addTable($this->gridTable);
                $assets->addRow();
                foreach (['ক্রয়ের তারিখ', 'ভাউচার', 'সম্পদের নাম', 'ক্রয়মূল্য', 'পূর্বের খাত', 'বর্তমান অবস্থান'] as $header) {
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

            $this->addObservationTable($section, '', $finding['statsRows'] ?? []);

            if (($finding['detail_type'] ?? '') === 'dep_compare') {
                $section->addText($finding['detail_intro'] ?? 'নিম্নে বিস্তারিত দেওয়া হলো:', $this->fontBold, ['spaceBefore' => 80]);
                $dep = $section->addTable($this->gridTable);
                $dep->addRow();
                foreach (['গ্রুপ', 'মূল্য (প্রতিবেদন)', 'মূল্য (রেজিস্টার)', 'পার্থক্য', 'অবচয় (প্রতিবেদন)', 'অবচয় (রেজিস্টার)', 'পার্থক্য'] as $header) {
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
                foreach (['পণ্যের নাম', 'পণ্যের গ্রুপ', 'ক্রয়ের তারিখ', 'ভাউচার নং', 'টাকার পরিমাণ', 'কোটেশনের অবস্থা'] as $header) {
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

            $this->addObservationTable($section, '', $finding['statsRows'] ?? []);

            if (($finding['detail_type'] ?? 'stock') === 'stock') {
                $section->addText($finding['detail_intro'] ?? 'নিম্নে বিস্তারিত দেওয়া হলো:', $this->fontBold, ['spaceBefore' => 80]);
                $stocks = $section->addTable($this->gridTable);
                $stocks->addRow();
                foreach (['পণ্যের নাম', 'ক্রয়ের তারিখ ও ভাউচার নং', 'ক্রয়কৃত পণ্যের মূল্য', 'স্টক রেজিস্টারের অবস্থা'] as $header) {
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

            $this->addObservationTable($section, '', $finding['statsRows'] ?? []);

            if (($finding['detail_type'] ?? '') === 'samity_collection') {
                $section->addText($finding['detail_intro'] ?? 'বিস্তারিত নিম্নে দেওয়া হলো:', $this->fontBold, ['spaceBefore' => 80]);
                $samity = $section->addTable($this->gridTable);
                $samity->addRow();
                foreach (['সমিতি', 'সদস্য/আইডি', 'তারিখ', 'বা:স:', 'স্বেচ্ছা', 'মেয়াদী', 'কিস্তি', 'মোট', 'জমা তারিখ', 'জমা টাকা', 'পার্থক্য', 'কর্মী'] as $header) {
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

            $this->addObservationTable($section, '', $finding['statsRows'] ?? []);

            if (($finding['detail_type'] ?? '') === 'passbook_installment') {
                $section->addText($finding['detail_intro'] ?? 'নিম্নে বিস্তারিত দেওয়া হলো:', $this->fontBold, ['spaceBefore' => 80]);
                $rows = $section->addTable($this->gridTable);
                $rows->addRow();
                foreach (['সমিতি নং', 'সদস্য/আইডি', 'তারিখ', 'সঞ্চয় আদায়', 'কিস্তি/সেবা', 'সঞ্চয় সমন্বয়'] as $header) {
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
                foreach (['ক্রমিক', 'সমিতি/সদস্য', 'নাম', 'খাত', 'বিতরণ তারিখ', 'প্রকৃত মেয়াদ', 'শেষ তারিখ', 'সফট মেয়াদ', 'বিতরণ টাকা', 'অতিরিক্ত সেবা'] as $header) {
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

            $this->addObservationTable($section, '', $finding['statsRows'] ?? []);

            if (($finding['detail_type'] ?? 'none') === 'arrears_compare') {
                $section->addText($finding['detail_intro'] ?: 'নিম্নে বিস্তারিত দেওয়া হলো:', $this->fontBold, ['spaceBefore' => 80]);
                $arrears = $section->addTable($this->gridTable);
                $arrears->addRow();
                foreach ([
                    'সমিতি নং', 'সদস্যের নাম ও আইডি', 'ঋণ বিতরণের তারিখ', 'ঋণের পরিমাণ',
                    'প্রকৃত আদায়যোগ্য তারিখ', 'সফটওয়্যারে আদায়যোগ্য তারিখ', 'কিস্তি আদায়ের তারিখ',
                    'প্রকৃত বকেয়া', 'সফটওয়্যারে বকেয়া',
                ] as $header) {
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

            $this->addObservationTable($section, '', $finding['statsRows'] ?? []);

            if (($finding['detail_type'] ?? 'none') === 'passbook_absent') {
                $passbook = $section->addTable($this->gridTable);
                $passbook->addRow();
                foreach ([
                    'কর্মীর নাম', 'সমিতি নং', 'মোট সদস্য', 'প্রাপ্ত পাসবই সংখ্যা',
                    'অনুপস্থিত পাসবই সংখ্যা', 'নিরীক্ষা/ পরিবীক্ষণ কর্মকর্তার মন্তব্য',
                ] as $header) {
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

            $this->addObservationTable($section, '', $finding['statsRows'] ?? []);

            if (($finding['detail_type'] ?? 'none') === 'savings_partial_adjust') {
                $section->addText($finding['detail_intro'] ?: 'বিস্তারিত নিম্নে দেওয়া হল:', $this->fontBold, ['spaceBefore' => 80]);
                $adjust = $section->addTable($this->gridTable);
                $adjust->addRow();
                $adjust->addCell(1200, ['vMerge' => 'restart'])->addText('সমিতি নং', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                $adjust->addCell(1800, ['vMerge' => 'restart'])->addText('সদস্যের নাম ও আইডি', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                $adjust->addCell(2400, ['gridSpan' => 2])->addText('১ম দফা ঋণের আংশিক সমন্বয়', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                $adjust->addRow();
                $adjust->addCell(1200, ['vMerge' => 'continue']);
                $adjust->addCell(1800, ['vMerge' => 'continue']);
                $adjust->addCell(1200)->addText('তারিখ', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                $adjust->addCell(1200)->addText('টাকা', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
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

            $this->addObservationTable($section, '', $finding['statsRows'] ?? []);

            if (($finding['detail_type'] ?? 'none') === 'dropout_savings_refund') {
                $section->addText($finding['detail_intro'] ?: 'বিস্তারিত নিম্নে দেওয়া হল:', $this->fontBold, ['spaceBefore' => 80]);
                $refund = $section->addTable($this->gridTable);
                $refund->addRow();
                $refund->addCell(1500)->addText('তারিখ', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                $refund->addCell(1800)->addText('সমিতি/সদস্য নং', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                $refund->addCell(2400)->addText('সদস্যের নাম', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                $refund->addCell(1800)->addText('সঞ্চয় ফেরতের পরিমাণ', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
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
                $compare->addCell(1800)->addText('মাসের নাম', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                $compare->addCell(2200)->addText('ম্যানুয়ালী সঞ্চয় সমন্বয়', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                $compare->addCell(2200)->addText('সফটওয়্যার অনুযায়ী সঞ্চয় সমন্বয়', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
                $compare->addCell(1800)->addText('পার্থক্য', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
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
        $this->addSpacer($section, 120);
        $section->addText($data['page19_compliance_title'] ?? '', $this->fontBold, ['spaceAfter' => 80]);
        $section->addText(
            'নিরীক্ষাকাল: '.($data['page19_compliance_period'] ?? '').'    ফলোআপের তারিখ: '.($data['page19_compliance_followup_date'] ?? ''),
            ['name' => self::FONT, 'size' => 8],
            ['spaceAfter' => 80]
        );

        $compliance = $section->addTable($this->gridTable);
        $compliance->addRow();
        foreach (['পূর্ববর্তী অনুচ্ছেদ নং', 'অনুসন্ধান/পর্যবেক্ষণ', 'প্রথম আবিষ্কারের নিরীক্ষাকাল', 'ব্যবস্থাপনার জবাব', 'বর্তমান অবস্থা', 'বর্তমান অনুচ্ছেদ নং'] as $header) {
            $compliance->addCell(1500)->addText($header, ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
        }
        foreach ($data['page19ComplianceRows'] ?? [] as $row) {
            $compliance->addRow();
            $compliance->addCell(1500)->addText((string) ($row['prev_para_no'] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
            $compliance->addCell(2000)->addText((string) ($row['findings'] ?? ''), ['name' => self::FONT, 'size' => 7]);
            $compliance->addCell(1500)->addText((string) ($row['first_discovery_period'] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
            $compliance->addCell(2000)->addText((string) ($row['management_reply'] ?? ''), ['name' => self::FONT, 'size' => 7]);
            $compliance->addCell(1500)->addText((string) ($row['current_status'] ?? ''), ['name' => self::FONT, 'size' => 7]);
            $compliance->addCell(1500)->addText((string) ($row['current_para_no'] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
        }
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
        $this->addSpacer($section, 120);
        $section->addText($data['page20_it_title'] ?? '', $this->fontBold, ['alignment' => Jc::CENTER, 'spaceAfter' => 80]);
        $section->addText(
            trim(($data['page20_it_org_line1'] ?? '')."\n".($data['page20_it_org_line2'] ?? '')."\n".($data['page20_it_org_line3'] ?? '')),
            ['name' => self::FONT, 'size' => 9],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 80]
        );
        $section->addText(
            'কর্মসূচীর নাম: '.($data['page20_it_program'] ?? '').'    শাখার নাম: '.($data['page20_it_branch'] ?? ''),
            ['name' => self::FONT, 'size' => 8],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 80]
        );
        $section->addText($data['page20_it_instruction'] ?? 'প্রযোজ্য ক্ষেত্রে টিক চিহ্ন দিন', $this->fontBold, ['alignment' => Jc::CENTER, 'spaceAfter' => 80]);

        $checklist = $section->addTable($this->gridTable);
        $checklist->addRow();
        $checklist->addCell(800, ['vMerge' => 'restart'])->addText('ক্রমিক', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
        $checklist->addCell(2200, ['vMerge' => 'restart'])->addText('বিবরণ', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
        $checklist->addCell(1800, ['gridSpan' => 3])->addText('Compliance', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
        $checklist->addCell(1200, ['vMerge' => 'restart'])->addText('Action Owner', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
        $checklist->addCell(1500, ['vMerge' => 'restart'])->addText('Management Comments', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
        $checklist->addCell(1500, ['vMerge' => 'restart'])->addText('Recommendation', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
        $checklist->addRow();
        $checklist->addCell(800, ['vMerge' => 'continue']);
        $checklist->addCell(2200, ['vMerge' => 'continue']);
        $checklist->addCell(600)->addText('Yes', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
        $checklist->addCell(600)->addText('No', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
        $checklist->addCell(600)->addText('N/A', ['name' => self::FONT, 'size' => 7, 'bold' => true], ['alignment' => Jc::CENTER]);
        $checklist->addCell(1200, ['vMerge' => 'continue']);
        $checklist->addCell(1500, ['vMerge' => 'continue']);
        $checklist->addCell(1500, ['vMerge' => 'continue']);

        foreach ($data['page20ItChecklistRows'] ?? [] as $row) {
            $compliance = (string) ($row['compliance'] ?? '');
            $checklist->addRow();
            $checklist->addCell(800)->addText((string) ($row['sl_no'] ?? ''), ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
            $checklist->addCell(2200)->addText((string) ($row['description'] ?? ''), ['name' => self::FONT, 'size' => 7]);
            $checklist->addCell(600)->addText($compliance === 'yes' ? '✓' : '', ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
            $checklist->addCell(600)->addText($compliance === 'no' ? '✓' : '', ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
            $checklist->addCell(600)->addText($compliance === 'na' ? '✓' : '', ['name' => self::FONT, 'size' => 7], ['alignment' => Jc::CENTER]);
            $checklist->addCell(1200)->addText((string) ($row['action_owner'] ?? ''), ['name' => self::FONT, 'size' => 7]);
            $checklist->addCell(1500)->addText((string) ($row['management_comments'] ?? ''), ['name' => self::FONT, 'size' => 7]);
            $checklist->addCell(1500)->addText((string) ($row['recommendation'] ?? ''), ['name' => self::FONT, 'size' => 7]);
        }
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
        $headers = [
            'Area of Observation',
            'Compliance Area',
            'Year of Reporting',
            'External Observation',
            'Compliance',
            'Internal Index No',
        ];
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
