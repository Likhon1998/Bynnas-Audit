<?php

namespace Database\Seeders;

use App\Models\AuditChecklistSubmission;
use App\Models\AuditIndicator;
use App\Models\AuditReport;
use App\Models\AuditReportChecklistFile;
use App\Models\Shakha;
use App\Models\User;
use App\Support\AuditTableHeaders;
use App\Support\BanglaNumerals;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Wipe all audit reports + findings, then create 5 fully documented September 2026 reports
 * linked to existing Finding Matrix headings so Summary/Matrix populate automatically.
 */
class SeptemberFullReportsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->first()
            ?? User::query()->orderBy('id')->first();

        if (! $admin) {
            $this->command?->error('No user found — cannot seed reports.');

            return;
        }

        $shakhas = Shakha::query()->with('area')->where('status', 'active')->orderBy('id')->limit(5)->get();
        if ($shakhas->count() < 5) {
            $shakhas = Shakha::query()->with('area')->orderBy('id')->limit(5)->get();
        }
        if ($shakhas->count() < 5) {
            $this->command?->error('Need at least 5 shakhas. No existing report data was deleted.');

            return;
        }

        $catalog = AuditIndicator::query()
            ->where(function ($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            })
            ->whereNotNull('title')
            ->where('title', '!=', '')
            ->orderBy('category')
            ->orderBy('indicator_code')
            ->get(['id', 'title', 'indicator_code', 'category', 'sub_category', 'risk_rating']);

        if ($catalog->count() < 4) {
            $this->command?->error('Need at least 4 existing Finding Matrix headings. No report data was deleted.');

            return;
        }

        $indicatorMap = $catalog->keyBy('indicator_code');
        $blueprints = $this->reportBlueprints();

        // Drop findings whose codes are missing from the live catalog.
        foreach ($blueprints as &$blueprint) {
            $blueprint['findings'] = array_values(array_filter(
                $blueprint['findings'],
                fn (array $finding) => $indicatorMap->has((string) ($finding['indicator_code'] ?? ''))
            ));
        }
        unset($blueprint);

        $blueprints = array_values(array_filter(
            $blueprints,
            fn (array $blueprint) => count($blueprint['findings']) > 0
        ));

        if ($blueprints === []) {
            // Fallback: build from any 4 category-diverse live headings.
            $indicators = $catalog
                ->unique(fn (AuditIndicator $indicator) => trim((string) $indicator->category) ?: 'indicator-'.$indicator->id)
                ->take(4)
                ->values();
            if ($indicators->count() < 4) {
                $indicators = $indicators
                    ->merge($catalog->whereNotIn('id', $indicators->pluck('id')))
                    ->take(4)
                    ->values();
            }
            $indicatorMap = $indicators->keyBy('indicator_code');
            $blueprints = $this->logicalBlueprintsFromExistingHeadings($indicators);
        }

        $usedCodes = collect($blueprints)
            ->flatMap(fn (array $b) => collect($b['findings'])->pluck('indicator_code'))
            ->unique()
            ->values();

        $this->command?->info(
            'Using '.$usedCodes->count().' Finding Matrix headings across reports: '
            .$usedCodes->implode(', ')
        );

        $ratings = ['Major', 'Medium', 'Minor', 'Major', 'Medium'];
        $controlMap = [
            'Satisfactory' => 'Satisfactory (E)',
            'Minor' => 'Minor (D)',
            'Medium' => 'Medium (C)',
            'Major' => 'Major (B)',
            'Unsatisfactory' => 'Unsatisfactory (F)',
        ];

        $storedFiles = $this->reportStoragePaths();

        // All database destruction + reconstruction is atomic. Any incomplete Matrix sync
        // rolls the entire operation back, preserving the previous production dataset.
        DB::transaction(function () use (
            $admin,
            $shakhas,
            $blueprints,
            $indicatorMap,
            $ratings,
            $controlMap
        ): void {
            $this->wipeAllReports();

            foreach ($shakhas->values() as $i => $shakha) {
                $bp = $blueprints[$i];
                $control = $ratings[$i];
                $memo = 'DSK/IA/SEP-2026/'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT);

                $auditStart = '2026-09-0'.(1 + ($i % 5));
                $auditEnd = '2026-09-'.str_pad((string) (5 + ($i % 5)), 2, '0', STR_PAD_LEFT);
                $reportDate = '2026-09-'.str_pad((string) (12 + ($i % 10)), 2, '0', STR_PAD_LEFT);

                $pages = $this->buildPagesData(
                    $shakha,
                    $bp,
                    $indicatorMap,
                    $controlMap[$control],
                    $reportDate,
                    $i
                );

                $progress = AuditReport::computeProgress($pages, [
                    'memo_no' => $memo,
                    'auditor_name' => $admin->name,
                ]);

                $report = AuditReport::query()->create([
                    'shakha_id' => $shakha->id,
                    'user_id' => $admin->id,
                    'status' => AuditReport::STATUS_COMPLETED,
                    'report_month' => 9,
                    'report_year' => 2026,
                    'memo_no' => $memo,
                    'report_date' => $reportDate,
                    'control_rating' => $control,
                    'shakha_display_name' => $shakha->name.($shakha->code ? ' ('.$shakha->code.')' : ''),
                    'area_display_name' => $shakha->area?->name ?? '',
                    'audit_period_label' => 'সেপ্টেম্বর ২০২৬',
                    'audit_start_date' => $auditStart,
                    'audit_end_date' => $auditEnd,
                    'working_days' => 5 + ($i % 5),
                    'period_scope' => 'Full Branch Audit',
                    'draft_sent_date' => '2026-09-'.str_pad((string) (8 + ($i % 5)), 2, '0', STR_PAD_LEFT),
                    'comments_received_date' => '2026-09-'.str_pad((string) (10 + ($i % 5)), 2, '0', STR_PAD_LEFT),
                    'auditor_name' => $admin->name,
                    'auditor_designation' => 'Internal Audit Officer',
                    'pages_data' => $pages,
                    'current_tab' => 'page4',
                    'progress_pct' => max(100, $progress),
                    'last_saved_at' => now(),
                    'completed_at' => now(),
                ]);

                $synced = app(\App\Services\AuditSummaryService::class)->syncFromReport($report);
                $expected = count($bp['findings']);
                if ($synced !== $expected) {
                    throw new RuntimeException(
                        "{$memo}: expected {$expected} Matrix rows, synchronized {$synced}."
                    );
                }

                $this->command?->info("Created: {$memo} — {$shakha->name} (matrix synced: {$synced})");
            }
        });

        foreach ($storedFiles as $path) {
            Storage::disk('public')->delete($path);
        }

        $this->command?->info('Done: 5 full September 2026 reports (matrix + consolidated summary ready).');
    }

    protected function wipeAllReports(): void
    {
        if (Schema::hasTable('audit_report_checklist_format')) {
            DB::table('audit_report_checklist_format')->delete();
        }
        if (Schema::hasTable('audit_report_checklist_files')) {
            AuditReportChecklistFile::query()->delete();
        }
        if (Schema::hasTable('audit_report_checklist_items')) {
            DB::table('audit_report_checklist_items')->delete();
        }
        if (Schema::hasTable('audit_checklist_submissions') && Schema::hasColumn('audit_checklist_submissions', 'audit_report_id')) {
            AuditChecklistSubmission::query()->whereNotNull('audit_report_id')->delete();
        }

        $reportCount = AuditReport::query()->count();
        AuditReport::query()->delete();

        $findingCount = 0;
        if (Schema::hasTable('audit_findings')) {
            $findingCount = \App\Models\AuditFinding::query()->count();
            \App\Models\AuditFinding::query()->delete();
        }

        $this->command?->warn("Deleted {$reportCount} report(s) and {$findingCount} finding(s).");
    }

    /**
     * Capture report-owned uploads before deleting their database records.
     *
     * @return list<string>
     */
    protected function reportStoragePaths(): array
    {
        $paths = AuditReport::query()
            ->whereNotNull('logo_path')
            ->pluck('logo_path');

        if (Schema::hasTable('audit_report_checklist_files')) {
            $paths = $paths->merge(
                AuditReportChecklistFile::query()->whereNotNull('stored_path')->pluck('stored_path')
            );
        }

        return $paths
            ->map(fn ($path) => trim((string) $path))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Build five complete report structures around headings that already exist in the Matrix.
     * Every selected heading is reused across all five branches for useful consolidated summaries.
     *
     * @param  \Illuminate\Support\Collection<int, AuditIndicator>  $indicators
     * @return list<array{theme:string,findings:list<array<string,mixed>>}>
     */
    protected function logicalBlueprintsFromExistingHeadings($indicators): array
    {
        $ratings = ['Major (B)', 'Medium (C)', 'Minor (D)', 'Medium (C)'];
        $blueprints = [];

        for ($reportIndex = 0; $reportIndex < 5; $reportIndex++) {
            $findings = [];

            foreach ($indicators->values() as $findingIndex => $indicator) {
                $sectionNo = $findingIndex + 1;
                $sample = 24 + ($reportIndex * 3) + ($findingIndex * 2);
                $instances = 2 + (($reportIndex + $findingIndex) % 6);
                $population = $sample * (4 + ($findingIndex % 3));
                $amount = 12500 + ($reportIndex * 4750) + ($findingIndex * 8250);
                $category = trim((string) ($indicator->category ?: 'অভ্যন্তরীণ নিয়ন্ত্রণ'));
                $title = trim((string) $indicator->title);
                $sampleBn = BanglaNumerals::fromInt($sample);
                $instancesBn = BanglaNumerals::fromInt($instances);

                $findings[] = [
                    'section' => [
                        BanglaNumerals::fromInt($sectionNo).'.০',
                        BanglaNumerals::fromInt($sectionNo).'.০ '.$category,
                    ],
                    'serial' => BanglaNumerals::fromInt($sectionNo).'.১',
                    'indicator_code' => (string) $indicator->indicator_code,
                    'title' => 'শিরোনাম',
                    'body' => $title,
                    'amount' => BanglaNumerals::fromLatin(number_format($amount)),
                    'rating' => $ratings[$findingIndex % count($ratings)],
                    'criteria' => 'প্রতিষ্ঠানের অনুমোদিত নীতিমালা, কার্যপদ্ধতি ও সংশ্লিষ্ট নিয়ন্ত্রণ নির্দেশনা অনুযায়ী '
                        .$title.' সংক্রান্ত কার্যক্রম যথাযথভাবে সম্পন্ন ও প্রমাণসহ সংরক্ষণ করতে হবে।',
                    'observation' => "নিরীক্ষায় {$sampleBn}টি নমুনা যাচাই করে {$instancesBn}টি ক্ষেত্রে “{$title}” সংক্রান্ত ব্যত্যয় পাওয়া গেছে।",
                    'stats' => [(string) $population, (string) $sample, (string) $instances],
                    'risk' => 'নিয়ন্ত্রণের ব্যত্যয় অব্যাহত থাকলে আর্থিক ক্ষতি, ভুল প্রতিবেদন অথবা নীতিমালা পরিপালনে ঘাটতি সৃষ্টি হতে পারে।',
                    'root' => 'নিয়মিত তদারকি, নথি যাচাই ও দায়িত্বভিত্তিক পর্যালোচনা পর্যাপ্ত ছিল না।',
                    'reco' => 'সংশ্লিষ্ট নথি সংশোধন করে নিয়ন্ত্রণ চেকলিস্ট চালু এবং শাখা ব্যবস্থাপকের মাসিক পর্যালোচনা নিশ্চিত করা।',
                    'jobab' => 'শাখা ব্যবস্থাপক ব্যত্যয়গুলো যাচাই করে সংশোধন এবং পরবর্তী মাস থেকে নিয়মিত তদারকি নিশ্চিত করবেন।',
                    'status' => $reportIndex % 2 === 0 ? 'চলমান' : 'সমাধানের পথে',
                ];
            }

            $blueprints[] = [
                'theme' => 'শাখাভিত্তিক সমন্বিত নিরীক্ষা — প্রতিবেদন '.($reportIndex + 1),
                'findings' => $findings,
            ];
        }

        return $blueprints;
    }

    /**
     * @return list<array{theme:string,findings:list<array<string,mixed>>}>
     */
    protected function reportBlueprints(): array
    {
        $blueprints = [
            [
                'theme' => 'অর্থ, হিসাব ও প্রশাসন সংক্রান্ত',
                'findings' => [
                    [
                        'section' => ['১.০', '১.০ অর্থ, হিসাব ও প্রশাসন সংক্রান্ত'],
                        'serial' => '১.১',
                        'indicator_code' => '২০০০-১',
                        'title' => 'শিরোনাম',
                        'body' => 'দৈনিক আর্থিক চাহিদা রেজিস্টারে প্রদানকৃত চাহিদা অপেক্ষায় প্রকৃত খরচ কম হওয়া',
                        'amount' => '৪৫,২৫০',
                        'rating' => 'Major (B)',
                        'criteria' => 'দৈনিক আর্থিক চাহিদা রেজিস্টারে উল্লেখিত চাহিদার সাথে প্রকৃত খরচ মিল রেখে হিসাব রাখতে হবে।',
                        'observation' => 'নমুনা যাচাইয়ে দেখা যায়, চাহিদা রেজিস্টারে উল্লেখিত পরিমাণের চেয়ে প্রকৃত খরচ উল্লেখযোগ্যভাবে কম।',
                        'stats' => ['১২০', '২৫', '০৮'],
                        'risk' => 'অতিরিক্ত চাহিদা উত্তোলন ও তহবিল অপব্যবহারের আশঙ্কা।',
                        'root' => 'চাহিদা ও প্রকৃত খরচের দৈনিক মিল যাচাই না করা।',
                        'reco' => 'প্রতিদিন চাহিদা vs প্রকৃত খরচ মিলিয়ে রেজিস্টার হালনাগাদ করা।',
                        'jobab' => 'শাখা ব্যবস্থাপক চাহিদা রেজিস্টার নিয়মিত মিল যাচাই করবেন।',
                        'status' => 'চলমান',
                    ],
                    [
                        'section' => null,
                        'serial' => '১.২',
                        'indicator_code' => '২০০০-২',
                        'title' => 'শিরোনাম',
                        'body' => 'ঋণ অনুমোদন ব্যতীত দৈনিক আর্থিক চাহিদা রেজিস্টারে ঋণ বিতরণের চাহিদা রাখা যা ঋণ নীতিমালা বহি:র্ভুত',
                        'amount' => '১৮,৭৫০',
                        'rating' => 'Medium (C)',
                        'criteria' => 'ঋণ বিতরণের চাহিদা কেবল অনুমোদিত ঋণের বিপরীতে রাখা যাবে।',
                        'observation' => 'কয়েকটি ক্ষেত্রে অনুমোদন ছাড়াই ঋণ বিতরণের চাহিদা রেজিস্টারে এন্ট্রি পাওয়া গেছে।',
                        'stats' => ['৮৫', '২৫', '০৬'],
                        'risk' => 'ঋণ নীতিমালা লঙ্ঘন ও অননুমোদিত বিতরণের ঝুঁকি।',
                        'root' => 'চাহিদা এন্ট্রির পূর্বে অনুমোদন যাচাই না করা।',
                        'reco' => 'অনুমোদিত ঋণ তালিকা ছাড়া চাহিদা এন্ট্রি নিষিদ্ধ করা।',
                        'jobab' => 'এবিএম অনুমোদন যাচাই প্রক্রিয়া চালু করবেন।',
                        'status' => 'সমাধানের পথে',
                    ],
                    [
                        'section' => ['২.০', '২.০ নগদ উত্তোলন ও বিতরণ'],
                        'serial' => '২.১',
                        'indicator_code' => '২০০০-৩',
                        'title' => 'শিরোনাম',
                        'body' => 'দৈনিক আর্থিক চাহিদা রেজিস্টারে খরচের চাহিদা না রেখে ব্যাংক থেকে টাকা উত্তোলন করে ঋণ বিতরন ও খরচ করা',
                        'amount' => '২,১৫,০০০',
                        'rating' => 'Major (B)',
                        'criteria' => 'ব্যাংক উত্তোলনের পূর্বে খরচের চাহিদা রেজিস্টারে এন্ট্রি বাধ্যতামূলক।',
                        'observation' => 'চাহিদা এন্ট্রি ছাড়াই ব্যাংক উত্তোলন করে ঋণ বিতরণ ও খরচ করা হয়েছে।',
                        'stats' => ['৩২০', '৪০', '০৫'],
                        'risk' => 'নগদ নিয়ন্ত্রণ দুর্বল ও অনিয়মের সুযোগ।',
                        'root' => 'উত্তোলন পূর্ব চেকলিস্ট অনুসরণ না করা।',
                        'reco' => 'চাহিদা এন্ট্রি ছাড়া ব্যাংক উত্তোলন নিষিদ্ধ করা।',
                        'jobab' => 'বিএম উত্তোলন পূর্ব যাচাই নিশ্চিত করবেন।',
                        'status' => 'চলমান',
                    ],
                ],
            ],
            [
                'theme' => 'স্টক ও কর্মসূচী (ঋণ) সংক্রান্ত',
                'findings' => [
                    [
                        'section' => ['১.০', '১.০ স্টক ও মজুদ নিয়ন্ত্রণ'],
                        'serial' => '১.১',
                        'indicator_code' => '২০০০-৪',
                        'title' => 'শিরোনাম',
                        'body' => 'স্টক/মজুদ রেজিষ্টারে স্টেশনারী/অন্যান্য ঋণ কার্যক্রমের প্রিন্টিং সামগ্রী এন্ট্রি না দেওয়া এবং স্টক রেজিস্টার আপডেট না থাকা।',
                        'amount' => '৩,২০০',
                        'rating' => 'Minor (D)',
                        'criteria' => 'স্টেশনারি ও প্রিন্টিং সামগ্রীর প্রাপ্তি, ব্যবহার ও অবশিষ্ট স্টক নিয়মিত রেজিস্টারে হালনাগাদ রাখতে হবে।',
                        'observation' => 'ভৌত যাচাইয়ে স্টক রেজিস্টার হালনাগাদ নয় এবং এন্ট্রি অনুপস্থিত।',
                        'stats' => ['১', '১', '১'],
                        'risk' => 'সম্পদ অপচয়/অসঙ্গতির আশঙ্কা।',
                        'root' => 'দৈনিক স্টক আপডেট না করা।',
                        'reco' => 'সাপ্তাহিক স্টক ভেরিফিকেশন চালু করা।',
                        'jobab' => 'অফিস সহকারী রেজিস্টার হালনাগাদ করবেন।',
                        'status' => 'চলমান',
                    ],
                    [
                        'section' => ['২.০', '২.০ কর্মসূচী সংক্রান্ত — ঋণ'],
                        'serial' => '২.১',
                        'indicator_code' => '১০০০-১',
                        'title' => 'শিরোনাম',
                        'body' => 'পাসবইতে এন্ট্রি না দিয়ে সদস্যর কাছ থেকে কিস্তি আদায় করা (সদস্যর নাম,আইডি, সমিতি নং, আদায় তারিখ)',
                        'amount' => '১২,৬০০',
                        'rating' => 'Major (B)',
                        'criteria' => 'প্রতিটি কিস্তি আদায়ের সময় সদস্যের পাসবইয়ে তারিখ ও পরিমাণ লিখে স্বাক্ষর করতে হবে।',
                        'observation' => 'কয়েকটি ক্ষেত্রে পাসবইয়ে এন্ট্রি ছাড়াই কিস্তি আদায় করা হয়েছে।',
                        'stats' => ['৬', '৬', '১'],
                        'risk' => 'আর্থিক অনিয়ম ও সদস্য অসন্তোষের সম্ভাবনা।',
                        'root' => 'পাসবই এন্ট্রি নিয়ন্ত্রণ দুর্বল।',
                        'reco' => 'আদায়ের সাথে সাথে পাসবই এন্ট্রি বাধ্যতামূলক করা।',
                        'jobab' => 'মাঠকর্মীরা তালিকাভুক্ত সদস্যদের পাসবই হালনাগাদ করবে।',
                        'status' => 'চলমান',
                    ],
                    [
                        'section' => null,
                        'serial' => '২.২',
                        'indicator_code' => '১০০০-২',
                        'title' => 'শিরোনাম',
                        'body' => 'কর্মী কর্তৃক পাশ বইয়ের ব্যালেন্স কাটাকাটি করে আত্নসাৎ করা',
                        'amount' => '৯২,৪০০',
                        'rating' => 'Critical (A)',
                        'criteria' => 'পাসবইয়ের ব্যালেন্স কাটাকাটি নিষিদ্ধ; যেকোনো সংশোধন অনুমোদিত পদ্ধতিতে হতে হবে।',
                        'observation' => 'পাসবইয়ে কাটাকাটি ও ব্যালেন্স অসঙ্গতি পাওয়া গেছে।',
                        'stats' => ['৩০', '৩০', '০৭'],
                        'risk' => 'আত্মসাৎ ও প্রতিষ্ঠানের আর্থিক ক্ষতির ঝুঁকি।',
                        'root' => 'পাসবই ক্রসচেক ও তদারকি দুর্বল।',
                        'reco' => 'নিয়মিত পাসবই ক্রসচেক ও দায়ী কর্মীর বিরুদ্ধে ব্যবস্থা।',
                        'jobab' => 'বিএম তদন্ত করে নিরীক্ষা দলে জবাব দিবেন।',
                        'status' => 'চলমান',
                    ],
                ],
            ],
            [
                'theme' => 'কর্মসূচী ও স্থায়ী সম্পদ',
                'findings' => [
                    [
                        'section' => ['১.০', '১.০ কর্মসূচী সংক্রান্ত — ঋণ'],
                        'serial' => '১.১',
                        'indicator_code' => '১০০০-৩',
                        'title' => 'শিরোনাম',
                        'body' => 'যাতায়াতে মোটরসাইকেল ব্যবহার করে বিল নেওয়া হচ্ছে সিএনজি+রিক্সা',
                        'amount' => '৮,৫০০',
                        'rating' => 'Medium (C)',
                        'criteria' => 'যাতায়াত বিল প্রকৃত ব্যবহৃত যানবাহন অনুযায়ী দাখিল করতে হবে।',
                        'observation' => 'মোটরসাইকেল ব্যবহারেও সিএনজি/রিক্সা বিল দাখিলের প্রমাণ পাওয়া গেছে।',
                        'stats' => ['০৩', '০৩', '০২'],
                        'risk' => 'খরচের অতিরিক্ত দাবি ও নীতি লঙ্ঘন।',
                        'root' => 'যাতায়াত বিল যাচাই দুর্বল।',
                        'reco' => 'যানবাহন লগ ও বিল মিলিয়ে অনুমোদন দেওয়া।',
                        'jobab' => 'বিএম বিল যাচাই কঠোর করবেন।',
                        'status' => 'সমাধানের পথে',
                    ],
                    [
                        'section' => null,
                        'serial' => '১.২',
                        'indicator_code' => '১০০০-৪',
                        'title' => 'শিরোনাম',
                        'body' => 'সহকারী ব্যবস্থাপক কর্তৃক বাস্তবে পাস বই ক্রসচেক না করেই রির্পোট করা হয়েছে',
                        'amount' => '৬,২০০',
                        'rating' => 'Medium (C)',
                        'criteria' => 'সহকারী ব্যবস্থাপককে বাস্তবে পাসবই ক্রসচেক করে প্রতিবেদন দিতে হবে।',
                        'observation' => 'ক্রসচেক ছাড়াই পাসবই সংক্রান্ত রিপোর্ট দাখিল করা হয়েছে।',
                        'stats' => ['৪৫', '১২', '০৩'],
                        'risk' => 'ভুল/অসম্পূর্ণ তথ্যের ভিত্তিতে সিদ্ধান্ত।',
                        'root' => 'ক্রসচেক পদ্ধতি অনুসরণ না করা।',
                        'reco' => 'ক্রসচেক সই ছাড়া রিপোর্ট গ্রহণ না করা।',
                        'jobab' => 'এবিএম ক্রসচেক চেকলিস্ট চালু করবেন।',
                        'status' => 'সমাধানের পথে',
                    ],
                    [
                        'section' => ['২.০', '২.০ স্থায়ী সম্পদ সংক্রান্ত'],
                        'serial' => '২.১',
                        'indicator_code' => '৩০০০-১',
                        'title' => 'শিরোনাম',
                        'body' => 'স্থায়ী সম্পদ ক্রয়ের কোটেশন সংগ্রহ না করা',
                        'amount' => '৪১,৫০০',
                        'rating' => 'Major (B)',
                        'criteria' => 'নির্ধারিত সীমার উপরে ক্রয়ে ন্যূনতম কোটেশন সংগ্রহ করতে হবে।',
                        'observation' => 'কয়েকটি স্থায়ী সম্পদ ক্রয়ে কোটেশন সংগ্রহ করা হয়নি।',
                        'stats' => ['১২', '৪', '২'],
                        'risk' => 'স্বচ্ছতা ও মূল্য সুবিধা না পাওয়ার আশঙ্কা।',
                        'root' => 'ক্রয় নীতিমালা অনুসরণে ঘাটতি।',
                        'reco' => 'ক্রয় ফাইলে কোটেশন তুলনা সারণী বাধ্যতামূলক করা।',
                        'jobab' => 'বিএম ভবিষ্যতে নীতিমালা মেনে ক্রয় করবেন।',
                        'status' => 'চলমান',
                    ],
                ],
            ],
            [
                'theme' => 'স্থায়ী সম্পদ ও অর্থ-হিসাব',
                'findings' => [
                    [
                        'section' => ['১.০', '১.০ স্থায়ী সম্পদ সংক্রান্ত'],
                        'serial' => '১.১',
                        'indicator_code' => '৩০০০-২',
                        'title' => 'শিরোনাম',
                        'body' => 'স্থায়ী সম্পদ ক্রয়ের ক্রয় কমিটি ব্যতীত ক্রয় করা',
                        'amount' => '৬৫,০০০',
                        'rating' => 'Major (B)',
                        'criteria' => 'নির্ধারিত সীমার ক্রয় ক্রয় কমিটির মাধ্যমে সম্পন্ন করতে হবে।',
                        'observation' => 'ক্রয় কমিটি ছাড়াই স্থায়ী সম্পদ ক্রয় করা হয়েছে।',
                        'stats' => ['৪২', '১৮', '০৩'],
                        'risk' => 'স্বচ্ছতাহীন ক্রয় ও নীতি লঙ্ঘন।',
                        'root' => 'ক্রয় কমিটি গঠন/অনুমোদন এড়িয়ে যাওয়া।',
                        'reco' => 'ক্রয় কমিটি অনুমোদন ছাড়া ক্রয় নিষিদ্ধ করা।',
                        'jobab' => 'শাখা কমিটি প্রক্রিয়া মেনে চলবে।',
                        'status' => 'চলমান',
                    ],
                    [
                        'section' => null,
                        'serial' => '১.২',
                        'indicator_code' => '৩০০০-৩',
                        'title' => 'শিরোনাম',
                        'body' => 'স্থায়ী সম্পদ ক্রয়ের মূল্য ব্যাংক চেকে না দিয়ে নগদে পরিশোধ করা',
                        'amount' => '২৭,৮০০',
                        'rating' => 'Medium (C)',
                        'criteria' => 'স্থায়ী সম্পদ ক্রয়ের মূল্য ব্যাংক চেকের মাধ্যমে পরিশোধ করতে হবে।',
                        'observation' => 'কিছু ক্রয়ে নগদে মূল্য পরিশোধ করা হয়েছে।',
                        'stats' => ['৮', '৮', '২'],
                        'risk' => 'নগদ লেনদেনে অনিয়ম ও নিরীক্ষা ঝুঁকি।',
                        'root' => 'পেমেন্ট নীতিমালা অনুসরণ না করা।',
                        'reco' => 'নগদ পরিশোধ নিষিদ্ধ করে চেক/ট্রান্সফার বাধ্যতামূলক করা।',
                        'jobab' => 'হিসাবরক্ষক পেমেন্ট পদ্ধতি সংশোধন করবেন।',
                        'status' => 'সমাধানের পথে',
                    ],
                    [
                        'section' => ['২.০', '২.০ অর্থ ও হিসাব সংক্রান্ত'],
                        'serial' => '২.১',
                        'indicator_code' => '৪০০০-১',
                        'title' => 'শিরোনাম',
                        'body' => 'প্রোডাক্ট অনুযায়ী/মোট MIS ও AIS প্রতিবেদনের সাথে সঞ্চয়স্থিতির পার্থক্য',
                        'amount' => '২২,৪০০',
                        'rating' => 'Medium (C)',
                        'criteria' => 'MIS ও AIS প্রতিবেদনে সঞ্চয়স্থিতি মিল রাখতে হবে।',
                        'observation' => 'প্রোডাক্টভিত্তিক/মোট সঞ্চয়স্থিতিতে MIS ও AIS-এর মধ্যে পার্থক্য পাওয়া গেছে।',
                        'stats' => ['১০', '১০', '০৪'],
                        'risk' => 'ভুল আর্থিক প্রতিবেদন ও সিদ্ধান্ত ঝুঁকি।',
                        'root' => 'দুই সিস্টেমের নিয়মিত সমন্বয় না করা।',
                        'reco' => 'মাসিক MIS–AIS রিকনসিলিয়েশন বাধ্যতামূলক করা।',
                        'jobab' => 'হিসাবরক্ষক পার্থক্য সমন্বয় করবেন।',
                        'status' => 'চলমান',
                    ],
                ],
            ],
            [
                'theme' => 'অর্থ-হিসাব ও মানব সম্পদ',
                'findings' => [
                    [
                        'section' => ['১.০', '১.০ অর্থ ও হিসাব সংক্রান্ত'],
                        'serial' => '১.১',
                        'indicator_code' => '৪০০০-২',
                        'title' => 'শিরোনাম',
                        'body' => 'প্রোডাক্ট অনুযায়ী/মোট MIS ও AIS প্রতিবেদনের সাথে ঋণস্থিতির পার্থক্য',
                        'amount' => '৩৫,০০০',
                        'rating' => 'Major (B)',
                        'criteria' => 'MIS ও AIS প্রতিবেদনে ঋণস্থিতি মিল রাখতে হবে।',
                        'observation' => 'ঋণস্থিতিতে MIS ও AIS-এর মধ্যে অসঙ্গতি পাওয়া গেছে।',
                        'stats' => ['২৮', '২৮', '০২'],
                        'risk' => 'ঋণ পোর্টফোলিও ভুল প্রতিবেদনের ঝুঁকি।',
                        'root' => 'সিস্টেম সমন্বয় ও যাচাই দুর্বল।',
                        'reco' => 'মাসিক ঋণস্থিতি রিকনসিলিয়েশন সম্পন্ন করা।',
                        'jobab' => 'শাখা অসঙ্গতি নিরসন করবে।',
                        'status' => 'সমাধানের পথে',
                    ],
                    [
                        'section' => null,
                        'serial' => '১.২',
                        'indicator_code' => '৫০০০-১',
                        'title' => 'শিরোনাম',
                        'body' => 'অনুমোদন ব্যতীত কুক নিয়োগ',
                        'amount' => '৭,৮০০',
                        'rating' => 'Medium (C)',
                        'criteria' => 'কুকসহ সকল নিয়োগ যথাযথ অনুমোদন সাপেক্ষে করতে হবে।',
                        'observation' => 'অনুমোদন ছাড়া কুক নিয়োগের প্রমাণ পাওয়া গেছে।',
                        'stats' => ['২০', '২০', '০১'],
                        'risk' => 'HR নীতি লঙ্ঘন ও অননুমোদিত ব্যয়।',
                        'root' => 'নিয়োগ অনুমোদন প্রক্রিয়া এড়িয়ে যাওয়া।',
                        'reco' => 'অনুমোদন ছাড়া কোনো নিয়োগ না করা।',
                        'jobab' => 'বিএম অনুমোদন প্রক্রিয়া সম্পন্ন করবেন।',
                        'status' => 'চলমান',
                    ],
                    [
                        'section' => ['২.০', '২.০ মানব সম্পদ সংক্রান্ত'],
                        'serial' => '২.১',
                        'indicator_code' => '৫০০০-৩',
                        'title' => 'শিরোনাম',
                        'body' => 'অভিযোগ বক্স স্থাপন না করা',
                        'amount' => '০',
                        'rating' => 'Minor (D)',
                        'criteria' => 'শাখায় অভিযোগ বক্স স্থাপন ও ব্যবহার নিশ্চিত করতে হবে।',
                        'observation' => 'শাখায় অভিযোগ বক্স স্থাপন করা হয়নি।',
                        'stats' => ['১', '১', '১'],
                        'risk' => 'অভিযোগ গ্রহণ ও সমাধান ব্যবস্থা দুর্বল।',
                        'root' => 'কমপ্লায়েন্স চেকলিস্ট অনুসরণ না করা।',
                        'reco' => 'অবিলম্বে অভিযোগ বক্স স্থাপন ও মাসিক খোলা।',
                        'jobab' => 'শাখা অভিযোগ বক্স স্থাপন করবে।',
                        'status' => 'চলমান',
                    ],
                ],
            ],
        ];

        // Shared Matrix heading across all demo reports for Summary consolidation.
        $commonFinding = [
            'section' => ['৩.০', '৩.০ অর্থ ও হিসাব সংক্রান্ত'],
            'serial' => '৩.১',
            'indicator_code' => '৪০০০-৩',
            'title' => 'শিরোনাম',
            'body' => 'AIS প্রতিবেদনে সুফলন প্রোডাক্টে ঋণাত্বক সঞ্চয়স্থিতি পার্থক্য',
            'amount' => '১২,৫০০',
            'rating' => 'Medium (C)',
            'criteria' => 'AIS প্রতিবেদনে সঞ্চয়স্থিতি ঋণাত্মক থাকা যাবে না; পার্থক্য নিরসন করতে হবে।',
            'observation' => 'সুফলন প্রোডাক্টে AIS-এ ঋণাত্মক সঞ্চয়স্থিতি দেখা গেছে।',
            'stats' => ['১২০', '৩০', '০৪'],
            'risk' => 'ভুল সঞ্চয় প্রতিবেদন ও সিস্টেম অসঙ্গতি।',
            'root' => 'AIS ডেটা যাচাই ও কারেকশন না করা।',
            'reco' => 'ঋণাত্মক সঞ্চয়স্থিতি অবিলম্বে সমন্বয় করা।',
            'jobab' => 'হিসাবরক্ষক AIS সংশোধন সম্পন্ন করবেন।',
            'status' => 'সমাধানের পথে',
        ];

        foreach ($blueprints as &$blueprint) {
            $blueprint['findings'][] = $commonFinding;
        }
        unset($blueprint);

        return $blueprints;
    }

    /**
     * @param  \Illuminate\Support\Collection<string, AuditIndicator>  $indicators
     * @param  array{theme:string,findings:list<array<string,mixed>>}  $blueprint
     * @return array<string, mixed>
     */
    protected function buildPagesData(
        Shakha $shakha,
        array $blueprint,
        $indicators,
        string $defaultFindingRating,
        string $reportDate,
        int $seedIndex
    ): array {
        $blocks = [];
        $tocRows = [];
        $financialFindings = [];
        $usedIndicatorIds = [];

        foreach ($blueprint['findings'] as $fIndex => $finding) {
            if (! empty($finding['section'])) {
                [$secSerial, $secTitle] = $finding['section'];
                $blocks[] = [
                    'type' => 'section',
                    'serial' => $secSerial,
                    'title' => $secTitle,
                ];
                $tocRows[] = [
                    'type' => 'section',
                    'serial' => $secSerial,
                    'finding' => preg_replace('/^'.preg_quote($secSerial, '/').'\s*/u', '', $secTitle) ?: $secTitle,
                    'amount' => '০',
                    'rating' => '—',
                    'status' => '—',
                    'page_no' => BanglaNumerals::fromInt(4),
                    'preview_page' => 2,
                ];
            }

            $indicatorCode = (string) ($finding['indicator_code'] ?? '');
            $indicator = $indicators->get($indicatorCode);
            if (! $indicator) {
                throw new \RuntimeException("Finding Matrix indicator {$indicatorCode} is unavailable.");
            }
            if (in_array((int) $indicator->id, $usedIndicatorIds, true)) {
                throw new \RuntimeException(
                    "Finding Matrix indicator {$indicatorCode} is duplicated inside one report."
                );
            }
            $usedIndicatorIds[] = (int) $indicator->id;

            // Exact, prevalidated Matrix heading; never assign a random heading to unrelated evidence.
            $matrixTitle = trim((string) ($indicator?->title ?? ''));
            if ($matrixTitle === '') {
                $matrixTitle = (string) ($finding['body'] ?? 'শিরোনাম');
            }

            $rating = $this->ratingFromIndicator($indicator, (string) ($finding['rating'] ?? $defaultFindingRating));
            $serial = (string) $finding['serial'];

            $amount = trim((string) ($finding['amount'] ?? ''));
            if ($amount === '' || $amount === '—' || $amount === '-') {
                $amount = BanglaNumerals::fromLatin((string) (5000 + ($seedIndex * 3 + $fIndex) * 1750));
            }

            $findingBlock = [
                'type' => 'finding',
                'serial' => $serial,
                'title' => 'শিরোনাম',
                'body' => $matrixTitle,
                'rating' => $rating,
                'amount' => $amount,
                'indicator_id' => $indicator?->id,
                'indicator_code' => $indicator?->indicator_code,
            ];
            $blocks[] = $findingBlock;
            $financialFindings[] = $findingBlock;

            $blocks[] = [
                'type' => 'criteria',
                'label' => 'প্রচলিত নিয়ম (Criteria):',
                'body' => (string) $finding['criteria'],
            ];
            $blocks[] = [
                'type' => 'observation',
                'label' => 'পর্যবেক্ষণ (Observation) :',
                'body' => (string) $finding['observation'],
            ];

            [$pop, $sample, $inst] = $finding['stats'];
            $pct = $this->pct($sample, $inst);
            $blocks[] = [
                'type' => 'stats',
                'heading' => 'Report Rating Box:',
                'rows' => [[
                    'total_population' => BanglaNumerals::fromLatin($pop),
                    'sample_size' => BanglaNumerals::fromLatin($sample),
                    'instances_found' => BanglaNumerals::fromLatin($inst),
                    'percentage' => $pct,
                ]],
                'linked_indicator_id' => $indicator?->id,
                'linked_indicator_code' => $indicator?->indicator_code,
                'linked_finding_serial' => $serial,
                'linked_finding_title' => $matrixTitle,
                'link_manual' => false,
            ];

            $blocks[] = [
                'type' => 'observation',
                'label' => 'ঝুঁকি/প্রভাব (Risk/Implication) :',
                'body' => (string) $finding['risk'],
            ];
            $blocks[] = [
                'type' => 'observation',
                'label' => 'মূল কারণ (Root Cause):',
                'body' => (string) $finding['root'],
            ];
            $blocks[] = [
                'type' => 'observation',
                'label' => 'সুপারিশ (Recommendation) :',
                'body' => (string) $finding['reco'],
            ];
            $blocks[] = [
                'type' => 'jobab_table',
                'rows' => [
                    ['cells' => ['শাখা ব্যবস্থাপকের জবাব', (string) $finding['jobab']]],
                    ['cells' => ['সমস্যা সমাধানের ক্ষেত্রে দায়িত্বপ্রাপ্ত কর্মীর নাম/আইডি ও গৃহীত পদক্ষেপ', 'শাখা ব্যবস্থাপক / এবিএম — পদক্ষেপ গ্রহণ চলমান']],
                    ['cells' => ['সমাধানের প্রকৃত সময়কাল/সম্ভাব্য সময়কাল (তারিখ)', '৩০/০৯/২০২৬']],
                ],
            ];

            $tocRows[] = [
                'type' => 'item',
                'serial' => $serial,
                'finding' => $matrixTitle,
                'amount' => $amount,
                'rating' => $rating,
                'status' => (string) ($finding['status'] ?? 'চলমান'),
                'page_no' => BanglaNumerals::fromInt(4 + $fIndex),
                'preview_page' => 2,
            ];
        }

        $glanceMult = 1 + ($seedIndex * 0.12);
        $glanceRows = [
            ['left_label' => 'মোট ঋণস্থিতি', 'left_value' => $this->money(18500000 * $glanceMult), 'right_label' => 'PAR', 'right_value' => number_format(2.1 + $seedIndex * 0.3, 1).'%'],
            ['left_label' => 'ঋণস্থিতি / বকেয়া হার', 'left_value' => number_format(3.2 + $seedIndex * 0.2, 1).'%', 'right_label' => 'শাখার চলতি আদায়ের হার', 'right_value' => number_format(96 - $seedIndex, 1).'%'],
            ['left_label' => 'প্রতি মাঠকর্মী অনুযায়ী ঋণী সংখ্যা (গড়)', 'left_value' => (string) (185 + $seedIndex * 8), 'right_label' => 'শাখা লাভের অবস্থান (মোট)', 'right_value' => $this->money(420000 + $seedIndex * 35000)],
            ['left_label' => 'প্রতি মাঠকর্মী অনুযায়ী ঋণস্থিতি (গড়)', 'left_value' => $this->money(2100000 * $glanceMult), 'right_label' => 'শাখার সক্রিয় সদস্য সংখ্যা', 'right_value' => (string) (1850 + $seedIndex * 120)],
            ['left_label' => 'মোট সঞ্চয়স্থিতি', 'left_value' => $this->money(6200000 * $glanceMult), 'right_label' => 'শাখার মোট ঋণী সংখ্যা', 'right_value' => (string) (1420 + $seedIndex * 95)],
            ['left_label' => 'সঞ্চয় ও ঋণস্থিতির হার', 'left_value' => number_format(33 + $seedIndex, 1).'%', 'right_label' => 'মোট কর্মী সংখ্যা', 'right_value' => (string) (11 + $seedIndex)],
        ];

        $staffColumns = ['কর্মকর্তার নাম', 'পরিচিতি নং', 'পদবী', 'সংস্থায় যোগদানের তারিখ', 'শাখায় যোগদানের তারিখ'];
        $staffNames = [
            ['মোঃ রফিকুল ইসলাম', 'BM', 'শাখা ব্যবস্থাপক'],
            ['নাজমা আক্তার', 'ABM', 'সহকারী শাখা ব্যবস্থাপক'],
            ['সোহেল রানা', 'FO-01', 'মাঠকর্মী'],
            ['ফাতেমা বেগম', 'FO-02', 'মাঠকর্মী'],
        ];
        $staffRows = [];
        foreach ($staffNames as $si => $person) {
            $staffRows[] = [
                'cells' => [
                    $person[0],
                    strtoupper(substr($shakha->code ?? 'BR', 0, 3)).'-'.str_pad((string) ($si + 1), 3, '0', STR_PAD_LEFT),
                    $person[2],
                    '12/0'.(1 + $si).'/201'.(5 + $si),
                    '01/0'.(2 + $seedIndex).'/202'.(2 + ($si % 3)),
                ],
            ];
        }

        $sections = [];
        $current = null;
        foreach ($blocks as $block) {
            $type = $block['type'] ?? '';
            if ($type === 'section') {
                if ($current !== null) {
                    $sections[] = $current;
                }
                $current = [
                    'serial' => $block['serial'],
                    'title' => $block['title'],
                    'findings' => [],
                ];
            } elseif ($type === 'finding' && $current !== null) {
                $current['findings'][] = [
                    'serial' => $block['serial'] ?? '',
                    'title' => $block['title'] ?? 'শিরোনাম',
                    'body' => $block['body'] ?? '',
                    'rating' => $block['rating'] ?? '',
                    'amount' => $block['amount'] ?? '',
                    'indicator_id' => $block['indicator_id'] ?? null,
                    'indicator_code' => $block['indicator_code'] ?? null,
                ];
            }
        }
        if ($current !== null) {
            $sections[] = $current;
        }

        return [
            'meta' => [
                'tabs_done' => [
                    'cover' => true,
                    'page2' => true,
                    'page3' => true,
                    'page4' => true,
                ],
                'active_tab' => 'page4',
                'seeded' => true,
                'seed_theme' => $blueprint['theme'],
                'note' => 'Full documented September 2026 demo report',
            ],
            'tableHeaders' => AuditTableHeaders::defaults(),
            'page2' => [
                'glance_as_of' => '30 September 2026',
                'branch_opening_date' => '2015-0'.(1 + $seedIndex).'-15',
                'staff_info_as_of' => $reportDate,
                'glanceRows' => $glanceRows,
                'staffColumns' => $staffColumns,
                'staffRows' => $staffRows,
            ],
            'toc' => [
                'rows' => $tocRows,
            ],
            'page3' => [
                'sign_auditor_name' => 'Bynnas Admin',
                'sign_auditor_designation' => 'Internal Audit Officer',
                'sign_auditor_date' => $reportDate,
                'sign_bm_name' => $staffNames[0][0],
                'sign_bm_date' => $reportDate,
                'sign_abm_name' => $staffNames[1][0],
                'sign_abm_date' => $reportDate,
            ],
            'page4' => [
                'financial_section_title' => $sections[0]['title'] ?? '১.০ আর্থিক নিরীক্ষা',
                'financialFindings' => $financialFindings,
                'reportSections' => $sections,
                'reportBlocks' => $blocks,
                'financial_criteria' => 'প্রতিষ্ঠানের নির্দেশনা ও জাতীয় রাজস্ব বোর্ড (এনবিআর)-এর নির্দেশনা অনুযায়ী প্রযোজ্য ভ্যাট ও ট্যাক্স নির্ধারিত হারে সরকারি কোষাগারে জমা দিতে হবে।',
                'vatObservationRows' => [[
                    'total_population' => BanglaNumerals::fromLatin('120'),
                    'sample_size' => BanglaNumerals::fromLatin('25'),
                    'instances_found' => BanglaNumerals::fromLatin('8'),
                    'percentage' => $this->pct('25', '8'),
                ]],
                'taxObservationRows' => [[
                    'total_population' => BanglaNumerals::fromLatin('95'),
                    'sample_size' => BanglaNumerals::fromLatin('20'),
                    'instances_found' => BanglaNumerals::fromLatin('5'),
                    'percentage' => $this->pct('20', '5'),
                ]],
            ],
        ];
    }

    protected function pct(string $sample, string $instances): string
    {
        $s = BanglaNumerals::toFloat($sample);
        $i = BanglaNumerals::toFloat($instances);
        if ($s === null || $s == 0.0 || $i === null) {
            return '';
        }

        return BanglaNumerals::fromInt((int) round(($i / $s) * 100)).'%';
    }

    protected function ratingFromIndicator(?AuditIndicator $indicator, string $fallback): string
    {
        $risk = trim((string) ($indicator?->risk_rating ?? ''));
        if ($risk === '') {
            return $fallback;
        }

        $riskLower = mb_strtolower($risk);

        return match (true) {
            str_contains($riskLower, 'major') || str_contains($risk, 'উচ্চ') || str_contains($risk, 'গুরুতর') => 'Major (B)',
            str_contains($riskLower, 'medium') || str_contains($riskLower, 'moderate') || str_contains($risk, 'মধ্যম') || str_contains($risk, 'মাঝারি') => 'Medium (C)',
            str_contains($riskLower, 'minor') || str_contains($risk, 'নিম্ন') || str_contains($risk, 'সামান্য') || str_contains($riskLower, 'low') => 'Minor (D)',
            str_contains($riskLower, 'unsatisfactory') || str_contains($risk, 'অসন্তোষ') || str_contains($riskLower, 'critical') => 'Unsatisfactory (F)',
            str_contains($riskLower, 'satisfactory') || str_contains($risk, 'সন্তোষ') => 'Satisfactory (E)',
            default => $fallback,
        };
    }

    protected function money(float $amount): string
    {
        return number_format((int) round($amount));
    }
}
