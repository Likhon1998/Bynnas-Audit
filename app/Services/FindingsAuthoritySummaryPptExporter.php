<?php

namespace App\Services;

use PhpOffice\PhpPresentation\DocumentLayout;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Shape\Chart\Series;
use PhpOffice\PhpPresentation\Shape\Chart\Type\Bar;
use PhpOffice\PhpPresentation\Shape\RichText;
use PhpOffice\PhpPresentation\Slide;
use PhpOffice\PhpPresentation\Slide\Animation;
use PhpOffice\PhpPresentation\Slide\Transition;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Border;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Fill;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Polished DSK leadership PowerPoint for Findings Summary.
 */
class FindingsAuthoritySummaryPptExporter
{
    private const NAVY = '0B1F36';

    private const NAVY_MID = '163A5F';

    private const ACCENT = '0EA5A4';

    private const GOLD = 'D4A017';

    private const EMERALD = '059669';

    private const ROSE = 'E11D48';

    private const SLATE = '1E293B';

    private const MUTED = '64748B';

    private const LINE = 'E2E8F0';

    private const ROW_ALT = 'F1F5F9';

    private const WHITE = 'FFFFFF';

    private const SOFT = 'F8FAFC';

    public function __construct(
        private AuditSummaryService $summary,
    ) {}

    public function download(int $month, int $year): StreamedResponse
    {
        $groups = $this->summary->getMonthUnderheadingSummary($month, $year);
        $periodLabel = date('F', mktime(0, 0, 0, $month, 1)).' '.$year;
        $stats = $this->aggregate($groups);

        $presentation = new PhpPresentation;
        $presentation->getLayout()->setDocumentLayout(DocumentLayout::LAYOUT_SCREEN_16X9);
        $presentation->getDocumentProperties()
            ->setCreator('Bynnas Audit')
            ->setCompany('DSK')
            ->setTitle('DSK Findings Summary — '.$periodLabel)
            ->setSubject('Internal Audit Findings Summary')
            ->setDescription('Leadership presentation for DSK monthly audit findings.');

        $presentation->removeSlideByIndex(0);

        $this->addCoverSlide($presentation, $periodLabel, $stats);
        $this->addSnapshotSlide($presentation, $periodLabel, $stats);
        $this->addInsightsSlide($presentation, $periodLabel, $stats);
        $this->addTopPercentageSlide($presentation, $stats['flat']);
        $this->addTopAmountSlide($presentation, $stats['flat']);
        $this->addChartSlide($presentation, $stats['flat'], $periodLabel);
        $this->addCategorySlide($presentation, $groups, $stats);
        $this->addDetailSlides($presentation, $groups, $periodLabel);
        $this->addClosingSlide($presentation, $periodLabel, $stats);

        $filename = sprintf('DSK-Findings-Summary-%04d-%02d.pptx', $year, $month);

        return response()->streamDownload(function () use ($presentation) {
            $tmp = tempnam(sys_get_temp_dir(), 'dsk-ppt-');
            $pptx = $tmp.'.pptx';
            @unlink($tmp);

            try {
                $writer = IOFactory::createWriter($presentation, 'PowerPoint2007');
                $writer->save($pptx);
                $handle = fopen($pptx, 'rb');
                if ($handle !== false) {
                    fpassthru($handle);
                    fclose($handle);
                }
            } finally {
                if (is_file($pptx)) {
                    @unlink($pptx);
                }
            }
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $groups
     * @return array{
     *   indicators:int,
     *   amount:float,
     *   samples:int,
     *   irregularities:int,
     *   branches:int,
     *   groups:int,
     *   overall_pct:float|null,
     *   flat:list<array<string,mixed>>,
     *   top_pct:?array<string,mixed>,
     *   top_amount:?array<string,mixed>
     * }
     */
    protected function aggregate(array $groups): array
    {
        $flat = [];
        $amount = 0.0;
        $samples = 0;
        $irre = 0;
        $branchKeys = [];

        foreach ($groups as $group) {
            foreach ($group['rows'] as $row) {
                $flat[] = array_merge($row, [
                    'category' => $group['category'],
                    'sub_category' => $group['sub_category'],
                ]);
                $amount += (float) $row['amount'];
                $samples += (int) $row['samples'];
                $irre += (int) $row['irregularities'];
                foreach (preg_split('/\s*,\s*/', (string) ($row['branches'] ?? '')) ?: [] as $branch) {
                    $branch = trim($branch);
                    if ($branch !== '' && $branch !== '—') {
                        $branchKeys[$branch] = true;
                    }
                }
            }
        }

        $byPct = $flat;
        usort($byPct, fn ($a, $b) => ($b['percentage'] ?? -1) <=> ($a['percentage'] ?? -1));
        $byAmt = $flat;
        usort($byAmt, fn ($a, $b) => ($b['amount'] ?? 0) <=> ($a['amount'] ?? 0));

        return [
            'indicators' => count($flat),
            'amount' => $amount,
            'samples' => $samples,
            'irregularities' => $irre,
            'branches' => count($branchKeys),
            'groups' => count($groups),
            'overall_pct' => $samples > 0 ? round(($irre / $samples) * 100, 2) : null,
            'flat' => $flat,
            'top_pct' => $byPct[0] ?? null,
            'top_amount' => $byAmt[0] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $stats
     */
    protected function addCoverSlide(PhpPresentation $presentation, string $periodLabel, array $stats): void
    {
        $slide = $this->newSlide($presentation, Transition::TRANSITION_FADE);
        $this->rect($slide, 0, 0, 960, 540, self::NAVY);
        $this->rect($slide, 0, 0, 18, 540, self::ACCENT);
        $this->rect($slide, 0, 500, 960, 40, self::NAVY_MID);

        $eyebrow = $this->text($slide, 'DSK  ·  INTERNAL AUDIT DIRECTORATE', 50, 90, 860, 28, [
            'size' => 13, 'bold' => true, 'color' => self::ACCENT,
        ]);
        $title = $this->text($slide, 'Monthly Findings Summary', 50, 140, 860, 70, [
            'size' => 38, 'bold' => true, 'color' => self::WHITE,
        ]);
        $period = $this->text($slide, $periodLabel, 50, 215, 860, 40, [
            'size' => 24, 'bold' => true, 'color' => self::GOLD,
        ]);
        $line = $this->text(
            $slide,
            sprintf(
                '%d indicators   |   BDT %s   |   %s irregularities   |   %d branches',
                $stats['indicators'],
                number_format($stats['amount'], 0),
                number_format($stats['irregularities']),
                $stats['branches']
            ),
            50,
            290,
            860,
            34,
            ['size' => 14, 'color' => 'CBD5E1']
        );
        $this->text($slide, 'Confidential leadership briefing  ·  Generated from Bynnas Audit', 50, 510, 700, 24, [
            'size' => 11, 'color' => '94A3B8',
        ]);
        $this->text($slide, 'Bynnas Audit', 760, 510, 160, 24, [
            'size' => 11, 'bold' => true, 'color' => self::WHITE, 'align' => Alignment::HORIZONTAL_RIGHT,
        ]);

        $this->animateShapes($slide, [$eyebrow, $title, $period, $line]);
    }

    /**
     * @param  array<string, mixed>  $stats
     */
    protected function addSnapshotSlide(PhpPresentation $presentation, string $periodLabel, array $stats): void
    {
        $slide = $this->newSlide($presentation, Transition::TRANSITION_PUSH_LEFT);
        $this->contentChrome($slide, '01  ·  Executive snapshot', $periodLabel);

        $cards = [
            ['Indicators', (string) $stats['indicators'], 'Findings with report evidence', self::NAVY_MID],
            ['Amount (BDT)', number_format($stats['amount'], 0), 'Total monetary exposure', self::EMERALD],
            ['Sample size', number_format($stats['samples']), 'Items reviewed in rating boxes', self::NAVY_MID],
            ['Irregularities', number_format($stats['irregularities']), 'Instances found', self::ROSE],
            ['Branches', (string) $stats['branches'], 'Shakhas with findings', self::SLATE],
            ['Irregularity %', $stats['overall_pct'] === null ? '—' : number_format($stats['overall_pct'], 2).'%', 'Irregularities ÷ Sample size', '0F766E'],
        ];

        $shapes = [];
        foreach ($cards as $i => $card) {
            $col = $i % 3;
            $row = intdiv($i, 3);
            $x = 36 + ($col * 305);
            $y = 105 + ($row * 185);
            $shapes = array_merge($shapes, $this->metricCard($slide, $x, $y, 290, 165, $card[0], $card[1], $card[2], $card[3]));
        }
        $this->animateShapes($slide, $shapes);
    }

    /**
     * @param  array<string, mixed>  $stats
     */
    protected function addInsightsSlide(PhpPresentation $presentation, string $periodLabel, array $stats): void
    {
        $slide = $this->newSlide($presentation, Transition::TRANSITION_FADE);
        $this->contentChrome($slide, '02  ·  Key talking points', $periodLabel);

        $points = [];
        $points[] = sprintf(
            'Month coverage: %d indicators across %d branches, with overall irregularity rate of %s.',
            $stats['indicators'],
            $stats['branches'],
            $stats['overall_pct'] === null ? 'n/a' : number_format($stats['overall_pct'], 2).'%'
        );
        $points[] = sprintf('Total amount under review: BDT %s.', number_format($stats['amount'], 2));

        if (is_array($stats['top_pct'] ?? null)) {
            $top = $stats['top_pct'];
            $points[] = sprintf(
                'Highest irregularity %%: %s (%s) at %s — %s.',
                (string) $top['code'],
                $this->short((string) $top['title'], 70),
                $top['percentage'] === null ? '—' : number_format((float) $top['percentage'], 2).'%',
                (string) ($top['branches'] ?: 'branch n/a')
            );
        }
        if (is_array($stats['top_amount'] ?? null)) {
            $top = $stats['top_amount'];
            $points[] = sprintf(
                'Largest amount: BDT %s on %s (%s) — %s.',
                number_format((float) $top['amount'], 2),
                (string) $top['code'],
                $this->short((string) $top['title'], 60),
                (string) ($top['branches'] ?: 'branch n/a')
            );
        }
        $points[] = 'Recommendation: prioritize high irregularity % items first, then largest amount exposures for recovery follow-up.';

        $shapes = [];
        $y = 108;
        foreach ($points as $i => $point) {
            $this->rect($slide, 36, $y, 888, 68, $i % 2 === 0 ? self::SOFT : self::WHITE);
            $this->rect($slide, 36, $y, 8, 68, $i === 0 ? self::ACCENT : ($i === count($points) - 1 ? self::GOLD : self::NAVY_MID));
            $badge = $this->text($slide, str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT), 56, $y + 18, 50, 32, [
                'size' => 16, 'bold' => true, 'color' => self::ACCENT,
            ]);
            $body = $this->text($slide, $point, 110, $y + 12, 790, 46, [
                'size' => 13, 'color' => self::SLATE,
            ]);
            $shapes[] = $badge;
            $shapes[] = $body;
            $y += 78;
        }
        $this->animateShapes($slide, $shapes);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    protected function addTopPercentageSlide(PhpPresentation $presentation, array $rows): void
    {
        $slide = $this->newSlide($presentation, Transition::TRANSITION_PUSH_LEFT);
        $this->contentChrome($slide, '03  ·  Priority watchlist', 'Highest irregularity %');

        $sorted = $rows;
        usort($sorted, fn ($a, $b) => ($b['percentage'] ?? -1) <=> ($a['percentage'] ?? -1));
        $top = array_slice($sorted, 0, 5);
        $this->rankedCards($slide, $top, 'percentage');
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    protected function addTopAmountSlide(PhpPresentation $presentation, array $rows): void
    {
        $slide = $this->newSlide($presentation, Transition::TRANSITION_PUSH_LEFT);
        $this->contentChrome($slide, '04  ·  Financial exposure', 'Highest amount');

        $sorted = $rows;
        usort($sorted, fn ($a, $b) => ($b['amount'] ?? 0) <=> ($a['amount'] ?? 0));
        $top = array_slice($sorted, 0, 5);
        $this->rankedCards($slide, $top, 'amount');
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    protected function addChartSlide(PhpPresentation $presentation, array $rows, string $periodLabel): void
    {
        $slide = $this->newSlide($presentation, Transition::TRANSITION_FADE);
        $this->contentChrome($slide, '05  ·  Visual comparison', $periodLabel);

        $sorted = $rows;
        usort($sorted, fn ($a, $b) => ($b['percentage'] ?? -1) <=> ($a['percentage'] ?? -1));
        $top = array_slice($sorted, 0, 6);

        if ($top === []) {
            $this->text($slide, 'No findings available for charting.', 40, 220, 880, 40, [
                'size' => 16, 'color' => self::MUTED, 'align' => Alignment::HORIZONTAL_CENTER,
            ]);

            return;
        }

        $values = [];
        foreach ($top as $row) {
            $label = (string) $row['code'];
            $values[$label] = (float) ($row['percentage'] ?? 0);
        }

        $series = new Series('Irregularity %', $values);
        $series->setShowValue(true);
        $series->getFill()->setFillType(Fill::FILL_SOLID)->setStartColor(new Color('FF'.self::ACCENT));
        $series->getFont()->setSize(10)->setColor(new Color('FF'.self::SLATE));

        $bar = new Bar;
        $bar->addSeries($series);

        $chart = $slide->createChartShape();
        $chart->setResizeProportional(false)
            ->setHeight(380)
            ->setWidth(900)
            ->setOffsetX(30)
            ->setOffsetY(100);
        $chart->getTitle()->setVisible(true)->setText('Top indicators by irregularity %');
        $chart->getTitle()->getFont()->setBold(true)->setSize(14)->setColor(new Color('FF'.self::NAVY));
        $chart->getLegend()->setVisible(false);
        $chart->getPlotArea()->setType($bar);
        $chart->getPlotArea()->getAxisX()->getFont()->setSize(10);
        $chart->getPlotArea()->getAxisY()->getFont()->setSize(10);

        $anim = new Animation;
        $anim->addShape($chart);
        $slide->addAnimation($anim);
    }

    /**
     * @param  list<array<string, mixed>>  $groups
     * @param  array<string, mixed>  $stats
     */
    protected function addCategorySlide(PhpPresentation $presentation, array $groups, array $stats): void
    {
        $slide = $this->newSlide($presentation, Transition::TRANSITION_PUSH_LEFT);
        $this->contentChrome($slide, '06  ·  Category coverage', 'Underheading breakdown');

        if ($groups === []) {
            $this->text($slide, 'No category data for this period.', 40, 220, 880, 40, [
                'size' => 16, 'color' => self::MUTED, 'align' => Alignment::HORIZONTAL_CENTER,
            ]);

            return;
        }

        $table = $slide->createTableShape(5);
        $table->setWidth(900)->setOffsetX(30)->setOffsetY(105);
        $header = $table->createRow();
        $header->setHeight(36);
        $this->fillRowCells($header, ['Heading', 'Sub-heading', 'Indicators', 'Amount (BDT)', 'Sample size'], true, [220, 260, 100, 160, 160]);

        $totalSamples = 0;
        foreach ($groups as $i => $group) {
            $row = $table->createRow();
            $row->setHeight(34);
            $amount = array_sum(array_map(fn ($r) => (float) $r['amount'], $group['rows']));
            $samples = array_sum(array_map(fn ($r) => (int) $r['samples'], $group['rows']));
            $totalSamples += $samples;
            $this->fillRowCells($row, [
                $this->short((string) $group['category'], 36),
                $this->short((string) $group['sub_category'], 40),
                (string) count($group['rows']),
                number_format($amount, 2),
                number_format($samples),
            ], false, [220, 260, 100, 160, 160], $i % 2 === 1);
        }

        $this->text(
            $slide,
            sprintf(
                '%d underheadings  ·  %d indicators  ·  sample size %s  ·  amount BDT %s',
                $stats['groups'],
                $stats['indicators'],
                number_format($totalSamples),
                number_format($stats['amount'], 2)
            ),
            30,
            505,
            900,
            24,
            ['size' => 11, 'color' => self::MUTED]
        );

        $anim = new Animation;
        $anim->addShape($table);
        $slide->addAnimation($anim);
    }

    /**
     * @param  list<array<string, mixed>>  $groups
     */
    protected function addDetailSlides(PhpPresentation $presentation, array $groups, string $periodLabel): void
    {
        $flat = [];
        foreach ($groups as $group) {
            foreach ($group['rows'] as $row) {
                $flat[] = array_merge($row, [
                    'category' => $group['category'],
                    'sub_category' => $group['sub_category'],
                ]);
            }
        }

        if ($flat === []) {
            $slide = $this->newSlide($presentation, Transition::TRANSITION_FADE);
            $this->contentChrome($slide, '07  ·  Full findings annex', $periodLabel);
            $this->text($slide, 'No report findings to present for this month.', 40, 220, 880, 40, [
                'size' => 16, 'color' => self::MUTED, 'align' => Alignment::HORIZONTAL_CENTER,
            ]);

            return;
        }

        $chunks = array_chunk($flat, 4);
        $total = count($chunks);
        foreach ($chunks as $index => $chunk) {
            $slide = $this->newSlide($presentation, Transition::TRANSITION_PUSH_LEFT);
            $this->contentChrome(
                $slide,
                sprintf('07  ·  Full findings annex (%d/%d)', $index + 1, $total),
                $periodLabel
            );
            $this->detailCards($slide, $chunk, $index * 4);
        }
    }

    /**
     * @param  array<string, mixed>  $stats
     */
    protected function addClosingSlide(PhpPresentation $presentation, string $periodLabel, array $stats): void
    {
        $slide = $this->newSlide($presentation, Transition::TRANSITION_FADE);
        $this->rect($slide, 0, 0, 960, 540, self::NAVY);
        $this->rect($slide, 0, 0, 18, 540, self::GOLD);

        $a = $this->text($slide, 'Thank you', 50, 150, 860, 60, [
            'size' => 40, 'bold' => true, 'color' => self::WHITE,
        ]);
        $b = $this->text($slide, 'DSK Internal Audit  ·  Findings Summary  ·  '.$periodLabel, 50, 230, 860, 36, [
            'size' => 16, 'color' => self::ACCENT,
        ]);
        $c = $this->text(
            $slide,
            sprintf(
                '%d indicators  ·  BDT %s  ·  %s irregularities  ·  %d branches',
                $stats['indicators'],
                number_format($stats['amount'], 0),
                number_format($stats['irregularities']),
                $stats['branches']
            ),
            50,
            290,
            860,
            30,
            ['size' => 13, 'color' => 'CBD5E1']
        );
        $d = $this->text($slide, 'Questions & discussion welcome', 50, 370, 860, 32, [
            'size' => 18, 'bold' => true, 'color' => self::GOLD,
        ]);
        $this->text($slide, 'Prepared with Bynnas Audit', 50, 500, 860, 24, [
            'size' => 11, 'color' => '94A3B8',
        ]);
        $this->animateShapes($slide, [$a, $b, $c, $d]);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    protected function rankedCards(Slide $slide, array $rows, string $mode): void
    {
        if ($rows === []) {
            $this->text($slide, 'No findings available.', 40, 220, 880, 40, [
                'size' => 16, 'color' => self::MUTED, 'align' => Alignment::HORIZONTAL_CENTER,
            ]);

            return;
        }

        $shapes = [];
        $y = 100;
        foreach ($rows as $i => $row) {
            $rankColor = match ($i) {
                0 => self::ROSE,
                1 => 'EA580C',
                2 => self::GOLD,
                default => self::NAVY_MID,
            };
            $highlight = $mode === 'percentage'
                ? ($row['percentage'] === null ? '—' : number_format((float) $row['percentage'], 2).'%')
                : 'BDT '.number_format((float) $row['amount'], 0);

            $this->rect($slide, 30, $y, 900, 74, self::WHITE);
            $this->rect($slide, 30, $y, 6, 74, $rankColor);

            $shapes[] = $this->text($slide, '#'.($i + 1), 48, $y + 20, 48, 34, [
                'size' => 18, 'bold' => true, 'color' => $rankColor,
            ]);
            $shapes[] = $this->text($slide, (string) $row['code'], 100, $y + 8, 110, 22, [
                'size' => 11, 'bold' => true, 'color' => self::MUTED,
            ]);
            $shapes[] = $this->text($slide, $this->short((string) $row['title'], 78), 100, $y + 28, 520, 36, [
                'size' => 13, 'bold' => true, 'color' => self::SLATE,
            ]);
            $shapes[] = $this->text($slide, $highlight, 640, $y + 10, 170, 28, [
                'size' => 16, 'bold' => true, 'color' => $rankColor, 'align' => Alignment::HORIZONTAL_RIGHT,
            ]);
            $meta = sprintf(
                'Amount %s  ·  Sample %s  ·  Irreg %s  ·  Branches %s · %s',
                number_format((float) $row['amount'], 0),
                number_format((int) $row['samples']),
                number_format((int) $row['irregularities']),
                number_format((int) ($row['branch_count'] ?? 0)),
                $this->short((string) ($row['branches'] ?: '—'), 28)
            );
            $shapes[] = $this->text($slide, $meta, 640, $y + 40, 270, 28, [
                'size' => 10, 'color' => self::MUTED, 'align' => Alignment::HORIZONTAL_RIGHT,
            ]);
            $y += 80;
        }
        $this->animateShapes($slide, $shapes);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    protected function detailCards(Slide $slide, array $rows, int $startIndex): void
    {
        $shapes = [];
        foreach ($rows as $i => $row) {
            $col = $i % 2;
            $rowIdx = intdiv($i, 2);
            $x = 24 + ($col * 468);
            $y = 98 + ($rowIdx * 200);

            $this->rect($slide, $x, $y, 448, 185, self::WHITE);
            $this->rect($slide, $x, $y, 448, 8, self::ACCENT);
            $this->rect($slide, $x, $y + 8, 448, 1, self::LINE);

            $shapes[] = $this->text($slide, sprintf('%02d  ·  %s', $startIndex + $i + 1, (string) $row['code']), $x + 14, $y + 18, 420, 22, [
                'size' => 11, 'bold' => true, 'color' => self::ACCENT,
            ]);
            $shapes[] = $this->text($slide, $this->short((string) $row['title'], 95), $x + 14, $y + 42, 420, 42, [
                'size' => 12, 'bold' => true, 'color' => self::SLATE,
            ]);
            $shapes[] = $this->text(
                $slide,
                $this->short((string) ($row['category'] ?? ''), 40).'  ·  '.$this->short((string) ($row['sub_category'] ?? ''), 40),
                $x + 14,
                $y + 88,
                420,
                20,
                ['size' => 10, 'color' => self::MUTED]
            );

            $grid = [
                ['Amount', number_format((float) $row['amount'], 2)],
                ['Sample size', number_format((int) $row['samples'])],
                ['Irregularities', number_format((int) $row['irregularities'])],
                ['Irregularity %', $row['percentage'] === null ? '—' : number_format((float) $row['percentage'], 2).'%'],
                ['Total branch', number_format((int) ($row['branch_count'] ?? 0))],
                ['Branch name', $this->short((string) ($row['branches'] ?: '—'), 28)],
            ];
            $gx = $x + 14;
            $gy = $y + 116;
            foreach ($grid as $gi => $cell) {
                $cx = $gx + (($gi % 3) * 140);
                $cy = $gy + (intdiv($gi, 3) * 30);
                $shapes[] = $this->text($slide, $cell[0].': '.$cell[1], $cx, $cy, 135, 26, [
                    'size' => 10, 'color' => self::SLATE,
                ]);
            }
        }
        $this->animateShapes($slide, $shapes);
    }

    /**
     * @return list<RichText>
     */
    protected function metricCard(
        Slide $slide,
        int $x,
        int $y,
        int $w,
        int $h,
        string $label,
        string $value,
        string $hint,
        string $accent
    ): array {
        $this->rect($slide, $x, $y, $w, $h, self::WHITE);
        $this->rect($slide, $x, $y, 8, $h, $accent);
        $a = $this->text($slide, $label, $x + 24, $y + 24, $w - 40, 26, [
            'size' => 12, 'bold' => true, 'color' => self::MUTED,
        ]);
        $b = $this->text($slide, $value, $x + 24, $y + 58, $w - 40, 48, [
            'size' => 26, 'bold' => true, 'color' => self::SLATE,
        ]);
        $c = $this->text($slide, $hint, $x + 24, $y + 120, $w - 40, 28, [
            'size' => 11, 'color' => self::MUTED,
        ]);

        return [$a, $b, $c];
    }

    protected function contentChrome(Slide $slide, string $title, string $subtitle): void
    {
        $this->rect($slide, 0, 0, 960, 540, self::SOFT);
        $this->rect($slide, 0, 0, 960, 78, self::NAVY);
        $this->rect($slide, 0, 78, 960, 4, self::ACCENT);
        $this->text($slide, 'DSK Internal Audit', 28, 10, 400, 20, [
            'size' => 10, 'bold' => true, 'color' => self::ACCENT,
        ]);
        $this->text($slide, $title, 28, 30, 700, 36, [
            'size' => 18, 'bold' => true, 'color' => self::WHITE,
        ]);
        $this->text($slide, $subtitle, 740, 34, 190, 28, [
            'size' => 11, 'color' => '94A3B8', 'align' => Alignment::HORIZONTAL_RIGHT,
        ]);
        $this->rect($slide, 0, 520, 960, 20, self::NAVY);
        $this->text($slide, 'Confidential  ·  Bynnas Audit Findings Summary', 28, 521, 900, 18, [
            'size' => 9, 'color' => '94A3B8',
        ]);
    }

    protected function newSlide(PhpPresentation $presentation, string $transitionType): Slide
    {
        $slide = $presentation->createSlide();
        $transition = (new Transition)
            ->setTransitionType($transitionType)
            ->setSpeed(Transition::SPEED_MEDIUM)
            ->setManualTrigger(true);
        $slide->setTransition($transition);

        return $slide;
    }

    /**
     * @param  list<\PhpOffice\PhpPresentation\AbstractShape|RichText>  $shapes
     */
    protected function animateShapes(Slide $slide, array $shapes): void
    {
        foreach ($shapes as $shape) {
            if ($shape === null) {
                continue;
            }
            $animation = new Animation;
            $animation->addShape($shape);
            $slide->addAnimation($animation);
        }
    }

    protected function rect(Slide $slide, int $x, int $y, int $w, int $h, string $hex): RichText
    {
        $shape = $slide->createRichTextShape()
            ->setHeight($h)
            ->setWidth($w)
            ->setOffsetX($x)
            ->setOffsetY($y);
        $shape->getFill()->setFillType(Fill::FILL_SOLID)->setStartColor(new Color('FF'.$hex));
        $shape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        return $shape;
    }

    /**
     * @param  array{size?:int,bold?:bool,color?:string,align?:string}  $opts
     */
    protected function text(Slide $slide, string $text, int $x, int $y, int $w, int $h, array $opts = []): RichText
    {
        $shape = $slide->createRichTextShape()
            ->setHeight($h)
            ->setWidth($w)
            ->setOffsetX($x)
            ->setOffsetY($y);

        $align = $opts['align'] ?? Alignment::HORIZONTAL_LEFT;
        $shape->getActiveParagraph()->getAlignment()->setHorizontal($align)->setVertical(Alignment::VERTICAL_CENTER);

        $run = $shape->createTextRun($text);
        $run->getFont()
            ->setBold((bool) ($opts['bold'] ?? false))
            ->setSize((int) ($opts['size'] ?? 14))
            ->setColor(new Color('FF'.($opts['color'] ?? self::NAVY)));

        return $shape;
    }

    /**
     * @param  list<string>  $values
     * @param  list<int>  $widths
     */
    protected function fillRowCells($row, array $values, bool $isHeader, array $widths, bool $alt = false): void
    {
        foreach ($values as $i => $value) {
            $cell = $row->nextCell();
            $cell->setWidth($widths[$i] ?? 100);
            $fillColor = $isHeader ? self::NAVY : ($alt ? self::ROW_ALT : self::WHITE);
            $cell->getFill()->setFillType(Fill::FILL_SOLID)->setStartColor(new Color('FF'.$fillColor));

            foreach ([
                $cell->getBorders()->getTop(),
                $cell->getBorders()->getBottom(),
                $cell->getBorders()->getLeft(),
                $cell->getBorders()->getRight(),
            ] as $border) {
                $border->setLineStyle(Border::LINE_SINGLE)
                    ->setColor(new Color('FF'.self::LINE))
                    ->setLineWidth(1);
            }

            $text = $cell->createTextRun((string) $value);
            $text->getFont()
                ->setBold($isHeader || $i >= 2)
                ->setSize($isHeader ? 11 : 10)
                ->setColor(new Color('FF'.($isHeader ? self::WHITE : self::SLATE)));

            $align = $cell->getActiveParagraph()->getAlignment();
            $align->setVertical(Alignment::VERTICAL_CENTER);
            $align->setHorizontal($i >= 2 ? Alignment::HORIZONTAL_CENTER : Alignment::HORIZONTAL_LEFT);
        }
    }

    protected function short(string $text, int $max): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
        if (mb_strlen($text) <= $max) {
            return $text;
        }

        return mb_substr($text, 0, max(1, $max - 1)).'…';
    }
}
