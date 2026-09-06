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

/**
 * Wipe all audit reports, then create 5 fully documented September 2026 reports.
 */
class SeptemberFullReportsSeeder extends Seeder
{
    public function run(): void
    {
        $this->wipeAllReports();

        $admin = User::query()->where('email', 'admin@bynnasaudit.com')->first()
            ?? User::query()->orderBy('id')->first();

        if (! $admin) {
            $this->command?->error('No user found — cannot seed reports.');

            return;
        }

        $shakhas = Shakha::query()->with('area')->orderBy('id')->limit(5)->get();
        if ($shakhas->count() < 5) {
            $this->command?->error('Need at least 5 shakhas.');

            return;
        }

        $indicators = AuditIndicator::query()
            ->whereNotNull('title')
            ->where('title', '!=', '')
            ->orderBy('id')
            ->limit(120)
            ->get(['id', 'title', 'indicator_code', 'risk_rating']);

        if ($indicators->count() < 15) {
            $this->command?->error('Need Finding Matrix indicators (AuditIndicator) to build শিরোনাম links.');

            return;
        }

        $ratings = ['Satisfactory', 'Minor', 'Medium', 'Major', 'Unsatisfactory'];
        $controlMap = [
            'Satisfactory' => 'Satisfactory (E)',
            'Minor' => 'Minor (D)',
            'Medium' => 'Medium (C)',
            'Major' => 'Major (B)',
            'Unsatisfactory' => 'Unsatisfactory (F)',
        ];

        $blueprints = $this->reportBlueprints();

        foreach ($shakhas->values() as $i => $shakha) {
            $bp = $blueprints[$i];
            $control = $ratings[$i];
            $memo = 'DSK/IA/SEP-2026/'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT);

            $auditStart = '2026-09-0'.(1 + $i);
            $auditEnd = '2026-09-'.str_pad((string) (5 + $i), 2, '0', STR_PAD_LEFT);
            $reportDate = '2026-09-'.str_pad((string) (12 + $i), 2, '0', STR_PAD_LEFT);

            $pages = $this->buildPagesData(
                $shakha,
                $bp,
                $indicators,
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
                'working_days' => 5 + $i,
                'period_scope' => 'Full Branch Audit',
                'draft_sent_date' => '2026-09-'.str_pad((string) (8 + $i), 2, '0', STR_PAD_LEFT),
                'comments_received_date' => '2026-09-'.str_pad((string) (10 + $i), 2, '0', STR_PAD_LEFT),
                'auditor_name' => $admin->name,
                'auditor_designation' => 'Internal Audit Officer',
                'pages_data' => $pages,
                'current_tab' => 'page4',
                'progress_pct' => max(100, $progress),
                'last_saved_at' => now(),
                'completed_at' => now(),
            ]);

            try {
                $synced = app(\App\Services\AuditSummaryService::class)->syncFromReport($report);
                $this->command?->info("Created: {$memo} — {$shakha->name} (matrix synced: {$synced})");
            } catch (\Throwable $e) {
                report($e);
                $this->command?->info("Created: {$memo} — {$shakha->name} (matrix sync skipped)");
            }
        }

        $this->command?->info('Done: 5 full September 2026 reports.');
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
            AuditChecklistSubmission::query()->whereNotNull('audit_report_id')->update(['audit_report_id' => null]);
        }

        $count = AuditReport::query()->count();
        AuditReport::query()->delete();

        // Clear September 2026 matrix rows so re-seeded links stay clean.
        if (Schema::hasTable('audit_findings')) {
            \App\Models\AuditFinding::query()
                ->where('audit_month', 9)
                ->where('audit_year', 2026)
                ->delete();
        }

        $this->command?->warn("Deleted {$count} previous audit report(s).");
    }

    /**
     * @return list<array{theme:string,findings:list<array<string,mixed>>}>
     */
    protected function reportBlueprints(): array
    {
        return [
            [
                'theme' => 'আর্থিক নিয়ন্ত্রণ ও ভ্যাট-ট্যাক্স',
                'findings' => [
                    [
                        'section' => ['১.০', '১.০ আর্থিক নিরীক্ষা (Financial Audit)'],
                        'serial' => '১.১',
                        'title' => 'শিরোনাম',
                        'body' => 'ভ্যাট ও ট্যাক্স নির্ধারিত সময়ে সরকারি কোষাগারে জমা না দিয়ে হস্তমজুদ রাখা হয়েছে।',
                        'amount' => '৪৫,২৫০',
                        'rating' => 'Major (B)',
                        'criteria' => 'প্রতিষ্ঠানের আর্থিক নীতিমালা ও এনবিআর নির্দেশনা অনুযায়ী প্রযোজ্য ভ্যাট-ট্যাক্স নির্ধারিত হারে ও সময়ে জমা দিতে হবে।',
                        'observation' => 'নমুনা যাচাইয়ে দেখা যায়, আগস্ট–সেপ্টেম্বর মাসে উৎসে কর কর্তনকৃত অর্থ গড়ে ৮–১২ দিন হস্তমজুদ রাখা হয়েছে।',
                        'stats' => ['১২০', '২৫', '০৮'],
                        'risk' => 'বহিঃনিরীক্ষা কর্তৃক আপত্তি ও জরিমানার আশঙ্কা।',
                        'root' => 'শাখায় সময়মতো জমাদানের তদারকি দুর্বল।',
                        'reco' => 'প্রতি মাসের নির্ধারিত তারিখের মধ্যে ভ্যাট-ট্যাক্স জমা নিশ্চিত করতে চেকলিস্ট চালু করা।',
                        'jobab' => 'শাখা ব্যবস্থাপক জানিয়েছেন—অবিলম্বে জমাদান নিয়মিত করা হবে।',
                        'status' => 'চলমান',
                    ],
                    [
                        'section' => null,
                        'serial' => '১.২',
                        'title' => 'শিরোনাম',
                        'body' => 'কিছু খরচ ভাউচারে সহপ্রমাণক অসম্পূর্ণ অবস্থায় অনুমোদিত হয়েছে।',
                        'amount' => '১৮,৭৫০',
                        'rating' => 'Medium (C)',
                        'criteria' => 'প্রতিটি খরচের সাথে প্রয়োজনীয় সহপ্রমাণক সংরক্ষণ ও যাচাই বাধ্যতামূলক।',
                        'observation' => '২৫টি ভাউচারের মধ্যে ৬টিতে বিল/রশিদ সংযুক্ত ছিল না।',
                        'stats' => ['৮৫', '২৫', '০৬'],
                        'risk' => 'অনুপযুক্ত খরচ অনুমোদনের ঝুঁকি।',
                        'root' => 'ভাউচার পর্যালোচনায় চেকলিস্ট ব্যবহার না করা।',
                        'reco' => 'প্রতিটি ভাউচারে সহপ্রমাণক চেকলিস্ট সংযুক্ত করে অনুমোদন দেওয়া।',
                        'jobab' => 'এবিএম দায়িত্ব নিয়ে চেকলিস্ট চালু করবেন।',
                        'status' => 'সমাধানের পথে',
                    ],
                    [
                        'section' => ['২.০', '২.০ ঋণ ও সঞ্চয় পরিচালনা'],
                        'serial' => '২.১',
                        'title' => 'শিরোনাম',
                        'body' => 'কিছু ঋণ নথিতে গ্যারান্টর তথ্য ও আপডেটেড ছবি অনুপস্থিত।',
                        'amount' => '২,১৫,০০০',
                        'rating' => 'Minor (D)',
                        'criteria' => 'ঋণ অনুমোদনের পূর্বে সকল প্রয়োজনীয় কাগজপত্র সম্পূর্ণ থাকতে হবে।',
                        'observation' => '৪০টি ঋণ ফাইলের মধ্যে ৫টিতে গ্যারান্টর NID কপি ছিল না।',
                        'stats' => ['৩২০', '৪০', '০৫'],
                        'risk' => 'ঋণ আদায়ে আইনি জটিলতা সৃষ্টি হতে পারে।',
                        'root' => 'ফাইল কমপ্লিটনেস চেক উপেক্ষা।',
                        'reco' => 'নতুন ঋণ অনুমোদনের আগে ফাইল চেকলিস্ট বাধ্যতামূলক করা।',
                        'jobab' => 'মাঠকর্মীরা অনুপস্থিত কাগজপত্র ৭ দিনের মধ্যে সংগ্রহ করবে।',
                        'status' => 'চলমান',
                    ],
                ],
            ],
            [
                'theme' => 'নগদ ব্যবস্থাপনা ও অভ্যন্তরীণ নিয়ন্ত্রণ',
                'findings' => [
                    [
                        'section' => ['১.০', '১.০ নগদ ও ব্যাংক ব্যবস্থাপনা'],
                        'serial' => '১.১',
                        'title' => 'শিরোনাম',
                        'body' => 'ক্যাশ লিমিট অতিক্রম করে একাধিক দিন নগদ হাতে রাখা হয়েছে।',
                        'amount' => '৯২,৪০০',
                        'rating' => 'Major (B)',
                        'criteria' => 'শাখার অনুমোদিত নগদ সীমা অতিক্রম করা যাবে না; অতিরিক্ত অর্থ ব্যাংকে জমা দিতে হবে।',
                        'observation' => 'সেপ্টেম্বর মাসের ৭টি কার্যদিবসে ক্যাশ ব্যালেন্স নির্ধারিত সীমার উপরে ছিল।',
                        'stats' => ['৩০', '৩০', '০৭'],
                        'risk' => 'চুরি/অনিয়মের ঝুঁকি বৃদ্ধি পায়।',
                        'root' => 'দৈনিক ক্যাশ মনিটরিং দুর্বল।',
                        'reco' => 'প্রতিদিন বিকালে সীমা অতিক্রম করলে ব্যাংক জমা বাধ্যতামূলক করা।',
                        'jobab' => 'বিএম প্রতিদিন ক্যাশ রিপোর্ট পর্যালোচনা করবেন।',
                        'status' => 'চলমান',
                    ],
                    [
                        'section' => null,
                        'serial' => '১.২',
                        'title' => 'শিরোনাম',
                        'body' => 'ব্যাংক সমন্বয় বিবরণী (BRS) নিয়মিত প্রস্তুত হয়নি।',
                        'amount' => '৮,৫০০',
                        'rating' => 'Medium (C)',
                        'criteria' => 'প্রতি মাস শেষে ব্যাংক সমন্বয় বিবরণী প্রস্তুত ও যাচাই করতে হবে।',
                        'observation' => 'জুলাই ও আগস্ট মাসের BRS ফাইলে সংরক্ষিত ছিল না।',
                        'stats' => ['০৩', '০৩', '০২'],
                        'risk' => 'ব্যাংক ও বইয়ের পার্থক্য অদৃশ্য থাকতে পারে।',
                        'root' => 'হিসাবরক্ষকের কাজে সময়সূচি মেনে চলা হয়নি।',
                        'reco' => 'মাসের ৫ তারিখের মধ্যে BRS সম্পন্ন ও স্বাক্ষরিত রাখা।',
                        'jobab' => 'হিসাবরক্ষক আগামী সপ্তাহে পূর্ববর্তী BRS সম্পন্ন করবেন।',
                        'status' => 'সমাধানের পথে',
                    ],
                    [
                        'section' => ['২.০', '২.০ অভ্যন্তরীণ নিয়ন্ত্রণ'],
                        'serial' => '২.১',
                        'title' => 'শিরোনাম',
                        'body' => 'স্ট্যাম্প রেজিস্টারে স্টক ও ব্যবহারের হিসাব মিলছে না।',
                        'amount' => '৩,২০০',
                        'rating' => 'Minor (D)',
                        'criteria' => 'স্ট্যাম্প ক্রয়, ব্যবহার ও অবশিষ্ট স্টকের হিসাব নিয়মিত হালনাগাদ রাখতে হবে।',
                        'observation' => 'রেজিস্টার অনুযায়ী ৮০টি স্ট্যাম্প থাকা উচিত; ভৌত গণনায় পাওয়া গেছে ৭২টি।',
                        'stats' => ['১', '১', '১'],
                        'risk' => 'সম্পদ অপচয়/অসঙ্গতির আশঙ্কা।',
                        'root' => 'দৈনিক স্টক আপডেট না করা।',
                        'reco' => 'সাপ্তাহিক স্ট্যাম্প স্টক ভেরিফিকেশন চালু করা।',
                        'jobab' => 'অফিস সহকারী রেজিস্টার হালনাগাদ করবেন।',
                        'status' => 'চলমান',
                    ],
                ],
            ],
            [
                'theme' => 'সদস্য ভর্তি ও সমিতি পরিচালনা',
                'findings' => [
                    [
                        'section' => ['১.০', '১.০ সদস্য ভর্তি ও KYC'],
                        'serial' => '১.১',
                        'title' => 'শিরোনাম',
                        'body' => 'নতুন সদস্য ফর্মে NID যাচাই ও ছবি সংযুক্তি অসম্পূর্ণ।',
                        'amount' => '১৫,৭৫০',
                        'rating' => 'Medium (C)',
                        'criteria' => 'সদস্য ভর্তির সময় NID, ছবি ও প্রয়োজনীয় তথ্য সম্পূর্ণ যাচাই করতে হবে।',
                        'observation' => '৫০টি নতুন সদস্য ফর্মের মধ্যে ৯টিতে ছবি বা NID কপি অনুপস্থিত।',
                        'stats' => ['২১০', '৫০', '০৯'],
                        'risk' => 'পরিচয় যাচাই দুর্বল হলে প্রতারণার ঝুঁকি।',
                        'root' => 'ভর্তি চেকলিস্ট পুরোপুরি অনুসরণ না করা।',
                        'reco' => 'অসম্পূর্ণ ফর্ম অনুমোদন না দেওয়া; চেকলিস্ট বাধ্যতামূলক।',
                        'jobab' => 'মাঠকর্মীরা ১০ দিনের মধ্যে নথি সম্পূর্ণ করবে।',
                        'status' => 'চলমান',
                    ],
                    [
                        'section' => null,
                        'serial' => '১.২',
                        'title' => 'শিরোনাম',
                        'body' => 'কিছু সমিতির সভা রেজুলেশন নিয়মিত হালনাগাদ নেই।',
                        'amount' => '৬,২০০',
                        'rating' => 'Minor (D)',
                        'criteria' => 'সমিতি সভার সিদ্ধান্ত লিখিতভাবে সংরক্ষণ করতে হবে।',
                        'observation' => '১২টি সমিতির মধ্যে ৩টিতে গত ২ মাসের রেজুলেশন পাওয়া যায়নি।',
                        'stats' => ['৪৫', '১২', '০৩'],
                        'risk' => 'শাসন ব্যবস্থায় স্বচ্ছতা কমে যায়।',
                        'root' => 'সভা নথি সংরক্ষণে অমনোযোগ।',
                        'reco' => 'প্রতি সভার পর ৪৮ ঘণ্টার মধ্যে রেজুলেশন ফাইলভুক্ত করা।',
                        'jobab' => 'কেন্দ্র ব্যবস্থাপক নথি হালনাগাদ করবেন।',
                        'status' => 'সমাধানের পথে',
                    ],
                    [
                        'section' => ['২.০', '২.০ সঞ্চয় আদায়'],
                        'serial' => '২.১',
                        'title' => 'শিরোনাম',
                        'body' => 'কিছু কেন্দ্রে সঞ্চয় আদায়ের রশিদ সিরিয়াল গ্যাপ রয়েছে।',
                        'amount' => '১২,৬০০',
                        'rating' => 'Major (B)',
                        'criteria' => 'রশিদ বইয়ের সিরিয়াল ধারাবাহিক ও হিসাবভুক্ত থাকতে হবে।',
                        'observation' => 'রশিদ নং ১০৪৫–১০৫০ এর ব্যবহার/বাতিলের ব্যাখ্যা নেই।',
                        'stats' => ['৬', '৬', '১'],
                        'risk' => 'আর্থিক অনিয়মের সম্ভাবনা।',
                        'root' => 'রশিদ নিয়ন্ত্রণ রেজিস্টার যথাযথ নয়।',
                        'reco' => 'হারানো/বাতিল রশিদের তদন্ত ও লিখিত ব্যাখ্যা সংরক্ষণ।',
                        'jobab' => 'বিএম তদন্ত করে নিরীক্ষা দলে জবাব দিবেন।',
                        'status' => 'চলমান',
                    ],
                ],
            ],
            [
                'theme' => 'বাজেট, ব্যয় ও স্থায়ী সম্পদ',
                'findings' => [
                    [
                        'section' => ['১.০', '১.০ বাজেট ও ব্যয় নিয়ন্ত্রণ'],
                        'serial' => '১.১',
                        'title' => 'শিরোনাম',
                        'body' => 'আপ্যায়ন ও স্টেশনারি খাতে বাজেটের চেয়ে ব্যয় বেশি হয়েছে।',
                        'amount' => '২৭,৮০০',
                        'rating' => 'Medium (C)',
                        'criteria' => 'অনুমোদিত বাজেট সীমার মধ্যে ব্যয় নিয়ন্ত্রণ করতে হবে।',
                        'observation' => 'সেপ্টেম্বর পর্যন্ত আপ্যায়ন খাতে বাজেটের ১২৮% ব্যয় দেখা গেছে।',
                        'stats' => ['৮', '৮', '২'],
                        'risk' => 'বাজেট শৃঙ্খলা ভঙ্গ ও অতিরিক্ত ব্যয়।',
                        'root' => 'মাসভিত্তিক বাজেট মনিটরিং না থাকা।',
                        'reco' => 'প্রতি মাসে বাজেট vs প্রকৃত ব্যয় রিভিউ করা।',
                        'jobab' => 'শাখা আগামী মাস থেকে খাতভিত্তিক নিয়ন্ত্রণ করবে।',
                        'status' => 'চলমান',
                    ],
                    [
                        'section' => null,
                        'serial' => '১.২',
                        'title' => 'শিরোনাম',
                        'body' => 'কিছু স্থায়ী সম্পদের ট্যাগ ও রেজিস্টার এন্ট্রি মিলছে না।',
                        'amount' => '৬৫,০০০',
                        'rating' => 'Minor (D)',
                        'criteria' => 'সকল স্থায়ী সম্পদ ট্যাগসহ রেজিস্টারে হালনাগাদ থাকতে হবে।',
                        'observation' => '১৮টি সম্পদের মধ্যে ৩টিতে অ্যাসেট ট্যাগ অনুপস্থিত।',
                        'stats' => ['৪২', '১৮', '০৩'],
                        'risk' => 'সম্পদ হারানোর ঝুঁকি।',
                        'root' => 'ফিজিক্যাল ভেরিফিকেশন নিয়মিত নয়।',
                        'reco' => 'ত্রৈমাসিক অ্যাসেট ভেরিফিকেশন সম্পন্ন করা।',
                        'jobab' => 'অফিস সহকারী ট্যাগ সংযুক্ত করবেন।',
                        'status' => 'সমাধানের পথে',
                    ],
                    [
                        'section' => ['২.০', '২.০ ক্রয় ও কোটেশন'],
                        'serial' => '২.১',
                        'title' => 'শিরোনাম',
                        'body' => 'নির্ধারিত সীমার উপরে ক্রয়ে পর্যাপ্ত কোটেশন সংগ্রহ করা হয়নি।',
                        'amount' => '৪১,৫০০',
                        'rating' => 'Major (B)',
                        'criteria' => 'নির্ধারিত সীমার উপরে ক্রয়ে ন্যূনতম ৩টি কোটেশন সংগ্রহ করতে হবে।',
                        'observation' => '৪টি ক্রয়ের মধ্যে ২টিতে মাত্র ১টি কোটেশন পাওয়া গেছে।',
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
                'theme' => 'কমপ্লায়েন্স, আইটি ও ফলোআপ',
                'findings' => [
                    [
                        'section' => ['১.০', '১.০ পূর্ববর্তী নিরীক্ষা ফলোআপ'],
                        'serial' => '১.১',
                        'title' => 'শিরোনাম',
                        'body' => 'পূর্ববর্তী নিরীক্ষার কয়েকটি সুপারিশ এখনো বাস্তবায়িত হয়নি।',
                        'amount' => '২২,৪০০',
                        'rating' => 'Medium (C)',
                        'criteria' => 'নিরীক্ষা সুপারিশ নির্ধারিত সময়ে বাস্তবায়ন ও অবস্থা হালনাগাদ করতে হবে।',
                        'observation' => 'গতলোআপ তালিকার ১০টির মধ্যে ৪টি এখনো Open।',
                        'stats' => ['১০', '১০', '০৪'],
                        'risk' => 'পুনরাবৃত্ত অনিয়ম অব্যাহত থাকতে পারে।',
                        'root' => 'ফলোআপ মনিটরিং দুর্বল।',
                        'reco' => 'মাসিক ফলোআপ মিটিং ও স্ট্যাটাস আপডেট চালু করা।',
                        'jobab' => 'বিএম অক্টোবরের মধ্যে Open আইটেম ক্লোজ করবেন।',
                        'status' => 'চলমান',
                    ],
                    [
                        'section' => null,
                        'serial' => '১.২',
                        'title' => 'শিরোনাম',
                        'body' => 'সিস্টেম ইউজার অ্যাক্সেস রিভিউ নিয়মিত নয়; সাবেক কর্মীর অ্যাকাউন্ট সক্রিয়।',
                        'amount' => '৩৫,০০০',
                        'rating' => 'Major (B)',
                        'criteria' => 'কর্মচারী বদলি/প্রস্থানের সাথে সাথে সিস্টেম অ্যাক্সেস নিষ্ক্রিয় করতে হবে।',
                        'observation' => '২টি সাবেক ইউজার অ্যাকাউন্ট এখনো Active অবস্থায় পাওয়া গেছে।',
                        'stats' => ['২৮', '২৮', '০২'],
                        'risk' => 'অননুমোদিত ডেটা অ্যাক্সেসের ঝুঁকি।',
                        'root' => 'HR ও আইটি হ্যান্ডওভার প্রক্রিয়া দুর্বল।',
                        'reco' => 'প্রস্থান চেকলিস্টে সিস্টেম ডিঅ্যাক্টিভেশন যুক্ত করা।',
                        'jobab' => 'আইটি সাপোর্ট অবিলম্বে অ্যাকাউন্ট নিষ্ক্রিয় করবে।',
                        'status' => 'সমাধানের পথে',
                    ],
                    [
                        'section' => ['২.০', '২.০ ডকুমেন্টেশন ও সংরক্ষণ'],
                        'serial' => '২.১',
                        'title' => 'শিরোনাম',
                        'body' => 'গুরুত্বপূর্ণ নিরীক্ষা ও নীতিমালা ফাইলের সংরক্ষণ ব্যবস্থা অগোছালো।',
                        'amount' => '৭,৮০০',
                        'rating' => 'Satisfactory (E)',
                        'criteria' => 'গুরুত্বপূর্ণ নথি নির্ধারিত ফাইলিং সিস্টেমে সংরক্ষণ করতে হবে।',
                        'observation' => 'সামগ্রিকভাবে ফাইলিং গ্রহণযোগ্য; সূচিপত্র হালনাগাদ করা হয়েছে।',
                        'stats' => ['২০', '২০', '০১'],
                        'risk' => 'নথি খুঁজে পেতে বিলম্ব হতে পারে।',
                        'root' => 'ফাইল ইনডেক্স মাঝে মাঝে আপডেট হয়।',
                        'reco' => 'ত্রৈমাসিক ফাইল ইনডেক্স রিভিউ অব্যাহত রাখা।',
                        'jobab' => 'শাখা বর্তমান অনুশীলন বজায় রাখবে।',
                        'status' => 'সম্পন্ন',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, AuditIndicator>  $indicators
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

            $indicator = $indicators->get(($seedIndex * 3 + $fIndex) % max(1, $indicators->count()));
            // শিরোনাম must come from Finding Matrix indicator title (linked).
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
