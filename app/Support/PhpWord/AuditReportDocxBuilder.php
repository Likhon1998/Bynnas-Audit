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
