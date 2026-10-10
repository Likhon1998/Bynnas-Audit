<?php

namespace Database\Seeders;

use App\Livewire\MakeAuditReport;
use App\Models\AuditChecklistFormat;
use App\Models\AuditChecklistSubmission;
use App\Models\AuditFinding;
use App\Models\AuditIndicator;
use App\Models\AuditReport;
use App\Models\MonthlyAssignment;
use App\Models\Rule;
use App\Models\Shakha;
use App\Models\ShakhaEmployee;
use App\Models\User;
use App\Services\AuditSummaryService;
use App\Services\ChecklistReportInfluenceService;
use App\Services\VisitAuditWorkService;
use App\Support\AuditChecklistCatalog;
use App\Support\AuditComplianceHeading;
use App\Support\AuditScoreSheet;
use App\Support\AuditTableHeaders;
use App\Support\BanglaNumerals;
use App\Support\CustomTableSchema;
use Carbon\Carbon;
use Database\Seeders\Demo\OctoberDemoReportContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Three complete October 2026 demo audit reports (production-safe).
 *
 * Touches only its own reports (pages_data.meta.demo_seed); re-running replaces them.
 * Shakhas that already have a real October 2026 report are never used.
 *
 *   php artisan db:seed --class=OctoberDemoReportsSeeder --force
 */
class OctoberDemoReportsSeeder extends Seeder
{
    public const DEMO_TAG = 'october-2026-demo';

    protected const MONTH = 10;

    protected const YEAR = 2026;

    protected const MARKS = ['y' => '✓', 'n' => '✗', '-' => 'N/A'];

    /** Set by `demo:october-reports --owner=` — owns (and can edit) all three reports. */
    public static ?User $ownerOverride = null;

    /** @var list<int> Set by `demo:october-reports --share=` — extra users who see the reports. */
    public static array $shareWith = [];

    protected bool $rosterEnsured = false;

    public function run(): void
    {
        $blueprints = OctoberDemoReportContent::reports();

        $indicators = AuditIndicator::query()->get()
            ->keyBy(fn (AuditIndicator $indicator) => trim((string) $indicator->indicator_code));
        $missing = collect($blueprints)
            ->flatMap(fn (array $bp) => $this->findingsOf($bp))
            ->pluck('code')
            ->unique()
            ->reject(fn (string $code) => $indicators->has($code))
            ->values();
        if ($missing->isNotEmpty()) {
            $this->command?->error('Findings Matrix headings missing: '.$missing->implode(', ')
                .'. Run: php artisan db:seed --class=AuditIndicatorSeeder --force — nothing was changed.');

            return;
        }

        app(VisitAuditWorkService::class)->ensureFormatsExist();
        $formats = AuditChecklistFormat::query()->get()->keyBy('code');

        $targets = $this->pickTargets(count($blueprints));
        if (count($targets) < count($blueprints)) {
            $this->command?->error('Need '.count($blueprints).' shakhas without an October 2026 report — found '
                .count($targets).'. Nothing was changed.');

            return;
        }

        $rules = $this->ruleStatements();
        $previousUser = Auth::user();

        try {
            DB::transaction(function () use ($blueprints, $targets, $indicators, $formats, $rules): void {
                foreach ($blueprints as $i => $bp) {
                    $shakha = $targets[$i]['shakha'];
                    $owner = $targets[$i]['owner'];

                    $this->removeDemoReport($shakha);
                    $staff = $this->staffFor($shakha);

                    $report = $this->createReport($bp, $shakha, $owner, $staff, $indicators, $rules);
                    $viewers = $this->shareReport($report, $owner);
                    $this->seedChecklists($report, $bp, $staff, $formats, $owner);
                    $this->composeWithEditor($report, $owner);

                    $report->refresh();
                    $report->update([
                        'status' => AuditReport::STATUS_COMPLETED,
                        'progress_pct' => 100,
                        'completed_at' => Carbon::parse($bp['dates']['report'].' 17:30:00'),
                        'current_tab' => 'page4',
                        'last_saved_at' => now(),
                    ]);

                    $synced = app(AuditSummaryService::class)->syncFromReport($report->fresh());
                    $expected = count($this->findingsOf($bp));
                    if ($synced !== $expected) {
                        throw new RuntimeException("{$report->memo_no}: expected {$expected} Findings Matrix rows, synced {$synced}.");
                    }

                    $this->command?->info(sprintf(
                        'Created #%d — %s (%s) · owner: %s · %d findings · checklist %s · also visible to: %s',
                        $report->id,
                        $report->shakha_display_name,
                        $bp['control_rating'],
                        $owner->name,
                        $synced,
                        $this->checklistLabel($report),
                        $viewers ?: '—'
                    ));
                }
            });
        } finally {
            if ($previousUser) {
                Auth::setUser($previousUser);
            } else {
                Auth::forgetUser();
            }
        }

        $this->command?->info('Done: 3 October 2026 demo reports (checklists, Findings Matrix and অভিযুক্ত কর্মী ready).');
    }

    /**
     * @param  array<string, mixed>  $bp
     * @return list<array<string, mixed>>
     */
    protected function findingsOf(array $bp): array
    {
        return collect($bp['sections'])->flatMap(fn (array $section) => $section['findings'])->values()->all();
    }

    // ---------------------------------------------------------------------
    // Target shakhas + owners
    // ---------------------------------------------------------------------

    /**
     * @return list<array{shakha: Shakha, owner: User}>
     */
    protected function pickTargets(int $count): array
    {
        $targets = [];
        $used = [];

        $add = function (?Shakha $shakha, ?User $owner) use (&$targets, &$used): void {
            if (! $shakha || ! $owner || isset($used[$shakha->id])) {
                return;
            }
            $targets[] = ['shakha' => $shakha, 'owner' => $owner];
            $used[$shakha->id] = true;
        };

        // 1) Re-run: keep the same shakhas / owners as the previous demo seed.
        foreach ($this->existingDemoReports() as $report) {
            $add($report->shakha, $report->user);
        }

        // 2) October visit plan: the assigned officer becomes the report maker.
        if (count($targets) < $count && Schema::hasTable('monthly_assignments')) {
            $monthStart = sprintf('%04d-%02d-01', self::YEAR, self::MONTH);
            $monthEnd = Carbon::parse($monthStart)->endOfMonth()->toDateString();

            $assignments = MonthlyAssignment::query()
                ->whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->whereDate('start_date', '<=', $monthEnd)
                ->whereDate('end_date', '>=', $monthStart)
                ->whereHas('workItem', fn ($q) => $q->where('schedulable_type', Shakha::class))
                ->with('workItem')
                ->orderBy('start_date')
                ->get();

            foreach ($assignments as $assignment) {
                if (count($targets) >= $count) {
                    break;
                }
                $shakhaId = (int) ($assignment->workItem?->schedulable_id ?? 0);
                if ($shakhaId < 1 || isset($used[$shakhaId]) || $this->hasRealOctoberReport($shakhaId)) {
                    continue;
                }
                $owner = $assignment->employee_id
                    ? $this->activeUsers()->where('employee_id', $assignment->employee_id)->first()
                    : null;
                $add(Shakha::query()->find($shakhaId), $owner);
            }
        }

        // 3) Any shakha without an October report, owned by an audit officer.
        if (count($targets) < $count) {
            $owner = $this->fallbackOwner();
            $candidates = Shakha::query()->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")->orderBy('id')->get();
            foreach ($candidates as $shakha) {
                if (count($targets) >= $count) {
                    break;
                }
                if (isset($used[$shakha->id]) || $this->hasRealOctoberReport((int) $shakha->id)) {
                    continue;
                }
                $add($shakha, $owner);
            }
        }

        $targets = array_slice($targets, 0, $count);
        if (self::$ownerOverride) {
            foreach ($targets as $i => $target) {
                $targets[$i]['owner'] = self::$ownerOverride;
            }
        }

        return $targets;
    }

    /**
     * The report list only shows a report to its owner and collaborators, so share each
     * demo report with every Super Admin plus any `--share` users.
     *
     * @return string Names of the users it was shared with
     */
    protected function shareReport(AuditReport $report, User $owner): string
    {
        $users = $this->activeUsers()->get()
            ->filter(fn (User $user) => $user->isSuperAdmin())
            ->merge(User::query()->whereIn('id', self::$shareWith)->get())
            ->unique('id')
            ->reject(fn (User $user) => (int) $user->id === (int) $owner->id)
            ->values();

        $report->collaborators()->syncWithoutDetaching($users->pluck('id')->all());

        return $users->pluck('name')->implode(', ');
    }

    /**
     * @return Collection<int, AuditReport>
     */
    protected function existingDemoReports(): Collection
    {
        return AuditReport::query()
            ->with(['shakha', 'user'])
            ->where('report_month', self::MONTH)
            ->where('report_year', self::YEAR)
            ->orderBy('id')
            ->get()
            ->filter(fn (AuditReport $report) => $this->isDemoReport($report))
            ->values();
    }

    protected function isDemoReport(AuditReport $report): bool
    {
        $pages = is_array($report->pages_data) ? $report->pages_data : [];

        return ($pages['meta']['demo_seed'] ?? null) === self::DEMO_TAG;
    }

    protected function hasRealOctoberReport(int $shakhaId): bool
    {
        return AuditReport::query()
            ->where('shakha_id', $shakhaId)
            ->where('report_month', self::MONTH)
            ->where('report_year', self::YEAR)
            ->get()
            ->contains(fn (AuditReport $report) => ! $this->isDemoReport($report));
    }

    protected function activeUsers()
    {
        return User::query()->where(fn ($q) => $q->where('is_active', true)->orWhereNull('is_active'));
    }

    protected function fallbackOwner(): ?User
    {
        $officer = $this->activeUsers()
            ->whereNotNull('employee_id')
            ->where(fn ($q) => $q->where('is_superadmin', false)->orWhereNull('is_superadmin'))
            ->orderBy('id')
            ->get()
            ->first(fn (User $user) => ! $user->isSuperAdmin() && $user->roleKey() !== 'admin');

        return $officer
            ?? $this->activeUsers()->get()->first(fn (User $user) => $user->isSuperAdmin())
            ?? User::query()->orderBy('id')->first();
    }

    protected function removeDemoReport(Shakha $shakha): void
    {
        $reports = AuditReport::query()
            ->where('shakha_id', $shakha->id)
            ->where('report_month', self::MONTH)
            ->where('report_year', self::YEAR)
            ->get()
            ->filter(fn (AuditReport $report) => $this->isDemoReport($report));

        foreach ($reports as $report) {
            AuditChecklistSubmission::query()->where('audit_report_id', $report->id)->delete();
            $report->delete();
        }

        if ($reports->isNotEmpty()) {
            AuditFinding::query()
                ->where('shakha_id', $shakha->id)
                ->where('audit_month', self::MONTH)
                ->where('audit_year', self::YEAR)
                ->delete();
        }
    }

    // ---------------------------------------------------------------------
    // Staff roster → placeholders / অভিযুক্ত কর্মী
    // ---------------------------------------------------------------------

    /**
     * @return array{people: array<string, array<string, mixed>>, count: int}
     */
    protected function staffFor(Shakha $shakha): array
    {
        $employees = $this->activeEmployees($shakha);
        if ($employees->count() < 6 && ! $this->rosterEnsured) {
            $this->rosterEnsured = true;
            $this->call(ShakhaEmployeeRosterSeeder::class);
            $employees = $this->activeEmployees($shakha);
        }
        if ($employees->isEmpty()) {
            throw new RuntimeException("Shakha #{$shakha->id} has no active employees.");
        }

        $matchers = [
            'bm' => fn (string $d) => str_contains($d, 'শাখা ব্যবস্থাপক') && ! str_contains($d, 'সহকারী'),
            'abm' => fn (string $d) => str_contains($d, 'সহকারী') && str_contains($d, 'ব্যবস্থাপক'),
            'acct' => fn (string $d) => str_contains($d, 'হিসাব'),
            'loan' => fn (string $d) => str_contains($d, 'ঋণ কর্মকর্তা'),
            'sav' => fn (string $d) => str_contains($d, 'সঞ্চয়'),
            'fo1' => fn (string $d) => str_contains($d, 'মাঠ'),
            'fo2' => fn (string $d) => str_contains($d, 'মাঠ'),
            'co' => fn (string $d) => str_contains($d, 'সংগঠক') || str_contains($d, 'কমিউনিটি'),
            'office' => fn (string $d) => str_contains($d, 'অফিস'),
        ];

        $usedIds = [];
        $people = [];
        foreach ($matchers as $role => $match) {
            $found = $employees->first(fn (ShakhaEmployee $e) => ! isset($usedIds[$e->id]) && $match((string) $e->designation));
            if ($found) {
                $usedIds[$found->id] = true;
                $people[$role] = $this->person($found);
            }
        }
        foreach (array_keys($matchers) as $role) {
            if (isset($people[$role])) {
                continue;
            }
            $spare = $employees->first(fn (ShakhaEmployee $e) => ! isset($usedIds[$e->id])) ?? $employees->first();
            $usedIds[$spare->id] = true;
            $people[$role] = $this->person($spare);
        }

        $byTenure = $employees
            ->filter(fn (ShakhaEmployee $e) => $e->joined_shakha_at !== null)
            ->sortBy(fn (ShakhaEmployee $e) => $e->joined_shakha_at->timestamp)
            ->values();
        $people['long1'] = $this->person($byTenure->get(0) ?? $employees->get(0));
        $people['long2'] = $this->person($byTenure->get(1) ?? $employees->get(1) ?? $employees->get(0));

        return ['people' => $people, 'count' => $employees->count()];
    }

    /**
     * @return Collection<int, ShakhaEmployee>
     */
    protected function activeEmployees(Shakha $shakha): Collection
    {
        return ShakhaEmployee::query()
            ->where('shakha_id', $shakha->id)
            ->where('status', ShakhaEmployee::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    protected function person(ShakhaEmployee $employee): array
    {
        $name = trim((string) $employee->name);
        $code = trim((string) $employee->employee_code);

        return [
            'id' => (int) $employee->id,
            'code' => $code,
            'name' => $name,
            'designation' => (string) $employee->designation,
            'label' => $code !== '' ? $name.' ('.$code.')' : $name,
            'joined_org' => $employee->joined_organization_at?->format('d/m/Y') ?? '',
            'joined_shakha' => $employee->joined_shakha_at?->format('d/m/Y') ?? '',
            'since' => $employee->joined_shakha_at
                ? BanglaNumerals::fromLatin($employee->joined_shakha_at->format('d/m/Y'))
                : 'দীর্ঘদিন',
        ];
    }

    /**
     * @param  array{people: array<string, array<string, mixed>>, count: int}  $staff
     */
    protected function fill(string $text, array $staff): string
    {
        $map = ['{staff_count}' => BanglaNumerals::fromInt($staff['count'])];
        foreach ($staff['people'] as $role => $p) {
            $map['{'.$role.'.name}'] = $p['name'];
            $map['{'.$role.'.since}'] = $p['since'];
            $map['{'.$role.'}'] = $p['label'];
        }

        return strtr($text, $map);
    }

    // ---------------------------------------------------------------------
    // Rule book
    // ---------------------------------------------------------------------

    /**
     * Rule-book statements by serial (live DB text wins; bundled export is the fallback).
     *
     * @return array<int, string>
     */
    protected function ruleStatements(): array
    {
        $file = base_path('database/data/rule-book.json');
        $bundled = is_file($file) ? (array) (json_decode((string) file_get_contents($file), true)['rules'] ?? []) : [];

        $statements = [];
        foreach ($bundled as $row) {
            $serial = (int) ($row['serial'] ?? 0);
            $statement = trim((string) ($row['statement'] ?? ''));
            $title = trim((string) ($row['title'] ?? ''));
            if ($serial < 1 || $statement === '') {
                continue;
            }
            $live = Rule::query()->where('statement', $statement)->first()
                ?? ($title !== '' ? Rule::query()->where('title', $title)->first() : null);
            $statements[$serial] = $live ? trim((string) $live->criteriaText()) : $statement;
        }

        return $statements;
    }

    /**
     * @param  array<string, mixed>  $finding
     * @param  array<int, string>  $rules
     */
    protected function criteriaFor(array $finding, array $rules): string
    {
        $parts = [];
        foreach ((array) ($finding['rules'] ?? []) as $serial) {
            if (! isset($rules[(int) $serial])) {
                throw new RuntimeException("Rule book serial {$serial} is missing (database/data/rule-book.json).");
            }
            $parts[] = $rules[(int) $serial];
        }
        if (! empty($finding['criteria'])) {
            $parts[] = (string) $finding['criteria'];
        }

        return implode(' ', $parts);
    }

    // ---------------------------------------------------------------------
    // Report + blocks
    // ---------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $bp
     * @param  array{people: array<string, array<string, mixed>>, count: int}  $staff
     * @param  Collection<string, AuditIndicator>  $indicators
     * @param  array<int, string>  $rules
     */
    protected function createReport(
        array $bp,
        Shakha $shakha,
        User $owner,
        array $staff,
        Collection $indicators,
        array $rules,
    ): AuditReport {
        $shakha->loadMissing('area');
        $dates = $bp['dates'];
        $display = trim($shakha->name.($shakha->code ? ' ('.$shakha->code.')' : ''));
        $designation = trim((string) ($owner->employee?->position?->title ?? '')) ?: 'অফিসার অডিট';
        $people = $staff['people'];

        [$blocks, $tocRows] = $this->buildBlocks($bp, $staff, $indicators, $rules, $display);

        $staffRows = [];
        foreach (['bm', 'abm', 'acct', 'fo1', 'fo2'] as $role) {
            $p = $people[$role];
            $staffRows[] = ['cells' => [
                $p['name'],
                $p['code'],
                $p['designation'],
                BanglaNumerals::fromLatin($p['joined_org']),
                BanglaNumerals::fromLatin($p['joined_shakha']),
            ]];
        }

        $glanceRows = [];
        foreach ($bp['glance'] as [$leftLabel, $leftValue, $rightLabel, $rightValue]) {
            $glanceRows[] = [
                'left_label' => $leftLabel,
                'left_value' => $this->fill($leftValue, $staff),
                'right_label' => $rightLabel,
                'right_value' => $this->fill($rightValue, $staff),
            ];
        }

        $pages = [
            'meta' => [
                'tabs_done' => ['cover' => true, 'page2' => true, 'page3' => true, 'page4' => true],
                'active_tab' => 'page4',
                'demo_seed' => self::DEMO_TAG,
                'demo_key' => $bp['key'],
                'seed_theme' => $bp['theme'],
            ],
            'tableHeaders' => AuditTableHeaders::defaults(),
            'page2' => [
                'glance_as_of' => '30 September 2026',
                'branch_opening_date' => optional($shakha->opening_date ?? $shakha->opened_at)?->toDateString() ?: '',
                'staff_info_as_of' => $dates['report'],
                'glanceRows' => $glanceRows,
                'staffColumns' => ['কর্মকর্তার নাম', 'পরিচিতি নং', 'পদবী', 'সংস্থায় যোগদানের তারিখ', 'শাখায় যোগদানের তারিখ'],
                'staffRows' => $staffRows,
            ],
            'toc' => ['rows' => $tocRows],
            'page3' => [
                'sign_auditor_name' => $owner->name,
                'sign_auditor_designation' => $designation,
                'sign_auditor_date' => $dates['report'],
                'sign_bm_name' => $people['bm']['name'],
                'sign_bm_date' => $dates['draft_sent'],
                'sign_abm_name' => $people['abm']['name'],
                'sign_abm_date' => $dates['draft_sent'],
            ],
            'page4' => ['reportBlocks' => $blocks],
        ];

        return AuditReport::query()->create([
            'shakha_id' => $shakha->id,
            'user_id' => $owner->id,
            'status' => AuditReport::STATUS_DRAFT,
            'report_month' => self::MONTH,
            'report_year' => self::YEAR,
            'memo_no' => 'অডিট/শাখা - '.($shakha->code ?: $shakha->id).'/'.self::YEAR,
            'report_date' => $dates['report'],
            'control_rating' => $bp['control_rating'],
            'shakha_display_name' => $display,
            'area_display_name' => (string) ($shakha->area?->name ?? ''),
            'audit_period_label' => '০১ জুলাই ২০২৬ হতে ৩০ সেপ্টেম্বর ২০২৬',
            'audit_start_date' => $dates['start'],
            'audit_end_date' => $dates['end'],
            'working_days' => $dates['working_days'],
            'period_scope' => 'Full Branch Audit',
            'draft_sent_date' => $dates['draft_sent'],
            'comments_received_date' => $dates['comments_received'],
            'auditor_name' => $owner->name,
            'auditor_designation' => $designation,
            'pages_data' => $pages,
            'current_tab' => 'page4',
            'progress_pct' => 0,
            'last_saved_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $bp
     * @param  array{people: array<string, array<string, mixed>>, count: int}  $staff
     * @param  Collection<string, AuditIndicator>  $indicators
     * @param  array<int, string>  $rules
     * @return array{0: list<array<string, mixed>>, 1: list<array<string, mixed>>}
     */
    protected function buildBlocks(array $bp, array $staff, Collection $indicators, array $rules, string $display): array
    {
        $blocks = [];
        $tocRows = [];
        $scoreRows = [];
        $sectionNo = 0;

        foreach ($bp['sections'] as $section) {
            $sectionNo++;
            $sectionSerial = BanglaNumerals::fromInt($sectionNo).'.০';
            $formatCode = $section['checklist'] ?? null;
            $formatHeading = $formatCode ? (string) (AuditChecklistCatalog::findByCode($formatCode)['heading'] ?? $formatCode) : '';

            $sectionBlock = [
                'type' => 'section',
                'serial' => $sectionSerial,
                'title' => $sectionSerial.' '.$section['title'],
            ];
            if ($formatCode) {
                $sectionBlock += [
                    'checklist_pack' => true,
                    'from_checklist' => true,
                    'checklist_format_code' => $formatCode,
                    'checklist_seed_key' => $formatCode.':section',
                ];
            }
            $blocks[] = $sectionBlock;

            foreach ($section['findings'] as $fIndex => $finding) {
                $indicator = $indicators->get($finding['code']);
                $serial = BanglaNumerals::fromInt($sectionNo).'.'.BanglaNumerals::fromInt($fIndex + 1);
                $title = trim((string) $indicator->title);
                $seedKey = $formatCode
                    ? ChecklistReportInfluenceService::formatSummarySeedKey($formatCode).($fIndex > 0 ? ':'.($fIndex + 1) : '')
                    : null;
                $packMeta = fn (string $suffix = '') => $formatCode ? [
                    'checklist_pack' => true,
                    'checklist_seed_key' => $seedKey.$suffix,
                    'checklist_format_code' => $formatCode,
                ] : [];

                $blocks[] = [
                    'type' => 'finding',
                    'serial' => $serial,
                    'title' => 'শিরোনাম',
                    'body' => $title,
                    'rating' => $finding['rating'],
                    'amount' => $this->taka((int) $finding['amount']),
                    'indicator_id' => (int) $indicator->id,
                    'indicator_code' => (string) $indicator->indicator_code,
                ] + ($formatCode ? [
                    'source' => 'checklist',
                    'from_checklist' => true,
                    'checklist_source_label' => $formatHeading,
                ] + $packMeta() : []);

                $blocks[] = [
                    'type' => 'criteria',
                    'label' => 'প্রচলিত নিয়ম (Criteria):',
                    'body' => $this->criteriaFor($finding, $rules),
                ] + $packMeta(':criteria');

                $observation = [
                    'type' => 'observation',
                    'label' => 'পর্যবেক্ষণ (Observation) :',
                    'body' => $this->fill($finding['observation'], $staff),
                ];
                $accused = array_map(fn (string $role) => [
                    'id' => $staff['people'][$role]['id'],
                    'code' => $staff['people'][$role]['code'],
                    'name' => $staff['people'][$role]['name'],
                ], (array) ($finding['people'] ?? []));
                if ($accused !== []) {
                    $observation['matrix_people'] = $accused;
                    $observation['show_matrix_people'] = true;
                }
                if ($formatCode) {
                    $observation += [
                        'from_checklist' => true,
                        'checklist_source_label' => $formatHeading,
                        'checklist_source_detail' => 'সূত্র: চেকলিস্ট — '.$formatHeading,
                    ] + $packMeta(':observation');
                }
                $blocks[] = $observation;

                if (! empty($finding['table'])) {
                    $blocks[] = $this->customTable((string) $finding['table'], $staff);
                }

                [$population, $sample, $instances] = $finding['stats'];
                $blocks[] = [
                    'type' => 'stats',
                    'heading' => 'Report Rating Box:',
                    'rows' => [[
                        'total_population' => $this->num($population),
                        'sample_size' => $this->num($sample),
                        'instances_found' => $this->num($instances),
                        'percentage' => BanglaNumerals::fromInt((int) round($instances / max(1, $sample) * 100)).'%',
                    ]],
                    'linked_indicator_id' => (int) $indicator->id,
                    'linked_indicator_code' => (string) $indicator->indicator_code,
                    'linked_finding_serial' => $serial,
                    'linked_finding_title' => $title,
                    'link_manual' => false,
                ];

                foreach ([
                    'risk' => 'ঝুঁকি/প্রভাব (Risk/Implication) :',
                    'root' => 'মূল কারণ (Root Cause):',
                    'reco' => 'সুপারিশ (Recommendation) :',
                ] as $key => $label) {
                    $blocks[] = ['type' => 'observation', 'label' => $label, 'body' => $this->fill($finding[$key], $staff)];
                }

                [$reply, $action, $deadline] = $finding['jobab'];
                $blocks[] = [
                    'type' => 'jobab_table',
                    'rows' => [
                        ['cells' => ['শাখা ব্যবস্থাপকের জবাব', $this->fill($reply, $staff)]],
                        ['cells' => ['সমস্যা সমাধানের ক্ষেত্রে দায়িত্বপ্রাপ্ত কর্মীর নাম/আইডি ও গৃহীত পদক্ষেপ', $this->fill($action, $staff)]],
                        ['cells' => ['সমাধানের প্রকৃত সময়কাল/সম্ভাব্য সময়কাল (তারিখ)', $deadline]],
                    ],
                ];

                $tocRows[] = ['type' => 'item', 'serial' => $serial, 'status' => $finding['status']];
                $scoreRows[] = [
                    'title' => $finding['score_title'].' ('.$serial.')',
                    'category' => AuditScoreSheet::categoryFromFindingRating($finding['rating']),
                    'sample_size' => (string) $sample,
                    'risk_weight' => '',
                    'instance_size' => (string) $instances,
                    'extra' => [],
                ];
            }
        }

        $next = fn () => BanglaNumerals::fromInt(++$sectionNo).'.০';
        $endDate = BanglaNumerals::fromLatin(Carbon::parse($bp['dates']['end'])->format('d/m/Y'));

        $serial = $next();
        $blocks[] = [
            'type' => 'compliance_table',
            'serial' => $serial,
            'title' => $serial.' '.AuditComplianceHeading::DEFAULT_BN,
            'title_en' => AuditComplianceHeading::DEFAULT_EN,
            'period' => $bp['compliance']['period'],
            'followup_date' => $endDate,
            'headers' => array_values(AuditTableHeaders::defaults()['compliance']),
            'rows' => array_map(fn (array $r) => [
                'prev_para_no' => $r[0],
                'findings' => $r[1],
                'first_discovery_period' => $r[2],
                'management_reply' => $r[3],
                'current_status' => $r[4],
                'current_para_no' => $r[5],
                'extra' => [],
            ], $bp['compliance']['rows']),
        ];

        $composer = $this->composer();

        $serial = $next();
        $itRows = $composer->itTemplateRows();
        foreach ($bp['it'] as $index => [$compliance, $owner, $comments, $recommendation]) {
            $itRows[$index]['compliance'] = $compliance;
            $itRows[$index]['action_owner'] = $this->fill($owner, $staff);
            $itRows[$index]['management_comments'] = $comments;
            $itRows[$index]['recommendation'] = $recommendation;
        }
        $blocks[] = [
            'type' => 'it_checklist',
            'serial' => $serial,
            'title' => $serial.' আইটি (সফটওয়্যার) সংক্রান্ত চেকলিস্ট',
            'org_line1' => 'ডিএসকে “অভ্যন্তরীণ নিরীক্ষা বিভাগ”',
            'org_line2' => 'আইটি (সফটওয়্যার) বিষয়ক সংক্রান্ত',
            'org_line3' => '',
            'program' => 'ক্ষুদ্র ঋণ',
            'branch' => $display,
            'instruction' => 'প্রযোজ্য ক্ষেত্রে টিক চিহ্ন দিন',
            'headers_r1' => array_values(AuditTableHeaders::defaults()['it_r1']),
            'headers_r2' => array_values(AuditTableHeaders::defaults()['it_r2']),
            'extra_headers' => [],
            'rows' => $itRows,
        ];

        $serial = $next();
        $externalRows = $composer->externalTemplateRows();
        foreach ($bp['external'] as $index => [$compliance, $internalIndex]) {
            $externalRows[$index]['compliance'] = $compliance;
            $externalRows[$index]['internal_index_no'] = $internalIndex;
        }
        $blocks[] = [
            'type' => 'external_audit',
            'serial' => $serial,
            'title' => $serial.' Compliance of Previous External Audit Report',
            'branch_label' => 'Name of Branch----',
            'branch' => $display,
            'headers' => array_values(AuditTableHeaders::defaults()['external_audit']),
            'rows' => $externalRows,
        ];

        $serial = $next();
        $adjustments = AuditScoreSheet::defaultAdjustments();
        foreach ($bp['adjustments'] as $index => $value) {
            $adjustments[$index]['value'] = $value;
        }
        $blocks[] = [
            'type' => 'audit_score',
            'serial' => $serial,
            'branch_name_code' => $display,
            'branch_category' => '',
            'audit_period' => '০১ জুলাই ২০২৬ হতে ৩০ সেপ্টেম্বর ২০২৬',
            'section_label' => 'Sample-based observations',
            'extra_headers' => [],
            'rows' => $scoreRows,
            'adjustments' => $adjustments,
            'subsequent' => AuditScoreSheet::defaultSubsequent(),
        ];

        return [$blocks, $tocRows];
    }

    /**
     * @param  array{people: array<string, array<string, mixed>>, count: int}  $staff
     * @return array<string, mixed>
     */
    protected function customTable(string $key, array $staff): array
    {
        $col = fn (string $label, array $children = [], ?float $width = null) => CustomTableSchema::columnNode($label, $children, $width);
        $fo1 = $staff['people']['fo1']['name'];
        $fo2 = $staff['people']['fo2']['name'];

        if ($key === 'r1_embezzlement') {
            $rows = [
                ['মোছাঃ রহিমা খাতুন (২১৪০৩৬)', '১২', 46800, 28300, $fo1],
                ['মোছাঃ শাহনাজ পারভীন (২১৪০৫২)', '১২', 52150, 29750, $fo1],
                ['জাহানারা বেগম (২১৫১১৮)', '১৭', 23600, 13800, $fo1],
                ['মোছাঃ আকলিমা আক্তার (২১৫১৪৪)', '১৭', 38900, 23300, $fo1],
                ['রেহেনা বেগম (২১৬২০৭)', '২৩', 41300, 20000, $fo2],
                ['মোছাঃ ফরিদা ইয়াসমিন (২১৬২৩১)', '২৩', 27000, 16000, $fo2],
                ['নাসরিন আক্তার (২১৭০৮৯)', '২৮', 30500, 16500, $fo2],
            ];
            $bodyRows = [];
            $sumBook = $sumSoft = 0;
            foreach ($rows as $i => [$member, $samity, $book, $soft, $fo]) {
                $sumBook += $book;
                $sumSoft += $soft;
                $bodyRows[] = ['cells' => [
                    BanglaNumerals::fromInt($i + 1), $member, $samity,
                    $this->taka($book), $this->taka($soft), $this->taka($book - $soft), $fo,
                ], 'is_total' => false, 'lead_colspan' => 1];
            }
            $bodyRows[] = ['cells' => [
                'মোট', '', '', $this->taka($sumBook), $this->taka($sumSoft), $this->taka($sumBook - $sumSoft), '',
            ], 'is_total' => true, 'lead_colspan' => 3];

            return CustomTableSchema::normalize([
                'type' => 'custom_table',
                'title' => 'সদস্যভিত্তিক সঞ্চয় স্থিতির পার্থক্য (টাকা):',
                'columns' => [
                    $col('ক্রম', [], 6),
                    $col('সদস্যের নাম ও আইডি', [], 24),
                    $col('সমিতি নং', [], 8),
                    $col('সঞ্চয় স্থিতি', [
                        $col('পাসবই অনুযায়ী (মূল লেখা)'),
                        $col('সফটওয়্যার অনুযায়ী'),
                        $col('পার্থক্য'),
                    ]),
                    $col('দায়িত্বপ্রাপ্ত মাঠ কর্মকর্তা', [], 16),
                ],
                'rows' => $bodyRows,
                'merges' => [],
            ]);
        }

        if ($key !== 'r2_assets') {
            return $this->flatTable($key, $staff);
        }

        $purchases = [
            ['১৪/০৭/২০২৬', 'ল্যাপটপ (Core i5) — ১টি', 68500, '১', 'নেই', 'নগদ', 'ভা-০৭১৪'],
            ['০৩/০৮/২০২৬', 'স্টিল আলমারি — ২টি', 32000, '০', 'নেই', 'নগদ', 'ভা-০৮০৩'],
            ['১৯/০৮/২০২৬', 'অফিস টেবিল ও চেয়ার — ৩ সেট', 23500, '৩', 'নেই', 'চেক', 'ভা-০৮১৯'],
            ['১০/০৯/২০২৬', 'আইপিএস ব্যাটারি — ১টি', 10000, '১', 'প্রযোজ্য নয়', 'নগদ', 'ভা-০৯১০'],
        ];
        $bodyRows = [];
        $total = 0;
        foreach ($purchases as [$date, $item, $price, $quotes, $committee, $payment, $voucher]) {
            $total += $price;
            $bodyRows[] = ['cells' => [$date, $item, $this->taka($price), $quotes, $committee, $payment, $voucher], 'is_total' => false, 'lead_colspan' => 1];
        }
        $bodyRows[] = ['cells' => ['মোট', '', $this->taka($total), '', '', '', ''], 'is_total' => true, 'lead_colspan' => 2];

        return CustomTableSchema::normalize([
            'type' => 'custom_table',
            'title' => 'জুলাই–সেপ্টেম্বর ২০২৬ সময়ে ক্রয়কৃত স্থায়ী সম্পদের বিবরণ:',
            'columns' => [
                $col('ক্রয়ের তারিখ', [], 12),
                $col('সম্পদের বিবরণ', [], 26),
                $col('মূল্য (টাকা)', [], 12),
                $col('ক্রয় প্রক্রিয়া', [
                    $col('কোটেশন (সংখ্যা)'),
                    $col('ক্রয় কমিটির অনুমোদন'),
                ]),
                $col('পরিশোধ পদ্ধতি', [], 10),
                $col('ভাউচার নং', [], 10),
            ],
            'rows' => $bodyRows,
            'merges' => [],
        ]);
    }

    /**
     * Single-header evidence tables; the money column is summed into a total row.
     *
     * @param  array{people: array<string, array<string, mixed>>, count: int}  $staff
     * @return array<string, mixed>
     */
    protected function flatTable(string $key, array $staff): array
    {
        $bm = $staff['people']['bm']['name'];
        $fo1 = $staff['people']['fo1']['name'];
        $fo2 = $staff['people']['fo2']['name'];
        $co = $staff['people']['co']['name'];
        $late = 'অনুমোদনের তারিখ পরবর্তীতে বসানো (ভিন্ন কালি)';

        $tables = [
            'r1_passbook' => [
                'title' => 'আদায় হলেও পাসবইয়ে পোস্টিং নেই এমন কিস্তি (টাকা):',
                'columns' => [['ক্রম', 6], ['সদস্যের নাম ও আইডি', 24], ['সমিতি নং', 8], ['আদায়ের তারিখ (সদস্যের ভাষ্য)', 14], ['সফটওয়্যারে পোস্টিং', 14], ['বিলম্ব (দিন)', 10], ['কিস্তির অর্থ', 12]],
                'money' => 6,
                'rows' => [
                    ['মোছাঃ কোহিনূর বেগম (২১৩০২২)', '০৩', '০৫/০৮/২০২৬', '০৮/০৮/২০২৬', '৩', 2400],
                    ['রাবেয়া খাতুন (২১৩০৪৭)', '০৩', '০৫/০৮/২০২৬', '১৩/০৮/২০২৬', '৮', 1850],
                    ['মোছাঃ শামীমা আক্তার (২১৩২১৮)', '০৫', '১২/০৮/২০২৬', '১৪/০৮/২০২৬', '২', 2250],
                    ['মর্জিনা বেগম (২১৩২৪০)', '০৫', '১২/০৮/২০২৬', '২১/০৮/২০২৬', '৯', 1600],
                    ['মোছাঃ রহিমা খাতুন (২১৪০৩৬)', '১২', '২৬/০৮/২০২৬', '৩১/০৮/২০২৬', '৫', 2100],
                    ['মোছাঃ শাহনাজ পারভীন (২১৪০৫২)', '১২', '০২/০৯/২০২৬', '০৮/০৯/২০২৬', '৬', 2800],
                    ['জাহানারা বেগম (২১৫১১৮)', '১৭', '০৯/০৯/২০২৬', '১২/০৯/২০২৬', '৩', 1950],
                    ['সুমি আক্তার (২১৫১৬৩)', '১৭', '১৬/০৯/২০২৬', '২২/০৯/২০২৬', '৬', 1500],
                    ['মোছাঃ আকলিমা আক্তার (২১৫১৪৪)', '১৭', '২৩/০৯/২০২৬', '২৯/০৯/২০২৬', '৬', 2000],
                ],
            ],
            'r1_negative' => [
                'title' => 'সুফলন প্রোডাক্টে সঞ্চয়স্থিতির অতিরিক্ত সমন্বয় (৩০/০৯/২০২৬, টাকা):',
                'columns' => [['ক্রম', 6], ['সদস্যের নাম ও আইডি', 24], ['সমিতি নং', 8], ['সমন্বয়ের তারিখ', 12], ['সমন্বয়ের দিনে সঞ্চয়স্থিতি', 14], ['সমন্বয়কৃত অর্থ', 12], ['ঋণাত্মক স্থিতি (–)', 12]],
                'money' => 6,
                'rows' => [
                    ['মোছাঃ সালমা বেগম (২১৮০১৪)', '২৩', '০৬/০৮/২০২৬', '১২,৮০০', '২২,০০০', 9200],
                    ['আমেনা খাতুন (২১৮০৩৯)', '২৩', '০৬/০৮/২০২৬', '৯,৭০০', '১৭,৫০০', 7800],
                    ['মোছাঃ রাশিদা আক্তার (২১৯১০২)', '২৮', '২০/০৮/২০২৬', '১৪,১০০', '২২,৫০০', 8400],
                    ['ফাতেমা বেগম (২১৯১২৮)', '২৮', '২০/০৮/২০২৬', '৮,৫০০', '১৫,০০০', 6500],
                    ['মোছাঃ নুরজাহান (২২০০৪৭)', '৩১', '২৭/০৮/২০২৬', '১১,০০০', '১৮,০০০', 7000],
                ],
            ],
            'r2_refunds' => [
                'title' => 'রেজিস্টারে এন্ট্রি ছাড়া/অসম্পূর্ণ এন্ট্রিসহ সঞ্চয় ফেরত (টাকা):',
                'columns' => [['ক্রম', 6], ['সদস্যের নাম ও আইডি', 22], ['সমিতি নং', 8], ['ফেরতের তারিখ', 12], ['ভাউচার নং', 11], ['রেজিস্টারের অবস্থা', 25], ['ফেরতের অর্থ', 12]],
                'money' => 6,
                'rows' => [
                    ['মোছাঃ জোৎস্না বেগম (৩১০১১২)', '০৪', '০৯/০৭/২০২৬', 'SR-০৭০৯', 'এন্ট্রি নেই', 5000],
                    ['হালিমা খাতুন (৩১০১৪৫)', '০৪', '২১/০৭/২০২৬', 'SR-০৭২১', 'মোবাইল নম্বর নেই', 3500],
                    ['মোছাঃ পারুল আক্তার (৩১১০২৭)', '১০', '০৪/০৮/২০২৬', 'SR-০৮০৪', 'এন্ট্রি নেই; ফোনে অনুমোদন', 4200],
                    ['সেলিনা বেগম (৩১১০৬৮)', '১০', '১৮/০৮/২০২৬', 'SR-০৮১৮', 'মোবাইল নম্বর নেই', 2800],
                    ['মোছাঃ আনোয়ারা বেগম (৩১২২০৩)', '১৬', '০১/০৯/২০২৬', 'SR-০৯০১', 'এন্ট্রি নেই; ফোনে অনুমোদন', 4500],
                    ['শাহিদা আক্তার (৩১২২৩৯)', '১৬', '১৫/০৯/২০২৬', 'SR-০৯১৫', 'মোবাইল নম্বর নেই', 3000],
                    ['মোছাঃ বিলকিস বেগম (৩১২২৭৪)', '১৬', '২৪/০৯/২০২৬', 'SR-০৯২৪', 'এন্ট্রি নেই', 3500],
                ],
            ],
            'r2_depreciation' => [
                'title' => '২০২৫-২৬ অর্থবছরে কম ধার্যকৃত অবচয় (টাকা):',
                'columns' => [['ক্রম', 6], ['সম্পদের বিবরণ', 22], ['ক্রয়ের তারিখ', 12], ['ক্রয়মূল্য', 12], ['ধার্যযোগ্য অবচয়', 13], ['ধার্যকৃত অবচয়', 13], ['কম ধার্য', 12]],
                'money' => 6,
                'rows' => [
                    ['ল্যাপটপ (Core i3) — ১', '১০/০২/২০১৯', '৪৩,০০০', '২,১৫০', '০', 2150],
                    ['ল্যাপটপ (Core i3) — ২', '১০/০২/২০১৯', '৪৩,০০০', '২,১৫০', '০', 2150],
                    ['ল্যাপটপ (Core i3) — ৩', '১০/০২/২০১৯', '৪৩,০০০', '২,১৫০', '০', 2150],
                    ['লেজার প্রিন্টার', '১৫/০৩/২০২১', '১৮,৩০০', '৩,৬৬০', '১,৮৩০', 1830],
                    ['মোটরসাইকেল (১০০ সিসি) — ১', '০১/০৬/২০২০', '১,৩০,০০০', '১৩,০০০', '৯,৭৫০', 3250],
                    ['মোটরসাইকেল (১০০ সিসি) — ২', '০১/০৬/২০২০', '১,৩০,০০০', '১৩,০০০', '৯,৭৫০', 3250],
                ],
            ],
            'r3_passbook' => [
                'title' => 'পাসবইয়ে অলিখিত সর্বশেষ কিস্তি (টাকা):',
                'columns' => [['ক্রম', 6], ['সদস্যের নাম ও আইডি', 26], ['সমিতি নং', 10], ['আদায়ের তারিখ', 14], ['সফটওয়্যারে পোস্টিং', 16], ['কিস্তির অর্থ', 12]],
                'money' => 5,
                'rows' => [
                    ['মোছাঃ রুবিনা খাতুন (৪১০৩১৫)', '০৭', '২৯/০৯/২০২৬', '২৯/০৯/২০২৬', 2450],
                    ['নাছিমা বেগম (৪১০৩৪২)', '০৭', '২৯/০৯/২০২৬', '২৯/০৯/২০২৬', 2100],
                    ['মোছাঃ তাসলিমা আক্তার (৪১১০৮৮)', '১১', '৩০/০৯/২০২৬', '৩০/০৯/২০২৬', 1800],
                ],
            ],
            'r3_cash' => [
                'title' => 'চাহিদার তুলনায় অতিরিক্ত উত্তোলনের দিনসমূহ (টাকা):',
                'columns' => [['ক্রম', 6], ['তারিখ', 12], ['চাহিদার পরিমাণ', 14], ['ব্যাংক থেকে উত্তোলন', 14], ['প্রকৃত খরচ', 13], ['দিনশেষে নগদ স্থিতি', 14], ['অতিরিক্ত উত্তোলন', 14]],
                'money' => 6,
                'rows' => [
                    ['১১/০৮/২০২৬', '৮৫,০০০', '৮৫,০০০', '৮০,২০০', '১৮,৬০০', 4800],
                    ['০২/০৯/২০২৬', '৬০,০০০', '৬০,০০০', '৫৬,১০০', '১৫,২০০', 3900],
                    ['২২/০৯/২০২৬', '৭০,০০০', '৭০,০০০', '৬৬,৪০০', '১৪,৯০০', 3600],
                ],
            ],
            'r3_conveyance' => [
                'title' => 'মোটরসাইকেল ব্যবহারের দিনে দাবিকৃত সিএনজি ভাড়া (টাকা):',
                'columns' => [['ক্রম', 6], ['মাস', 12], ['কর্মীর নাম', 20], ['লগবই অনুযায়ী মোটরসাইকেল ব্যবহারের দিন', 18], ['উদাহরণ (তারিখ)', 30], ['দাবিকৃত ভাড়া', 14]],
                'money' => 5,
                'rows' => [
                    ['আগস্ট ২০২৬', $fo2, '৮', '০৬/০৮, ২০/০৮/২০২৬', 2350],
                    ['সেপ্টেম্বর ২০২৬', $fo2, '৬', '০৩/০৯, ১৭/০৯/২০২৬', 1850],
                ],
            ],
            'r3_fdr' => [
                'title' => 'ভুল স্কিম কোডে খোলা মেয়াদি সঞ্চয় হিসাব (টাকা):',
                'columns' => [['ক্রম', 6], ['সদস্যের নাম ও আইডি', 22], ['হিসাব খোলার তারিখ', 13], ['AIS অনুযায়ী স্কিম', 16], ['MIS অনুযায়ী স্কিম', 15], ['মুনাফার হার (AIS / MIS)', 14], ['জমার পরিমাণ', 12]],
                'money' => 6,
                'rows' => [
                    ['মোছাঃ ফেরদৌসী বেগম (৪১৫০০৯)', '১২/০৩/২০২৬', 'DSK Double Scheme', 'FDR-Personal', '১২.০% / ৯.৫%', 120000],
                    ['আব্দুল মালেক (৪১৫০২৪)', '০৮/০৫/২০২৬', 'DSK Double Scheme', 'FDR-Personal', '১২.০% / ৯.৫%', 80000],
                ],
            ],
            'r3_inactive' => [
                'title' => 'সদস্যের উপস্থিতি ছাড়া সমন্বয়কৃত নিষ্ক্রিয় হিসাব (টাকা):',
                'columns' => [['ক্রম', 6], ['সদস্যের নাম ও আইডি', 22], ['সমিতি নং', 8], ['সর্বশেষ লেনদেন', 13], ['সমন্বয়ের তারিখ', 13], ['সভায় সদস্যের উপস্থিতি', 16], ['ঋণাত্মক স্থিতি (–)', 12]],
                'money' => 6,
                'rows' => [
                    ['মোছাঃ লাভলী বেগম (৪১২১০৬)', '০৯', '১৪/১১/২০২৫', '১৯/০৮/২০২৬', 'অনুপস্থিত', 4800],
                    ['শাহানাজ আক্তার (৪১২১৩৩)', '০৯', '০২/১২/২০২৫', '১৯/০৮/২০২৬', 'অনুপস্থিত', 3900],
                    ['মোছাঃ মমতাজ বেগম (৪১৩০৫১)', '১৫', '২৭/০১/২০২৬', '০৯/০৯/২০২৬', 'অনুপস্থিত (এলাকা ত্যাগ)', 3600],
                    ['রোকসানা খাতুন (৪১৩০৭৭)', '১৫', '১০/০২/২০২৬', '০৯/০৯/২০২৬', 'অনুপস্থিত', 3300],
                ],
            ],
            'r1_bank' => [
                'title' => 'চাহিদা রেজিস্টারে এন্ট্রি ছাড়া ব্যাংক উত্তোলন (টাকা):',
                'columns' => [['ক্রম', 6], ['উত্তোলনের তারিখ', 13], ['চেক নং', 12], ['উত্তোলিত অর্থ', 13], ['চাহিদা রেজিস্টার', 14], ['অর্থের ব্যবহার (ক্যাশবুক অনুযায়ী)', 26], ['চেক স্বাক্ষরকারী', 16]],
                'money' => 3,
                'rows' => [
                    ['০৭/০৭/২০২৬', 'SB-৪৪১২৭৮', 75000, 'এন্ট্রি নেই', 'ঋণ বিতরণ (৩ জন সদস্য)', $bm],
                    ['২৩/০৭/২০২৬', 'SB-৪৪১২৯১', 50000, 'এন্ট্রি নেই', 'ঋণ বিতরণ ও অফিস ভাড়া', $bm],
                    ['১২/০৮/২০২৬', 'SB-৪৪১৩১৬', 65000, 'এন্ট্রি নেই', 'ঋণ বিতরণ (২ জন সদস্য)', $bm],
                    ['০৯/০৯/২০২৬', 'SB-৪৪১৩৫২', 50000, 'এন্ট্রি নেই', 'সঞ্চয় ফেরত ও বিদ্যুৎ বিল', $bm],
                ],
            ],
            'r2_loans' => [
                'title' => 'চাহিদার তারিখ ও ঋণ প্রস্তাব অনুমোদনের তারিখের অসঙ্গতি (টাকা):',
                'columns' => [['ক্রম', 6], ['সদস্যের নাম ও আইডি', 22], ['সমিতি নং', 8], ['ঋণের পরিমাণ', 12], ['চাহিদার তারিখ', 12], ['প্রস্তাব অনুমোদনের তারিখ', 13], ['মন্তব্য', 27]],
                'money' => 3,
                'rows' => [
                    ['মোছাঃ হাসিনা বেগম (৩১২০৪৫)', '০৮', 40000, '০৪/০৮/২০২৬', '০৬/০৮/২০২৬', 'অনুমোদনের ২ দিন আগে চাহিদা'],
                    ['মোছাঃ লাইলী আক্তার (৩১২০৭১)', '০৮', 35000, '০৪/০৮/২০২৬', '০৫/০৮/২০২৬', 'অনুমোদনের ১ দিন আগে চাহিদা'],
                    ['শিরিন সুলতানা (৩১৩১১৯)', '১৪', 30000, '১৮/০৮/২০২৬', '২১/০৮/২০২৬', $late],
                    ['মোছাঃ রোকেয়া খাতুন (৩১৩১৩৬)', '১৪', 25000, '০১/০৯/২০২৬', '০২/০৯/২০২৬', 'অনুমোদনের ১ দিন আগে চাহিদা'],
                    ['পারভীন আক্তার (৩১৪০২২)', '১৯', 30000, '১৫/০৯/২০২৬', '১৮/০৯/২০২৬', $late],
                    ['মোছাঃ মর্জিনা বেগম (৩১৪০৫৮)', '১৯', 25000, '১৫/০৯/২০২৬', '১৬/০৯/২০২৬', 'অনুমোদনের ১ দিন আগে চাহিদা'],
                ],
            ],
            'r2_conveyance' => [
                'title' => 'মোটরসাইকেল ব্যবহারের দিনে দাবিকৃত সিএনজি/রিকশা ভাড়া (টাকা):',
                'columns' => [['ক্রম', 6], ['মাস', 12], ['কর্মীর নাম', 20], ['লগবই অনুযায়ী মোটরসাইকেল ব্যবহারের দিন', 18], ['একই দিনে দাবিকৃত ভাড়া', 14], ['উদাহরণ (তারিখ)', 30]],
                'money' => 4,
                'rows' => [
                    ['জুলাই ২০২৬', $fo1, '৯', 2860, '০৮/০৭, ১৫/০৭, ২৯/০৭/২০২৬'],
                    ['আগস্ট ২০২৬', $fo1, '১১', 3240, '১১/০৮ ও ২৫/০৮/২০২৬ (জ্বালানি বিলও দাবি)'],
                    ['জুলাই ২০২৬', $co, '৬', 1780, '১০/০৭, ২৪/০৭/২০২৬'],
                    ['সেপ্টেম্বর ২০২৬', $co, '৭', 1960, '০৪/০৯, ১৮/০৯/২০২৬'],
                ],
            ],
            'r3_loan_products' => [
                'title' => 'প্রোডাক্টভিত্তিক ঋণস্থিতি: MIS বনাম AIS (৩০/০৯/২০২৬, টাকা):',
                'columns' => [['ক্রম', 6], ['ঋণ প্রোডাক্ট', 18], ['MIS অনুযায়ী', 16], ['AIS অনুযায়ী', 16], ['পার্থক্য', 12], ['কারণ', 32]],
                'money' => 4,
                'rows' => [
                    ['জাগরণ', '৪৮,৪৩,৬০০', '৪৮,৬২,৩০০', 18700, 'আংশিক সমন্বয়কৃত ৩টি ঋণ MIS-এ বন্ধ'],
                    ['অগ্রসর', '৩১,০০,৮০০', '৩১,১৩,৩০০', 12500, 'আংশিক সমন্বয়কৃত ১টি ঋণ MIS-এ বন্ধ'],
                    ['বুনিয়াদ', '৬,৮৪,২০০', '৬,৮৪,২০০', 0, 'মিল আছে'],
                    ['সুফলন', '১৪,২২,৫০০', '১৪,২২,৫০০', 0, 'মিল আছে'],
                    ['কৃষি ঋণ', '৯,৫৬,০০০', '৯,৫৬,০০০', 0, 'মিল আছে'],
                ],
            ],
            'r3_stock' => [
                'title' => 'স্টক রেজিস্টার বনাম বাস্তব মজুদ (গণনার তারিখ ০৬/১০/২০২৬):',
                'columns' => [['ক্রম', 6], ['আইটেম', 20], ['রেজিস্টার অনুযায়ী স্থিতি', 14], ['বাস্তব মজুদ', 12], ['পার্থক্য', 10], ['একক মূল্য (টাকা)', 12], ['এন্ট্রি-বিহীন মূল্য (টাকা)', 16]],
                'money' => 6,
                'rows' => [
                    ['পাসবই', '১৪০টি', '২৪০টি', '১০০টি', '১২', 1200],
                    ['ঋণ আবেদন ফরম', '১২০টি', '৩২০টি', '২০০টি', '৪', 800],
                    ['ভর্তি ফরম', '৮০টি', '২৩০টি', '১৫০টি', '৩', 450],
                    ['রশিদ বই', '৪টি', '১৪টি', '১০টি', '৬০', 600],
                    ['A4 কাগজ', '৩ রিম', '৫ রিম', '২ রিম', '৪০০', 800],
                ],
            ],
        ];
        if (! isset($tables[$key])) {
            throw new RuntimeException("Unknown demo table '{$key}'.");
        }
        $table = $tables[$key];
        $money = $table['money'];

        $bodyRows = [];
        $total = 0;
        foreach ($table['rows'] as $i => $row) {
            $total += $row[$money - 1];
            $cells = [BanglaNumerals::fromInt($i + 1)];
            foreach ($row as $c => $value) {
                $cells[] = $c === $money - 1 ? $this->taka((int) $value) : (string) $value;
            }
            $bodyRows[] = ['cells' => $cells, 'is_total' => false, 'lead_colspan' => 1];
        }
        $totalCells = array_fill(0, count($table['columns']), '');
        $totalCells[0] = 'মোট';
        $totalCells[$money] = $this->taka($total);
        $bodyRows[] = ['cells' => $totalCells, 'is_total' => true, 'lead_colspan' => min(2, $money)];

        return CustomTableSchema::normalize([
            'type' => 'custom_table',
            'title' => $table['title'],
            'columns' => array_map(
                fn (array $c) => CustomTableSchema::columnNode($c[0], [], (float) $c[1]),
                $table['columns']
            ),
            'rows' => $bodyRows,
            'merges' => [],
        ]);
    }

    // ---------------------------------------------------------------------
    // Checklists (formats 1–5, saved as evidence)
    // ---------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $bp
     * @param  array{people: array<string, array<string, mixed>>, count: int}  $staff
     * @param  Collection<string, AuditChecklistFormat>  $formats
     */
    protected function seedChecklists(AuditReport $report, array $bp, array $staff, Collection $formats, User $owner): void
    {
        $linkedSummary = [];
        foreach ($bp['sections'] as $section) {
            if (! empty($section['checklist'])) {
                $linkedSummary[$section['checklist']] = $this->fill($section['findings'][0]['observation'], $staff);
            }
        }

        $savedAt = Carbon::parse($bp['dates']['end'].' 16:00:00');
        $foName = fn (string $role) => $role === '' ? '' : ($staff['people'][$role]['name'] ?? $role);

        foreach (['format-1', 'format-2', 'format-3', 'format-4', 'format-5'] as $n => $code) {
            $format = $formats->get($code);
            $definition = AuditChecklistCatalog::findByCode($code);
            if (! $format || ! $definition) {
                throw new RuntimeException("Checklist {$code} is not available.");
            }
            $spec = $bp['checklists'][$code];
            $wp = fn (int $row) => 'WP-'.BanglaNumerals::fromInt($n + 1).'/'.BanglaNumerals::fromLatin(str_pad((string) $row, 2, '0', STR_PAD_LEFT));

            $summary = $spec['summary'] ?? null;
            if ($code === 'format-1') {
                $sections = [];
                foreach ($definition['sections'] as $key => $section) {
                    $rows = [];
                    foreach ($spec['sections'][$key] as $r => [$society, $date, $role, $marks]) {
                        $rows[] = [
                            'society_name' => $society,
                            'start_date' => $date,
                            'field_worker' => $foName($role),
                            'checks' => $this->marks($marks, (int) $section['check_count']),
                            'wp_ref' => $wp($r + 1),
                        ];
                    }
                    $sections[$key] = $rows;
                }
                $payload = ['sections' => $sections, 'section_summaries' => $spec['section_summaries']];
                $summary = collect($definition['sections'])
                    ->map(fn (array $section, string $key) => $section['label'].":\n".$spec['section_summaries'][$key])
                    ->implode("\n\n");
            } elseif ($code === 'format-2') {
                $payload = ['rows' => array_map(fn (array $r, int $i) => [
                    'society_name' => $r[0],
                    'fo_name' => $foName($r[1]),
                    'member_name' => $r[2],
                    'checks' => $this->marks($r[3], (int) $definition['check_count']),
                    'wp_ref' => $wp($i + 1),
                ], $spec['rows'], array_keys($spec['rows']))];
            } elseif ($code === 'format-3') {
                $items = [];
                $marks = (string) $spec['items'];
                if (mb_strlen($marks) !== count($definition['questions'])) {
                    throw new RuntimeException("{$bp['key']} format-3 needs ".count($definition['questions']).' item marks.');
                }
                foreach (mb_str_split($marks) as $i => $mark) {
                    $incidents = (int) ($spec['incidents'][$i] ?? 0);
                    $items[] = [
                        'compliance' => $mark === 'n' ? 'no' : 'yes',
                        'incident_count' => $incidents > 0 ? BanglaNumerals::fromInt($incidents) : '',
                        'wp_ref' => $mark === 'n' ? $wp($i + 1) : '',
                    ];
                }
                $payload = [
                    'stats_rows' => array_map(fn (array $r) => [
                        'fo_name' => $foName($r[0]),
                        'society_no' => $r[1],
                        'formed_date' => $r[2],
                        'accepted_date' => $r[3],
                        'member_count' => $r[4],
                        'borrower_count' => $r[5],
                        'savings_balance' => $r[6],
                        'loan_balance' => $r[7],
                        'arrear_count' => $r[8],
                        'arrear_amount' => $r[9],
                    ], $spec['stats']),
                    'items' => $items,
                ];
            } elseif ($code === 'format-4') {
                $payload = ['rows' => array_map(fn (array $r, int $i) => [
                    'society_name' => $r[0],
                    'member_name' => $r[1],
                    'savings_amount' => $r[2],
                    'loan_amount' => $r[3],
                    'checks' => $this->marks($r[4], (int) $definition['check_count']),
                    'wp_ref' => $wp($i + 1),
                ], $spec['rows'], array_keys($spec['rows']))];
            } else {
                $payload = ['rows' => array_map(fn (array $r, int $i) => [
                    'society_name' => $r[0],
                    'member_name' => $r[1],
                    'refund_date' => $r[2],
                    'voucher_no' => $r[3],
                    'amount' => $r[4],
                    'checks' => $this->marks($r[5], (int) $definition['check_count']),
                    'wp_ref' => $wp($i + 1),
                ], $spec['rows'], array_keys($spec['rows']))];
            }

            $summary = $summary ?? ($linkedSummary[$code] ?? null);
            if ($summary === null || trim($summary) === '') {
                throw new RuntimeException("{$bp['key']} {$code} has no summary.");
            }

            $report->checklistFormats()->syncWithoutDetaching([$format->id]);
            AuditChecklistSubmission::query()->create([
                'user_id' => $owner->id,
                'audit_report_id' => $report->id,
                'audit_checklist_format_id' => $format->id,
                'heading' => $format->heading,
                'shakha_name' => $report->shakha_display_name,
                'audit_period' => $report->audit_period_label,
                'payload' => $payload,
                'summary' => $this->fill($summary, $staff),
                'status' => 'evidence',
                'saved_at' => $savedAt,
            ]);
        }
    }

    /**
     * @return list<string>
     */
    protected function marks(string $pattern, int $count): array
    {
        $chars = mb_str_split($pattern);
        if (count($chars) !== $count) {
            throw new RuntimeException("Checklist marks '{$pattern}' must have {$count} entries.");
        }

        return array_map(fn (string $c) => self::MARKS[$c] ?? '', $chars);
    }

    protected function checklistLabel(AuditReport $report): string
    {
        $progress = app(VisitAuditWorkService::class)->checklistProgress($report->fresh());

        return ($progress['done'] ?? 0).'/'.($progress['required'] ?? 0);
    }

    // ---------------------------------------------------------------------
    // Editor pipeline (same normalise / renumber / TOC / save as the wizard)
    // ---------------------------------------------------------------------

    protected function composer(): MakeAuditReport
    {
        return new class extends MakeAuditReport
        {
            /** @return list<array<string, mixed>> */
            public function itTemplateRows(): array
            {
                return $this->defaultPage20ItChecklistRows();
            }

            /** @return list<array<string, mixed>> */
            public function externalTemplateRows(): array
            {
                return $this->defaultExternalAuditRows();
            }

            public function composeAndSave(AuditReport $report): void
            {
                $statusBySerial = [];
                foreach ((array) ($report->pages_data['toc']['rows'] ?? []) as $row) {
                    $statusBySerial[(string) ($row['serial'] ?? '')] = (string) ($row['status'] ?? '');
                }

                $this->hydrateFromReport($report);
                $this->normalizeReportBlocks();
                $this->renumberSectionsAndFindings();
                $this->syncSectionsFromReportBlocks();
                $this->syncLegacyFinancialFromReportSections();
                $this->syncLegacyUtilityFromBlocks();
                $this->relinkStatsBlocksToFindings();
                $this->rebuildTocFromReportBlocks();

                foreach ($this->tocRows as $i => $row) {
                    if (($row['type'] ?? '') === 'item') {
                        $this->tocRows[$i]['status'] = $statusBySerial[(string) ($row['serial'] ?? '')] ?? 'চলমান';
                    }
                }

                $this->activeTab = 'page4';
                $this->persistDraft(markTab: 'page4');
            }
        };
    }

    protected function composeWithEditor(AuditReport $report, User $owner): void
    {
        Auth::setUser($owner);
        $this->composer()->composeAndSave($report->fresh());
    }

    // ---------------------------------------------------------------------
    // Numbers
    // ---------------------------------------------------------------------

    /** Bangla taka with lakh grouping: 112600 → ১,১২,৬০০. */
    protected function taka(int $amount): string
    {
        $digits = (string) abs($amount);
        if (strlen($digits) > 3) {
            $head = substr($digits, 0, -3);
            $head = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $head);
            $digits = $head.','.substr($digits, -3);
        }

        return BanglaNumerals::fromLatin(($amount < 0 ? '-' : '').$digits);
    }

    protected function num(int $value): string
    {
        return $this->taka($value);
    }
}
