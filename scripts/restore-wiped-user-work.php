<?php

/**
 * Rebuild the wiped local work we still know about:
 * - Draft report for Bogura Branch 2 (Sep 2026)
 * - All 5 checklist formats attached + sample marks (summaries empty)
 * - Finding শিরোনাম with amount 1000 synced into Findings Matrix
 */

use App\Models\AuditChecklistFormat;
use App\Models\AuditChecklistSubmission;
use App\Models\AuditIndicator;
use App\Models\AuditReport;
use App\Models\Shakha;
use App\Models\User;
use App\Services\AuditSummaryService;
use App\Services\VisitAuditWorkService;
use App\Support\AuditChecklistCatalog;
use Illuminate\Support\Str;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = User::query()->where('email', 'admin@bynnasaudit.com')->first()
    ?? User::query()->orderBy('id')->first();
if (! $user) {
    fwrite(STDERR, "No users found — run php artisan db:seed first.\n");
    exit(1);
}

$shakha = Shakha::query()->where('name', 'Bogura Branch 2')->first()
    ?? Shakha::query()->where('code', 'SHA-056')->first();
if (! $shakha) {
    fwrite(STDERR, "Bogura Branch 2 not found.\n");
    exit(1);
}

$visitWork = app(VisitAuditWorkService::class);
$visitWork->ensureFormatsExist();

$report = AuditReport::query()
    ->where('shakha_id', $shakha->id)
    ->where('report_month', 9)
    ->where('report_year', 2026)
    ->where('status', AuditReport::STATUS_DRAFT)
    ->where('shakha_display_name', 'like', '%Bogura Branch 2%')
    ->first();

if (! $report) {
    $report = AuditReport::query()->create([
        'user_id' => $user->id,
        'shakha_id' => $shakha->id,
        'report_month' => 9,
        'report_year' => 2026,
        'status' => AuditReport::STATUS_DRAFT,
        'shakha_display_name' => $shakha->name.' ('.$shakha->code.')',
        'area_display_name' => $shakha->area?->name,
        'auditor_name' => $user->name,
        'memo_no' => 'DSK/IA/SEP-2026/BOGURA-2',
        'last_saved_at' => now(),
        'progress_pct' => 35,
        'current_tab' => 'page4',
        'pages_data' => [
            'meta' => [
                'tabs_done' => ['cover' => true, 'page2' => false, 'page3' => false, 'page4' => true],
                'active_tab' => 'page4',
            ],
            'page4' => ['reportBlocks' => []],
        ],
    ]);
}

echo "Report #{$report->id} shakha={$shakha->name}\n";

$formats = AuditChecklistFormat::query()->orderBy('format_number')->get();
$report->checklistFormats()->sync($formats->pluck('id')->all());

foreach (AuditChecklistCatalog::all() as $def) {
    $format = AuditChecklistFormat::query()->where('code', $def['code'])->first();
    if (! $format) {
        continue;
    }

    $payload = sampleChecklistPayload($def);

    AuditChecklistSubmission::query()->updateOrCreate(
        [
            'audit_report_id' => $report->id,
            'audit_checklist_format_id' => $format->id,
        ],
        [
            'user_id' => $user->id,
            'heading' => (string) ($format->heading ?: ($def['heading'] ?? 'Checklist')),
            'shakha_name' => $shakha->name,
            'audit_period' => 'September 2026',
            'status' => 'draft',
            'payload' => $payload,
            'summary' => null,
            'saved_at' => now(),
        ]
    );

    echo "Checklist {$def['code']} filled (summary empty)\n";
}

$indicatorTitle = 'Kormi kortik financial irregularities songghotito kora.';
$indicator = AuditIndicator::query()->where('title', $indicatorTitle)->first();
if (! $indicator) {
    $indicator = AuditIndicator::query()->create([
        'category' => 'নিরীক্ষা প্রতিবেদন',
        'sub_category' => null,
        'indicator_code' => 'রিপোর্ট-'.now('Asia/Dhaka')->format('ymdHis').'-'.Str::lower(Str::random(4)),
        'title' => $indicatorTitle,
        'risk_rating' => null,
        'is_active' => true,
    ]);
}

$indicatorAltTitle = 'Kormi kortik financial regularities';
$indicatorAlt = AuditIndicator::query()->where('title', $indicatorAltTitle)->first();
if (! $indicatorAlt) {
    $indicatorAlt = AuditIndicator::query()->create([
        'category' => 'নিরীক্ষা প্রতিবেদন',
        'sub_category' => null,
        'indicator_code' => 'রিপোর্ট-'.now('Asia/Dhaka')->format('ymdHis').'-'.Str::lower(Str::random(4)),
        'title' => $indicatorAltTitle,
        'risk_rating' => null,
        'is_active' => true,
    ]);
}

$pages = (array) $report->pages_data;
$page4 = (array) ($pages['page4'] ?? []);
$blocks = array_values((array) ($page4['reportBlocks'] ?? []));

$hasSection6 = false;
foreach ($blocks as $block) {
    if (($block['type'] ?? '') === 'section' && str_starts_with((string) ($block['serial'] ?? ''), '৬')) {
        $hasSection6 = true;
        break;
    }
}

if (! $hasSection6) {
    $blocks[] = [
        'type' => 'section',
        'serial' => '৬.০',
        'title' => '',
    ];
}

$already = false;
foreach ($blocks as $block) {
    if (($block['type'] ?? '') === 'finding' && (int) ($block['indicator_id'] ?? 0) === (int) $indicator->id) {
        $already = true;
        break;
    }
}

if (! $already) {
    $blocks[] = [
        'type' => 'finding',
        'serial' => '৬.১',
        'title' => 'শিরোনাম',
        'body' => $indicator->title,
        'rating' => '',
        'amount' => '1000',
        'indicator_id' => $indicator->id,
        'indicator_code' => $indicator->indicator_code,
    ];
    $blocks[] = [
        'type' => 'finding',
        'serial' => '৬.২',
        'title' => 'শিরোনাম',
        'body' => $indicatorAlt->title,
        'rating' => '',
        'amount' => '0',
        'indicator_id' => $indicatorAlt->id,
        'indicator_code' => $indicatorAlt->indicator_code,
    ];
}

$page4['reportBlocks'] = $blocks;
$pages['page4'] = $page4;
$report->forceFill([
    'pages_data' => $pages,
    'last_saved_at' => now(),
    'current_tab' => 'page4',
])->save();

$touched = app(AuditSummaryService::class)->syncFromReport($report->fresh());

echo "Indicators: {$indicator->indicator_code} (1000), {$indicatorAlt->indicator_code}\n";
echo "Matrix cells upserted: {$touched}\n";
echo "Open report: /audits?report={$report->id}\n";
echo "Checklist: /audits/{$report->id}/checklist\n";

/**
 * @param  array<string, mixed>  $def
 * @return array<string, mixed>
 */
function sampleChecklistPayload(array $def): array
{
    $payload = AuditChecklistCatalog::blankPayload($def);
    $code = (string) ($def['code'] ?? '');
    $layout = (string) ($def['layout'] ?? '');

    $failMarks = ['✗', 'না', 'N/A'];
    $passMarks = ['✓', 'হ্যাঁ'];

    if (isset($payload['sections']) && is_array($payload['sections'])) {
        foreach ($payload['sections'] as $sectionKey => &$section) {
            if (! is_array($section)) {
                continue;
            }
            // Format 1 stores section => list of rows (not nested under 'rows').
            $rows = array_is_list($section) ? $section : (array) ($section['rows'] ?? []);
            foreach ($rows as $r => &$row) {
                if (! is_array($row)) {
                    continue;
                }
                if (array_key_exists('society_name', $row)) {
                    $row['society_name'] = $r === 0 ? 'আলোক সমিতি' : 'সূর্য সমিতি';
                }
                if (array_key_exists('member_name', $row)) {
                    $row['member_name'] = $r === 0 ? 'রহিম উদ্দিন' : 'সালমা খাতুন';
                }
                if (isset($row['checks']) && is_array($row['checks'])) {
                    foreach ($row['checks'] as $c => &$mark) {
                        $mark = ($c === 1 || $c === 3) ? $failMarks[$c % 3] : $passMarks[$c % 2];
                    }
                    unset($mark);
                }
            }
            unset($row);
            if (array_is_list($section)) {
                $section = $rows;
            } else {
                $section['rows'] = $rows;
            }
            if (! isset($payload['section_summaries']) || ! is_array($payload['section_summaries'])) {
                $payload['section_summaries'] = [];
            }
            $payload['section_summaries'][$sectionKey] = '';
        }
        unset($section);
    }

    if (($layout === 'society_management' || $code === 'format-3') && isset($payload['items']) && is_array($payload['items'])) {
        foreach ($payload['items'] as $i => &$item) {
            if (! is_array($item)) {
                continue;
            }
            $item['compliance'] = ($i % 5 === 2) ? 'no' : 'yes';
            $item['incident_count'] = ($i % 5 === 2) ? '2' : '0';
            $item['wp_ref'] = 'WP-'.($i + 1);
        }
        unset($item);

        if (isset($payload['stats_rows']) && is_array($payload['stats_rows'])) {
            $payload['stats_rows'][0] = array_merge(
                AuditChecklistCatalog::blankManagementStatsRow(),
                [
                    'fo_name' => 'করিম আলী',
                    'society_no' => 'S-101',
                    'formed_date' => '2024-01-12',
                    'accepted_date' => '2024-02-01',
                    'member_count' => '28',
                    'borrower_count' => '22',
                    'savings_balance' => '145000',
                    'loan_balance' => '320000',
                    'arrear_count' => '2',
                    'arrear_amount' => '8500',
                ]
            );
            if (isset($payload['stats_rows'][1])) {
                $payload['stats_rows'][1] = array_merge(
                    AuditChecklistCatalog::blankManagementStatsRow(),
                    [
                        'fo_name' => 'সালমা বেগম',
                        'society_no' => 'S-205',
                        'formed_date' => '2023-11-05',
                        'accepted_date' => '2023-12-01',
                        'member_count' => '35',
                        'borrower_count' => '30',
                        'savings_balance' => '210000',
                        'loan_balance' => '410000',
                        'arrear_count' => '1',
                        'arrear_amount' => '3200',
                    ]
                );
            }
        }
    }

    if (isset($payload['rows']) && is_array($payload['rows'])) {
        foreach ($payload['rows'] as $r => &$row) {
            if (! is_array($row)) {
                continue;
            }
            if (isset($row['member_name'])) {
                $row['member_name'] = $r === 0 ? 'আব্দুল করিম' : 'নাজমা বেগম';
            }
            if (isset($row['society_name'])) {
                $row['society_name'] = $r === 0 ? 'নতুন বাতি সমিতি' : 'শেফালি সমিতি';
            }
            if (isset($row['checks']) && is_array($row['checks'])) {
                foreach ($row['checks'] as $c => &$mark) {
                    $mark = ($c === 0 || $c === 4) ? '✗' : '✓';
                }
                unset($mark);
            }
        }
        unset($row);
    }

    $payload['summary'] = '';

    return $payload;
}
