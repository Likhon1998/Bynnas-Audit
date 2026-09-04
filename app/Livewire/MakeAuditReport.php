<?php

namespace App\Livewire;

use App\Models\AuditIndicator;
use App\Models\AuditReport;
use App\Models\Shakha;
use App\Services\AuditReportDocService;
use App\Services\AuditReportPdfService;
use App\Services\UserAccessService;
use App\Support\AuditReportPaginator;
use App\Support\AuditTableHeaders;
use App\Support\CustomTableSchema;
use App\Support\ExcelTsvParser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MakeAuditReport extends Component
{
    use WithFileUploads;

    /** select | wizard */
    public string $step = 'select';

    public string $activeTab = 'cover';

    public ?int $reportId = null;

    public ?int $shakha_id = null;

    public int $report_month;

    public int $report_year;

    public string $memo_no = '';

    public string $report_date = '';

    public string $control_rating = 'Satisfactory';

    public string $shakha_display_name = '';

    public string $area_display_name = '';

    public string $audit_period_label = '';

    public string $audit_start_date = '';

    public string $audit_end_date = '';

    public ?int $working_days = null;

    public string $period_scope = '';

    public string $draft_sent_date = '';

    public string $comments_received_date = '';

    public string $auditor_name = '';

    public string $auditor_designation = 'অফিসার অডিট';

    public bool $showPreview = false;

    /** @var mixed */
    public $logoUpload = null;

    public ?string $logo_path = null;

    public string $glance_as_of = '';

    public string $branch_opening_date = '';

    public string $staff_info_as_of = '';

    /** @var array<int, array{left_label:string,left_value:string,right_label:string,right_value:string}> */
    public array $glanceRows = [];

    /** @var array<int, string> */
    public array $staffColumns = [];

    /** @var array<int, array{cells: array<int, string>}> */
    public array $staffRows = [];

    /**
     * Editable column headings shared across report tables.
     *
     * @var array<string, list<string>>
     */
    public array $tableHeaders = [];

    /**
     * সূচিপত্র rows spanning page 2 (bottom) + page 3.
     *
     * @var array<int, array{type:string,serial:string,finding:string,amount:string,rating:string,status:string,page_no:string,preview_page:int}>
     */
    public array $tocRows = [];

    public string $sign_auditor_name = '';

    public string $sign_auditor_designation = '';

    public string $sign_auditor_date = '';

    public string $sign_bm_name = '';

    public string $sign_bm_date = '';

    public string $sign_abm_name = '';

    public string $sign_abm_date = '';

    public string $financial_section_title = '১.০ আর্থিক নিরীক্ষা (Financial Audit) :';

    /** @var array<int, array{serial:string,title:string,body:string,rating:string,amount?:string}> */
    public array $financialFindings = [];

    /**
     * Full report body: multiple sections, each with its own শিরোনাম list.
     * TOC is built only from these.
     *
     * @var array<int, array{serial:string,title:string,findings:list<array<string,mixed>>}>
     */
    public array $reportSections = [];

    /**
     * Ordered page-4 body blocks — insert/move anywhere.
     * type: section|finding|criteria|observation|stats|custom_table (legacy: vat|tax → stats)
     *
     * @var array<int, array<string, mixed>>
     */
    public array $reportBlocks = [];

    /** Custom table builder modal (null = closed). */
    public ?int $customTableEditorIndex = null;

    public ?int $customTableSelR = null;

    public ?int $customTableSelC = null;

    public int $customTableMergeRows = 2;

    public int $customTableMergeCols = 1;

    public int $customTableSizeCols = 4;

    public int $customTableSizeRows = 5;

    /** @var list<array{block:int,merges:list<array<string,mixed>>}> */
    public array $customTableMergeHistory = [];

    public string $financial_criteria = '';

    /** @var array<int, array{total_population:string,sample_size:string,instances_found:string,percentage:string}> */
    public array $vatObservationRows = [];

    /** @var array<int, array{total_population:string,sample_size:string,instances_found:string,percentage:string}> */
    public array $taxObservationRows = [];

    /** Page 5 — continuation of ১.১/১.২ detail + finding ১.৩ */
    /** @var array<int, array{date_month:string,voucher_no:string,description:string,expense_amount:string,vat_applicable:string,vat_paid:string,vat_diff:string,tax_applicable:string,tax_paid:string,tax_diff:string,is_total:bool}> */
    public array $expenseDetailRows = [];

    public string $expense_detail_risk = '';

    public string $expense_detail_root_cause = '';

    public string $expense_detail_recommendation = '';

    public string $expense_detail_bm_reply = '';

    public string $expense_detail_responsible = '';

    public string $expense_detail_resolution_date = '';

    public string $finding13_serial = '১.৩';

    public string $finding13_title = 'শিরোনাম';

    public string $finding13_body = '';

    public ?int $finding13_indicator_id = null;

    public ?string $finding13_indicator_code = null;

    public string $finding13_amount = '';

    public string $finding13_rating = 'Major (B)';

    public string $finding13_criteria = '';

    public string $finding13_observation = '';

    /** @var array<int, array{total_population:string,sample_size:string,instances_found:string,percentage:string}> */
    public array $finding13_statsRows = [];

    /** @var array<int, array{description:string,month_name:string,withdrawal_date:string,deposit_date:string,amount:string,holding_period:string}> */
    public array $finding13_depositRows = [];

    public string $finding13_risk = '';

    public string $finding13_root_cause = '';

    public string $finding13_recommendation = '';

    public string $finding13_bm_reply = '';

    public string $finding13_responsible = '';

    public string $finding13_resolution_date = '';

    /**
     * Page 6 — findings ১.৪ / ১.৫ (voucher-detail pattern).
     *
     * @var array<int, array<string, mixed>>
     */
    public array $page6Findings = [];

    /**
     * Page 7 — findings ১.৬ / ১.৭.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $page7Findings = [];

    /** @var list<array<string, mixed>> */
    public array $page8Findings = [];

    /** @var list<array<string, mixed>> */
    public array $page9Findings = [];

    /** @var list<array<string, mixed>> */
    public array $page10Findings = [];

    public string $page10_section_title = '২.০. স্থায়ী সম্পদ নিরীক্ষা (Fixed Asset Audit)';

    /** @var list<array<string, mixed>> */
    public array $page11Findings = [];

    /** @var list<array<string, mixed>> */
    public array $page12Findings = [];

    public string $page12_section_title = '৩.০. মজুদ ব্যবস্থাপনা নিরীক্ষা (Stock management Audit)';

    /** @var list<array<string, mixed>> */
    public array $page13Findings = [];

    public string $page13_section_title = '৪.০ কার্যক্রম/পরিচালন (Operational Audit) :';

    /** @var list<array<string, mixed>> */
    public array $page14Findings = [];

    /** @var list<array<string, mixed>> */
    public array $page15Findings = [];

    /** @var list<array<string, mixed>> */
    public array $page16Findings = [];

    /** @var list<array<string, mixed>> */
    public array $page17Findings = [];

    /** @var list<array<string, mixed>> */
    public array $page18Findings = [];

    public string $page19_compliance_title = '৫.০০ বিগত অভ্যন্তরীণ নিরীক্ষা প্রতিবেদনের জবাবের কমপ্লায়েন্স (Compliance of Previous Internal Audit Report Reply)';

    public string $page19_compliance_period = '';

    public string $page19_compliance_followup_date = '';

    /** @var list<array<string, string>> */
    public array $page19ComplianceRows = [];

    public string $page20_it_title = '৬.০০ আইটি (সফটওয়্যার) সংক্রান্ত চেকলিস্ট';

    public string $page20_it_org_line1 = 'ডিএসকে';

    public string $page20_it_org_line2 = '“অভ্যন্তরীণ নিরীক্ষা বিভাগ”';

    public string $page20_it_org_line3 = 'আইটি(সফটওয়্যার) বিষয়ক সংক্রান্ত';

    public string $page20_it_program = 'ক্ষুদ্র ঋণ';

    public string $page20_it_branch = '';

    public string $page20_it_instruction = 'প্রযোজ্য ক্ষেত্রে টিক চিহ্ন দিন';

    /** @var list<array<string, string>> */
    public array $page20ItChecklistRows = [];

    public string $page21_section_title = '৭.০০ Compliance of Previous External Audit Report';

    public string $page21_year_of_reporting = '';

    public string $page21_branch_name = '';

    /** @var list<array<string, string>> */
    public array $page21ExternalAuditRows = [];

    public string $page21_sign_label = 'নিরীক্ষা কর্মকর্তার স্বাক্ষরঃ';

    public string $page21_sign_name = '';

    public string $page21_sign_designation = '';

    /** @var list<string> */
    public array $findingRatings = [
        '',
        'Major (B)',
        'Medium (C)',
        'Minor (D)',
        'Unsatisfactory (F)',
    ];

    public ?string $lastAutoSavedAt = null;

    public string $autoSaveHint = '';

    public function mount(): void
    {
        $this->auditor_name = (string) (auth()->user()?->name ?? '');
        $this->report_month = (int) now()->month;
        $this->report_year = (int) now()->year;
        $this->report_date = now()->toDateString();
        $this->memo_no = 'অডিট/শাখা - ';
        $this->applyMonthYearDefaults();
        $this->ensureTableHeadersDefaults();
        $this->ensurePage2Defaults();
        $this->ensureTocDefaults();
        $this->ensureSignatureDefaults();
        $this->ensureFinancialAuditDefaults();
        $this->ensurePage5Defaults();
        $this->ensurePage6Defaults();
        $this->ensurePage7Defaults();
        $this->ensurePage8Defaults();
        $this->ensurePage9Defaults();
        $this->ensurePage10Defaults();
        $this->ensurePage11Defaults();
        $this->ensurePage12Defaults();
        $this->ensurePage13Defaults();
        $this->ensurePage14Defaults();
        $this->ensurePage15Defaults();
        $this->ensurePage16Defaults();
        $this->ensurePage17Defaults();
        $this->ensurePage18Defaults();
        $this->ensurePage19Defaults();
        $this->ensurePage20Defaults();
        $this->ensurePage21Defaults();
    }

    public function updatedLogoUpload(): void
    {
        $this->validate([
            'logoUpload' => ['required', 'image', 'max:2048'],
        ], [
            'logoUpload.image' => 'লোগো একটি ছবি হতে হবে (jpg/png/webp)।',
            'logoUpload.max' => 'লোগো সর্বোচ্চ 2MB হতে পারে।',
        ]);

        if ($this->logo_path) {
            Storage::disk('public')->delete($this->logo_path);
        }

        $this->logo_path = $this->logoUpload->store('audit-logos', 'public');
        $this->logoUpload = null;

        if ($this->reportId) {
            AuditReport::query()->whereKey($this->reportId)->update([
                'logo_path' => $this->logo_path,
            ]);
        }
    }

    public function removeLogo(): void
    {
        if ($this->logo_path) {
            Storage::disk('public')->delete($this->logo_path);
        }

        $this->logo_path = null;
        $this->logoUpload = null;

        if ($this->reportId) {
            AuditReport::query()->whereKey($this->reportId)->update([
                'logo_path' => null,
            ]);
        }
    }

    public function updatedReportMonth(): void
    {
        $this->applyMonthYearDefaults();
    }

    public function updatedReportYear(): void
    {
        $this->applyMonthYearDefaults();
    }

    public function updatedShakhaId($value): void
    {
        if ($value === '' || $value === null) {
            $this->shakha_id = null;
        } else {
            $this->shakha_id = (int) $value;
            $this->resetErrorBag('shakha_id');
        }
    }

    public function selectShakha(int $id): void
    {
        $this->shakha_id = $id;
        $this->resetErrorBag('shakha_id');
    }

    public function clearShakha(): void
    {
        $this->shakha_id = null;
    }

    public function startReport($shakhaId = null): void
    {
        if ($shakhaId !== null && $shakhaId !== '') {
            $this->shakha_id = (int) $shakhaId;
        }

        if (! $this->shakha_id) {
            $this->addError('shakha_id', 'শাখা নির্বাচন করুন (ড্রপডাউন থেকে বেছে নিন)।');

            return;
        }

        if (! app(UserAccessService::class)->canAccessShakha(auth()->user(), (int) $this->shakha_id)) {
            $this->addError('shakha_id', 'আপনি এই শাখায় assign নন — রিপোর্ট শুরু করা যাবে না।');

            return;
        }

        $this->validate([
            'shakha_id' => ['required', 'integer', 'exists:shakhas,id'],
            'report_month' => ['required', 'integer', 'min:1', 'max:12'],
            'report_year' => ['required', 'integer', 'min:2000', 'max:2100'],
        ], [
            'shakha_id.required' => 'শাখা নির্বাচন করুন (ড্রপডাউন থেকে বেছে নিন)।',
            'shakha_id.exists' => 'নির্বাচিত শাখা পাওয়া যায়নি।',
            'report_month.required' => 'মাস নির্বাচন করুন।',
            'report_year.required' => 'বছর নির্বাচন করুন।',
        ]);

        $this->applyMonthYearDefaults();

        $userId = (int) (auth()->id() ?? 0);
        if ($userId > 0) {
            $openDrafts = AuditReport::query()
                ->ownedBy($userId)
                ->drafts()
                ->count();

            if ($openDrafts >= AuditReport::MAX_CONCURRENT_DRAFTS) {
                $this->addError(
                    'shakha_id',
                    'আপনি একসাথে সর্বোচ্চ '.AuditReport::MAX_CONCURRENT_DRAFTS.'টি চলমান রিপোর্ট রাখতে পারবেন। আগে একটি চালিয়ে শেষ করুন বা Dashboard থেকে Continue করুন।'
                );

                return;
            }
        }

        $shakha = Shakha::query()->with('area')->findOrFail($this->shakha_id);

        $this->shakha_display_name = trim($shakha->name.($shakha->code ? ' ('.$shakha->code.')' : ''));
        $this->area_display_name = (string) ($shakha->area?->name ?? '');
        $this->memo_no = 'অডিট/শাখা - '.($shakha->code ?: $shakha->id).'/'.$this->report_year;
        $this->branch_opening_date = optional($shakha->opening_date ?? $shakha->opened_at)?->toDateString() ?: '';
        $this->ensureTableHeadersDefaults();
        $this->ensurePage2Defaults();
        $this->ensureTocDefaults();
        $this->ensureSignatureDefaults();
        $this->ensureFinancialAuditDefaults();
        $this->ensurePage5Defaults();
        $this->ensurePage6Defaults();
        $this->ensurePage7Defaults();
        $this->ensurePage8Defaults();
        $this->ensurePage9Defaults();
        $this->ensurePage10Defaults();
        $this->ensurePage11Defaults();
        $this->ensurePage12Defaults();
        $this->ensurePage13Defaults();
        $this->ensurePage14Defaults();
        $this->ensurePage15Defaults();
        $this->ensurePage16Defaults();
        $this->ensurePage17Defaults();
        $this->ensurePage18Defaults();
        $this->ensurePage19Defaults();
        $this->ensurePage20Defaults();
        $this->ensurePage21Defaults();
        $this->sign_auditor_name = $this->auditor_name;
        $this->sign_auditor_designation = $this->auditor_designation;

        try {
            $report = AuditReport::query()->create([
                'user_id' => auth()->id(),
                'shakha_id' => $this->shakha_id,
                'report_month' => $this->report_month,
                'report_year' => $this->report_year,
                'status' => AuditReport::STATUS_DRAFT,
                'current_tab' => 'cover',
                'progress_pct' => 0,
                'last_saved_at' => now(),
                'memo_no' => $this->memo_no,
                'report_date' => $this->report_date ?: null,
                'control_rating' => $this->control_rating,
                'shakha_display_name' => $this->shakha_display_name,
                'area_display_name' => $this->area_display_name,
                'audit_period_label' => $this->audit_period_label,
                'audit_start_date' => $this->audit_start_date ?: null,
                'audit_end_date' => $this->audit_end_date ?: null,
                'working_days' => $this->working_days,
                'period_scope' => $this->period_scope,
                'auditor_name' => $this->auditor_name,
                'auditor_designation' => $this->auditor_designation,
                'logo_path' => $this->logo_path,
                'pages_data' => [
                    'meta' => [
                        'tabs_done' => [
                            'cover' => false,
                            'page2' => false,
                            'page3' => false,
                            'page4' => false,
                        ],
                        'active_tab' => 'cover',
                    ],
                    'tableHeaders' => $this->tableHeaders,
                    'page2' => $this->page2Payload(),
                    'toc' => $this->tocPayload(),
                    'page3' => $this->page3Payload(),
                    'page4' => $this->page4Payload(),
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);
            $this->addError('shakha_id', 'প্রতিবেদন শুরু করা যায়নি। আবার চেষ্টা করুন।');

            return;
        }

        $this->reportId = $report->id;
        $this->step = 'wizard';
        $this->activeTab = 'cover';
        $this->lastAutoSavedAt = now('Asia/Dhaka')->format('h:i A');
        $this->autoSaveHint = 'Draft saved '.$this->lastAutoSavedAt;
        $this->sign_auditor_name = $this->auditor_name;
        $this->sign_auditor_designation = $this->auditor_designation;
        $this->resetErrorBag();
    }

    public function resumeReport(int $reportId): void
    {
        $report = AuditReport::query()
            ->when(auth()->id(), fn ($q) => $q->ownedBy((int) auth()->id()))
            ->findOrFail($reportId);

        $this->hydrateFromReport($report);
        $this->step = 'wizard';
        $this->showPreview = false;
        $this->resetErrorBag();
    }

    public function updatedActiveTab(): void
    {
        if ($this->activeTab === 'page4') {
            $this->ensureFinancialAuditDefaults();
        }

        if ($this->reportId && $this->step === 'wizard') {
            $this->autoSaveDraft();
        }
    }

    /**
     * Silent DB persist — used by poll + tab changes. Always stores the draft.
     */
    public function autoSaveDraft(): void
    {
        if (! $this->reportId || $this->step !== 'wizard') {
            return;
        }

        try {
            $this->persistDraft(markTab: null, flash: false);
            $this->lastAutoSavedAt = now('Asia/Dhaka')->format('h:i A');
            $this->autoSaveHint = 'Auto-saved '.$this->lastAutoSavedAt;
        } catch (\Throwable $e) {
            report($e);
            $this->autoSaveHint = 'Auto-save failed';
        }
    }

    public function completeReport(): void
    {
        if (! $this->reportId) {
            return;
        }

        $this->ensureFinancialAuditDefaults();
        $this->syncAllFinancialFindingsToToc();
        $this->persistDraft(markTab: 'page4', flash: false);

        AuditReport::query()->whereKey($this->reportId)->update([
            'status' => AuditReport::STATUS_COMPLETED,
            'progress_pct' => 100,
            'completed_at' => now(),
            'current_tab' => 'page4',
            'last_saved_at' => now(),
        ]);

        session()->flash('status', 'প্রতিবেদন সম্পন্ন ও সংরক্ষিত হয়েছে।');
        $this->backToSelect(saveFirst: false);
    }

    public function deleteDraft(int $reportId): void
    {
        $report = AuditReport::query()
            ->when(auth()->id(), fn ($q) => $q->ownedBy((int) auth()->id()))
            ->drafts()
            ->findOrFail($reportId);

        if ($report->logo_path) {
            Storage::disk('public')->delete($report->logo_path);
        }

        $report->delete();
        session()->flash('status', 'খসড়া রিপোর্ট মুছে ফেলা হয়েছে।');
    }

    public function updatedAuditStartDate(): void
    {
        $this->refreshDerivedDates();
    }

    public function updatedAuditEndDate(): void
    {
        $this->refreshDerivedDates();
    }

    protected function applyMonthYearDefaults(): void
    {
        try {
            $start = Carbon::create($this->report_year, $this->report_month, 1)->startOfDay();
            $end = $start->copy()->endOfMonth()->startOfDay();

            $this->audit_start_date = $start->toDateString();
            $this->audit_end_date = $end->toDateString();
            $this->working_days = $this->calcWorkingDays($this->audit_start_date, $this->audit_end_date);
            $this->audit_period_label = $this->formatBnRange($this->audit_start_date, $this->audit_end_date);
            $this->period_scope = $start->format('F Y');
            $this->report_date = $end->toDateString();
            $this->glance_as_of = $end->format('d F Y');
            if ($this->staff_info_as_of === '') {
                $this->staff_info_as_of = $end->toDateString();
            }
        } catch (\Throwable) {
            // keep existing values
        }
    }

    protected function refreshDerivedDates(): void
    {
        if ($this->audit_start_date && $this->audit_end_date) {
            $this->working_days = $this->calcWorkingDays($this->audit_start_date, $this->audit_end_date);
            $this->audit_period_label = $this->formatBnRange($this->audit_start_date, $this->audit_end_date);
        }
    }

    public function saveCover(): void
    {
        $this->report_date = $this->normalizeDateInput($this->report_date) ?? $this->report_date;
        $this->audit_start_date = $this->normalizeDateInput($this->audit_start_date) ?? $this->audit_start_date;
        $this->audit_end_date = $this->normalizeDateInput($this->audit_end_date) ?? $this->audit_end_date;
        $this->draft_sent_date = $this->normalizeDateInput($this->draft_sent_date) ?? $this->draft_sent_date;
        $this->comments_received_date = $this->normalizeDateInput($this->comments_received_date) ?? $this->comments_received_date;

        $this->validate([
            'shakha_id' => ['required', 'exists:shakhas,id'],
            'auditor_name' => ['required', 'string', 'max:255'],
            'memo_no' => ['required', 'string', 'max:255'],
            'report_date' => ['required', 'date'],
            'audit_start_date' => ['required', 'date'],
            'audit_end_date' => ['required', 'date', 'after_or_equal:audit_start_date'],
        ]);

        $this->persistDraft(markTab: 'cover', flash: true, flashMessage: 'Cover page সংরক্ষণ হয়েছে।');
        $this->activeTab = 'page2';
    }

    public function savePage2(): void
    {
        $this->ensureTocDefaults();
        $this->persistDraft(markTab: 'page2', flash: true, flashMessage: 'পৃষ্ঠা ২ সংরক্ষণ হয়েছে।');
        $this->activeTab = 'page3';
    }

    public function savePage3(): void
    {
        $this->ensureTocDefaults();
        $this->ensureSignatureDefaults();
        $this->persistDraft(markTab: 'page3', flash: true, flashMessage: 'পৃষ্ঠা ৩ (সূচিপত্র + শ্রেণীবিন্যাস + স্বাক্ষর) সংরক্ষণ হয়েছে।');
        $this->activeTab = 'page4';
        $this->ensureFinancialAuditDefaults();
    }

    public function savePage4(): void
    {
        $this->ensureFinancialAuditDefaults();
        $this->syncAllFinancialFindingsToToc();
        $this->persistDraft(markTab: 'page4', flash: true, flashMessage: 'পৃষ্ঠা ৪ (আর্থিক নিরীক্ষা) সংরক্ষণ হয়েছে।');
    }

    public function savePage5(): void
    {
        $this->ensurePage5Defaults();
        $this->syncFinding13ToToc();
        $this->persistDraft(markTab: 'page5', flash: true, flashMessage: 'পৃষ্ঠা ৫ (বিস্তারিত + ১.৩) সংরক্ষণ হয়েছে।');
        $this->activeTab = 'page6';
    }

    public function savePage6(): void
    {
        $this->ensurePage6Defaults();
        $this->syncPage6FindingsToToc();
        $this->persistDraft(markTab: 'page6', flash: true, flashMessage: 'পৃষ্ঠা ৬ (১.৪–১.৫) সংরক্ষণ হয়েছে।');
        $this->activeTab = 'page7';
    }

    public function savePage7(): void
    {
        $this->ensurePage7Defaults();
        $this->syncPage7FindingsToToc();
        $this->persistDraft(markTab: 'page7', flash: true, flashMessage: 'পৃষ্ঠা ৭ (১.৬–১.৭) সংরক্ষণ হয়েছে।');
        $this->activeTab = 'page8';
    }

    public function savePage8(): void
    {
        $this->ensurePage8Defaults();
        $this->syncPage8FindingsToToc();
        $this->persistDraft(markTab: 'page8', flash: true, flashMessage: 'পৃষ্ঠা ৮ (১.৮) সংরক্ষণ হয়েছে।');
        $this->activeTab = 'page9';
    }

    public function savePage9(): void
    {
        $this->ensurePage9Defaults();
        $this->syncPage9FindingsToToc();
        $this->persistDraft(markTab: 'page9', flash: true, flashMessage: 'পৃষ্ঠা ৯ (১.৯–১.১০) সংরক্ষণ হয়েছে।');
        $this->activeTab = 'page10';
    }

    public function savePage10(): void
    {
        $this->ensurePage10Defaults();
        $this->syncPage10FindingsToToc();
        $this->persistDraft(markTab: 'page10', flash: true, flashMessage: 'পৃষ্ঠা ১০ (২.১ স্থায়ী সম্পদ) সংরক্ষণ হয়েছে।');
        $this->activeTab = 'page11';
    }

    public function savePage11(): void
    {
        $this->ensurePage11Defaults();
        $this->syncPage11FindingsToToc();
        $this->persistDraft(markTab: 'page11', flash: true, flashMessage: 'পৃষ্ঠা ১১ (২.২–২.৩) সংরক্ষণ হয়েছে।');
        $this->activeTab = 'page12';
    }

    public function savePage12(): void
    {
        $this->ensurePage12Defaults();
        $this->syncPage12FindingsToToc();
        $this->persistDraft(markTab: 'page12', flash: true, flashMessage: 'পৃষ্ঠা ১২ (৩.১ মজুদ) সংরক্ষণ হয়েছে।');
        $this->activeTab = 'page13';
    }

    public function savePage13(): void
    {
        $this->ensurePage13Defaults();
        $this->syncPage13FindingsToToc();
        $this->persistDraft(markTab: 'page13', flash: true, flashMessage: 'পৃষ্ঠা ১৩ (৪.১–৪.২) সংরক্ষণ হয়েছে।');
        $this->activeTab = 'page14';
    }

    public function savePage14(): void
    {
        $this->ensurePage14Defaults();
        $this->syncPage14FindingsToToc();
        $this->persistDraft(markTab: 'page14', flash: true, flashMessage: 'পৃষ্ঠা ১৪ (৪.৩–৪.৪) সংরক্ষণ হয়েছে।');
        $this->activeTab = 'page15';
    }

    public function savePage15(): void
    {
        $this->ensurePage15Defaults();
        $this->syncPage15FindingsToToc();
        $this->persistDraft(markTab: 'page15', flash: true, flashMessage: 'পৃষ্ঠা ১৫ (৪.৫–৪.৬) সংরক্ষণ হয়েছে।');
        $this->activeTab = 'page16';
    }

    public function savePage16(): void
    {
        $this->ensurePage16Defaults();
        $this->ensurePage17Defaults();
        $this->syncPage16FindingsToToc();
        $this->persistDraft(markTab: 'page16', flash: true, flashMessage: 'পৃষ্ঠা ১৬ (৪.৭–৪.৮) সংরক্ষণ হয়েছে।');
        $this->activeTab = 'page17';
    }

    public function savePage17(): void
    {
        $this->ensurePage17Defaults();
        $this->ensurePage18Defaults();
        $this->syncPage17FindingsToToc();
        $this->persistDraft(markTab: 'page17', flash: true, flashMessage: 'পৃষ্ঠা ১৭ (৪.৯) সংরক্ষণ হয়েছে।');
        $this->activeTab = 'page18';
    }

    public function savePage18(): void
    {
        $this->ensurePage18Defaults();
        $this->syncPage18FindingsToToc();
        $this->persistDraft(markTab: 'page18', flash: true, flashMessage: 'পৃষ্ঠা ১৮ (৪.১০–৪.১১) সংরক্ষণ হয়েছে।');
        $this->activeTab = 'page19';
    }

    public function savePage19(): void
    {
        $this->ensurePage19Defaults();
        $this->syncPage19SectionsToToc();
        $this->persistDraft(markTab: 'page19', flash: true, flashMessage: 'পৃষ্ঠা ১৯ (৫.০০ কমপ্লায়েন্স) সংরক্ষণ হয়েছে।');
        $this->activeTab = 'page20';
    }

    public function savePage20(): void
    {
        $this->ensurePage20Defaults();
        $this->syncPage19SectionsToToc();
        $this->persistDraft(markTab: 'page20', flash: true, flashMessage: 'পৃষ্ঠা ২০ (৬.০০ আইটি) সংরক্ষণ হয়েছে।');
        $this->activeTab = 'page21';
    }

    public function savePage21(): void
    {
        $this->ensurePage21Defaults();
        $this->syncPage21SectionsToToc();
        $this->persistDraft(markTab: 'page21', flash: true, flashMessage: 'পৃষ্ঠা ২১ (৭.০০ বহিঃ নিরীক্ষা) সংরক্ষণ হয়েছে।');
    }

    public function updatedFinancialFindings(mixed $value, ?string $key = null): void
    {
        // reportBlocks owns order; legacy financialFindings is kept for older paths only.
        $this->rebuildTocFromReportBlocks();
    }

    public function updatedFinancialSectionTitle(): void
    {
        $this->ensureReportBlocksDefaults();
        foreach ($this->reportBlocks as $i => $block) {
            if (($block['type'] ?? '') === 'section') {
                $this->reportBlocks[$i]['title'] = $this->financial_section_title;
                break;
            }
        }
        $this->syncSectionsFromReportBlocks();
        $this->rebuildTocFromReportBlocks();
    }

    public function updatedReportSections(mixed $value, ?string $key = null): void
    {
        // reportBlocks is source of truth for order; keep legacy flat findings in sync only.
        $this->syncLegacyFinancialFromReportSections();
        $this->rebuildTocFromReportBlocks();
    }

    public function updatedReportBlocks(mixed $value, ?string $key = null): void
    {
        $this->syncSectionsFromReportBlocks();
        $this->syncLegacyFinancialFromReportSections();
        $this->syncLegacyUtilityFromBlocks();
        $this->rebuildTocFromReportBlocks();
    }

    public function addFinancialFinding(): void
    {
        $this->ensureReportBlocksDefaults();
        $this->insertBlockAt(count($this->reportBlocks), 'finding');
    }

    public function removeFinancialFinding(int $index): void
    {
        $this->ensureReportBlocksDefaults();
        $findingIndexes = [];
        foreach ($this->reportBlocks as $i => $block) {
            if (($block['type'] ?? '') === 'finding') {
                $findingIndexes[] = $i;
            }
        }
        if (! isset($findingIndexes[$index])) {
            return;
        }
        $this->removeBlock($findingIndexes[$index]);
    }

    public function addReportSection(): void
    {
        $this->ensureReportBlocksDefaults();
        $this->insertBlockAt(count($this->reportBlocks), 'section');
    }

    public function removeReportSection(int $sectionIndex): void
    {
        $this->ensureReportBlocksDefaults();
        $sectionIndexes = [];
        foreach ($this->reportBlocks as $i => $block) {
            if (($block['type'] ?? '') === 'section') {
                $sectionIndexes[] = $i;
            }
        }
        if (! isset($sectionIndexes[$sectionIndex])) {
            return;
        }
        $start = $sectionIndexes[$sectionIndex];
        $end = $sectionIndexes[$sectionIndex + 1] ?? count($this->reportBlocks);
        // Don't delete shared criteria/observation trailing blocks unless they're between sections
        $removeUntil = $end;
        for ($i = $start; $i < $end; $i++) {
            $type = $this->reportBlocks[$i]['type'] ?? '';
            if ($this->isUtilityBlockType($type)) {
                $removeUntil = $i;
                break;
            }
        }
        array_splice($this->reportBlocks, $start, $removeUntil - $start);
        $this->reportBlocks = array_values($this->reportBlocks);
        $this->afterBlocksChanged();
    }

    public function addFindingToSection(int $sectionIndex, int $afterFindingIndex = -1): void
    {
        $this->ensureReportBlocksDefaults();
        $sectionIndexes = [];
        foreach ($this->reportBlocks as $i => $block) {
            if (($block['type'] ?? '') === 'section') {
                $sectionIndexes[] = $i;
            }
        }
        if (! isset($sectionIndexes[$sectionIndex])) {
            $this->insertBlockAt(count($this->reportBlocks), 'finding');

            return;
        }

        $start = $sectionIndexes[$sectionIndex];
        $end = $sectionIndexes[$sectionIndex + 1] ?? $this->firstUtilityBlockIndex();
        $findingPositions = [];
        for ($i = $start + 1; $i < $end; $i++) {
            if (($this->reportBlocks[$i]['type'] ?? '') === 'finding') {
                $findingPositions[] = $i;
            }
        }

        if ($afterFindingIndex < 0 || $afterFindingIndex >= count($findingPositions) - 1) {
            $insertAt = $end;
        } else {
            $insertAt = $findingPositions[$afterFindingIndex] + 1;
        }

        $this->insertBlockAt($insertAt, 'finding');
    }

    public function removeFindingFromSection(int $sectionIndex, int $findingIndex): void
    {
        $this->ensureReportBlocksDefaults();
        $sectionIndexes = [];
        foreach ($this->reportBlocks as $i => $block) {
            if (($block['type'] ?? '') === 'section') {
                $sectionIndexes[] = $i;
            }
        }
        if (! isset($sectionIndexes[$sectionIndex])) {
            return;
        }
        $start = $sectionIndexes[$sectionIndex];
        $end = $sectionIndexes[$sectionIndex + 1] ?? $this->firstUtilityBlockIndex();
        $findingPositions = [];
        for ($i = $start + 1; $i < $end; $i++) {
            if (($this->reportBlocks[$i]['type'] ?? '') === 'finding') {
                $findingPositions[] = $i;
            }
        }
        if (! isset($findingPositions[$findingIndex])) {
            return;
        }
        $this->removeBlock($findingPositions[$findingIndex]);
    }

    /**
     * Insert a block at any absolute index (0 = top of page body).
     * $type: section|finding|criteria|observation|stats|custom_table|risk|root_cause|recommendation|jobab_table|followup_pack
     */
    public function insertBlockAt(int $index, string $type = 'finding'): void
    {
        $this->ensureReportBlocksDefaults();
        $allowed = [
            'section', 'finding', 'criteria', 'observation', 'stats', 'custom_table',
            'risk', 'root_cause', 'recommendation', 'jobab_table', 'followup_pack', 'text_box',
        ];
        $type = in_array($type, $allowed, true) ? $type : 'finding';
        $index = max(0, min($index, count($this->reportBlocks)));

        if ($type === 'section') {
            $serial = $this->nextSectionSerialFromBlocks();
            $block = [
                'type' => 'section',
                'serial' => $serial,
                'title' => $serial.' নতুন বিভাগ',
            ];
            array_splice($this->reportBlocks, $index, 0, [$block]);
            array_splice($this->reportBlocks, $index + 1, 0, [[
                'type' => 'finding',
                ...$this->blankFindingRow($this->nextFindingSerialNearIndex($index + 1)),
            ]]);
        } elseif ($type === 'criteria') {
            array_splice($this->reportBlocks, $index, 0, [$this->blankCriteriaBlock('')]);
        } elseif ($type === 'observation' || $type === 'text_box') {
            array_splice($this->reportBlocks, $index, 0, [$this->blankObservationBlock('নতুন বক্স:', '')]);
        } elseif ($type === 'risk') {
            array_splice($this->reportBlocks, $index, 0, [$this->blankRiskBox()]);
        } elseif ($type === 'root_cause') {
            array_splice($this->reportBlocks, $index, 0, [$this->blankRootCauseBox()]);
        } elseif ($type === 'recommendation') {
            array_splice($this->reportBlocks, $index, 0, [$this->blankRecommendationBox()]);
        } elseif ($type === 'jobab_table') {
            array_splice($this->reportBlocks, $index, 0, [$this->blankJobabBlock()]);
        } elseif ($type === 'followup_pack') {
            $pack = [
                $this->blankRiskBox(),
                $this->blankRootCauseBox(),
                $this->blankRecommendationBox(),
                $this->blankJobabBlock(),
            ];
            array_splice($this->reportBlocks, $index, 0, $pack);
        } elseif ($type === 'stats') {
            array_splice($this->reportBlocks, $index, 0, [$this->blankStatsBlock('Report Rating Box:')]);
        } elseif ($type === 'custom_table') {
            array_splice($this->reportBlocks, $index, 0, [CustomTableSchema::blank(4, 5)]);
        } else {
            array_splice($this->reportBlocks, $index, 0, [[
                'type' => 'finding',
                ...$this->blankFindingRow($this->nextFindingSerialNearIndex($index)),
            ]]);
        }

        $this->reportBlocks = array_values($this->reportBlocks);
        $this->afterBlocksChanged();
    }

    public function removeBlock(int $index): void
    {
        if (! isset($this->reportBlocks[$index])) {
            return;
        }

        $type = $this->reportBlocks[$index]['type'] ?? '';

        if ($type === 'section') {
            $end = count($this->reportBlocks);
            for ($i = $index + 1; $i < count($this->reportBlocks); $i++) {
                $t = $this->reportBlocks[$i]['type'] ?? '';
                if ($t === 'section' || $this->isUtilityBlockType($t)) {
                    $end = $i;
                    break;
                }
            }
            // Remove section heading + its findings until next section/utility
            array_splice($this->reportBlocks, $index, $end - $index);
        } else {
            unset($this->reportBlocks[$index]);
        }

        $this->reportBlocks = array_values($this->reportBlocks);
        if ($this->customTableEditorIndex !== null) {
            if ($this->customTableEditorIndex === $index || ($type === 'section' && $this->customTableEditorIndex >= $index)) {
                $this->closeCustomTableEditor();
            } elseif ($this->customTableEditorIndex > $index) {
                $this->customTableEditorIndex--;
            }
        }
        $this->afterBlocksChanged();
    }

    public function moveBlock(int $index, string $direction): void
    {
        $this->ensureReportBlocksDefaults();
        $swapWith = $direction === 'up' ? $index - 1 : $index + 1;
        if (! isset($this->reportBlocks[$index], $this->reportBlocks[$swapWith])) {
            return;
        }

        $tmp = $this->reportBlocks[$index];
        $this->reportBlocks[$index] = $this->reportBlocks[$swapWith];
        $this->reportBlocks[$swapWith] = $tmp;
        $this->afterBlocksChanged();
    }

    public function addObservationBlockRow(int $blockIndex): void
    {
        $this->ensureReportBlocksDefaults();
        if (! isset($this->reportBlocks[$blockIndex]) || ! $this->isStatsLike($this->reportBlocks[$blockIndex]['type'] ?? '')) {
            return;
        }
        $rows = array_values((array) ($this->reportBlocks[$blockIndex]['rows'] ?? []));
        $rows[] = $this->blankObservationRow();
        $this->reportBlocks[$blockIndex]['rows'] = $rows;
        $this->afterBlocksChanged();
    }

    public function removeObservationBlockRow(int $blockIndex, int $rowIndex): void
    {
        $this->ensureReportBlocksDefaults();
        if (! isset($this->reportBlocks[$blockIndex]) || ! $this->isStatsLike($this->reportBlocks[$blockIndex]['type'] ?? '')) {
            return;
        }
        $rows = array_values((array) ($this->reportBlocks[$blockIndex]['rows'] ?? []));
        if (! isset($rows[$rowIndex]) || count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->reportBlocks[$blockIndex]['rows'] = array_values($rows);
        $this->afterBlocksChanged();
    }

    public function applyCustomTableTemplate(int $blockIndex, string $template = 'expense'): void
    {
        if (! isset($this->reportBlocks[$blockIndex]) || ($this->reportBlocks[$blockIndex]['type'] ?? '') !== 'custom_table') {
            return;
        }
        $this->reportBlocks[$blockIndex] = $template === 'blank'
            ? CustomTableSchema::blank(4, 5)
            : CustomTableSchema::expenseVatTaxTemplate();
        $block = CustomTableSchema::normalize($this->reportBlocks[$blockIndex]);
        $this->reportBlocks[$blockIndex] = $block;
        if ($this->customTableEditorIndex === $blockIndex) {
            $this->customTableSizeCols = max(1, count($block['columns']));
            $this->customTableSizeRows = max(1, count($block['rows']));
            $this->customTableSelR = null;
            $this->customTableSelC = null;
        }
        // No TOC/autosave on every tweak — saved when editor closes
    }

    public function addCustomTableColumn(int $blockIndex, ?string $parentId = null): void
    {
        if (! $this->mutateCustomTable($blockIndex, function (array $block) use ($parentId) {
            $columns = $block['columns'];
            $parentPath = $parentId
                ? CustomTableSchema::findColumnPath($columns, $parentId)
                : null;
            if ($parentId !== null && $parentPath === null) {
                return $block;
            }
            $label = $parentPath === null ? 'কলাম '.(count($columns) + 1) : 'সাব-কলাম';
            $block['columns'] = CustomTableSchema::addColumn(
                $columns,
                $parentPath,
                CustomTableSchema::columnNode($label)
            );

            return CustomTableSchema::syncRowWidths($block);
        })) {
            return;
        }
        if ($this->customTableEditorIndex === $blockIndex) {
            $this->customTableSizeCols = max(1, count($this->reportBlocks[$blockIndex]['columns'] ?? []));
        }
    }

    public function removeCustomTableColumn(int $blockIndex, string $columnId): void
    {
        if (! $this->mutateCustomTable($blockIndex, function (array $block) use ($columnId) {
            $path = CustomTableSchema::findColumnPath($block['columns'], $columnId);
            if ($path === null) {
                return $block;
            }
            if (count($path) === 1 && count($block['columns']) <= 1) {
                return $block;
            }
            $block['columns'] = CustomTableSchema::removeColumnAt($block['columns'], $path);

            return CustomTableSchema::syncRowWidths($block);
        })) {
            return;
        }
        if ($this->customTableEditorIndex === $blockIndex) {
            $this->customTableSizeCols = max(1, count($this->reportBlocks[$blockIndex]['columns'] ?? []));
        }
    }

    public function addCustomTableRow(int $blockIndex): void
    {
        if (! $this->mutateCustomTable($blockIndex, function (array $block) {
            $leaf = CustomTableSchema::leafCount($block['columns']);
            $block['rows'][] = CustomTableSchema::blankRow($leaf);

            return $block;
        })) {
            return;
        }
        if ($this->customTableEditorIndex === $blockIndex) {
            $this->customTableSizeRows = max(1, count($this->reportBlocks[$blockIndex]['rows'] ?? []));
        }
    }

    public function removeCustomTableRow(int $blockIndex, int $rowIndex): void
    {
        if (! $this->mutateCustomTable($blockIndex, function (array $block) use ($rowIndex) {
            $rows = $block['rows'];
            if (! isset($rows[$rowIndex]) || count($rows) <= 1) {
                return $block;
            }
            unset($rows[$rowIndex]);
            $block['rows'] = array_values($rows);
            $block['merges'] = CustomTableSchema::normalizeMerges(
                $block['merges'] ?? [],
                count($block['rows']),
                CustomTableSchema::leafCount($block['columns'])
            );

            return $block;
        })) {
            return;
        }
        if ($this->customTableEditorIndex === $blockIndex) {
            $this->customTableSizeRows = max(1, count($this->reportBlocks[$blockIndex]['rows'] ?? []));
        }
    }

    public function toggleCustomTableTotalRow(int $blockIndex, int $rowIndex): void
    {
        $this->mutateCustomTable($blockIndex, function (array $block) use ($rowIndex) {
            if (! isset($block['rows'][$rowIndex])) {
                return $block;
            }
            $isTotal = ! (bool) ($block['rows'][$rowIndex]['is_total'] ?? false);
            $block['rows'][$rowIndex]['is_total'] = $isTotal;
            if ($isTotal) {
                $block['rows'][$rowIndex]['lead_colspan'] = max(1, min(
                    CustomTableSchema::leafCount($block['columns']),
                    (int) ($block['rows'][$rowIndex]['lead_colspan'] ?? 3)
                ));
            } else {
                $block['rows'][$rowIndex]['lead_colspan'] = 1;
            }

            return $block;
        });
    }

    public function openCustomTableEditor(int $blockIndex): void
    {
        if (! isset($this->reportBlocks[$blockIndex]) || ($this->reportBlocks[$blockIndex]['type'] ?? '') !== 'custom_table') {
            return;
        }
        $block = CustomTableSchema::normalize($this->reportBlocks[$blockIndex]);
        $this->reportBlocks[$blockIndex] = $block;
        $this->customTableEditorIndex = $blockIndex;
        $this->customTableSelR = null;
        $this->customTableSelC = null;
        $this->customTableMergeRows = 2;
        $this->customTableMergeCols = 1;
        $this->customTableSizeCols = max(1, count($block['columns']));
        $this->customTableSizeRows = max(1, count($block['rows']));
        $this->customTableMergeHistory = [];
    }

    public function closeCustomTableEditor(): void
    {
        $this->customTableEditorIndex = null;
        $this->customTableSelR = null;
        $this->customTableSelC = null;
        $this->customTableMergeHistory = [];
        // One save when leaving — not on every click/keystroke
        $this->afterBlocksChanged();
        $this->js('delete document.body.dataset.ctEditor');
    }

    /** Instant selection — no HTML re-render. */
    public function selectCustomTableCell(int $r, int $c): void
    {
        if ($this->customTableEditorIndex === null) {
            return;
        }
        $this->customTableSelR = max(0, $r);
        $this->customTableSelC = max(0, $c);
        $this->skipRender();
    }

    public function applyCustomTableMerge(?int $r = null, ?int $c = null, ?int $rowspan = null, ?int $colspan = null): void
    {
        $blockIndex = $this->customTableEditorIndex;
        $r = $r ?? $this->customTableSelR;
        $c = $c ?? $this->customTableSelC;
        if ($blockIndex === null || $r === null || $c === null) {
            return;
        }
        $rs = max(1, min(100, $rowspan ?? $this->customTableMergeRows));
        $cs = max(1, min(20, $colspan ?? $this->customTableMergeCols));
        $this->customTableSelR = $r;
        $this->customTableSelC = $c;
        $this->customTableMergeRows = $rs;
        $this->customTableMergeCols = $cs;
        $this->pushCustomTableMergeHistory($blockIndex);
        $this->mutateCustomTable($blockIndex, function (array $block) use ($r, $c, $rs, $cs) {
            return CustomTableSchema::setMerge($block, $r, $c, $rs, $cs);
        });
    }

    public function clearCustomTableMerge(?int $r = null, ?int $c = null): void
    {
        $blockIndex = $this->customTableEditorIndex;
        $r = $r ?? $this->customTableSelR;
        $c = $c ?? $this->customTableSelC;
        if ($blockIndex === null || $r === null || $c === null) {
            return;
        }
        $this->pushCustomTableMergeHistory($blockIndex);
        $this->mutateCustomTable($blockIndex, function (array $block) use ($r, $c) {
            return CustomTableSchema::clearMergeAt($block, $r, $c);
        });
    }

    /** Grow/shrink selected merge by 1 (e.g. one less row). */
    public function adjustCustomTableMerge(?int $r = null, ?int $c = null, int $deltaRows = 0, int $deltaCols = 0): void
    {
        $blockIndex = $this->customTableEditorIndex;
        $r = $r ?? $this->customTableSelR;
        $c = $c ?? $this->customTableSelC;
        if ($blockIndex === null || $r === null || $c === null) {
            return;
        }
        if ($deltaRows === 0 && $deltaCols === 0) {
            return;
        }
        $this->pushCustomTableMergeHistory($blockIndex);
        $this->mutateCustomTable($blockIndex, function (array $block) use ($r, $c, $deltaRows, $deltaCols) {
            return CustomTableSchema::adjustMerge($block, $r, $c, $deltaRows, $deltaCols);
        });
        $merge = CustomTableSchema::mergeAt($this->reportBlocks[$blockIndex], $r, $c);
        if ($merge !== null) {
            $this->customTableSelR = $merge['r'];
            $this->customTableSelC = $merge['c'];
            $this->customTableMergeRows = $merge['rowspan'];
            $this->customTableMergeCols = $merge['colspan'];
        } else {
            $this->customTableMergeRows = 1;
            $this->customTableMergeCols = 1;
        }
    }

    public function undoCustomTableMerge(): void
    {
        $entry = array_pop($this->customTableMergeHistory);
        if ($entry === null) {
            return;
        }
        $blockIndex = (int) ($entry['block'] ?? -1);
        if ($blockIndex < 0 || ! isset($this->reportBlocks[$blockIndex]) || ($this->reportBlocks[$blockIndex]['type'] ?? '') !== 'custom_table') {
            return;
        }
        $this->mutateCustomTable($blockIndex, function (array $block) use ($entry) {
            $block['merges'] = array_values((array) ($entry['merges'] ?? []));

            return $block;
        });
    }

    /**
     * @param  list<array<string,mixed>>|null  $merges
     */
    protected function pushCustomTableMergeHistory(int $blockIndex): void
    {
        if (! isset($this->reportBlocks[$blockIndex])) {
            return;
        }
        $merges = array_values((array) ($this->reportBlocks[$blockIndex]['merges'] ?? []));
        $this->customTableMergeHistory[] = [
            'block' => $blockIndex,
            'merges' => $merges,
        ];
        if (count($this->customTableMergeHistory) > 30) {
            $this->customTableMergeHistory = array_slice($this->customTableMergeHistory, -30);
        }
    }

    public function resizeCustomTable(?int $cols = null, ?int $rows = null): void
    {
        $blockIndex = $this->customTableEditorIndex;
        if ($blockIndex === null) {
            return;
        }
        $cols = max(1, min(20, $cols ?? $this->customTableSizeCols));
        $rows = max(1, min(100, $rows ?? $this->customTableSizeRows));
        $this->customTableSizeCols = $cols;
        $this->customTableSizeRows = $rows;
        if (! $this->mutateCustomTable($blockIndex, function (array $block) use ($cols, $rows) {
            return CustomTableSchema::resize($block, $cols, $rows);
        })) {
            return;
        }
        $this->customTableSelR = null;
        $this->customTableSelC = null;
    }

    public function setCustomTableLeafWidth(int $blockIndex, string $leafId, $width): void
    {
        $w = is_numeric($width) ? (float) $width : 0.0;
        if ($w <= 0) {
            return;
        }
        $this->mutateCustomTable($blockIndex, function (array $block) use ($leafId, $w) {
            $block['columns'] = CustomTableSchema::setLeafWidth($block['columns'], $leafId, $w);

            return $block;
        });
    }

    /** Sync cell text without re-rendering the wizard. */
    public function setCustomTableCell(int $blockIndex, int $rowIndex, int $colIndex, string $value): void
    {
        if (! isset($this->reportBlocks[$blockIndex]) || ($this->reportBlocks[$blockIndex]['type'] ?? '') !== 'custom_table') {
            return;
        }
        if (! isset($this->reportBlocks[$blockIndex]['rows'][$rowIndex])) {
            return;
        }
        $cells = array_values((array) ($this->reportBlocks[$blockIndex]['rows'][$rowIndex]['cells'] ?? []));
        while (count($cells) <= $colIndex) {
            $cells[] = '';
        }
        $cells[$colIndex] = $value;
        $this->reportBlocks[$blockIndex]['rows'][$rowIndex]['cells'] = $cells;
        $this->skipRender();
    }

    public function setCustomTableTitle(int $blockIndex, string $title): void
    {
        if (! isset($this->reportBlocks[$blockIndex]) || ($this->reportBlocks[$blockIndex]['type'] ?? '') !== 'custom_table') {
            return;
        }
        $this->reportBlocks[$blockIndex]['title'] = $title;
        $this->skipRender();
    }

    public function setCustomTableColumnLabel(int $blockIndex, string $columnId, string $label): void
    {
        if (! $this->mutateCustomTable($blockIndex, function (array $block) use ($columnId, $label) {
            $path = CustomTableSchema::findColumnPath($block['columns'], $columnId);
            if ($path === null) {
                return $block;
            }
            $col = CustomTableSchema::getColumnAt($block['columns'], $path);
            if ($col === null) {
                return $block;
            }
            $col['label'] = $label;
            $block['columns'] = CustomTableSchema::setColumnAt($block['columns'], $path, $col);

            return $block;
        })) {
            return;
        }
        $this->skipRender();
    }

    /**
     * @param  callable(array<string,mixed>): array<string,mixed>  $mutator
     */
    protected function mutateCustomTable(int $blockIndex, callable $mutator): bool
    {
        if (! isset($this->reportBlocks[$blockIndex]) || ($this->reportBlocks[$blockIndex]['type'] ?? '') !== 'custom_table') {
            return false;
        }
        $block = CustomTableSchema::normalize($this->reportBlocks[$blockIndex]);
        $this->reportBlocks[$blockIndex] = CustomTableSchema::normalize($mutator($block));

        return true;
    }

    public function applySectionFindingIndicator(int $sectionIndex, int $findingIndex, ?int $indicatorId, string $title): void
    {
        $this->ensureReportBlocksDefaults();
        // Map section/finding indexes onto flat blocks
        $sectionIndexes = [];
        foreach ($this->reportBlocks as $i => $block) {
            if (($block['type'] ?? '') === 'section') {
                $sectionIndexes[] = $i;
            }
        }
        if (! isset($sectionIndexes[$sectionIndex])) {
            return;
        }
        $start = $sectionIndexes[$sectionIndex];
        $end = $sectionIndexes[$sectionIndex + 1] ?? $this->firstUtilityBlockIndex();
        $findingPositions = [];
        for ($i = $start + 1; $i < $end; $i++) {
            if (($this->reportBlocks[$i]['type'] ?? '') === 'finding') {
                $findingPositions[] = $i;
            }
        }
        if (! isset($findingPositions[$findingIndex])) {
            return;
        }

        $this->applyBlockFindingIndicator($findingPositions[$findingIndex], $indicatorId, $title);
    }

    public function applyBlockFindingIndicator(int $blockIndex, ?int $indicatorId, string $title): void
    {
        $this->ensureReportBlocksDefaults();
        if (! isset($this->reportBlocks[$blockIndex]) || ($this->reportBlocks[$blockIndex]['type'] ?? '') !== 'finding') {
            return;
        }

        $title = trim($title);
        if ($title === '') {
            return;
        }

        $indicator = $this->resolveOrCreateIndicator($indicatorId, $title, 'নিরীক্ষা প্রতিবেদন');
        $this->reportBlocks[$blockIndex]['title'] = 'শিরোনাম';
        $this->reportBlocks[$blockIndex]['body'] = $indicator->title;
        $this->reportBlocks[$blockIndex]['indicator_id'] = $indicator->id;
        $this->reportBlocks[$blockIndex]['indicator_code'] = $indicator->indicator_code;
        if (blank($this->reportBlocks[$blockIndex]['rating'] ?? null) && filled($indicator->risk_rating)) {
            $this->reportBlocks[$blockIndex]['rating'] = $this->mapRiskToFindingRating((string) $indicator->risk_rating);
        }

        $this->afterBlocksChanged();
        $this->autoSaveHint = 'Indicator সংযুক্ত · সূচিপত্রে আপডেট';
    }

    protected function afterBlocksChanged(): void
    {
        $this->syncSectionsFromReportBlocks();
        $this->syncLegacyFinancialFromReportSections();
        $this->syncLegacyUtilityFromBlocks();
        $this->rebuildTocFromReportBlocks();
        if ($this->reportId) {
            $this->autoSaveDraft();
        }
    }

    protected function firstUtilityBlockIndex(): int
    {
        foreach ($this->reportBlocks as $i => $block) {
            if ($this->isUtilityBlockType($block['type'] ?? '')) {
                return $i;
            }
        }

        return count($this->reportBlocks);
    }

    protected function isUtilityBlockType(string $type): bool
    {
        return in_array($type, [
            'criteria', 'observation', 'stats', 'custom_table', 'vat', 'tax',
            'jobab_table', 'text_box',
        ], true);
    }

    protected function isStatsLike(string $type): bool
    {
        return in_array($type, ['stats', 'vat', 'tax'], true);
    }

    /** @deprecated */
    protected function isObservationLike(string $type): bool
    {
        return $this->isStatsLike($type) || $type === 'observation';
    }

    /**
     * @return array{type:string,label:string,body:string}
     */
    protected function blankCriteriaBlock(?string $body = null): array
    {
        $default = 'প্রতিষ্ঠানের নির্দেশনা ও জাতীয় রাজস্ব বোর্ড (এনবিআর)-এর নির্দেশনা অনুযায়ী প্রযোজ্য ভ্যাট ও ট্যাক্স নির্ধারিত হারে সরকারি কোষাগারে জমা দিতে হবে।';

        return [
            'type' => 'criteria',
            'label' => 'প্রচলিত নিয়ম (Criteria):',
            'body' => $body !== null ? $body : ($this->financial_criteria !== '' ? $this->financial_criteria : $default),
        ];
    }

    /**
     * Text-only observation (পর্যবেক্ষণ লিখুন).
     *
     * @return array{type:string,label:string,body:string}
     */
    protected function blankObservationBlock(string $label = 'পর্যবেক্ষণ (Observation) :', string $body = ''): array
    {
        return [
            'type' => 'observation',
            'label' => $label,
            'body' => $body,
        ];
    }

    /** @return array{type:string,label:string,body:string} */
    protected function blankRiskBox(): array
    {
        return $this->blankObservationBlock(
            'ঝুঁকি/প্রভাব (Risk/Implication) :',
            'এনবিআর এর নির্দেশনা অনুসরণ না করায় বহিঃনিরীক্ষা কর্তৃক আপত্তি উত্থাপিত হওয়ার আশঙ্কা।'
        );
    }

    /** @return array{type:string,label:string,body:string} */
    protected function blankRootCauseBox(): array
    {
        return $this->blankObservationBlock(
            'মূল কারণ (Root Cause):',
            'ব্যবস্থাপনা কর্তৃক সঠিক নির্দেশনা প্রদান না করা।'
        );
    }

    /** @return array{type:string,label:string,body:string} */
    protected function blankRecommendationBox(): array
    {
        return $this->blankObservationBlock(
            'সুপারিশ (Recommendation) :',
            'প্রযোজ্য সকল ক্ষেত্রে ট্যাক্স প্রদান নিশ্চিত করা।'
        );
    }

    /**
     * Branch manager reply / responsibility / timeline table (জবাব).
     *
     * @return array{type:string,rows:list<array{cells:list<string>}>}
     */
    protected function blankJobabBlock(): array
    {
        return [
            'type' => 'jobab_table',
            'rows' => [
                ['cells' => ['শাখা ব্যবস্থাপকের জবাব', '']],
                ['cells' => ['সমস্যা সমাধানের ক্ষেত্রে দায়িত্বপ্রাপ্ত কর্মীর নাম/আইডি ও গৃহীত পদক্ষেপ', '']],
                ['cells' => ['সমাধানের প্রকৃত সময়কাল/সম্ভাব্য সময়কাল (তারিখ)', '']],
            ],
        ];
    }

    /**
     * @param  array<string,mixed>  $block
     * @return array{type:string,rows:list<array{cells:list<string>}>}
     */
    protected function normalizeJobabBlock(array $block): array
    {
        $rows = [];
        $colCount = 2;
        foreach (array_values((array) ($block['rows'] ?? [])) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $cells = array_values(array_map('strval', (array) ($row['cells'] ?? [])));
            $colCount = max($colCount, count($cells), 2);
            $rows[] = ['cells' => $cells];
        }
        if ($rows === []) {
            return $this->blankJobabBlock();
        }
        foreach ($rows as $i => $row) {
            $cells = $row['cells'];
            while (count($cells) < $colCount) {
                $cells[] = '';
            }
            if (count($cells) > $colCount) {
                $cells = array_slice($cells, 0, $colCount);
            }
            $rows[$i] = ['cells' => $cells];
        }

        return [
            'type' => 'jobab_table',
            'rows' => $rows,
        ];
    }

    public function addJobabRow(int $blockIndex): void
    {
        if (! isset($this->reportBlocks[$blockIndex]) || ($this->reportBlocks[$blockIndex]['type'] ?? '') !== 'jobab_table') {
            return;
        }
        $block = $this->normalizeJobabBlock($this->reportBlocks[$blockIndex]);
        $cols = count($block['rows'][0]['cells'] ?? []) ?: 2;
        $block['rows'][] = ['cells' => array_fill(0, $cols, '')];
        $this->reportBlocks[$blockIndex] = $block;
        $this->afterBlocksChanged();
    }

    public function removeJobabRow(int $blockIndex, int $rowIndex): void
    {
        if (! isset($this->reportBlocks[$blockIndex]) || ($this->reportBlocks[$blockIndex]['type'] ?? '') !== 'jobab_table') {
            return;
        }
        $block = $this->normalizeJobabBlock($this->reportBlocks[$blockIndex]);
        if (! isset($block['rows'][$rowIndex]) || count($block['rows']) <= 1) {
            return;
        }
        unset($block['rows'][$rowIndex]);
        $block['rows'] = array_values($block['rows']);
        $this->reportBlocks[$blockIndex] = $block;
        $this->afterBlocksChanged();
    }

    public function addJobabColumn(int $blockIndex): void
    {
        if (! isset($this->reportBlocks[$blockIndex]) || ($this->reportBlocks[$blockIndex]['type'] ?? '') !== 'jobab_table') {
            return;
        }
        $block = $this->normalizeJobabBlock($this->reportBlocks[$blockIndex]);
        foreach ($block['rows'] as $i => $row) {
            $block['rows'][$i]['cells'][] = '';
        }
        $this->reportBlocks[$blockIndex] = $block;
        $this->afterBlocksChanged();
    }

    public function removeJobabColumn(int $blockIndex): void
    {
        if (! isset($this->reportBlocks[$blockIndex]) || ($this->reportBlocks[$blockIndex]['type'] ?? '') !== 'jobab_table') {
            return;
        }
        $block = $this->normalizeJobabBlock($this->reportBlocks[$blockIndex]);
        $cols = count($block['rows'][0]['cells'] ?? []);
        if ($cols <= 1) {
            return;
        }
        foreach ($block['rows'] as $i => $row) {
            $cells = $row['cells'];
            array_pop($cells);
            $block['rows'][$i]['cells'] = $cells;
        }
        $this->reportBlocks[$blockIndex] = $block;
        $this->afterBlocksChanged();
    }

    /**
     * Report Rating Box (stats table).
     *
     * @param  list<array{total_population:string,sample_size:string,instances_found:string,percentage:string}>|null  $rows
     * @return array{type:string,heading:string,rows:list<array<string,string>>}
     */
    protected function blankStatsBlock(string $heading = 'Report Rating Box:', ?array $rows = null): array
    {
        $heading = $this->normalizeStatsHeading($heading);
        $normalizedRows = [];
        foreach (array_values($rows ?? [$this->blankObservationRow()]) as $row) {
            $normalizedRows[] = array_merge($this->blankObservationRow(), is_array($row) ? $row : []);
        }
        if ($normalizedRows === []) {
            $normalizedRows = [$this->blankObservationRow()];
        }

        return [
            'type' => 'stats',
            'heading' => $heading,
            'rows' => $normalizedRows,
        ];
    }

    protected function normalizeStatsHeading(string $heading): string
    {
        $heading = trim($heading);
        $legacy = [
            'ভ্যাট সংক্রান্ত:',
            'ট্যাক্স সংক্রান্ত:',
            'সারণী:',
            'নতুন সারণী:',
            'সারণী (VAT/Tax)',
        ];
        if ($heading === '' || in_array($heading, $legacy, true)) {
            return 'Report Rating Box:';
        }

        return $heading;
    }

    protected function syncLegacyUtilityFromBlocks(): void
    {
        $firstCriteria = null;
        $statsBlocks = [];
        foreach ($this->reportBlocks as $block) {
            $type = $block['type'] ?? '';
            if ($type === 'criteria' && $firstCriteria === null) {
                $firstCriteria = $block;
            }
            if ($this->isStatsLike($type)) {
                $statsBlocks[] = $block;
            }
        }

        if ($firstCriteria !== null) {
            $this->financial_criteria = (string) ($firstCriteria['body'] ?? $this->financial_criteria);
        }

        if (isset($statsBlocks[0])) {
            $rows = array_values((array) ($statsBlocks[0]['rows'] ?? []));
            $this->vatObservationRows = $rows !== [] ? $rows : [$this->blankObservationRow()];
        }
        if (isset($statsBlocks[1])) {
            $rows = array_values((array) ($statsBlocks[1]['rows'] ?? []));
            $this->taxObservationRows = $rows !== [] ? $rows : [$this->blankObservationRow()];
        }
    }

    /**
     * Stable HTML/PDF anchor for a TOC ↔ finding serial (e.g. ১.১ → finding-1-1).
     */
    public static function findingAnchorId(?string $serial): string
    {
        $serial = trim((string) $serial);
        if ($serial === '') {
            return '';
        }

        $digits = ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9'];
        $ascii = strtr($serial, $digits);
        $slug = preg_replace('/[^A-Za-z0-9]+/', '-', $ascii) ?? '';
        $slug = trim($slug, '-');

        return $slug !== '' ? 'finding-'.$slug : '';
    }

    public static function sectionAnchorId(?string $serial): string
    {
        $id = self::findingAnchorId($serial);

        return $id !== '' ? str_replace('finding-', 'section-', $id) : '';
    }

    /**
     * Left outline: headlines (not page numbers). Click → switch tab + scroll.
     *
     * @return list<array{kind:string,label:string,tab:string,anchor:string,depth:int}>
     */
    public function outlineNavItems(): array
    {
        $this->ensureReportBlocksDefaults();
        $items = [
            ['kind' => 'fixed', 'label' => 'Cover Page', 'tab' => 'cover', 'anchor' => 'audit-cover', 'depth' => 0],
            ['kind' => 'fixed', 'label' => 'এক নজরে', 'tab' => 'page2', 'anchor' => 'audit-page2', 'depth' => 0],
            ['kind' => 'fixed', 'label' => 'সূচিপত্র ও শ্রেণীবিন্যাস', 'tab' => 'page3', 'anchor' => 'audit-page3', 'depth' => 0],
        ];

        foreach ($this->reportBlocks as $block) {
            $type = $block['type'] ?? '';
            if ($type === 'section') {
                $serial = trim((string) ($block['serial'] ?? ''));
                $title = trim((string) ($block['title'] ?? ''));
                $label = $title !== '' ? $title : ($serial !== '' ? $serial : 'বিভাগ');
                $items[] = [
                    'kind' => 'section',
                    'label' => $label,
                    'tab' => 'page4',
                    'anchor' => self::sectionAnchorId($serial !== '' ? $serial : $title),
                    'depth' => 0,
                ];
            } elseif ($type === 'finding') {
                $serial = trim((string) ($block['serial'] ?? ''));
                $body = trim((string) ($block['body'] ?? ''));
                $title = trim((string) ($block['title'] ?? ''));
                $text = $body !== '' ? $body : ($title !== '' && $title !== 'শিরোনাম' ? $title : 'শিরোনাম');
                $short = mb_strlen($text) > 48 ? mb_substr($text, 0, 48).'…' : $text;
                $label = $serial !== '' ? $serial.' '.$short : $short;
                $items[] = [
                    'kind' => 'finding',
                    'label' => $label,
                    'tab' => 'page4',
                    'anchor' => self::findingAnchorId($serial),
                    'depth' => 1,
                ];
            }
        }

        return $items;
    }

    public function goToOutlineItem(string $tab, string $anchor = ''): void
    {
        if (! in_array($tab, ['cover', 'page2', 'page3', 'page4'], true)) {
            return;
        }
        $this->activeTab = $tab;
        if ($anchor !== '') {
            $safe = json_encode($anchor, JSON_UNESCAPED_UNICODE);
            $this->js('setTimeout(() => { const el = document.getElementById('.$safe.'); if (el) el.scrollIntoView({ behavior: "smooth", block: "start" }); }, 160)');
        }
    }

    public function addVatObservationRow(): void
    {
        $this->ensureFinancialAuditDefaults();
        $this->vatObservationRows[] = $this->blankObservationRow();
    }

    public function removeVatObservationRow(int $index): void
    {
        if (! isset($this->vatObservationRows[$index]) || count($this->vatObservationRows) <= 1) {
            return;
        }

        unset($this->vatObservationRows[$index]);
        $this->vatObservationRows = array_values($this->vatObservationRows);
    }

    public function addTaxObservationRow(): void
    {
        $this->ensureFinancialAuditDefaults();
        $this->taxObservationRows[] = $this->blankObservationRow();
    }

    public function removeTaxObservationRow(int $index): void
    {
        if (! isset($this->taxObservationRows[$index]) || count($this->taxObservationRows) <= 1) {
            return;
        }

        unset($this->taxObservationRows[$index]);
        $this->taxObservationRows = array_values($this->taxObservationRows);
    }

    public function addGlanceRow(): void
    {
        $this->glanceRows[] = [
            'left_label' => '',
            'left_value' => '',
            'right_label' => '',
            'right_value' => '',
        ];
    }

    public function removeGlanceRow(int $index): void
    {
        if (! isset($this->glanceRows[$index]) || count($this->glanceRows) <= 1) {
            return;
        }

        unset($this->glanceRows[$index]);
        $this->glanceRows = array_values($this->glanceRows);
    }

    public function addStaffRow(): void
    {
        $this->ensurePage2Defaults();
        $this->staffRows[] = [
            'cells' => array_fill(0, count($this->staffColumns), ''),
        ];
    }

    public function removeStaffRow(int $index): void
    {
        if (! isset($this->staffRows[$index]) || count($this->staffRows) <= 1) {
            return;
        }

        unset($this->staffRows[$index]);
        $this->staffRows = array_values($this->staffRows);
    }

    public function addStaffColumn(): void
    {
        $this->ensurePage2Defaults();
        $this->staffColumns[] = 'নতুন কলাম';
        foreach ($this->staffRows as $i => $row) {
            $cells = $row['cells'] ?? [];
            $cells[] = '';
            $this->staffRows[$i]['cells'] = $cells;
        }
    }

    public function removeStaffColumn(int $index): void
    {
        if (! isset($this->staffColumns[$index]) || count($this->staffColumns) <= 1) {
            return;
        }

        unset($this->staffColumns[$index]);
        $this->staffColumns = array_values($this->staffColumns);

        foreach ($this->staffRows as $i => $row) {
            $cells = $row['cells'] ?? [];
            if (isset($cells[$index])) {
                unset($cells[$index]);
            }
            $this->staffRows[$i]['cells'] = array_values($cells);
        }
    }

    public function addTocRow(int $afterIndex = -1, int $previewPage = 2): void
    {
        $this->ensureTocDefaults();
        $row = [
            'type' => 'item',
            'serial' => '',
            'finding' => '',
            'amount' => '',
            'rating' => '',
            'status' => '',
            'page_no' => '',
            'preview_page' => $previewPage,
        ];

        $this->insertTocRow($row, $afterIndex, $previewPage);
    }

    public function addTocSection(int $afterIndex = -1, int $previewPage = 2): void
    {
        $this->ensureTocDefaults();
        $row = [
            'type' => 'section',
            'serial' => '',
            'finding' => '',
            'amount' => '',
            'rating' => '',
            'status' => '',
            'page_no' => '',
            'preview_page' => $previewPage,
        ];

        $this->insertTocRow($row, $afterIndex, $previewPage);
    }

    protected function insertTocRow(array $row, int $afterIndex, int $previewPage): void
    {
        if ($afterIndex >= 0 && $afterIndex < count($this->tocRows)) {
            array_splice($this->tocRows, $afterIndex + 1, 0, [$row]);

            return;
        }

        $lastForPage = null;
        foreach ($this->tocRows as $i => $existing) {
            if ((int) ($existing['preview_page'] ?? 2) === $previewPage) {
                $lastForPage = $i;
            }
        }

        if ($lastForPage === null) {
            $this->tocRows[] = $row;
        } else {
            array_splice($this->tocRows, $lastForPage + 1, 0, [$row]);
        }
    }

    public function removeTocRow(int $index): void
    {
        if (! isset($this->tocRows[$index]) || count($this->tocRows) <= 1) {
            return;
        }

        unset($this->tocRows[$index]);
        $this->tocRows = array_values($this->tocRows);
    }

    public function openPreview(): void
    {
        $this->showPreview = true;
    }

    public function closePreview(): void
    {
        $this->showPreview = false;
    }

    public function downloadPdf(): StreamedResponse
    {
        $data = $this->reportViewData();
        $filename = 'audit-report-'.$this->reportExportBasename().'.pdf';
        $binary = app(AuditReportPdfService::class)->output($data);

        return response()->streamDownload(
            function () use ($binary) {
                echo $binary;
            },
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    public function downloadDoc(): StreamedResponse
    {
        $data = $this->reportViewData();
        $filename = 'audit-report-'.$this->reportExportBasename().'.docx';
        $binary = app(AuditReportDocService::class)->output($data);

        return response()->streamDownload(
            function () use ($binary) {
                echo $binary;
            },
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        );
    }

    protected function reportExportBasename(): string
    {
        $safeName = preg_replace('/[^\pL\pN\-]+/u', '-', trim($this->shakha_display_name ?: 'audit')) ?: 'audit';

        return $safeName.'-'.$this->report_year.'-'.$this->report_month;
    }

    /**
     * @return array<string, mixed>
     */
    protected function reportViewData(): array
    {
        $this->ensurePage2Defaults();
        $this->ensureTocDefaults();
        $this->ensureSignatureDefaults();
        $this->ensureFinancialAuditDefaults();
        $this->ensurePage5Defaults();
        $this->ensurePage6Defaults();
        $this->ensurePage7Defaults();
        $this->ensurePage8Defaults();
        $this->ensurePage9Defaults();
        $this->ensurePage10Defaults();
        $this->ensurePage11Defaults();
        $this->ensurePage12Defaults();
        $this->ensurePage13Defaults();
        $this->ensurePage14Defaults();
        $this->ensurePage15Defaults();
        $this->ensurePage16Defaults();
        $this->ensurePage17Defaults();
        $this->ensurePage18Defaults();
        $this->ensurePage19Defaults();
        $this->ensurePage20Defaults();
        $this->ensurePage21Defaults();

        $logoDataUri = null;
        $logoPath = null;
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            $binary = Storage::disk('public')->get($this->logo_path);
            $mime = Storage::disk('public')->mimeType($this->logo_path) ?: 'image/png';
            $logoDataUri = 'data:'.$mime.';base64,'.base64_encode($binary);
            $logoPath = Storage::disk('public')->path($this->logo_path);
        }

        $document = $this->stampedDocument();

        return [
            'fontRegular' => storage_path('fonts/NotoSansBengali-Regular.ttf'),
            'fontBold' => storage_path('fonts/NotoSansBengali-Bold.ttf'),
            'logoDataUri' => $logoDataUri,
            'logoPath' => $logoPath,
            'documentSheets' => $document['sheets'],
            'ratingColor' => AuditReport::ratingColor($this->control_rating),
            'control_rating' => $this->control_rating,
            'memo_no' => $this->memo_no,
            'report_date' => $this->report_date,
            'shakha_display_name' => $this->shakha_display_name,
            'area_display_name' => $this->area_display_name,
            'audit_period_label' => $this->audit_period_label,
            'audit_start_date' => $this->audit_start_date,
            'audit_end_date' => $this->audit_end_date,
            'working_days' => $this->working_days,
            'period_scope' => $this->period_scope,
            'draft_sent_date' => $this->draft_sent_date,
            'comments_received_date' => $this->comments_received_date,
            'auditor_name' => $this->auditor_name,
            'auditor_designation' => $this->auditor_designation,
            'glance_as_of' => $this->glance_as_of,
            'branch_opening_date' => $this->branch_opening_date,
            'staff_info_as_of' => $this->staff_info_as_of,
            'glanceRows' => $this->glanceRows,
            'staffColumns' => $this->staffColumns,
            'staffRows' => $this->staffRows,
            'tableHeaders' => $this->tableHeaders,
            'sign_auditor_name' => $this->sign_auditor_name,
            'sign_auditor_designation' => $this->sign_auditor_designation,
            'sign_auditor_date' => $this->sign_auditor_date,
            'sign_bm_name' => $this->sign_bm_name,
            'sign_bm_date' => $this->sign_bm_date,
            'sign_abm_name' => $this->sign_abm_name,
            'sign_abm_date' => $this->sign_abm_date,
            'financial_section_title' => $this->financial_section_title,
            'financialFindings' => $this->financialFindings,
            'financial_criteria' => $this->financial_criteria,
            'vatObservationRows' => $this->vatObservationRows,
            'taxObservationRows' => $this->taxObservationRows,
            ...$this->page4Payload(),
        ];
    }

    public function backToSelect(bool $saveFirst = true): void
    {
        if ($saveFirst && $this->reportId && $this->step === 'wizard') {
            try {
                $this->persistDraft(markTab: null, flash: false);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $this->step = 'select';
        $this->reportId = null;
        $this->activeTab = 'cover';
        $this->showPreview = false;
        $this->logoUpload = null;
        $this->autoSaveHint = '';
        $this->lastAutoSavedAt = null;
    }

    protected function persistDraft(?string $markTab = null, bool $flash = false, string $flashMessage = ''): void
    {
        $report = AuditReport::query()->findOrFail($this->reportId);
        $pages = (array) $report->pages_data;
        $meta = (array) ($pages['meta'] ?? []);
        $tabsDone = (array) ($meta['tabs_done'] ?? [
            'cover' => false,
            'page2' => false,
            'page3' => false,
            'page4' => false,
        ]);

        if ($markTab !== null) {
            $tabsDone[$markTab] = true;
        }

        // Report ends at page 4 — drop legacy page5–21 progress keys.
        $tabsDone = array_intersect_key($tabsDone, array_flip(['cover', 'page2', 'page3', 'page4']));

        $meta['tabs_done'] = $tabsDone;
        $meta['active_tab'] = $this->activeTab;

        $pages['meta'] = $meta;
        $pages['tableHeaders'] = $this->tableHeaders;
        $pages['page2'] = $this->page2Payload();
        $pages['toc'] = $this->tocPayload();
        $pages['page3'] = $this->page3Payload();
        $pages['page4'] = $this->page4Payload();
        unset(
            $pages['page5'],
            $pages['page6'],
            $pages['page7'],
            $pages['page8'],
            $pages['page9'],
            $pages['page10'],
            $pages['page11'],
            $pages['page12'],
            $pages['page13'],
            $pages['page14'],
            $pages['page15'],
            $pages['page16'],
            $pages['page17'],
            $pages['page18'],
            $pages['page19'],
            $pages['page20'],
            $pages['page21'],
        );
        $progress = AuditReport::computeProgress($pages, [
            'memo_no' => $this->memo_no,
            'auditor_name' => $this->auditor_name,
        ]);

        $report->update([
            'user_id' => $report->user_id ?: auth()->id(),
            'report_month' => $this->report_month,
            'report_year' => $this->report_year,
            'memo_no' => $this->memo_no,
            'report_date' => $this->report_date ?: null,
            'control_rating' => $this->control_rating,
            'shakha_display_name' => $this->shakha_display_name,
            'area_display_name' => $this->area_display_name,
            'audit_period_label' => $this->audit_period_label,
            'audit_start_date' => $this->audit_start_date ?: null,
            'audit_end_date' => $this->audit_end_date ?: null,
            'working_days' => $this->working_days,
            'period_scope' => $this->period_scope,
            'draft_sent_date' => $this->draft_sent_date ?: null,
            'comments_received_date' => $this->comments_received_date ?: null,
            'auditor_name' => $this->auditor_name,
            'auditor_designation' => $this->auditor_designation,
            'logo_path' => $this->logo_path,
            'pages_data' => $pages,
            'current_tab' => $this->activeTab,
            'progress_pct' => $progress,
            'last_saved_at' => now(),
            'status' => $report->status === AuditReport::STATUS_COMPLETED
                ? AuditReport::STATUS_COMPLETED
                : AuditReport::STATUS_DRAFT,
        ]);

        if ($flash) {
            session()->flash('status', $flashMessage !== '' ? $flashMessage : 'সংরক্ষণ হয়েছে।');
        }
    }

    protected function hydrateFromReport(AuditReport $report): void
    {
        $pages = (array) $report->pages_data;
        $page2 = (array) ($pages['page2'] ?? []);
        $toc = (array) ($pages['toc'] ?? []);
        $page3 = (array) ($pages['page3'] ?? []);
        $page4 = (array) ($pages['page4'] ?? []);
        $page5 = (array) ($pages['page5'] ?? []);
        $page6 = (array) ($pages['page6'] ?? []);
        $page7 = (array) ($pages['page7'] ?? []);
        $page8 = (array) ($pages['page8'] ?? []);
        $page9 = (array) ($pages['page9'] ?? []);
        $page10 = (array) ($pages['page10'] ?? []);
        $page11 = (array) ($pages['page11'] ?? []);
        $page12 = (array) ($pages['page12'] ?? []);
        $page13 = (array) ($pages['page13'] ?? []);
        $page14 = (array) ($pages['page14'] ?? []);
        $page15 = (array) ($pages['page15'] ?? []);
        $page16 = (array) ($pages['page16'] ?? []);
        $page17 = (array) ($pages['page17'] ?? []);
        $page18 = (array) ($pages['page18'] ?? []);
        $page19 = (array) ($pages['page19'] ?? []);
        $page20 = (array) ($pages['page20'] ?? []);
        $page21 = (array) ($pages['page21'] ?? []);
        $meta = (array) ($pages['meta'] ?? []);

        $this->reportId = $report->id;
        $this->shakha_id = (int) $report->shakha_id;
        $this->report_month = (int) ($report->report_month ?: now()->month);
        $this->report_year = (int) ($report->report_year ?: now()->year);
        $this->memo_no = (string) ($report->memo_no ?? '');
        $this->report_date = optional($report->report_date)?->toDateString() ?: '';
        $this->control_rating = (string) ($report->control_rating ?: 'Satisfactory');
        $this->shakha_display_name = (string) ($report->shakha_display_name ?? '');
        $this->area_display_name = (string) ($report->area_display_name ?? '');
        $this->audit_period_label = (string) ($report->audit_period_label ?? '');
        $this->audit_start_date = optional($report->audit_start_date)?->toDateString() ?: '';
        $this->audit_end_date = optional($report->audit_end_date)?->toDateString() ?: '';
        $this->working_days = $report->working_days;
        $this->period_scope = (string) ($report->period_scope ?? '');
        $this->draft_sent_date = optional($report->draft_sent_date)?->toDateString() ?: '';
        $this->comments_received_date = optional($report->comments_received_date)?->toDateString() ?: '';
        $this->auditor_name = (string) ($report->auditor_name ?? '');
        $this->auditor_designation = (string) ($report->auditor_designation ?: 'অফিসার অডিট');
        $this->logo_path = $report->logo_path;

        $this->glance_as_of = (string) ($page2['glance_as_of'] ?? '');
        $this->branch_opening_date = (string) ($page2['branch_opening_date'] ?? '');
        $this->staff_info_as_of = (string) ($page2['staff_info_as_of'] ?? '');
        $this->glanceRows = array_values((array) ($page2['glanceRows'] ?? []));
        $this->staffColumns = array_values((array) ($page2['staffColumns'] ?? []));
        $this->staffRows = array_values((array) ($page2['staffRows'] ?? []));
        $this->tableHeaders = (array) ($pages['tableHeaders'] ?? []);
        $this->tocRows = array_values((array) ($toc['rows'] ?? []));

        $this->sign_auditor_name = (string) ($page3['sign_auditor_name'] ?? '');
        $this->sign_auditor_designation = (string) ($page3['sign_auditor_designation'] ?? '');
        $this->sign_auditor_date = (string) ($page3['sign_auditor_date'] ?? '');
        $this->sign_bm_name = (string) ($page3['sign_bm_name'] ?? '');
        $this->sign_bm_date = (string) ($page3['sign_bm_date'] ?? '');
        $this->sign_abm_name = (string) ($page3['sign_abm_name'] ?? '');
        $this->sign_abm_date = (string) ($page3['sign_abm_date'] ?? '');

        $this->financial_section_title = (string) ($page4['financial_section_title'] ?? $this->financial_section_title);
        $this->financialFindings = array_values((array) ($page4['financialFindings'] ?? []));
        $this->reportSections = array_values((array) ($page4['reportSections'] ?? []));
        $this->reportBlocks = array_values((array) ($page4['reportBlocks'] ?? []));
        // Keep every custom_table fully normalized (merges/widths) so it stays editable after resume
        foreach ($this->reportBlocks as $i => $block) {
            if (($block['type'] ?? '') === 'custom_table') {
                $this->reportBlocks[$i] = CustomTableSchema::normalize(is_array($block) ? $block : []);
            }
        }
        $this->financial_criteria = (string) ($page4['financial_criteria'] ?? '');
        $this->vatObservationRows = array_values((array) ($page4['vatObservationRows'] ?? []));
        $this->taxObservationRows = array_values((array) ($page4['taxObservationRows'] ?? []));

        $this->expenseDetailRows = array_values((array) ($page5['expenseDetailRows'] ?? []));
        $this->expense_detail_risk = (string) ($page5['expense_detail_risk'] ?? '');
        $this->expense_detail_root_cause = (string) ($page5['expense_detail_root_cause'] ?? '');
        $this->expense_detail_recommendation = (string) ($page5['expense_detail_recommendation'] ?? '');
        $this->expense_detail_bm_reply = (string) ($page5['expense_detail_bm_reply'] ?? '');
        $this->expense_detail_responsible = (string) ($page5['expense_detail_responsible'] ?? '');
        $this->expense_detail_resolution_date = (string) ($page5['expense_detail_resolution_date'] ?? '');
        $this->finding13_serial = (string) ($page5['finding13_serial'] ?? $this->finding13_serial);
        $this->finding13_title = (string) ($page5['finding13_title'] ?? $this->finding13_title);
        $this->finding13_body = (string) ($page5['finding13_body'] ?? '');
        $this->finding13_indicator_id = isset($page5['finding13_indicator_id'])
            ? (int) $page5['finding13_indicator_id']
            : null;
        $this->finding13_indicator_code = isset($page5['finding13_indicator_code'])
            ? (string) $page5['finding13_indicator_code']
            : null;
        $this->finding13_amount = (string) ($page5['finding13_amount'] ?? '');
        $this->finding13_rating = (string) ($page5['finding13_rating'] ?? $this->finding13_rating);
        $this->finding13_criteria = (string) ($page5['finding13_criteria'] ?? '');
        $this->finding13_observation = (string) ($page5['finding13_observation'] ?? '');
        $this->finding13_statsRows = array_values((array) ($page5['finding13_statsRows'] ?? []));
        $this->finding13_depositRows = array_values((array) ($page5['finding13_depositRows'] ?? []));
        $this->finding13_risk = (string) ($page5['finding13_risk'] ?? '');
        $this->finding13_root_cause = (string) ($page5['finding13_root_cause'] ?? '');
        $this->finding13_recommendation = (string) ($page5['finding13_recommendation'] ?? '');
        $this->finding13_bm_reply = (string) ($page5['finding13_bm_reply'] ?? '');
        $this->finding13_responsible = (string) ($page5['finding13_responsible'] ?? '');
        $this->finding13_resolution_date = (string) ($page5['finding13_resolution_date'] ?? '');

        $this->page6Findings = array_values((array) ($page6['page6Findings'] ?? []));
        $this->page7Findings = array_values((array) ($page7['page7Findings'] ?? []));
        $this->page8Findings = array_values((array) ($page8['page8Findings'] ?? []));
        $this->page9Findings = array_values((array) ($page9['page9Findings'] ?? []));
        $this->page10Findings = array_values((array) ($page10['page10Findings'] ?? []));
        $this->page10_section_title = (string) ($page10['page10_section_title'] ?? $this->page10_section_title);
        $this->page11Findings = array_values((array) ($page11['page11Findings'] ?? []));
        $this->page12Findings = array_values((array) ($page12['page12Findings'] ?? []));
        $this->page12_section_title = (string) ($page12['page12_section_title'] ?? $this->page12_section_title);
        $this->page13Findings = array_values((array) ($page13['page13Findings'] ?? []));
        $this->page13_section_title = (string) ($page13['page13_section_title'] ?? $this->page13_section_title);
        $this->page14Findings = array_values((array) ($page14['page14Findings'] ?? []));
        $this->page15Findings = array_values((array) ($page15['page15Findings'] ?? []));
        $this->page16Findings = array_values((array) ($page16['page16Findings'] ?? []));
        $this->page17Findings = array_values((array) ($page17['page17Findings'] ?? []));
        $this->page18Findings = array_values((array) ($page18['page18Findings'] ?? []));
        $this->page19_compliance_title = (string) ($page19['page19_compliance_title'] ?? $this->page19_compliance_title);
        $this->page19_compliance_period = (string) ($page19['page19_compliance_period'] ?? '');
        $this->page19_compliance_followup_date = (string) ($page19['page19_compliance_followup_date'] ?? '');
        $this->page19ComplianceRows = array_values((array) ($page19['page19ComplianceRows'] ?? []));
        $this->page20_it_title = (string) ($page20['page20_it_title'] ?? $this->page20_it_title);
        $this->page20_it_org_line1 = (string) ($page20['page20_it_org_line1'] ?? $this->page20_it_org_line1);
        $this->page20_it_org_line2 = (string) ($page20['page20_it_org_line2'] ?? $this->page20_it_org_line2);
        $this->page20_it_org_line3 = (string) ($page20['page20_it_org_line3'] ?? $this->page20_it_org_line3);
        $this->page20_it_program = (string) ($page20['page20_it_program'] ?? $this->page20_it_program);
        $this->page20_it_branch = (string) ($page20['page20_it_branch'] ?? '');
        $this->page20_it_instruction = (string) ($page20['page20_it_instruction'] ?? $this->page20_it_instruction);
        $this->page20ItChecklistRows = array_values((array) ($page20['page20ItChecklistRows'] ?? []));

        if ($this->page20ItChecklistRows === [] && ! empty($page19['page19ItChecklistRows'])) {
            $this->page20_it_title = (string) ($page19['page19_it_title'] ?? $this->page20_it_title);
            $this->page20_it_org_line1 = (string) ($page19['page19_it_org_line1'] ?? $this->page20_it_org_line1);
            $this->page20_it_org_line2 = (string) ($page19['page19_it_org_line2'] ?? $this->page20_it_org_line2);
            $this->page20_it_org_line3 = (string) ($page19['page19_it_org_line3'] ?? $this->page20_it_org_line3);
            $this->page20_it_program = (string) ($page19['page19_it_program'] ?? $this->page20_it_program);
            $this->page20_it_branch = (string) ($page19['page19_it_branch'] ?? '');
            $this->page20_it_instruction = (string) ($page19['page19_it_instruction'] ?? $this->page20_it_instruction);
            $this->page20ItChecklistRows = array_values((array) $page19['page19ItChecklistRows']);
        }

        $this->page21_section_title = (string) ($page21['page21_section_title'] ?? $this->page21_section_title);
        $this->page21_year_of_reporting = (string) ($page21['page21_year_of_reporting'] ?? '');
        $this->page21_branch_name = (string) ($page21['page21_branch_name'] ?? '');
        $this->page21ExternalAuditRows = array_values((array) ($page21['page21ExternalAuditRows'] ?? []));
        $this->page21_sign_label = (string) ($page21['page21_sign_label'] ?? $this->page21_sign_label);
        $this->page21_sign_name = (string) ($page21['page21_sign_name'] ?? '');
        $this->page21_sign_designation = (string) ($page21['page21_sign_designation'] ?? '');

        $this->ensureTableHeadersDefaults();
        $this->ensurePage2Defaults();
        $this->ensureTocDefaults();
        $this->ensureSignatureDefaults();
        $this->ensureFinancialAuditDefaults();
        // Drop legacy TOC rows — সূচিপত্র = report blocks / শিরোনাম only.
        $this->rebuildTocFromReportBlocks();

        $this->activeTab = (string) ($report->current_tab ?: ($meta['active_tab'] ?? 'cover'));
        if (! in_array($this->activeTab, ['cover', 'page2', 'page3', 'page4'], true)) {
            $this->activeTab = in_array($this->activeTab, ['page5', 'page6', 'page7', 'page8', 'page9', 'page10', 'page11', 'page12', 'page13', 'page14', 'page15', 'page16', 'page17', 'page18', 'page19', 'page20', 'page21'], true)
                ? 'page4'
                : 'cover';
        }

        $this->lastAutoSavedAt = optional($report->last_saved_at)?->timezone('Asia/Dhaka')->format('h:i A');
        $this->autoSaveHint = $this->lastAutoSavedAt ? 'Last saved '.$this->lastAutoSavedAt : '';
    }

    /**
     * @return array{sheets: list<array<string, mixed>>}
     */
    protected function stampedDocument(): array
    {
        return [
            'sheets' => AuditReportPaginator::buildSheets($this->tocRows),
        ];
    }

    /**
     * @return array<int, array{type:string,serial:string,finding:string,amount:string,rating:string,status:string,page_no:string,preview_page:int}>
     */
    public function tocRowsForPage(int $page): array
    {
        return array_values(array_filter(
            $this->tocRows,
            fn ($row) => (int) ($row['preview_page'] ?? 2) === $page
        ));
    }

    public static function findingRatingParts(?string $rating): array
    {
        if (! $rating) {
            return ['label' => '', 'code' => ''];
        }

        if (preg_match('/^(.+?)\s*\(([A-F])\)$/i', $rating, $matches)) {
            return [
                'label' => trim($matches[1]),
                'code' => strtoupper($matches[2]),
            ];
        }

        return ['label' => $rating, 'code' => ''];
    }

    public static function findingRatingStyle(?string $rating): array
    {
        $code = self::findingRatingParts($rating)['code'];

        return match ($code) {
            'B' => ['bg' => '#f8d5b8', 'color' => '#111111'],
            'C' => ['bg' => '#e67e22', 'color' => '#111111'],
            'D' => ['bg' => '#f5e6a3', 'color' => '#111111'],
            'E' => ['bg' => '#16a34a', 'color' => '#ffffff'],
            'F', 'A' => ['bg' => '#dc2626', 'color' => '#ffffff'],
            default => match ($rating) {
                'Major (B)' => ['bg' => '#f8d5b8', 'color' => '#111111'],
                'Medium (C)' => ['bg' => '#e67e22', 'color' => '#111111'],
                'Minor (D)' => ['bg' => '#f5e6a3', 'color' => '#111111'],
                'Unsatisfactory (F)' => ['bg' => '#dc2626', 'color' => '#ffffff'],
                default => ['bg' => '#ffffff', 'color' => '#111111'],
            },
        };
    }

    protected function ensureTableHeadersDefaults(): void
    {
        $this->tableHeaders = AuditTableHeaders::merge($this->tableHeaders);
    }

    protected function ensurePage2Defaults(): void
    {
        if ($this->glance_as_of === '' && $this->report_date) {
            try {
                $this->glance_as_of = Carbon::parse($this->report_date)->format('d F Y');
            } catch (\Throwable) {
                $this->glance_as_of = $this->report_date;
            }
        }

        if ($this->staff_info_as_of === '' && $this->report_date) {
            $this->staff_info_as_of = $this->report_date;
        }

        if ($this->glanceRows === []) {
            $pairs = [
                ['মোট ঋণস্থিতি', 'PAR'],
                ['ঋণস্থিতি / বকেয়া হার', 'শাখার চলতি আদায়ের হার'],
                ['প্রতি মাঠকর্মী অনুযায়ী ঋণী সংখ্যা (গড়)', 'শাখা লাভের অবস্থান (মোট)'],
                ['প্রতি মাঠকর্মী অনুযায়ী ঋণস্থিতি (গড়)', 'শাখার সক্রিয় সদস্য সংখ্যা'],
                ['মোট সঞ্চয়স্থিতি', 'শাখার মোট ঋণী সংখ্যা'],
                ['সঞ্চয় ও ঋণস্থিতির হার', 'মোট কর্মী সংখ্যা'],
            ];

            $this->glanceRows = collect($pairs)->map(fn ($pair) => [
                'left_label' => $pair[0],
                'left_value' => '',
                'right_label' => $pair[1],
                'right_value' => '',
            ])->all();
        }

        if ($this->staffColumns === []) {
            $this->staffColumns = [
                'কর্মকর্তার নাম',
                'পরিচিতি নং',
                'পদবী',
                'সংস্থায় যোগদানের তারিখ',
                'শাখায় যোগদানের তারিখ',
            ];
        }

        if ($this->staffRows === []) {
            $colCount = count($this->staffColumns);
            // Keep staff table short so page-2 glance + TOC fit on one A4 sheet.
            $this->staffRows = collect(range(1, 4))->map(fn () => [
                'cells' => array_fill(0, $colCount, ''),
            ])->all();
        } else {
            // keep cell count in sync with columns
            $colCount = count($this->staffColumns);
            foreach ($this->staffRows as $i => $row) {
                $cells = array_values($row['cells'] ?? []);
                if (count($cells) < $colCount) {
                    $cells = array_pad($cells, $colCount, '');
                } elseif (count($cells) > $colCount) {
                    $cells = array_slice($cells, 0, $colCount);
                }
                $this->staffRows[$i] = ['cells' => $cells];
            }
        }
    }

    protected function ensureTocDefaults(): void
    {
        if ($this->tocRows !== []) {
            return;
        }

        $make = function (string $type, string $serial, string $finding, string $rating, int $page): array {
            return [
                'type' => $type,
                'serial' => $serial,
                'finding' => $finding,
                'amount' => '',
                'rating' => $rating,
                'status' => '',
                'page_no' => '',
                'preview_page' => $page,
            ];
        };

        // Short report: cover → page4 financial (১.১–১.২).
        $this->tocRows = [
            $make('section', '১.০', 'অর্থ ও হিসাব নিরীক্ষা (Accounts and Financial Audit)', '', 2),
            $make('item', '১.১', 'ভ্যাট ও ট্যাক্স কর্তন না করা', 'Major (B)', 2),
            $make('item', '১.২', 'ভ্যাট ও ট্যাক্স পরিশোধ না করা', 'Major (B)', 2),
        ];
    }
    protected function ensureSignatureDefaults(): void
    {
        if ($this->sign_auditor_name === '') {
            $this->sign_auditor_name = $this->auditor_name;
        }
        if ($this->sign_auditor_designation === '') {
            $this->sign_auditor_designation = $this->auditor_designation;
        }
        if ($this->sign_auditor_date === '' && $this->report_date) {
            $this->sign_auditor_date = $this->report_date;
        }
    }

    protected function page2Payload(): array
    {
        return [
            'glance_as_of' => $this->glance_as_of,
            'branch_opening_date' => $this->branch_opening_date,
            'staff_info_as_of' => $this->staff_info_as_of,
            'glanceRows' => $this->glanceRows,
            'staffColumns' => $this->staffColumns,
            'staffRows' => $this->staffRows,
        ];
    }

    protected function tocPayload(): array
    {
        return [
            'rows' => $this->tocRows,
        ];
    }

    protected function ensureFinancialAuditDefaults(): void
    {
        if ($this->financial_criteria === '') {
            $this->financial_criteria = 'প্রতিষ্ঠানের নির্দেশনা ও জাতীয় রাজস্ব বোর্ড (এনবিআর)-এর নির্দেশনা অনুযায়ী প্রযোজ্য ভ্যাট ও ট্যাক্স নির্ধারিত হারে সরকারি কোষাগারে জমা দিতে হবে।';
        }

        if ($this->vatObservationRows === []) {
            $this->vatObservationRows = [$this->blankObservationRow()];
        }

        if ($this->taxObservationRows === []) {
            $this->taxObservationRows = [$this->blankObservationRow()];
        }

        $this->ensureReportBlocksDefaults();
        $this->rebuildTocFromReportBlocks();
    }

    protected function ensureReportBlocksDefaults(): void
    {
        if ($this->reportBlocks !== []) {
            $this->normalizeReportBlocks();
            $this->syncSectionsFromReportBlocks();
            $this->syncLegacyFinancialFromReportSections();

            return;
        }

        // Prefer saved reportSections, else legacy financial findings.
        $this->ensureReportSectionsDefaults();
        $this->syncReportBlocksFromSections();
        $this->normalizeReportBlocks();
    }

    protected function normalizeReportBlocks(): void
    {
        $blocks = array_values($this->reportBlocks);
        $migrated = [];

        foreach ($blocks as $block) {
            $type = $block['type'] ?? '';
            if ($type === 'section') {
                $migrated[] = array_merge(['type' => 'section', 'serial' => '১.০', 'title' => ''], $block, ['type' => 'section']);
            } elseif ($type === 'finding') {
                $migrated[] = array_merge(['type' => 'finding'], $this->blankFindingRow(''), is_array($block) ? $block : [], ['type' => 'finding']);
            } elseif ($type === 'criteria') {
                if (array_key_exists('body', $block)) {
                    $body = (string) $block['body'];
                } else {
                    $body = $this->financial_criteria !== ''
                        ? $this->financial_criteria
                        : $this->blankCriteriaBlock()['body'];
                }
                $migrated[] = [
                    'type' => 'criteria',
                    'label' => (string) ($block['label'] ?? 'প্রচলিত নিয়ম (Criteria):'),
                    'body' => $body,
                ];
            } elseif ($type === 'observation') {
                $migrated[] = $this->blankObservationBlock(
                    (string) ($block['label'] ?? 'পর্যবেক্ষণ (Observation) :'),
                    (string) ($block['body'] ?? '')
                );
                // Legacy combined (text + table in one block) → split
                if (array_key_exists('rows', $block)) {
                    $heading = (string) ($block['heading'] ?? '');
                    $rows = array_values((array) ($block['rows'] ?? []));
                    $migrated[] = $this->blankStatsBlock(
                        $heading !== '' ? $heading : 'Report Rating Box:',
                        $rows !== [] ? $rows : null
                    );
                }
            } elseif ($type === 'stats') {
                $migrated[] = $this->blankStatsBlock(
                    (string) ($block['heading'] ?? 'Report Rating Box:'),
                    array_values((array) ($block['rows'] ?? []))
                );
            } elseif ($type === 'vat') {
                if (trim((string) ($block['label'] ?? '')) !== '' || array_key_exists('body', $block)) {
                    $migrated[] = $this->blankObservationBlock(
                        (string) ($block['label'] ?: 'পর্যবেক্ষণ (Observation) :'),
                        (string) ($block['body'] ?? '')
                    );
                }
                $migrated[] = $this->blankStatsBlock(
                    (string) ($block['heading'] ?? 'Report Rating Box:'),
                    array_values((array) ($block['rows'] ?? ($this->vatObservationRows ?: [$this->blankObservationRow()])))
                );
            } elseif ($type === 'tax') {
                $migrated[] = $this->blankStatsBlock(
                    (string) ($block['heading'] ?? 'Report Rating Box:'),
                    array_values((array) ($block['rows'] ?? ($this->taxObservationRows ?: [$this->blankObservationRow()])))
                );
            } elseif ($type === 'custom_table') {
                $migrated[] = CustomTableSchema::normalize(is_array($block) ? $block : []);
            } elseif ($type === 'jobab_table') {
                $migrated[] = $this->normalizeJobabBlock(is_array($block) ? $block : []);
            } elseif ($type === 'text_box') {
                $migrated[] = $this->blankObservationBlock(
                    (string) ($block['label'] ?? 'নতুন বক্স:'),
                    (string) ($block['body'] ?? '')
                );
            }
        }

        $blocks = $migrated;

        // Do not force-recreate section/finding — user may delete the first/only ones.
        $hasCriteria = false;
        $hasObservation = false;
        $hasStats = false;
        foreach ($blocks as $b) {
            $t = $b['type'] ?? '';
            if ($t === 'criteria') {
                $hasCriteria = true;
            }
            if ($t === 'observation') {
                $hasObservation = true;
            }
            if ($this->isStatsLike($t)) {
                $hasStats = true;
            }
        }
        if (! $hasCriteria) {
            $blocks[] = $this->blankCriteriaBlock();
        }
        if (! $hasObservation) {
            $blocks[] = $this->blankObservationBlock();
        }
        if (! $hasStats) {
            $blocks[] = $this->blankStatsBlock(
                'Report Rating Box:',
                $this->vatObservationRows !== [] ? $this->vatObservationRows : null
            );
            $blocks[] = $this->blankStatsBlock(
                'Report Rating Box:',
                $this->taxObservationRows !== [] ? $this->taxObservationRows : null
            );
        }

        $this->reportBlocks = array_values($blocks);
        $this->syncLegacyUtilityFromBlocks();
    }

    protected function syncReportBlocksFromSections(): void
    {
        $blocks = [];
        foreach ($this->reportSections as $section) {
            $blocks[] = [
                'type' => 'section',
                'serial' => (string) ($section['serial'] ?? '১.০'),
                'title' => (string) ($section['title'] ?? ''),
            ];
            foreach ((array) ($section['findings'] ?? []) as $finding) {
                $blocks[] = array_merge(['type' => 'finding'], $this->blankFindingRow(''), is_array($finding) ? $finding : [], ['type' => 'finding']);
            }
        }

        if ($blocks === []) {
            $blocks = [
                [
                    'type' => 'section',
                    'serial' => '১.০',
                    'title' => $this->financial_section_title ?: '১.০ আর্থিক নিরীক্ষা (Financial Audit) :',
                ],
                ['type' => 'finding', ...$this->blankFindingRow('১.১')],
            ];
        }

        $blocks[] = $this->blankCriteriaBlock();
        $blocks[] = $this->blankObservationBlock();
        $blocks[] = $this->blankStatsBlock(
            'Report Rating Box:',
            $this->vatObservationRows !== [] ? $this->vatObservationRows : null
        );
        $blocks[] = $this->blankStatsBlock(
            'Report Rating Box:',
            $this->taxObservationRows !== [] ? $this->taxObservationRows : null
        );
        $this->reportBlocks = $blocks;
    }

    protected function syncSectionsFromReportBlocks(): void
    {
        $sections = [];
        $current = null;
        foreach ($this->reportBlocks as $block) {
            $type = $block['type'] ?? '';
            if ($type === 'section') {
                if ($current !== null) {
                    $sections[] = $current;
                }
                $current = [
                    'serial' => (string) ($block['serial'] ?? ''),
                    'title' => (string) ($block['title'] ?? ''),
                    'findings' => [],
                ];
            } elseif ($type === 'finding') {
                if ($current === null) {
                    $current = [
                        'serial' => '১.০',
                        'title' => $this->financial_section_title ?: '১.০ আর্থিক নিরীক্ষা',
                        'findings' => [],
                    ];
                }
                $finding = $block;
                unset($finding['type']);
                $current['findings'][] = array_merge($this->blankFindingRow(''), $finding);
            }
        }
        if ($current !== null) {
            $sections[] = $current;
        }
        if ($sections === []) {
            $sections = [[
                'serial' => '১.০',
                'title' => $this->financial_section_title ?: '১.০ আর্থিক নিরীক্ষা',
                'findings' => [$this->blankFindingRow('১.১')],
            ]];
        }
        $this->reportSections = $sections;
    }

    protected function nextSectionSerialFromBlocks(): string
    {
        $map = [
            '০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4',
            '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9',
        ];
        $max = 0;
        foreach ($this->reportBlocks as $block) {
            if (($block['type'] ?? '') !== 'section') {
                continue;
            }
            $latin = strtr((string) ($block['serial'] ?? ''), $map);
            if (preg_match('/^(\d+)/', $latin, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return \App\Support\BanglaNumerals::fromInt(max(1, $max + 1)).'.০';
    }

    protected function nextFindingSerialNearIndex(int $index): string
    {
        $map = [
            '০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4',
            '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9',
        ];
        $rev = array_flip($map);

        $prefix = '1';
        for ($i = min($index, count($this->reportBlocks) - 1); $i >= 0; $i--) {
            if (($this->reportBlocks[$i]['type'] ?? '') !== 'section') {
                continue;
            }
            $latin = strtr((string) ($this->reportBlocks[$i]['serial'] ?? '১.০'), $map);
            if (preg_match('/^(\d+)/', $latin, $m)) {
                $prefix = $m[1];
            }
            break;
        }
        // Also scan backward from index-1 if inserting at 0 with no prior section yet
        if ($index === 0) {
            foreach ($this->reportBlocks as $block) {
                if (($block['type'] ?? '') === 'section') {
                    $latin = strtr((string) ($block['serial'] ?? '১.০'), $map);
                    if (preg_match('/^(\d+)/', $latin, $m)) {
                        $prefix = $m[1];
                    }
                    break;
                }
            }
        }

        $max = 0;
        foreach ($this->reportBlocks as $block) {
            if (($block['type'] ?? '') !== 'finding') {
                continue;
            }
            $latin = strtr((string) ($block['serial'] ?? ''), $map);
            if (preg_match('/^'.$prefix.'[\.٫.](\d+)$/', $latin, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return strtr($prefix, $rev).'.'.\App\Support\BanglaNumerals::fromInt(max(1, $max + 1));
    }

    protected function rebuildTocFromReportBlocks(): void
    {
        $this->ensureReportBlocksDefaults();

        $preserved = [];
        foreach ($this->tocRows as $row) {
            if (($row['type'] ?? 'item') !== 'item') {
                continue;
            }
            $serial = trim((string) ($row['serial'] ?? ''));
            if ($serial === '') {
                continue;
            }
            $preserved[$serial] = [
                'amount' => (string) ($row['amount'] ?? ''),
                'status' => (string) ($row['status'] ?? ''),
                'page_no' => (string) ($row['page_no'] ?? ''),
                'preview_page' => (int) ($row['preview_page'] ?? 2),
            ];
        }

        $rows = [];
        foreach ($this->reportBlocks as $block) {
            $type = $block['type'] ?? '';
            if ($type === 'section') {
                $sectionSerial = trim((string) ($block['serial'] ?? '১.০')) ?: '১.০';
                $sectionTitle = trim((string) ($block['title'] ?? ''));
                $sectionLabel = preg_replace('/^'.preg_quote($sectionSerial, '/').'\s*/u', '', $sectionTitle) ?? $sectionTitle;
                $sectionLabel = trim($sectionLabel, " \t\n\r\0\x0B:");
                if ($sectionLabel === '') {
                    $sectionLabel = $sectionTitle !== '' ? $sectionTitle : 'বিভাগ';
                }
                $rows[] = [
                    'type' => 'section',
                    'serial' => $sectionSerial,
                    'finding' => $sectionLabel,
                    'amount' => '',
                    'rating' => '',
                    'status' => '',
                    'page_no' => '',
                    'preview_page' => 2,
                ];
            } elseif ($type === 'finding') {
                $serial = trim((string) ($block['serial'] ?? ''));
                if ($serial === '') {
                    continue;
                }
                $body = trim((string) ($block['body'] ?? ''));
                $title = trim((string) ($block['title'] ?? ''));
                $text = $body !== '' ? $body : ($title !== '' && $title !== 'শিরোনাম' ? $title : '');
                $prev = $preserved[$serial] ?? [];
                $rows[] = [
                    'type' => 'item',
                    'serial' => $serial,
                    'finding' => $text,
                    'amount' => (string) (($block['amount'] ?? '') !== '' ? $block['amount'] : ($prev['amount'] ?? '')),
                    'rating' => (string) ($block['rating'] ?? ''),
                    'status' => (string) ($prev['status'] ?? ''),
                    'page_no' => (string) ($prev['page_no'] ?? ''),
                    'preview_page' => (int) ($prev['preview_page'] ?? 2),
                ];
            }
        }

        $this->tocRows = $rows;
    }

    protected function ensureReportSectionsDefaults(): void
    {
        if ($this->reportSections !== []) {
            foreach ($this->reportSections as $s => $section) {
                $this->reportSections[$s] = array_merge([
                    'serial' => '',
                    'title' => '',
                    'findings' => [],
                ], is_array($section) ? $section : []);
                $findings = array_values((array) ($this->reportSections[$s]['findings'] ?? []));
                foreach ($findings as $f => $finding) {
                    $findings[$f] = array_merge($this->blankFindingRow(''), is_array($finding) ? $finding : []);
                }
                if ($findings === []) {
                    $findings = [$this->blankFindingRow($this->nextFindingSerialForSection($s))];
                }
                $this->reportSections[$s]['findings'] = $findings;
            }
            $this->syncLegacyFinancialFromReportSections();

            return;
        }

        // Migrate legacy single-section data.
        if ($this->financialFindings === []) {
            $this->syncFinancialFindingsFromToc();
        }

        foreach (array_keys($this->financialFindings) as $i) {
            $this->financialFindings[$i] = array_merge(
                $this->blankFindingRow(''),
                $this->financialFindings[$i]
            );
        }

        $sectionTitle = trim((string) $this->financial_section_title);
        if ($sectionTitle === '') {
            $sectionTitle = '১.০ আর্থিক নিরীক্ষা (Financial Audit) :';
        }

        $serial = '১.০';
        if (preg_match('/^(১[\.٫]০|[১-৯০-৯]+[\.٫]০)\s*/u', $sectionTitle, $m)) {
            $serial = trim($m[1]);
        }

        $this->reportSections = [[
            'serial' => $serial,
            'title' => $sectionTitle,
            'findings' => array_values($this->financialFindings),
        ]];

        $this->syncLegacyFinancialFromReportSections();
    }

    /**
     * @return array{serial:string,title:string,body:string,rating:string,amount:string,indicator_id:null,indicator_code:null}
     */
    protected function blankFindingRow(string $serial): array
    {
        return [
            'serial' => $serial,
            'title' => 'শিরোনাম',
            'body' => '',
            'rating' => '',
            'amount' => '',
            'indicator_id' => null,
            'indicator_code' => null,
        ];
    }

    protected function syncLegacyFinancialFromReportSections(): void
    {
        if ($this->reportSections === []) {
            return;
        }

        $first = $this->reportSections[0];
        $this->financial_section_title = (string) ($first['title'] ?? $this->financial_section_title);
        // Flat list used by older PDF callers that only know financialFindings:
        // keep first section there; full list is in reportSections.
        $this->financialFindings = array_values((array) ($first['findings'] ?? []));
    }

    protected function syncReportSectionsFromLegacyFinancial(): void
    {
        $this->ensureReportSectionsDefaults();
        if (! isset($this->reportSections[0])) {
            return;
        }
        $this->reportSections[0]['title'] = $this->financial_section_title;
        $this->reportSections[0]['findings'] = array_values($this->financialFindings);
    }

    /**
     * Bind a finding row to an existing indicator, or create a new indicator from free text.
     */
    public function applyFinancialIndicator(int $index, ?int $indicatorId, string $title): void
    {
        $this->applyFindingIndicator('financialFindings', $index, $indicatorId, $title);
    }

    /**
     * Paste Excel / Google Sheets clipboard (TSV/CSV) into a Livewire row array.
     *
     * @param  list<string>  $columns  Left-to-right field keys matching the UI table
     */
    public function pasteTable(string $path, string $tsv, array $columns = [], bool $replace = true): void
    {
        $path = trim($path);
        if ($path === '' || $columns === []) {
            return;
        }

        if (! $this->isAllowedPastePath($path)) {
            $this->autoSaveHint = 'এই টেবিলে পেস্ট অনুমোদিত নয়';

            return;
        }

        // Excel paste always replaces the existing table rows.
        $replace = true;

        $rows = ExcelTsvParser::toAssocRows($tsv, $columns);
        if ($rows === []) {
            $this->autoSaveHint = 'কোনো সারি পাওয়া যায়নি — Excel থেকে কলামসহ কপি করে আবার চেষ্টা করুন';

            return;
        }

        if (str_ends_with($path, 'ItChecklistRows') || str_contains($path, 'page20ItChecklistRows')) {
            $rows = array_map(fn (array $row) => $this->normalizeItChecklistPasteRow($row), $rows);
        }

        $this->assignPasteRows($path, $rows, $replace);

        $this->autoSaveHint = count($rows).' সারি Excel থেকে পেস্ট হয়েছে (আগের টেবিল প্রতিস্থাপিত)';

        if ($this->reportId) {
            $this->autoSaveDraft();
        }
    }

    /**
     * Wipe every row from a paste-enabled data table.
     */
    public function clearTable(string $path): void
    {
        $path = trim($path);
        if ($path === '' || ! $this->isAllowedPastePath($path)) {
            $this->autoSaveHint = 'এই টেবিল মুছে ফেলা যায়নি';

            return;
        }

        $this->assignPasteRows($path, [], true);

        $this->autoSaveHint = 'টেবিলের সব সারি মুছে ফেলা হয়েছে';

        if ($this->reportId) {
            $this->autoSaveDraft();
        }
    }

    /**
     * @param  'financialFindings'|'finding13'|'page6Findings'|'page7Findings'|'page8Findings'|'page9Findings'|'page10Findings'|'page11Findings'|'page12Findings'|'page13Findings'|'page14Findings'|'page15Findings'|'page16Findings'|'page17Findings'|'page18Findings'  $collection
     */
    public function applyFindingIndicator(string $collection, int $index, ?int $indicatorId, string $title): void
    {
        $allowed = [
            'financialFindings',
            'finding13',
            'page6Findings',
            'page7Findings',
            'page8Findings',
            'page9Findings',
            'page10Findings',
            'page11Findings',
            'page12Findings',
            'page13Findings',
            'page14Findings',
            'page15Findings',
            'page16Findings',
            'page17Findings',
            'page18Findings',
        ];

        if (! in_array($collection, $allowed, true)) {
            return;
        }

        $title = trim($title);
        if ($title === '') {
            return;
        }

        $category = $collection === 'financialFindings'
            ? 'আর্থিক নিরীক্ষা (রিপোর্ট)'
            : 'নিরীক্ষা প্রতিবেদন';

        $indicator = $this->resolveOrCreateIndicator($indicatorId, $title, $category);

        if ($collection === 'finding13') {
            $this->finding13_title = 'শিরোনাম';
            $this->finding13_body = $indicator->title;
            $this->finding13_indicator_id = $indicator->id;
            $this->finding13_indicator_code = $indicator->indicator_code;

            if (blank($this->finding13_rating) && filled($indicator->risk_rating)) {
                $this->finding13_rating = $this->mapRiskToFindingRating((string) $indicator->risk_rating);
            }

            $this->syncFinding13ToToc();
        } else {
            if (! isset($this->{$collection}[$index]) || ! is_array($this->{$collection}[$index])) {
                return;
            }

            $this->{$collection}[$index]['title'] = 'শিরোনাম';
            $this->{$collection}[$index]['body'] = $indicator->title;
            $this->{$collection}[$index]['indicator_id'] = $indicator->id;
            $this->{$collection}[$index]['indicator_code'] = $indicator->indicator_code;

            if (
                blank($this->{$collection}[$index]['rating'] ?? null)
                && filled($indicator->risk_rating)
            ) {
                $this->{$collection}[$index]['rating'] = $this->mapRiskToFindingRating(
                    (string) $indicator->risk_rating
                );
            }

            $this->syncTocAfterFindingIndicator($collection, $index);
            if ($collection === 'financialFindings') {
                $this->syncReportSectionsFromLegacyFinancial();
                $this->rebuildTocFromReportBlocks();
            }
        }

        $this->autoSaveHint = 'Indicator সংযুক্ত · সূচিপত্রে আপডেট';

        if ($this->reportId) {
            $this->autoSaveDraft();
        }
    }

    protected function isAllowedPastePath(string $path): bool
    {
        $flatRoots = [
            'vatObservationRows',
            'taxObservationRows',
            'finding13_statsRows',
            'finding13_depositRows',
            'expenseDetailRows',
            'page19ComplianceRows',
            'page20ItChecklistRows',
            'page21ExternalAuditRows',
            'tocRows',
            'glanceRows',
        ];

        if (in_array($path, $flatRoots, true)) {
            return true;
        }

        if (preg_match('/^reportBlocks\.(\d+)\.rows$/', $path)) {
            return true;
        }

        if (! preg_match(
            '/^(financialFindings|finding13|page(?:6|7|8|9|10|11|12|13|14|15|16|17|18)Findings)\.(\d+)\.([A-Za-z][A-Za-z0-9_]*)$/',
            $path,
            $m
        )) {
            return false;
        }

        $allowedKeys = [
            'statsRows',
            'voucherRows',
            'budgetRows',
            'bonusRows',
            'cofRows',
            'cashRows',
            'stampRows',
            'assetRows',
            'depRows',
            'quoteRows',
            'stockRows',
            'samityRows',
            'passbookRows',
            'sufolonRows',
            'arrearsRows',
            'passbookAbsentRows',
            'savingsAdjustRows',
            'dropoutRefundRows',
            'savingsAdjustCompareRows',
            'detailRows',
        ];

        return in_array($m[3], $allowedKeys, true);
    }

    /**
     * Keep intentionally empty lists after clear/Excel paste. Seed defaults only when the key is missing.
     *
     * @param  callable(): list<array<string, mixed>>  $default
     * @return list<array<string, mixed>>
     */
    protected function rowsFromFinding(array $finding, string $key, callable $default): array
    {
        if (array_key_exists($key, $finding)) {
            return array_values((array) $finding[$key]);
        }

        return array_values($default());
    }

    /**
     * @param  list<array<string, string>>  $rows
     */
    protected function assignPasteRows(string $path, array $rows, bool $replace): void
    {
        $parts = explode('.', $path);
        $root = array_shift($parts);

        if ($root === null || $root === '' || ! property_exists($this, $root) || ! is_array($this->{$root})) {
            return;
        }

        if ($parts === []) {
            $this->{$root} = $replace
                ? array_values($rows)
                : array_values(array_merge($this->{$root}, $rows));

            return;
        }

        $data = $this->{$root};
        $cursor = &$data;

        foreach ($parts as $i => $segment) {
            $isLast = $i === count($parts) - 1;

            if (ctype_digit((string) $segment)) {
                $segment = (int) $segment;
            }

            if ($isLast) {
                $existing = (isset($cursor[$segment]) && is_array($cursor[$segment]))
                    ? $cursor[$segment]
                    : [];
                $cursor[$segment] = $replace
                    ? array_values($rows)
                    : array_values(array_merge($existing, $rows));
            } else {
                if (! isset($cursor[$segment]) || ! is_array($cursor[$segment])) {
                    $cursor[$segment] = [];
                }
                $cursor = &$cursor[$segment];
            }
        }

        unset($cursor);
        $this->{$root} = $data;
    }

    /**
     * @param  array<string, string>  $row
     * @return array<string, string>
     */
    protected function normalizeItChecklistPasteRow(array $row): array
    {
        if (! isset($row['compliance'])) {
            return $row;
        }

        $raw = mb_strtolower(trim((string) $row['compliance']));
        $row['compliance'] = match (true) {
            in_array($raw, ['yes', 'y', 'হ্যাঁ', 'ha', '1', 'true'], true) => 'yes',
            in_array($raw, ['no', 'n', 'না', '0', 'false'], true) => 'no',
            in_array($raw, ['n/a', 'na', 'n.a.', 'প্রযোজ্য নয়', '-'], true) => 'na',
            default => $raw,
        };

        return $row;
    }

    protected function resolveOrCreateIndicator(?int $indicatorId, string $title, string $category): AuditIndicator
    {
        $indicator = null;

        if ($indicatorId) {
            $indicator = AuditIndicator::query()->find($indicatorId);
        }

        if (! $indicator) {
            $indicator = AuditIndicator::query()
                ->where('title', $title)
                ->first();
        }

        if (! $indicator) {
            $indicator = AuditIndicator::query()->create([
                'category' => $category,
                'sub_category' => null,
                'indicator_code' => $this->makeUniqueIndicatorCode(),
                'title' => $title,
                'risk_rating' => null,
                'is_active' => true,
            ]);
        }

        return $indicator;
    }

    protected function syncTocAfterFindingIndicator(string $collection, int $index): void
    {
            match ($collection) {
            'financialFindings' => $this->rebuildTocFromReportBlocks(),
            'page6Findings' => $this->syncPage6FindingsToToc(),
            'page7Findings' => $this->syncPage7FindingsToToc(),
            'page8Findings' => $this->syncPage8FindingsToToc(),
            'page9Findings' => $this->syncPage9FindingsToToc(),
            'page10Findings' => $this->syncPage10FindingsToToc(),
            'page11Findings' => $this->syncPage11FindingsToToc(),
            'page12Findings' => $this->syncPage12FindingsToToc(),
            'page13Findings' => $this->syncPage13FindingsToToc(),
            'page14Findings' => $this->syncPage14FindingsToToc(),
            'page15Findings' => $this->syncPage15FindingsToToc(),
            'page16Findings' => $this->syncPage16FindingsToToc(),
            'page17Findings' => $this->syncPage17FindingsToToc(),
            'page18Findings' => $this->syncPage18FindingsToToc(),
            default => null,
        };
    }

    protected function syncAllFinancialFindingsToToc(): void
    {
        $this->rebuildTocFromReportBlocks();
    }

    /**
     * @deprecated Use rebuildTocFromReportBlocks()
     */
    protected function rebuildTocFromReportSections(): void
    {
        $this->rebuildTocFromReportBlocks();
    }

    /** @deprecated Use rebuildTocFromReportSections() */
    protected function rebuildTocFromFinancialFindings(): void
    {
        $this->rebuildTocFromReportSections();
    }

    protected function nextSectionSerial(): string
    {
        $map = [
            '০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4',
            '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9',
        ];
        $max = 0;
        foreach ($this->reportSections as $section) {
            $serial = trim((string) ($section['serial'] ?? ''));
            $latin = strtr($serial, $map);
            if (preg_match('/^(\d+)/', $latin, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return \App\Support\BanglaNumerals::fromInt(max(1, $max + 1)).'.০';
    }

    protected function nextFindingSerialForSection(int $sectionIndex, ?string $sectionSerial = null): string
    {
        $map = [
            '০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4',
            '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9',
        ];
        $rev = array_flip($map);

        $sectionSerial = $sectionSerial ?? (string) ($this->reportSections[$sectionIndex]['serial'] ?? '১.০');
        $latinSection = strtr($sectionSerial, $map);
        $prefix = '1';
        if (preg_match('/^(\d+)/', $latinSection, $m)) {
            $prefix = $m[1];
        }
        $bnPrefix = strtr($prefix, $rev);

        $max = 0;
        foreach ((array) ($this->reportSections[$sectionIndex]['findings'] ?? []) as $finding) {
            $serial = trim((string) ($finding['serial'] ?? ''));
            $latin = strtr($serial, $map);
            if (preg_match('/^'.$prefix.'[\.٫.](\d+)$/', $latin, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return $bnPrefix.'.'.\App\Support\BanglaNumerals::fromInt(max(1, $max + 1));
    }

    protected function nextFinancialFindingSerial(): string
    {
        $this->ensureReportSectionsDefaults();

        return $this->nextFindingSerialForSection(0);
    }

    protected function makeUniqueIndicatorCode(): string
    {
        do {
            $code = 'রিপোর্ট-'.now('Asia/Dhaka')->format('ymdHis').'-'.Str::lower(Str::random(4));
        } while (AuditIndicator::query()->where('indicator_code', $code)->exists());

        return $code;
    }

    protected function mapRiskToFindingRating(string $risk): string
    {
        $risk = Str::lower(trim($risk));

        return match (true) {
            str_contains($risk, 'major') || str_contains($risk, 'উচ্চ') => 'Major (B)',
            str_contains($risk, 'medium') || str_contains($risk, 'moderate') || str_contains($risk, 'মধ্যম') => 'Medium (C)',
            str_contains($risk, 'minor') || str_contains($risk, 'নিম্ন') || str_contains($risk, 'low') => 'Minor (D)',
            str_contains($risk, 'unsatisfactory') || str_contains($risk, 'critical') => 'Unsatisfactory (F)',
            default => '',
        };
    }

    protected function syncTocFindingFromFinancial(int $index): void
    {
        $this->rebuildTocFromReportBlocks();
    }

    protected function tocInsertIndexForSerial(string $serial): int
    {
        $prefix = null;
        if (preg_match('/^(.+?)[\.٫]/u', $serial, $matches)) {
            $prefix = $matches[1];
        }

        $sectionSerial = $prefix !== null ? $prefix.'.০' : null;
        $lastFamilyIndex = null;
        $sectionIndex = null;

        foreach ($this->tocRows as $i => $row) {
            $rowSerial = (string) ($row['serial'] ?? '');

            if (
                $sectionSerial !== null
                && ($row['type'] ?? '') === 'section'
                && $rowSerial === $sectionSerial
            ) {
                $sectionIndex = $i;
            }

            if (
                $prefix !== null
                && ($row['type'] ?? 'item') === 'item'
                && preg_match('/^'.preg_quote($prefix, '/').'[\.٫]/u', $rowSerial)
            ) {
                $lastFamilyIndex = $i;
            }
        }

        if ($lastFamilyIndex !== null) {
            return $lastFamilyIndex + 1;
        }

        if ($sectionIndex !== null) {
            return $sectionIndex + 1;
        }

        return count($this->tocRows);
    }

    protected function syncFinancialFindingsFromToc(): void
    {
        $this->ensureTocDefaults();

        $wanted = ['১.১', '১.২'];
        $fromToc = [];

        foreach ($this->tocRows as $row) {
            if (($row['type'] ?? 'item') !== 'item') {
                continue;
            }
            $serial = trim((string) ($row['serial'] ?? ''));
            if (! in_array($serial, $wanted, true)) {
                continue;
            }
            $fromToc[$serial] = $row;
        }

        $findings = [];
        foreach ($wanted as $serial) {
            $row = $fromToc[$serial] ?? null;
            $findings[] = [
                'serial' => $serial,
                'title' => 'শিরোনাম',
                'body' => (string) ($row['finding'] ?? ($serial === '১.১'
                    ? 'ভ্যাট ও ট্যাক্স কর্তন না করা'
                    : 'ভ্যাট ও ট্যাক্স পরিশোধ না করা')),
                'rating' => (string) ($row['rating'] ?? 'Major (B)'),
                'amount' => (string) ($row['amount'] ?? ''),
                'indicator_id' => null,
                'indicator_code' => null,
            ];
        }

        $this->financialFindings = $findings;
    }

    /**
     * @return array{total_population:string,sample_size:string,instances_found:string,percentage:string}
     */
    protected function blankObservationRow(): array
    {
        return [
            'total_population' => '',
            'sample_size' => '',
            'instances_found' => '',
            'percentage' => '',
        ];
    }

    protected function page4Payload(): array
    {
        $this->ensureReportBlocksDefaults();
        $blocks = [];
        foreach ($this->reportBlocks as $block) {
            if (($block['type'] ?? '') === 'custom_table') {
                $blocks[] = CustomTableSchema::normalize(is_array($block) ? $block : []);
            } else {
                $blocks[] = $block;
            }
        }
        $this->reportBlocks = array_values($blocks);

        return [
            'financial_section_title' => $this->financial_section_title,
            'financialFindings' => $this->financialFindings,
            'reportSections' => $this->reportSections,
            'reportBlocks' => $this->reportBlocks,
            'financial_criteria' => $this->financial_criteria,
            'vatObservationRows' => $this->vatObservationRows,
            'taxObservationRows' => $this->taxObservationRows,
        ];
    }

    protected function ensurePage5Defaults(): void
    {
        if ($this->expenseDetailRows === []) {
            $descriptions = [
                'স্টেশনারী',
                'আপ্যায়ন ভ্যাটএবল',
                'আপ্যায়ন ননভ্যাটএবল',
                'ইন্টারনেট বিল',
                'ডাক ও ফটোকপি',
                'আইপিএস ও স্ক্যানার',
                'অফিস ভাড়া',
            ];

            $this->expenseDetailRows = collect($descriptions)
                ->map(fn (string $description) => $this->blankExpenseDetailRow($description))
                ->push($this->blankExpenseDetailRow('মোট', true))
                ->all();
        }

        if ($this->expense_detail_risk === '') {
            $this->expense_detail_risk = 'এনবিআর এর নির্দেশনা অনুসরণ না করায় বহিঃনিরীক্ষা কর্তৃক আপত্তি উত্থাপিত হওয়ার আশঙ্কা।';
        }

        if ($this->finding13_body === '') {
            $this->finding13_body = 'ভ্যাট ও ট্যাক্স এর টাকা …… মাসে …… দিন করে হস্তমজুদ রেখে সরকারী কোষাগারে জমা প্রদান';
        }

        if ($this->finding13_criteria === '') {
            $this->finding13_criteria = 'সংস্থার আর্থিক নীতিমালা অনুযায়ী কোন অর্থ হিসাব বহির্ভূত হস্তমজুদ না রাখা।';
        }

        if ($this->finding13_risk === '') {
            $this->finding13_risk = 'আর্থিক অনিয়ম বা তহবিল তছরুপ হওয়ার আশঙ্কা';
        }

        if ($this->finding13_statsRows === []) {
            $this->finding13_statsRows = [$this->blankObservationRow()];
        }

        if ($this->finding13_depositRows === []) {
            $this->finding13_depositRows = [
                $this->blankDepositRow('আয়কর (ট্যাক্স)'),
                $this->blankDepositRow('মুসক (ভ্যাট)'),
            ];
        }
    }

    /**
     * @return array{date_month:string,voucher_no:string,description:string,expense_amount:string,vat_applicable:string,vat_paid:string,vat_diff:string,tax_applicable:string,tax_paid:string,tax_diff:string,is_total:bool}
     */
    protected function blankExpenseDetailRow(string $description = '', bool $isTotal = false): array
    {
        return [
            'date_month' => '',
            'voucher_no' => '',
            'description' => $description,
            'expense_amount' => '',
            'vat_applicable' => '',
            'vat_paid' => '',
            'vat_diff' => '',
            'tax_applicable' => '',
            'tax_paid' => '',
            'tax_diff' => '',
            'is_total' => $isTotal,
        ];
    }

    /**
     * @return array{description:string,month_name:string,withdrawal_date:string,deposit_date:string,amount:string,holding_period:string}
     */
    protected function blankDepositRow(string $description = ''): array
    {
        return [
            'description' => $description,
            'month_name' => '',
            'withdrawal_date' => '',
            'deposit_date' => '',
            'amount' => '',
            'holding_period' => '',
        ];
    }

    public function addExpenseDetailRow(): void
    {
        $this->ensurePage5Defaults();

        $insertAt = count($this->expenseDetailRows);
        foreach ($this->expenseDetailRows as $i => $row) {
            if (! empty($row['is_total'])) {
                $insertAt = $i;
                break;
            }
        }

        array_splice($this->expenseDetailRows, $insertAt, 0, [$this->blankExpenseDetailRow()]);
        $this->expenseDetailRows = array_values($this->expenseDetailRows);
    }

    public function removeExpenseDetailRow(int $index): void
    {
        if (! isset($this->expenseDetailRows[$index])) {
            return;
        }

        if (! empty($this->expenseDetailRows[$index]['is_total'])) {
            return;
        }

        $nonTotal = collect($this->expenseDetailRows)->reject(fn ($row) => ! empty($row['is_total']))->count();
        if ($nonTotal <= 1) {
            return;
        }

        unset($this->expenseDetailRows[$index]);
        $this->expenseDetailRows = array_values($this->expenseDetailRows);
    }

    public function addFinding13StatsRow(): void
    {
        $this->ensurePage5Defaults();
        $this->finding13_statsRows[] = $this->blankObservationRow();
    }

    public function removeFinding13StatsRow(int $index): void
    {
        if (! isset($this->finding13_statsRows[$index]) || count($this->finding13_statsRows) <= 1) {
            return;
        }

        unset($this->finding13_statsRows[$index]);
        $this->finding13_statsRows = array_values($this->finding13_statsRows);
    }

    public function addFinding13DepositRow(): void
    {
        $this->ensurePage5Defaults();
        $this->finding13_depositRows[] = $this->blankDepositRow();
    }

    public function removeFinding13DepositRow(int $index): void
    {
        if (! isset($this->finding13_depositRows[$index]) || count($this->finding13_depositRows) <= 1) {
            return;
        }

        unset($this->finding13_depositRows[$index]);
        $this->finding13_depositRows = array_values($this->finding13_depositRows);
    }

    protected function syncFinding13ToToc(): void
    {
        $serial = trim($this->finding13_serial);
        $body = trim($this->finding13_body);
        if ($serial === '' || $body === '') {
            return;
        }

        $this->ensureTocDefaults();

        foreach ($this->tocRows as $i => $row) {
            if (($row['type'] ?? 'item') !== 'item') {
                continue;
            }

            if (($row['serial'] ?? '') !== $serial) {
                continue;
            }

            $this->tocRows[$i]['finding'] = $body;
            if (filled($this->finding13_rating)) {
                $this->tocRows[$i]['rating'] = $this->finding13_rating;
            }
            if (filled($this->finding13_amount)) {
                $this->tocRows[$i]['amount'] = $this->finding13_amount;
            }

            return;
        }

        $newRow = [
            'type' => 'item',
            'serial' => $serial,
            'finding' => $body,
            'amount' => $this->finding13_amount,
            'rating' => $this->finding13_rating,
            'status' => '',
            'page_no' => '',
            'preview_page' => 2,
        ];

        array_splice($this->tocRows, $this->tocInsertIndexForSerial($serial), 0, [$newRow]);
    }

    protected function page5Payload(): array
    {
        return [
            'expenseDetailRows' => $this->expenseDetailRows,
            'expense_detail_risk' => $this->expense_detail_risk,
            'expense_detail_root_cause' => $this->expense_detail_root_cause,
            'expense_detail_recommendation' => $this->expense_detail_recommendation,
            'expense_detail_bm_reply' => $this->expense_detail_bm_reply,
            'expense_detail_responsible' => $this->expense_detail_responsible,
            'expense_detail_resolution_date' => $this->expense_detail_resolution_date,
            'finding13_serial' => $this->finding13_serial,
            'finding13_title' => $this->finding13_title,
            'finding13_body' => $this->finding13_body,
            'finding13_indicator_id' => $this->finding13_indicator_id,
            'finding13_indicator_code' => $this->finding13_indicator_code,
            'finding13_amount' => $this->finding13_amount,
            'finding13_rating' => $this->finding13_rating,
            'finding13_criteria' => $this->finding13_criteria,
            'finding13_observation' => $this->finding13_observation,
            'finding13_statsRows' => $this->finding13_statsRows,
            'finding13_depositRows' => $this->finding13_depositRows,
            'finding13_risk' => $this->finding13_risk,
            'finding13_root_cause' => $this->finding13_root_cause,
            'finding13_recommendation' => $this->finding13_recommendation,
            'finding13_bm_reply' => $this->finding13_bm_reply,
            'finding13_responsible' => $this->finding13_responsible,
            'finding13_resolution_date' => $this->finding13_resolution_date,
        ];
    }

    protected function ensurePage6Defaults(): void
    {
        $criteria = 'আর্থিক বিধিমালা-৫.৮ এর ৫.৮.১ থেকে ৫.৮.৫ উপধারা অনুযায়ী সকল খরচের সহিত সহপ্রমাণক সংযুক্ত করা বাধ্যতামূলক এবং বাসা ভাড়ার ক্ষেত্রে প্রাপ্তি স্বীকারপত্র গ্রহণ করতে হবে।';

        if ($this->page6Findings === []) {
            $this->page6Findings = [
                $this->blankPage6Finding([
                    'serial' => '১.৪',
                    'body' => 'বাসা ভাড়া প্রদানের ক্ষেত্রে মালিকের নিকট থেকে বা ভাড়া গ্রহণকারীর নিকট থেকে কোন প্রাপ্তি স্বীকারপত্র গ্রহণ না করা এবং প্রদানকৃত চেকের ফটোকপিও সংরক্ষণ না করা …… মাসের ভাড়া …… টাকা।',
                    'rating' => 'Major (B)',
                    'criteria' => $criteria,
                    'detail_intro' => 'বিস্তারিত নিম্নে দেওয়া হলো:',
                    'risk' => "১. সঠিক ব্যক্তির নিকট ভাড়া প্রদান না হওয়ার আশঙ্কা।\n২. আর্থিক অনিয়ম বা তহবিল তছরুপ হওয়ার আশঙ্কা।",
                    'voucher_rows' => 4,
                ]),
                $this->blankPage6Finding([
                    'serial' => '১.৫',
                    'body' => 'খরচের সহপ্রমাণক ব্যতীত বিল পরিশোধ ০৪টি বিল ৫৯৪১ টাকা',
                    'amount' => '৫৯৪১',
                    'rating' => 'Medium (C)',
                    'criteria' => $criteria,
                    'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
                    'risk' => 'মূল ভাউচার ব্যতীত বিল পরিশোধ করায় একই বিল একাধিকবার পরিশোধিত হওয়ার আশঙ্কা এবং সংস্থার আর্থিক ক্ষতির সম্ভাবনা।',
                    'stats' => [
                        'total_population' => '১০',
                        'sample_size' => '০৪',
                        'instances_found' => '০৪',
                        'percentage' => '৪০%',
                    ],
                    'voucher_rows' => 4,
                ]),
            ];

            return;
        }

        foreach ($this->page6Findings as $i => $finding) {
            $stats = $this->rowsFromFinding($finding, 'statsRows', fn () => [$this->blankObservationRow()]);
            $vouchers = $this->rowsFromFinding(
                $finding,
                'voucherRows',
                fn () => array_fill(0, 4, $this->blankVoucherRow())
            );
            $this->page6Findings[$i] = array_merge($this->blankPage6Finding(), $finding, [
                'statsRows' => $stats,
                'voucherRows' => $vouchers,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function blankPage6Finding(array $overrides = []): array
    {
        $voucherCount = (int) ($overrides['voucher_rows'] ?? 4);
        $stats = $overrides['stats'] ?? null;
        unset($overrides['voucher_rows'], $overrides['stats']);

        return array_merge([
            'serial' => '',
            'title' => 'শিরোনাম',
            'body' => '',
            'indicator_id' => null,
            'indicator_code' => null,
            'amount' => '',
            'rating' => '',
            'criteria' => '',
            'observation' => '',
            'detail_intro' => 'বিস্তারিত নিম্নে দেওয়া হলো:',
            'statsRows' => [$stats ? array_merge($this->blankObservationRow(), $stats) : $this->blankObservationRow()],
            'voucherRows' => array_fill(0, max(1, $voucherCount), $this->blankVoucherRow()),
            'risk' => '',
            'root_cause' => '',
            'recommendation' => '',
            'bm_reply' => '',
            'responsible' => '',
            'resolution_date' => '',
        ], $overrides);
    }

    /**
     * @return array{date:string,voucher_type_no:string,description:string,amount:string,remarks:string}
     */
    protected function blankVoucherRow(): array
    {
        return [
            'date' => '',
            'voucher_type_no' => '',
            'description' => '',
            'amount' => '',
            'remarks' => '',
        ];
    }

    public function addPage6StatsRow(int $findingIndex): void
    {
        $this->ensurePage6Defaults();
        if (! isset($this->page6Findings[$findingIndex])) {
            return;
        }
        $this->page6Findings[$findingIndex]['statsRows'][] = $this->blankObservationRow();
    }

    public function removePage6StatsRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page6Findings[$findingIndex]['statsRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page6Findings[$findingIndex]['statsRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page6Findings[$findingIndex]['statsRows'] = array_values($rows);
    }

    public function addPage6VoucherRow(int $findingIndex): void
    {
        $this->ensurePage6Defaults();
        if (! isset($this->page6Findings[$findingIndex])) {
            return;
        }
        $this->page6Findings[$findingIndex]['voucherRows'][] = $this->blankVoucherRow();
    }

    public function removePage6VoucherRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page6Findings[$findingIndex]['voucherRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page6Findings[$findingIndex]['voucherRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page6Findings[$findingIndex]['voucherRows'] = array_values($rows);
    }

    protected function syncPage6FindingsToToc(): void
    {
        $this->ensureTocDefaults();

        foreach ($this->page6Findings as $finding) {
            $serial = trim((string) ($finding['serial'] ?? ''));
            $body = trim((string) ($finding['body'] ?? ''));
            if ($serial === '' || $body === '') {
                continue;
            }

            $updated = false;
            foreach ($this->tocRows as $i => $row) {
                if (($row['type'] ?? 'item') !== 'item') {
                    continue;
                }
                if (($row['serial'] ?? '') !== $serial) {
                    continue;
                }

                $this->tocRows[$i]['finding'] = $body;
                if (filled($finding['rating'] ?? null)) {
                    $this->tocRows[$i]['rating'] = (string) $finding['rating'];
                }
                if (filled($finding['amount'] ?? null)) {
                    $this->tocRows[$i]['amount'] = (string) $finding['amount'];
                }
                $updated = true;
                break;
            }

            if ($updated) {
                continue;
            }

            array_splice($this->tocRows, $this->tocInsertIndexForSerial($serial), 0, [[
                'type' => 'item',
                'serial' => $serial,
                'finding' => $body,
                'amount' => (string) ($finding['amount'] ?? ''),
                'rating' => (string) ($finding['rating'] ?? ''),
                'status' => '',
                'page_no' => '',
                'preview_page' => 2,
            ]]);
        }
    }

    protected function page6Payload(): array
    {
        return [
            'page6Findings' => $this->page6Findings,
        ];
    }

    protected function ensurePage7Defaults(): void
    {
        $bonusCriteria = 'সংস্থার চাকরির বিধিমালার ৫.২ উৎসব ভাতা; সংস্থার কর্মীদের প্রতি অর্থবছরে দু’টি উৎসব ভাতা দেয়া হবে। ১. প্রতিটি উৎসব ভাতা মূল বেতনের সমান হবে তবে কৃষকের ক্ষেত্রে মোট বেতনের অর্ধেক। ৬. ক) চাকরির মেয়াদ এক বছর পূর্ণ হয়েছে এমন কর্মীরা পুরো উৎসব ভাতা পাবেন। খ) চাকরির প্রথম ছয় মাস উৎসব ভাতা প্রযোজ্য হবে না। গ) যাদের চাকরির বয়স ছয় মাসের বেশি কিন্তু এক বছরের কম তাদের আনুপাতিক হারে উৎসব ভাতা প্রদেয় হবে।';

        if ($this->page7Findings === []) {
            $this->page7Findings = [
                $this->blankPage7Finding([
                    'serial' => '১.৬',
                    'body' => 'বাজেট অতিরিক্ত খরচের ক্ষেত্রে অনুমোদন না নেওয়া …… টি খাতে ……… টাকা',
                    'rating' => 'Medium (C)',
                    'criteria' => '০৮ আগস্ট’২২ তারিখে প্রকাশিত “সমন্বয় সভায় গৃহীত সিদ্ধান্তের আলোকে চিঠি-৩৯” এর ১০ নং সিদ্ধান্ত অনুযায়ী বাজেট বরাদ্দের অতিরিক্ত খরচ করা যাবেনা। বিশেষ প্রয়োজনে বাজেট অতিরিক্ত খরচ করতে হলে কর্তৃপক্ষের পূর্বানুমোদন গ্রহণ সাপেক্ষে অতিরিক্ত করা যাবে। খরচের পরে অনুমোদনের জন্য আবেদন করা যাবেনা।',
                    'detail_type' => 'budget',
                    'budget_year' => '২০২২-২০২৩',
                    'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
                    'risk' => 'অনুমোদন ব্যতীত খরচের ফলে শাখার অভ্যন্তরীণ নিয়ন্ত্রণ ব্যবস্থা ব্যাহত হতে পারে এবং অপ্রয়োজনীয় খরচের প্রবণতা বৃদ্ধি পেতে পারে।',
                    'stats' => [
                        'total_population' => '১৫',
                        'sample_size' => '০১৫',
                        'instances_found' => '০৮',
                        'percentage' => '৫৩%',
                    ],
                    'budget_heads' => [
                        'সমিতি প্রধান মিটিং খরচ',
                        'ইন্টারনেট বিল',
                        'স্টেশনারী অফিস সাপ্লাই',
                        'ব্যাংক চার্জ',
                        'বিবিধ খরচ',
                        'স্টাফ রিট্রিট',
                        'স্টাফ ইনসেন্টিভ',
                    ],
                ]),
                $this->blankPage7Finding([
                    'serial' => '১.৭',
                    'body' => 'প্রযোজ্য না হলেও চাকরির ২মাসের কম বয়সী কৃষককে বোনাস প্রদান ৪৫০ টাকা।',
                    'amount' => '৪৫০',
                    'rating' => 'Medium (C)',
                    'criteria' => $bonusCriteria,
                    'detail_type' => 'bonus',
                    'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
                    'risk' => 'নীতিমালা বহির্ভূত বোনাস প্রদান সংস্থার আর্থিক ক্ষতি',
                ]),
            ];

            return;
        }

        foreach ($this->page7Findings as $i => $finding) {
            // Older drafts stored ১.৭ without the bonus detail table.
            if (($finding['serial'] ?? '') === '১.৭' && ($finding['detail_type'] ?? 'none') === 'none') {
                $finding['detail_type'] = 'bonus';
                if (($finding['detail_intro'] ?? '') === '') {
                    $finding['detail_intro'] = 'নিম্নে বিস্তারিত দেওয়া হলো:';
                }
                if (($finding['risk'] ?? '') === '') {
                    $finding['risk'] = 'নীতিমালা বহির্ভূত বোনাস প্রদান সংস্থার আর্থিক ক্ষতি';
                }
            }

            $stats = $this->rowsFromFinding($finding, 'statsRows', fn () => [$this->blankObservationRow()]);
            $budgetRows = $this->rowsFromFinding(
                $finding,
                'budgetRows',
                fn () => (($finding['detail_type'] ?? 'none') === 'budget') ? $this->defaultBudgetRows() : []
            );
            $bonusRows = $this->rowsFromFinding(
                $finding,
                'bonusRows',
                fn () => (($finding['detail_type'] ?? 'none') === 'bonus') ? [$this->blankBonusRow()] : []
            );
            $this->page7Findings[$i] = array_merge($this->blankPage7Finding(), $finding, [
                'statsRows' => $stats,
                'budgetRows' => $budgetRows,
                'bonusRows' => $bonusRows,
                'budget_year' => (string) ($finding['budget_year'] ?? '২০২২-২০২৩'),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function blankPage7Finding(array $overrides = []): array
    {
        $stats = $overrides['stats'] ?? null;
        $heads = $overrides['budget_heads'] ?? null;
        unset($overrides['stats'], $overrides['budget_heads']);

        $detailType = (string) ($overrides['detail_type'] ?? 'none');
        $budgetRows = [];
        $bonusRows = [];
        if ($detailType === 'budget') {
            $budgetRows = is_array($heads)
                ? collect($heads)->map(fn ($h) => $this->blankBudgetRow((string) $h))->push($this->blankBudgetRow('মোট', true))->all()
                : $this->defaultBudgetRows();
        }
        if ($detailType === 'bonus') {
            $bonusRows = [$this->blankBonusRow()];
        }

        return array_merge([
            'serial' => '',
            'title' => 'শিরোনাম',
            'body' => '',
            'indicator_id' => null,
            'indicator_code' => null,
            'amount' => '',
            'rating' => '',
            'criteria' => '',
            'observation' => '',
            'detail_type' => 'none',
            'budget_year' => '২০২২-২০২৩',
            'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
            'statsRows' => [$stats ? array_merge($this->blankObservationRow(), $stats) : $this->blankObservationRow()],
            'budgetRows' => $budgetRows,
            'bonusRows' => $bonusRows,
            'risk' => '',
            'root_cause' => '',
            'recommendation' => '',
            'bm_reply' => '',
            'responsible' => '',
            'resolution_date' => '',
        ], $overrides);
    }

    /**
     * @return list<array{budget_head:string,budget_annual:string,budget_upto_june:string,actual_expense:string,difference:string,is_total:bool}>
     */
    protected function defaultBudgetRows(): array
    {
        $heads = [
            'সমিতি প্রধান মিটিং খরচ',
            'ইন্টারনেট বিল',
            'স্টেশনারী অফিস সাপ্লাই',
            'ব্যাংক চার্জ',
            'বিবিধ খরচ',
            'স্টাফ রিট্রিট',
            'স্টাফ ইনসেন্টিভ',
        ];

        return collect($heads)
            ->map(fn (string $head) => $this->blankBudgetRow($head))
            ->push($this->blankBudgetRow('মোট', true))
            ->all();
    }

    /**
     * @return array{budget_head:string,budget_annual:string,budget_upto_june:string,actual_expense:string,difference:string,is_total:bool}
     */
    protected function blankBudgetRow(string $head = '', bool $isTotal = false): array
    {
        return [
            'budget_head' => $head,
            'budget_annual' => '',
            'budget_upto_june' => '',
            'actual_expense' => '',
            'difference' => '',
            'is_total' => $isTotal,
        ];
    }

    /**
     * @return array{joining_date:string,bonus_date_voucher:string,service_age:string,bonus_amount:string}
     */
    protected function blankBonusRow(): array
    {
        return [
            'joining_date' => '',
            'bonus_date_voucher' => '',
            'service_age' => '',
            'bonus_amount' => '',
        ];
    }

    public function addPage7StatsRow(int $findingIndex): void
    {
        $this->ensurePage7Defaults();
        if (! isset($this->page7Findings[$findingIndex])) {
            return;
        }
        $this->page7Findings[$findingIndex]['statsRows'][] = $this->blankObservationRow();
    }

    public function removePage7StatsRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page7Findings[$findingIndex]['statsRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page7Findings[$findingIndex]['statsRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page7Findings[$findingIndex]['statsRows'] = array_values($rows);
    }

    public function addPage7BudgetRow(int $findingIndex): void
    {
        $this->ensurePage7Defaults();
        if (! isset($this->page7Findings[$findingIndex])) {
            return;
        }

        if (($this->page7Findings[$findingIndex]['detail_type'] ?? 'none') !== 'budget') {
            $this->page7Findings[$findingIndex]['detail_type'] = 'budget';
        }

        $rows = $this->page7Findings[$findingIndex]['budgetRows'] ?? [];
        if ($rows === []) {
            $this->page7Findings[$findingIndex]['budgetRows'] = $this->defaultBudgetRows();

            return;
        }

        $insertAt = count($rows);
        foreach ($rows as $i => $row) {
            if (! empty($row['is_total'])) {
                $insertAt = $i;
                break;
            }
        }
        array_splice($rows, $insertAt, 0, [$this->blankBudgetRow()]);
        $this->page7Findings[$findingIndex]['budgetRows'] = array_values($rows);
    }

    public function updatedPage7Findings(mixed $value, ?string $key = null): void
    {
        if (! is_string($key) || ! preg_match('/^(\d+)\.detail_type$/', $key, $matches)) {
            return;
        }

        $index = (int) $matches[1];
        if (! isset($this->page7Findings[$index])) {
            return;
        }

        if (($this->page7Findings[$index]['detail_type'] ?? '') === 'budget'
            && empty($this->page7Findings[$index]['budgetRows'])) {
            $this->page7Findings[$index]['budgetRows'] = $this->defaultBudgetRows();
        }

        if (($this->page7Findings[$index]['detail_type'] ?? '') === 'bonus'
            && empty($this->page7Findings[$index]['bonusRows'])) {
            $this->page7Findings[$index]['bonusRows'] = [$this->blankBonusRow()];
        }
    }

    public function addPage7BonusRow(int $findingIndex): void
    {
        $this->ensurePage7Defaults();
        if (! isset($this->page7Findings[$findingIndex])) {
            return;
        }
        if (($this->page7Findings[$findingIndex]['detail_type'] ?? 'none') !== 'bonus') {
            $this->page7Findings[$findingIndex]['detail_type'] = 'bonus';
        }
        $this->page7Findings[$findingIndex]['bonusRows'][] = $this->blankBonusRow();
    }

    public function removePage7BonusRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page7Findings[$findingIndex]['bonusRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page7Findings[$findingIndex]['bonusRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page7Findings[$findingIndex]['bonusRows'] = array_values($rows);
    }

    public function removePage7BudgetRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page7Findings[$findingIndex]['budgetRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page7Findings[$findingIndex]['budgetRows'];
        if (! empty($rows[$rowIndex]['is_total'])) {
            return;
        }
        $nonTotal = collect($rows)->reject(fn ($row) => ! empty($row['is_total']))->count();
        if ($nonTotal <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page7Findings[$findingIndex]['budgetRows'] = array_values($rows);
    }

    protected function syncPage7FindingsToToc(): void
    {
        $this->ensureTocDefaults();

        foreach ($this->page7Findings as $finding) {
            $serial = trim((string) ($finding['serial'] ?? ''));
            $body = trim((string) ($finding['body'] ?? ''));
            if ($serial === '' || $body === '') {
                continue;
            }

            $updated = false;
            foreach ($this->tocRows as $i => $row) {
                if (($row['type'] ?? 'item') !== 'item') {
                    continue;
                }
                if (($row['serial'] ?? '') !== $serial) {
                    continue;
                }

                $this->tocRows[$i]['finding'] = $body;
                if (filled($finding['rating'] ?? null)) {
                    $this->tocRows[$i]['rating'] = (string) $finding['rating'];
                }
                if (filled($finding['amount'] ?? null)) {
                    $this->tocRows[$i]['amount'] = (string) $finding['amount'];
                }
                $updated = true;
                break;
            }

            if ($updated) {
                continue;
            }

            array_splice($this->tocRows, $this->tocInsertIndexForSerial($serial), 0, [[
                'type' => 'item',
                'serial' => $serial,
                'finding' => $body,
                'amount' => (string) ($finding['amount'] ?? ''),
                'rating' => (string) ($finding['rating'] ?? ''),
                'status' => '',
                'page_no' => '',
                'preview_page' => 2,
            ]]);
        }
    }

    protected function page7Payload(): array
    {
        return [
            'page7Findings' => $this->page7Findings,
        ];
    }

    protected function ensurePage8Defaults(): void
    {
        if ($this->page8Findings === []) {
            $this->page8Findings = [
                $this->blankPage8Finding([
                    'serial' => '১.৮',
                    'body' => "২০২২-২৩ অর্থবছরের Cost of fund Calculations জুলাই'২২ হতে জুন'২৩ পর্যন্ত কম লাভ হিসাবভুক্ত করা হয় ২,৩৮৪ টাকা।",
                    'amount' => '২,৩৮৪',
                    'rating' => 'Minor (D)',
                    'criteria' => 'সংস্থার প্রধান কার্যালয়ের নির্দেশনা অনুযায়ী প্রধান কার্যালয় তহবিলের উপরে প্রতিমাসেই ১০% হিসাবে লাভ ধার্য করতে হবে।',
                    'detail_type' => 'cost_of_fund',
                    'detail_intro' => '',
                    'risk' => 'কম/বেশি লাভ হিসাবভুক্তি করণ শাখায় আয় ব্যয় এর উপরে প্রভাব ফেলে',
                    'stats' => [
                        'total_population' => '১২',
                        'sample_size' => '১২',
                        'instances_found' => '১০',
                        'percentage' => '৮৩%',
                    ],
                ]),
            ];

            return;
        }

        foreach ($this->page8Findings as $i => $finding) {
            $stats = $this->rowsFromFinding($finding, 'statsRows', fn () => [$this->blankObservationRow()]);
            $cofRows = $this->rowsFromFinding(
                $finding,
                'cofRows',
                fn () => (($finding['detail_type'] ?? 'cost_of_fund') === 'cost_of_fund') ? $this->defaultCostOfFundRows() : []
            );
            $this->page8Findings[$i] = array_merge($this->blankPage8Finding(), $finding, [
                'statsRows' => $stats,
                'cofRows' => $cofRows,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function blankPage8Finding(array $overrides = []): array
    {
        $stats = $overrides['stats'] ?? null;
        unset($overrides['stats']);

        $detailType = (string) ($overrides['detail_type'] ?? 'cost_of_fund');
        $cofRows = ($detailType === 'cost_of_fund') ? $this->defaultCostOfFundRows() : [];

        return array_merge([
            'serial' => '',
            'title' => 'শিরোনাম',
            'body' => '',
            'indicator_id' => null,
            'indicator_code' => null,
            'amount' => '',
            'rating' => '',
            'criteria' => '',
            'observation' => '',
            'detail_type' => 'cost_of_fund',
            'detail_intro' => '',
            'statsRows' => [$stats ? array_merge($this->blankObservationRow(), $stats) : $this->blankObservationRow()],
            'cofRows' => $cofRows,
            'risk' => '',
            'root_cause' => '',
            'recommendation' => '',
            'bm_reply' => '',
            'responsible' => '',
            'resolution_date' => '',
        ], $overrides);
    }

    /**
     * @return list<array{month_name:string,opening_balance:string,closing_balance:string,total_balance:string,avg_balance:string,profit_rate_10:string,monthly_profit:string,branch_charged:string,variance:string,is_total:bool}>
     */
    protected function defaultCostOfFundRows(): array
    {
        $months = [
            'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর',
            'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন',
        ];

        return collect($months)
            ->map(fn (string $month) => $this->blankCostOfFundRow($month))
            ->push($this->blankCostOfFundRow('মোট', true))
            ->all();
    }

    /**
     * @return array{month_name:string,opening_balance:string,closing_balance:string,total_balance:string,avg_balance:string,profit_rate_10:string,monthly_profit:string,branch_charged:string,variance:string,is_total:bool}
     */
    protected function blankCostOfFundRow(string $month = '', bool $isTotal = false): array
    {
        return [
            'month_name' => $month,
            'opening_balance' => '',
            'closing_balance' => '',
            'total_balance' => '',
            'avg_balance' => '',
            'profit_rate_10' => '',
            'monthly_profit' => '',
            'branch_charged' => '',
            'variance' => '',
            'is_total' => $isTotal,
        ];
    }

    public function addPage8StatsRow(int $findingIndex): void
    {
        $this->ensurePage8Defaults();
        if (! isset($this->page8Findings[$findingIndex])) {
            return;
        }
        $this->page8Findings[$findingIndex]['statsRows'][] = $this->blankObservationRow();
    }

    public function removePage8StatsRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page8Findings[$findingIndex]['statsRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page8Findings[$findingIndex]['statsRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page8Findings[$findingIndex]['statsRows'] = array_values($rows);
    }

    public function addPage8CofRow(int $findingIndex): void
    {
        $this->ensurePage8Defaults();
        if (! isset($this->page8Findings[$findingIndex])) {
            return;
        }

        $rows = $this->page8Findings[$findingIndex]['cofRows'] ?? [];
        if ($rows === []) {
            $this->page8Findings[$findingIndex]['cofRows'] = $this->defaultCostOfFundRows();

            return;
        }

        $insertAt = count($rows);
        foreach ($rows as $i => $row) {
            if (! empty($row['is_total'])) {
                $insertAt = $i;
                break;
            }
        }
        array_splice($rows, $insertAt, 0, [$this->blankCostOfFundRow()]);
        $this->page8Findings[$findingIndex]['cofRows'] = array_values($rows);
    }

    public function removePage8CofRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page8Findings[$findingIndex]['cofRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page8Findings[$findingIndex]['cofRows'];
        if (! empty($rows[$rowIndex]['is_total'])) {
            return;
        }
        $nonTotal = collect($rows)->reject(fn ($row) => ! empty($row['is_total']))->count();
        if ($nonTotal <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page8Findings[$findingIndex]['cofRows'] = array_values($rows);
    }

    protected function syncPage8FindingsToToc(): void
    {
        $this->ensureTocDefaults();

        foreach ($this->page8Findings as $finding) {
            $serial = trim((string) ($finding['serial'] ?? ''));
            $body = trim((string) ($finding['body'] ?? ''));
            if ($serial === '' || $body === '') {
                continue;
            }

            $updated = false;
            foreach ($this->tocRows as $i => $row) {
                if (($row['type'] ?? 'item') !== 'item') {
                    continue;
                }
                if (($row['serial'] ?? '') !== $serial) {
                    continue;
                }

                $this->tocRows[$i]['finding'] = $body;
                if (filled($finding['rating'] ?? null)) {
                    $this->tocRows[$i]['rating'] = (string) $finding['rating'];
                }
                if (filled($finding['amount'] ?? null)) {
                    $this->tocRows[$i]['amount'] = (string) $finding['amount'];
                }
                $updated = true;
                break;
            }

            if ($updated) {
                continue;
            }

            array_splice($this->tocRows, $this->tocInsertIndexForSerial($serial), 0, [[
                'type' => 'item',
                'serial' => $serial,
                'finding' => $body,
                'amount' => (string) ($finding['amount'] ?? ''),
                'rating' => (string) ($finding['rating'] ?? ''),
                'status' => '',
                'page_no' => '',
                'preview_page' => 2,
            ]]);
        }
    }

    protected function page8Payload(): array
    {
        return [
            'page8Findings' => $this->page8Findings,
        ];
    }

    protected function ensurePage9Defaults(): void
    {
        $cashRisk = "• অতিরিক্ত হাতে নগদ শাখার আর্থিক ঝুঁকি বৃদ্ধি করে।\n• প্রতিষ্ঠানের অর্থ ব্যক্তিগত কাজে ব্যবহারের সম্ভাবনা।";

        if ($this->page9Findings === []) {
            $this->page9Findings = [
                $this->blankPage9Finding([
                    'serial' => '১.৯',
                    'body' => 'শাখায় ১০,০০০ টাকার অতিরিক্ত অর্থ হাতে নগদ রাখা -----------দিন।',
                    'rating' => 'Minor (D)',
                    'criteria' => 'আর্থিক বিধিমালার অনুচ্ছেদ নং- ৮.৭.১৩, পৃষ্ঠা নং- ৩০ অনুযায়ী প্রতিদিন লেনদেন শেষে শাখা পর্যায়ে সর্বোচ্চ ২০০০ টাকার অধিক হাতে নগদ রাখলে অনুমোদন গ্রহণের প্রয়োজন হবে।',
                    'detail_type' => 'cash',
                    'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
                    'risk' => $cashRisk,
                    'stats' => [
                        'total_population' => '৯২',
                        'sample_size' => '৯২',
                        'instances_found' => '১৯',
                        'percentage' => '২১%',
                    ],
                ]),
                $this->blankPage9Finding([
                    'serial' => '১.১০',
                    'body' => 'প্রযোজ্য হলেও ৫০০ টাকা উপরে খরচের ক্ষেত্রে রেভিনিউ স্ট্যাম্প ব্যবহার না করা ৩-------টি বিলে যার খরচের পরিমাণ ---------- টাকা',
                    'rating' => 'Minor (D)',
                    'criteria' => 'সরকারি স্ট্যাম্প আইন এবং সংস্থার আর্থিক নীতিমালা অনুযায়ী ৫০০ টাকার উপরে যেকোন রাজস্ব খরচে বিলে ১০ টাকা মূল্যের বিশেষ আঠালোযুক্ত স্ট্যাম্প সংযুক্তি করা।',
                    'observation' => 'অত্র শাখায় নিরীক্ষাকালীন ০১/০৩/২০২৩ তারিখ হতে ৩০/০৬/২০২৩ তারিখ বিভিন্ন খরচের ভাউচার ও তার সহপ্রমাণক যাচাই করে দেখা যায় যে ৫০০ টাকার উপরে খরচের ক্ষেত্রে কিছু কিছু সহপ্রমাণকের মধ্যে রেভিনিউ স্ট্যাম্প ব্যবহার করা হয়নি।',
                    'detail_type' => 'stamp',
                    'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
                    'risk' => 'সরকারি আইন অমান্য করায় সংস্থা জরিমানার আওতায় আসতে পারে।',
                ]),
            ];

            return;
        }

        foreach ($this->page9Findings as $i => $finding) {
            $stats = $this->rowsFromFinding($finding, 'statsRows', fn () => [$this->blankObservationRow()]);
            $detailType = (string) ($finding['detail_type'] ?? 'none');
            $cashRows = $this->rowsFromFinding(
                $finding,
                'cashRows',
                fn () => ($detailType === 'cash') ? $this->defaultCashRows() : []
            );
            $stampRows = $this->rowsFromFinding(
                $finding,
                'stampRows',
                fn () => ($detailType === 'stamp') ? $this->defaultStampRows() : []
            );
            $this->page9Findings[$i] = array_merge($this->blankPage9Finding(), $finding, [
                'statsRows' => $stats,
                'cashRows' => $cashRows,
                'stampRows' => $stampRows,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function blankPage9Finding(array $overrides = []): array
    {
        $stats = $overrides['stats'] ?? null;
        unset($overrides['stats']);

        $detailType = (string) ($overrides['detail_type'] ?? 'none');
        $cashRows = $detailType === 'cash' ? $this->defaultCashRows() : [];
        $stampRows = $detailType === 'stamp' ? $this->defaultStampRows() : [];

        return array_merge([
            'serial' => '',
            'title' => 'শিরোনাম',
            'body' => '',
            'indicator_id' => null,
            'indicator_code' => null,
            'amount' => '',
            'rating' => '',
            'criteria' => '',
            'observation' => '',
            'detail_type' => 'none',
            'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
            'statsRows' => [$stats ? array_merge($this->blankObservationRow(), $stats) : $this->blankObservationRow()],
            'cashRows' => $cashRows,
            'stampRows' => $stampRows,
            'risk' => '',
            'root_cause' => '',
            'recommendation' => '',
            'bm_reply' => '',
            'responsible' => '',
            'resolution_date' => '',
        ], $overrides);
    }

    /**
     * @return list<array{date_1:string,cash_1:string,date_2:string,cash_2:string,date_3:string,cash_3:string}>
     */
    protected function defaultCashRows(): array
    {
        return array_fill(0, 6, $this->blankCashRow());
    }

    /**
     * @return array{date_1:string,cash_1:string,date_2:string,cash_2:string,date_3:string,cash_3:string}
     */
    protected function blankCashRow(): array
    {
        return [
            'date_1' => '',
            'cash_1' => '',
            'date_2' => '',
            'cash_2' => '',
            'date_3' => '',
            'cash_3' => '',
        ];
    }

    /**
     * @return list<array{date:string,voucher_no:string,amount:string,description:string}>
     */
    protected function defaultStampRows(): array
    {
        return array_fill(0, 4, $this->blankStampRow());
    }

    /**
     * @return array{date:string,voucher_no:string,amount:string,description:string}
     */
    protected function blankStampRow(): array
    {
        return [
            'date' => '',
            'voucher_no' => '',
            'amount' => '',
            'description' => '',
        ];
    }

    public function addPage9StatsRow(int $findingIndex): void
    {
        $this->ensurePage9Defaults();
        if (! isset($this->page9Findings[$findingIndex])) {
            return;
        }
        $this->page9Findings[$findingIndex]['statsRows'][] = $this->blankObservationRow();
    }

    public function removePage9StatsRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page9Findings[$findingIndex]['statsRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page9Findings[$findingIndex]['statsRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page9Findings[$findingIndex]['statsRows'] = array_values($rows);
    }

    public function addPage9CashRow(int $findingIndex): void
    {
        $this->ensurePage9Defaults();
        if (! isset($this->page9Findings[$findingIndex])) {
            return;
        }
        $this->page9Findings[$findingIndex]['cashRows'][] = $this->blankCashRow();
    }

    public function removePage9CashRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page9Findings[$findingIndex]['cashRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page9Findings[$findingIndex]['cashRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page9Findings[$findingIndex]['cashRows'] = array_values($rows);
    }

    public function addPage9StampRow(int $findingIndex): void
    {
        $this->ensurePage9Defaults();
        if (! isset($this->page9Findings[$findingIndex])) {
            return;
        }
        $this->page9Findings[$findingIndex]['stampRows'][] = $this->blankStampRow();
    }

    public function removePage9StampRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page9Findings[$findingIndex]['stampRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page9Findings[$findingIndex]['stampRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page9Findings[$findingIndex]['stampRows'] = array_values($rows);
    }

    public function updatedPage9Findings(mixed $value, ?string $key = null): void
    {
        if (! is_string($key) || ! preg_match('/^(\d+)\.detail_type$/', $key, $matches)) {
            return;
        }

        $index = (int) $matches[1];
        if (! isset($this->page9Findings[$index])) {
            return;
        }

        $type = (string) ($this->page9Findings[$index]['detail_type'] ?? '');
        if ($type === 'cash' && empty($this->page9Findings[$index]['cashRows'])) {
            $this->page9Findings[$index]['cashRows'] = $this->defaultCashRows();
        }
        if ($type === 'stamp' && empty($this->page9Findings[$index]['stampRows'])) {
            $this->page9Findings[$index]['stampRows'] = $this->defaultStampRows();
        }
    }

    protected function syncPage9FindingsToToc(): void
    {
        $this->ensureTocDefaults();

        foreach ($this->page9Findings as $finding) {
            $serial = trim((string) ($finding['serial'] ?? ''));
            $body = trim((string) ($finding['body'] ?? ''));
            if ($serial === '' || $body === '') {
                continue;
            }

            $updated = false;
            foreach ($this->tocRows as $i => $row) {
                if (($row['type'] ?? 'item') !== 'item') {
                    continue;
                }
                if (($row['serial'] ?? '') !== $serial) {
                    continue;
                }

                $this->tocRows[$i]['finding'] = $body;
                if (filled($finding['rating'] ?? null)) {
                    $this->tocRows[$i]['rating'] = (string) $finding['rating'];
                }
                if (filled($finding['amount'] ?? null)) {
                    $this->tocRows[$i]['amount'] = (string) $finding['amount'];
                }
                $updated = true;
                break;
            }

            if ($updated) {
                continue;
            }

            array_splice($this->tocRows, $this->tocInsertIndexForSerial($serial), 0, [[
                'type' => 'item',
                'serial' => $serial,
                'finding' => $body,
                'amount' => (string) ($finding['amount'] ?? ''),
                'rating' => (string) ($finding['rating'] ?? ''),
                'status' => '',
                'page_no' => '',
                'preview_page' => 2,
            ]]);
        }
    }

    protected function page9Payload(): array
    {
        return [
            'page9Findings' => $this->page9Findings,
        ];
    }

    protected function ensurePage10Defaults(): void
    {
        if ($this->page10_section_title === '') {
            $this->page10_section_title = '২.০. স্থায়ী সম্পদ নিরীক্ষা (Fixed Asset Audit)';
        }

        $criteria = 'সংস্থার আর্থিক নীতিমালার অনুচ্ছেদ ১২.১, ১২.১.১ ও ১২.১.২ অনুযায়ী এক বছরের অধিক আয়ুষ্কালসম্পন্ন এবং নির্ধারিত মূল্যসীমার উপরে ক্রয়কৃত সম্পদ (জমি ব্যতীত) স্থায়ী সম্পদ হিসাবে গণ্য হবে এবং স্থায়ী সম্পদ রেজিস্টারে (DSK Form No. FMS-022) এন্ট্রি করতে হবে।';

        if ($this->page10Findings === []) {
            $this->page10Findings = [
                $this->blankPage10Finding([
                    'serial' => '২.১',
                    'body' => 'স্থায়ী সম্পদ হলেও তা স্থায়ী সম্পদ হিসাবে না দেখানো এবং স্থায়ী সম্পদ রেজিস্টারে এন্ট্রি না করা যার মূল্য -------- টাকা',
                    'rating' => 'Medium (C)',
                    'criteria' => $criteria,
                    'detail_type' => 'asset',
                    'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
                    'risk' => 'সংস্থার নীতিমালাকে সঠিকভাবে অনুসরণ না করায় যে কোন খরচে আর্থিক অপচয় বা অনিয়ম সৃষ্টির আশংকা থাকে',
                ]),
            ];

            return;
        }

        foreach ($this->page10Findings as $i => $finding) {
            $stats = $this->rowsFromFinding($finding, 'statsRows', fn () => [$this->blankObservationRow()]);
            $assetRows = $this->rowsFromFinding(
                $finding,
                'assetRows',
                fn () => (($finding['detail_type'] ?? 'asset') === 'asset') ? [$this->blankAssetRow()] : []
            );
            $this->page10Findings[$i] = array_merge($this->blankPage10Finding(), $finding, [
                'statsRows' => $stats,
                'assetRows' => $assetRows,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function blankPage10Finding(array $overrides = []): array
    {
        $stats = $overrides['stats'] ?? null;
        unset($overrides['stats']);

        $detailType = (string) ($overrides['detail_type'] ?? 'asset');
        $assetRows = $detailType === 'asset' ? [$this->blankAssetRow()] : [];

        return array_merge([
            'serial' => '',
            'title' => 'শিরোনাম',
            'body' => '',
            'indicator_id' => null,
            'indicator_code' => null,
            'amount' => '',
            'rating' => '',
            'criteria' => '',
            'observation' => '',
            'detail_type' => 'asset',
            'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
            'statsRows' => [$stats ? array_merge($this->blankObservationRow(), $stats) : $this->blankObservationRow()],
            'assetRows' => $assetRows,
            'risk' => '',
            'root_cause' => '',
            'recommendation' => '',
            'bm_reply' => '',
            'responsible' => '',
            'resolution_date' => '',
        ], $overrides);
    }

    /**
     * @return array{purchase_date:string,voucher_no:string,asset_name:string,purchase_price:string,previous_head:string,current_location:string}
     */
    protected function blankAssetRow(): array
    {
        return [
            'purchase_date' => '',
            'voucher_no' => '',
            'asset_name' => '',
            'purchase_price' => '',
            'previous_head' => '',
            'current_location' => '',
        ];
    }

    public function addPage10StatsRow(int $findingIndex): void
    {
        $this->ensurePage10Defaults();
        if (! isset($this->page10Findings[$findingIndex])) {
            return;
        }
        $this->page10Findings[$findingIndex]['statsRows'][] = $this->blankObservationRow();
    }

    public function removePage10StatsRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page10Findings[$findingIndex]['statsRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page10Findings[$findingIndex]['statsRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page10Findings[$findingIndex]['statsRows'] = array_values($rows);
    }

    public function addPage10AssetRow(int $findingIndex): void
    {
        $this->ensurePage10Defaults();
        if (! isset($this->page10Findings[$findingIndex])) {
            return;
        }
        $this->page10Findings[$findingIndex]['assetRows'][] = $this->blankAssetRow();
    }

    public function removePage10AssetRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page10Findings[$findingIndex]['assetRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page10Findings[$findingIndex]['assetRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page10Findings[$findingIndex]['assetRows'] = array_values($rows);
    }

    protected function syncPage10FindingsToToc(): void
    {
        $this->ensureTocDefaults();

        foreach ($this->page10Findings as $finding) {
            $serial = trim((string) ($finding['serial'] ?? ''));
            $body = trim((string) ($finding['body'] ?? ''));
            if ($serial === '' || $body === '') {
                continue;
            }

            $updated = false;
            foreach ($this->tocRows as $i => $row) {
                if (($row['type'] ?? 'item') !== 'item') {
                    continue;
                }
                if (($row['serial'] ?? '') !== $serial) {
                    continue;
                }

                $this->tocRows[$i]['finding'] = $body;
                if (filled($finding['rating'] ?? null)) {
                    $this->tocRows[$i]['rating'] = (string) $finding['rating'];
                }
                if (filled($finding['amount'] ?? null)) {
                    $this->tocRows[$i]['amount'] = (string) $finding['amount'];
                }
                $updated = true;
                break;
            }

            if ($updated) {
                continue;
            }

            array_splice($this->tocRows, $this->tocInsertIndexForSerial($serial), 0, [[
                'type' => 'item',
                'serial' => $serial,
                'finding' => $body,
                'amount' => (string) ($finding['amount'] ?? ''),
                'rating' => (string) ($finding['rating'] ?? ''),
                'status' => '',
                'page_no' => '',
                'preview_page' => 3,
            ]]);
        }
    }

    protected function page10Payload(): array
    {
        return [
            'page10_section_title' => $this->page10_section_title,
            'page10Findings' => $this->page10Findings,
        ];
    }

    protected function ensurePage11Defaults(): void
    {
        $depCriteria = 'সংস্থার আর্থিক নীতিমালার অনুচ্ছেদ ১২ অনুযায়ী স্থায়ী সম্পদ রেজিস্টারে (DSK Form No. FMS-022) সম্পদের পরিচিতি নম্বরসহ এন্ট্রি করতে হবে, অবচয় Written Down Value পদ্ধতিতে হিসাব করতে হবে এবং বার্ষিক ভৌত যাচাই (DSK Form No. FMS-023) সম্পন্ন করতে হবে। মাসিক প্রতিবেদনের সাথে সম্পদ রেজিস্টার/এক্সেল শিটের প্রারম্ভিক মূল্য ও ক্রমপুঞ্জিত অবচয় মিল থাকতে হবে।';

        $quoteCriteria = 'সংস্থার আর্থিক নীতিমালার অনুচ্ছেদ ১২.২.১ অনুযায়ী ৩০,০০০ টাকার অধিক মূল্যের স্থায়ী সম্পদ ক্রয়ের ক্ষেত্রে কমপক্ষে ৩টি কোটেশন সংগ্রহ করতে হবে এবং ক্রয় কমিটির মাধ্যমে ক্রয় সম্পন্ন করতে হবে।';

        if ($this->page11Findings === []) {
            $this->page11Findings = [
                $this->blankPage11Finding([
                    'serial' => '২.২',
                    'body' => 'স্থায়ী সম্পদের প্রারম্ভিক সম্পদ মূল্য ও ক্রমপুঞ্জিত অবচয়ের প্রারম্ভিক স্থিতি মাসিক প্রতিবেদনের সাথে এক্সেল শিটের মিল পাওয়া যায়নি। সম্পদের মূল্য ------- টাকা, ক্রমপুঞ্জিত অবচয় ------- টাকা।',
                    'rating' => 'Medium (C)',
                    'criteria' => $depCriteria,
                    'detail_type' => 'dep_compare',
                    'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
                    'risk' => 'সম্পদ মূল্য ও ক্রমপুঞ্জিত অবচয়ের পার্থক্যের কারণে সমাপনি বছরের অবচয় নির্ণয়ে ভুল থাকার আশংকা যা খরচে বা আয়ে প্রভাব ফেলতে পারে।',
                    'stats' => [
                        'total_population' => '২৫',
                        'sample_size' => '২৫',
                        'instances_found' => '০২',
                        'percentage' => '০৮%',
                    ],
                ]),
                $this->blankPage11Finding([
                    'serial' => '২.৩',
                    'body' => 'প্রযোজ্য ক্ষেত্রে ৩টি কোটেশন সংগ্রহ করার প্রয়োজনীয়তা থাকলেও একক কোটেশনে আইপিএস ক্রয় করা হয়েছে- ৬৬,৩৯৪ টাকা।',
                    'amount' => '৬৬,৩৯৪',
                    'rating' => 'Minor (D)',
                    'criteria' => $quoteCriteria,
                    'observation' => 'নিরীক্ষাকালীন ০৮/০৪/২০২৩ তারিখে দেখা যায় যে আইপিএস ক্রয় করা হয়েছে ৬৬,৩৯৪ টাকায় একক কোটেশনের ভিত্তিতে (Rahimafrooz/MJ Electric), অথচ নীতিমালা অনুযায়ী ৩টি কোটেশন সংগ্রহ করা প্রয়োজন ছিল।',
                    'detail_type' => 'quote',
                    'detail_intro' => 'বিস্তারিত নিম্নে দেওয়া হলো:',
                    'risk' => "নিম্নমানের পণ্য সরবরাহের আশংকা।\nকম মূল্যের পণ্য অধিক মূল্যে ক্রয় আশংকা",
                ]),
            ];

            return;
        }

        foreach ($this->page11Findings as $i => $finding) {
            if (($finding['serial'] ?? '') === '২.৩' && ($finding['detail_type'] ?? 'none') === 'none') {
                $finding['detail_type'] = 'quote';
                if (trim((string) ($finding['risk'] ?? '')) === '') {
                    $finding['risk'] = "নিম্নমানের পণ্য সরবরাহের আশংকা।\nকম মূল্যের পণ্য অধিক মূল্যে ক্রয় আশংকা";
                }
                if (trim((string) ($finding['detail_intro'] ?? '')) === '') {
                    $finding['detail_intro'] = 'বিস্তারিত নিম্নে দেওয়া হলো:';
                }
            }

            $stats = $this->rowsFromFinding($finding, 'statsRows', fn () => [$this->blankObservationRow()]);
            $depRows = $this->rowsFromFinding(
                $finding,
                'depRows',
                fn () => (($finding['detail_type'] ?? 'none') === 'dep_compare') ? $this->defaultDepCompareRows() : []
            );
            $quoteRows = $this->rowsFromFinding(
                $finding,
                'quoteRows',
                fn () => (($finding['detail_type'] ?? 'none') === 'quote') ? $this->defaultQuoteRows() : []
            );
            $this->page11Findings[$i] = array_merge($this->blankPage11Finding(), $finding, [
                'statsRows' => $stats,
                'depRows' => $depRows,
                'quoteRows' => $quoteRows,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function blankPage11Finding(array $overrides = []): array
    {
        $stats = $overrides['stats'] ?? null;
        unset($overrides['stats']);

        $detailType = (string) ($overrides['detail_type'] ?? 'none');
        $depRows = $detailType === 'dep_compare' ? $this->defaultDepCompareRows() : [];
        $quoteRows = $detailType === 'quote' ? $this->defaultQuoteRows() : [];

        return array_merge([
            'serial' => '',
            'title' => 'শিরোনাম',
            'body' => '',
            'indicator_id' => null,
            'indicator_code' => null,
            'amount' => '',
            'rating' => '',
            'criteria' => '',
            'observation' => '',
            'detail_type' => 'none',
            'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
            'statsRows' => [$stats ? array_merge($this->blankObservationRow(), $stats) : $this->blankObservationRow()],
            'depRows' => $depRows,
            'quoteRows' => $quoteRows,
            'risk' => '',
            'root_cause' => '',
            'recommendation' => '',
            'bm_reply' => '',
            'responsible' => '',
            'resolution_date' => '',
        ], $overrides);
    }

    /**
     * @return list<array{asset_group:string,value_report:string,value_register:string,value_diff:string,dep_report:string,dep_register:string,dep_diff:string}>
     */
    protected function defaultDepCompareRows(): array
    {
        return array_fill(0, 5, $this->blankDepCompareRow());
    }

    /**
     * @return array{asset_group:string,value_report:string,value_register:string,value_diff:string,dep_report:string,dep_register:string,dep_diff:string}
     */
    protected function blankDepCompareRow(): array
    {
        return [
            'asset_group' => '',
            'value_report' => '',
            'value_register' => '',
            'value_diff' => '',
            'dep_report' => '',
            'dep_register' => '',
            'dep_diff' => '',
        ];
    }

    /**
     * @return list<array{product_name:string,product_group:string,purchase_date:string,voucher_no:string,amount:string,quote_status:string}>
     */
    protected function defaultQuoteRows(): array
    {
        return array_fill(0, 6, $this->blankQuoteRow());
    }

    /**
     * @return array{product_name:string,product_group:string,purchase_date:string,voucher_no:string,amount:string,quote_status:string}
     */
    protected function blankQuoteRow(): array
    {
        return [
            'product_name' => '',
            'product_group' => '',
            'purchase_date' => '',
            'voucher_no' => '',
            'amount' => '',
            'quote_status' => '',
        ];
    }

    public function addPage11StatsRow(int $findingIndex): void
    {
        $this->ensurePage11Defaults();
        if (! isset($this->page11Findings[$findingIndex])) {
            return;
        }
        $this->page11Findings[$findingIndex]['statsRows'][] = $this->blankObservationRow();
    }

    public function removePage11StatsRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page11Findings[$findingIndex]['statsRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page11Findings[$findingIndex]['statsRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page11Findings[$findingIndex]['statsRows'] = array_values($rows);
    }

    public function addPage11DepRow(int $findingIndex): void
    {
        $this->ensurePage11Defaults();
        if (! isset($this->page11Findings[$findingIndex])) {
            return;
        }
        $this->page11Findings[$findingIndex]['depRows'][] = $this->blankDepCompareRow();
    }

    public function removePage11DepRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page11Findings[$findingIndex]['depRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page11Findings[$findingIndex]['depRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page11Findings[$findingIndex]['depRows'] = array_values($rows);
    }

    public function addPage11QuoteRow(int $findingIndex): void
    {
        $this->ensurePage11Defaults();
        if (! isset($this->page11Findings[$findingIndex])) {
            return;
        }
        $this->page11Findings[$findingIndex]['quoteRows'][] = $this->blankQuoteRow();
    }

    public function removePage11QuoteRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page11Findings[$findingIndex]['quoteRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page11Findings[$findingIndex]['quoteRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page11Findings[$findingIndex]['quoteRows'] = array_values($rows);
    }

    public function updatedPage11Findings(mixed $value, ?string $key = null): void
    {
        if (! is_string($key) || ! preg_match('/^(\d+)\.detail_type$/', $key, $matches)) {
            return;
        }

        $index = (int) $matches[1];
        if (! isset($this->page11Findings[$index])) {
            return;
        }

        if (($this->page11Findings[$index]['detail_type'] ?? '') === 'dep_compare'
            && empty($this->page11Findings[$index]['depRows'])) {
            $this->page11Findings[$index]['depRows'] = $this->defaultDepCompareRows();
        }

        if (($this->page11Findings[$index]['detail_type'] ?? '') === 'quote'
            && empty($this->page11Findings[$index]['quoteRows'])) {
            $this->page11Findings[$index]['quoteRows'] = $this->defaultQuoteRows();
        }
    }

    protected function syncPage11FindingsToToc(): void
    {
        $this->ensureTocDefaults();

        foreach ($this->page11Findings as $finding) {
            $serial = trim((string) ($finding['serial'] ?? ''));
            $body = trim((string) ($finding['body'] ?? ''));
            if ($serial === '' || $body === '') {
                continue;
            }

            $updated = false;
            foreach ($this->tocRows as $i => $row) {
                if (($row['type'] ?? 'item') !== 'item') {
                    continue;
                }
                if (($row['serial'] ?? '') !== $serial) {
                    continue;
                }

                $this->tocRows[$i]['finding'] = $body;
                if (filled($finding['rating'] ?? null)) {
                    $this->tocRows[$i]['rating'] = (string) $finding['rating'];
                }
                if (filled($finding['amount'] ?? null)) {
                    $this->tocRows[$i]['amount'] = (string) $finding['amount'];
                }
                $updated = true;
                break;
            }

            if ($updated) {
                continue;
            }

            array_splice($this->tocRows, $this->tocInsertIndexForSerial($serial), 0, [[
                'type' => 'item',
                'serial' => $serial,
                'finding' => $body,
                'amount' => (string) ($finding['amount'] ?? ''),
                'rating' => (string) ($finding['rating'] ?? ''),
                'status' => '',
                'page_no' => '',
                'preview_page' => 3,
            ]]);
        }
    }

    protected function page11Payload(): array
    {
        return [
            'page11Findings' => $this->page11Findings,
        ];
    }

    protected function ensurePage12Defaults(): void
    {
        $stockCriteria = 'সংস্থার আর্থিক নীতিমালার অনুচ্ছেদ ১১.২ অনুযায়ী সকল ক্রয়কৃত/প্রাপ্ত মালামালের জন্য Store Requisition (SR) ফরম DSK-FMS-018 ব্যবহার করতে হবে। অনুচ্ছেদ ১১.২.৩–১১.২.৬ অনুযায়ী Stock Ledger (DSK-FMS-019) এ সকল প্রাপ্তি ও ইস্যু এন্ট্রি করে ব্যালেন্স হালনাগাদ রাখতে হবে এবং স্টক রেজিস্টার সঠিক নিয়মে সংরক্ষণ করতে হবে।';

        if ($this->page12_section_title === '') {
            $this->page12_section_title = '৩.০. মজুদ ব্যবস্থাপনা নিরীক্ষা (Stock management Audit)';
        }

        if ($this->page12Findings === []) {
            $this->page12Findings = [
                $this->blankPage12Finding([
                    'serial' => '৩.১',
                    'body' => 'স্টক রেজিস্টার সঠিক নিয়মে ব্যবহার না করা এবং ক্রয়কৃত স্টেশনারী দ্রব্যাদি স্টক রেজিস্টারে এন্ট্রি না করা মোট',
                    'rating' => 'Medium (C)',
                    'criteria' => $stockCriteria,
                    'detail_type' => 'stock',
                    'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
                    'risk' => "প্রয়োজনের অতিরিক্ত মালামাল ক্রয়ের মাধ্যমে আর্থিক ক্ষতির আশংকা\nমালামালের ব্যবহার জনিত অপচয় হওয়ার আশংকা",
                ]),
            ];

            return;
        }

        foreach ($this->page12Findings as $i => $finding) {
            $stats = $this->rowsFromFinding($finding, 'statsRows', fn () => [$this->blankObservationRow()]);
            $stockRows = $this->rowsFromFinding(
                $finding,
                'stockRows',
                fn () => (($finding['detail_type'] ?? 'stock') === 'stock') ? $this->defaultStockRows() : []
            );
            $this->page12Findings[$i] = array_merge($this->blankPage12Finding(), $finding, [
                'statsRows' => $stats,
                'stockRows' => $stockRows,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function blankPage12Finding(array $overrides = []): array
    {
        $stats = $overrides['stats'] ?? null;
        unset($overrides['stats']);

        $detailType = (string) ($overrides['detail_type'] ?? 'stock');
        $stockRows = $detailType === 'stock' ? $this->defaultStockRows() : [];

        return array_merge([
            'serial' => '',
            'title' => 'শিরোনাম',
            'body' => '',
            'indicator_id' => null,
            'indicator_code' => null,
            'amount' => '',
            'rating' => '',
            'criteria' => '',
            'observation' => '',
            'detail_type' => 'stock',
            'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
            'statsRows' => [$stats ? array_merge($this->blankObservationRow(), $stats) : $this->blankObservationRow()],
            'stockRows' => $stockRows,
            'risk' => '',
            'root_cause' => '',
            'recommendation' => '',
            'bm_reply' => '',
            'responsible' => '',
            'resolution_date' => '',
        ], $overrides);
    }

    /**
     * @return list<array{product_name:string,purchase_date_voucher:string,purchase_price:string,register_status:string}>
     */
    protected function defaultStockRows(): array
    {
        return array_fill(0, 10, $this->blankStockRow());
    }

    /**
     * @return array{product_name:string,purchase_date_voucher:string,purchase_price:string,register_status:string}
     */
    protected function blankStockRow(): array
    {
        return [
            'product_name' => '',
            'purchase_date_voucher' => '',
            'purchase_price' => '',
            'register_status' => '',
        ];
    }

    public function addPage12StatsRow(int $findingIndex): void
    {
        $this->ensurePage12Defaults();
        if (! isset($this->page12Findings[$findingIndex])) {
            return;
        }
        $this->page12Findings[$findingIndex]['statsRows'][] = $this->blankObservationRow();
    }

    public function removePage12StatsRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page12Findings[$findingIndex]['statsRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page12Findings[$findingIndex]['statsRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page12Findings[$findingIndex]['statsRows'] = array_values($rows);
    }

    public function addPage12StockRow(int $findingIndex): void
    {
        $this->ensurePage12Defaults();
        if (! isset($this->page12Findings[$findingIndex])) {
            return;
        }
        $this->page12Findings[$findingIndex]['stockRows'][] = $this->blankStockRow();
    }

    public function removePage12StockRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page12Findings[$findingIndex]['stockRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page12Findings[$findingIndex]['stockRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page12Findings[$findingIndex]['stockRows'] = array_values($rows);
    }

    public function updatedPage12Findings(mixed $value, ?string $key = null): void
    {
        if (! is_string($key) || ! preg_match('/^(\d+)\.detail_type$/', $key, $matches)) {
            return;
        }

        $index = (int) $matches[1];
        if (! isset($this->page12Findings[$index])) {
            return;
        }

        if (($this->page12Findings[$index]['detail_type'] ?? '') === 'stock'
            && empty($this->page12Findings[$index]['stockRows'])) {
            $this->page12Findings[$index]['stockRows'] = $this->defaultStockRows();
        }
    }

    protected function syncPage12FindingsToToc(): void
    {
        $this->ensureTocDefaults();

        foreach ($this->page12Findings as $finding) {
            $serial = trim((string) ($finding['serial'] ?? ''));
            $body = trim((string) ($finding['body'] ?? ''));
            if ($serial === '' || $body === '') {
                continue;
            }

            $updated = false;
            foreach ($this->tocRows as $i => $row) {
                if (($row['type'] ?? 'item') !== 'item') {
                    continue;
                }
                if (($row['serial'] ?? '') !== $serial) {
                    continue;
                }

                $this->tocRows[$i]['finding'] = $body;
                if (filled($finding['rating'] ?? null)) {
                    $this->tocRows[$i]['rating'] = (string) $finding['rating'];
                }
                if (filled($finding['amount'] ?? null)) {
                    $this->tocRows[$i]['amount'] = (string) $finding['amount'];
                }
                $updated = true;
                break;
            }

            if ($updated) {
                continue;
            }

            array_splice($this->tocRows, $this->tocInsertIndexForSerial($serial), 0, [[
                'type' => 'item',
                'serial' => $serial,
                'finding' => $body,
                'amount' => (string) ($finding['amount'] ?? ''),
                'rating' => (string) ($finding['rating'] ?? ''),
                'status' => '',
                'page_no' => '',
                'preview_page' => 3,
            ]]);
        }
    }

    protected function page12Payload(): array
    {
        return [
            'page12_section_title' => $this->page12_section_title,
            'page12Findings' => $this->page12Findings,
        ];
    }

    protected function ensurePage13Defaults(): void
    {
        $criteria41 = 'সংস্থার মাইক্রোক্রেডিট ম্যানুয়াল (অক্টোবর-২৩) এর অনুচ্ছেদ ১৩, ধারা ১০.২ অনুযায়ী মাঠকর্মীদের দৈনিক আদায়কৃত সঞ্চয়, বীমা, ফরম ফি ইত্যাদি অর্থ হিসাবরক্ষকের নিকট তৎক্ষণাৎ জমা দিতে হবে এবং অফিস তহবিলে যথাযথভাবে হিসাবভুক্ত করতে হবে।';

        if ($this->page13_section_title === '') {
            $this->page13_section_title = '৪.০ কার্যক্রম/পরিচালন (Operational Audit) :';
        }

        if ($this->page13Findings === []) {
            $this->page13Findings = [
                $this->blankPage13Finding([
                    'serial' => '৪.১',
                    'body' => 'আদায়কৃত সঞ্চয় অফিস তহবিলে জমা হওয়া -------- টাকা',
                    'amount' => '২,৬৬৮',
                    'rating' => 'Unsatisfactory (F)',
                    'criteria' => $criteria41,
                    'observation' => 'নিরীক্ষাকালীন ৪ জন মাঠকর্মী ও সহকারী ব্যবস্থাপকের এলাকার সদস্যদের পাসবই যাচাই ও সদস্যদের সাথে আলোচনায় দেখা যায় যে আদায়কৃত সঞ্চয় অফিস তহবিলে জমা না হয়ে মোট ২,৬৬৮ টাকার অনিয়ম পাওয়া গেছে।',
                    'detail_type' => 'samity_collection',
                    'detail_intro' => 'বিস্তারিত নিম্নে দেওয়া হলো:',
                    'risk' => 'শাখায় আর্থিক অনিয়ম সৃষ্টির আশংকা সৃষ্টি হতে পারে।',
                ]),
                $this->blankPage13Finding([
                    'serial' => '৪.২',
                    'body' => 'পাসবই পোস্টিং না দিয়ে সদস্যের নিকট থেকে সঞ্চয় আদায় করা ------ জন সদস্য -------- টাকা',
                    'rating' => 'Major (B)',
                    'detail_type' => 'none',
                    'detail_intro' => '',
                    'risk' => '',
                ]),
            ];

            return;
        }

        foreach ($this->page13Findings as $i => $finding) {
            $stats = $this->rowsFromFinding($finding, 'statsRows', fn () => [$this->blankObservationRow()]);
            $samityRows = $this->rowsFromFinding(
                $finding,
                'samityRows',
                fn () => (($finding['detail_type'] ?? 'none') === 'samity_collection')
                    ? $this->defaultSamityCollectionRows()
                    : []
            );
            $this->page13Findings[$i] = array_merge($this->blankPage13Finding(), $finding, [
                'statsRows' => $stats,
                'samityRows' => $samityRows,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function blankPage13Finding(array $overrides = []): array
    {
        $stats = $overrides['stats'] ?? null;
        unset($overrides['stats']);

        $detailType = (string) ($overrides['detail_type'] ?? 'none');
        $samityRows = $detailType === 'samity_collection' ? $this->defaultSamityCollectionRows() : [];

        return array_merge([
            'serial' => '',
            'title' => 'শিরোনাম',
            'body' => '',
            'indicator_id' => null,
            'indicator_code' => null,
            'amount' => '',
            'rating' => '',
            'criteria' => '',
            'observation' => '',
            'detail_type' => 'none',
            'detail_intro' => 'বিস্তারিত নিম্নে দেওয়া হলো:',
            'statsRows' => [$stats ? array_merge($this->blankObservationRow(), $stats) : $this->blankObservationRow()],
            'samityRows' => $samityRows,
            'risk' => '',
            'root_cause' => '',
            'recommendation' => '',
            'bm_reply' => '',
            'responsible' => '',
            'resolution_date' => '',
        ], $overrides);
    }

    /**
     * @return list<array<string, string>>
     */
    protected function defaultSamityCollectionRows(): array
    {
        return array_fill(0, 10, $this->blankSamityCollectionRow());
    }

    /**
     * @return array{samity_no:string,member_name_id:string,date:string,savings:string,voluntary:string,term:string,installment:string,total_collection:string,deposit_date:string,deposit_amount:string,difference:string,staff_name_id:string}
     */
    protected function blankSamityCollectionRow(): array
    {
        return [
            'samity_no' => '',
            'member_name_id' => '',
            'date' => '',
            'savings' => '',
            'voluntary' => '',
            'term' => '',
            'installment' => '',
            'total_collection' => '',
            'deposit_date' => '',
            'deposit_amount' => '',
            'difference' => '',
            'staff_name_id' => '',
        ];
    }

    public function addPage13StatsRow(int $findingIndex): void
    {
        $this->ensurePage13Defaults();
        if (! isset($this->page13Findings[$findingIndex])) {
            return;
        }
        $this->page13Findings[$findingIndex]['statsRows'][] = $this->blankObservationRow();
    }

    public function removePage13StatsRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page13Findings[$findingIndex]['statsRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page13Findings[$findingIndex]['statsRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page13Findings[$findingIndex]['statsRows'] = array_values($rows);
    }

    public function addPage13SamityRow(int $findingIndex): void
    {
        $this->ensurePage13Defaults();
        if (! isset($this->page13Findings[$findingIndex])) {
            return;
        }
        $this->page13Findings[$findingIndex]['samityRows'][] = $this->blankSamityCollectionRow();
    }

    public function removePage13SamityRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page13Findings[$findingIndex]['samityRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page13Findings[$findingIndex]['samityRows'];
        unset($rows[$rowIndex]);
        $this->page13Findings[$findingIndex]['samityRows'] = array_values($rows);
    }

    public function updatedPage13Findings(mixed $value, ?string $key = null): void
    {
        if (! is_string($key) || ! preg_match('/^(\d+)\.detail_type$/', $key, $matches)) {
            return;
        }

        $index = (int) $matches[1];
        if (! isset($this->page13Findings[$index])) {
            return;
        }

        if (($this->page13Findings[$index]['detail_type'] ?? '') === 'samity_collection'
            && empty($this->page13Findings[$index]['samityRows'])) {
            $this->page13Findings[$index]['samityRows'] = $this->defaultSamityCollectionRows();
        }
    }

    protected function syncPage13FindingsToToc(): void
    {
        $this->ensureTocDefaults();

        foreach ($this->page13Findings as $finding) {
            $serial = trim((string) ($finding['serial'] ?? ''));
            $body = trim((string) ($finding['body'] ?? ''));
            if ($serial === '' || $body === '') {
                continue;
            }

            $updated = false;
            foreach ($this->tocRows as $i => $row) {
                if (($row['type'] ?? 'item') !== 'item') {
                    continue;
                }
                if (($row['serial'] ?? '') !== $serial) {
                    continue;
                }

                $this->tocRows[$i]['finding'] = $body;
                if (filled($finding['rating'] ?? null)) {
                    $this->tocRows[$i]['rating'] = (string) $finding['rating'];
                }
                if (filled($finding['amount'] ?? null)) {
                    $this->tocRows[$i]['amount'] = (string) $finding['amount'];
                }
                $updated = true;
                break;
            }

            if ($updated) {
                continue;
            }

            array_splice($this->tocRows, $this->tocInsertIndexForSerial($serial), 0, [[
                'type' => 'item',
                'serial' => $serial,
                'finding' => $body,
                'amount' => (string) ($finding['amount'] ?? ''),
                'rating' => (string) ($finding['rating'] ?? ''),
                'status' => '',
                'page_no' => '',
                'preview_page' => 3,
            ]]);
        }
    }

    protected function page13Payload(): array
    {
        return [
            'page13_section_title' => $this->page13_section_title,
            'page13Findings' => $this->page13Findings,
        ];
    }

    protected function ensurePage14Defaults(): void
    {
        $criteria43 = '২৭/১০/২০২০ তারিখের সিদ্ধান্ত (স্মারক-৫২, ধারা-২) অনুযায়ী পাসবই পোস্টিং না দিয়ে সদস্যের নিকট থেকে সঞ্চয়/কিস্তি আদায় করা অনিয়ম উৎসাহিত করে এবং এ ধরনের আদায় করা যাবে না।';

        $criteria44 = 'সঞ্চয় ও ঋণ পরিচালন ম্যানুয়াল (পৃষ্ঠা ৬৫, অনুচ্ছেদ ৬.৯) অনুযায়ী সুফলন ঋণ হলো মৌসুমী ও কৃষিভিত্তিক মাইক্রোক্রেডিট। ফসলের প্রকৃতি ও বাজার অবস্থার ভিত্তিতে ঋণের মেয়াদ ৩ থেকে ১২ মাসের মধ্যে নির্ধারণ করতে হবে এবং সফটওয়্যারে সঠিক মেয়াদ পোস্টিং করতে হবে।';

        if ($this->page14Findings === []) {
            $this->page14Findings = [
                $this->blankPage14Finding([
                    'serial' => '৪.৩',
                    'body' => 'পাসবই পোস্টিং না দিয়ে সদস্যের নিকট থেকে কিস্তি আদায় করা ---------- জনকে --------- টাকা',
                    'rating' => 'Major (B)',
                    'criteria' => $criteria43,
                    'detail_type' => 'passbook_installment',
                    'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
                    'risk' => 'শাখায় আর্থিক অনিয়ম সৃষ্টির আশংকা, সদস্যদের সাথে ভুল বোঝাবুঝি এবং সংস্থার ভাবমূর্তি ক্ষুণ্ন হওয়ার আশংকা।',
                ]),
                $this->blankPage14Finding([
                    'serial' => '৪.৪',
                    'body' => 'সফটওয়্যারে সুফলন ঋণের মেয়াদ ভুল পোস্টিং করা ২১ জনের বিতরণ ১০.৮০ লক্ষ টাকা যাদের অতিরিক্ত সেবামূল্য দেখানো হয়েছে ৫৩,৬০০ টাকা।',
                    'amount' => '৫৩,৬০০',
                    'rating' => 'Medium (C)',
                    'criteria' => $criteria44,
                    'detail_type' => 'sufolon_term',
                    'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
                    'risk' => "সঠিক সময়ে আদায়যোগ্য না দেখানোর কারণে কর্মী মাঠ হতে টাকা আদায় করে আর্থিক অনিয়ম করার সুযোগ সৃষ্টি হতে পারে।\nসঠিক সময়ে আদায়যোগ্য না দেখানোর ফলে প্রকৃত বকেয়া না দেখানোর কারণে কর্মসূচির প্রকৃত চিত্র প্রদর্শিত হবে না।",
                ]),
            ];

            return;
        }

        foreach ($this->page14Findings as $i => $finding) {
            if (($finding['serial'] ?? '') === '৪.৪' && ($finding['detail_type'] ?? 'none') === 'none') {
                $finding['detail_type'] = 'sufolon_term';
                if (trim((string) ($finding['detail_intro'] ?? '')) === '') {
                    $finding['detail_intro'] = 'নিম্নে বিস্তারিত দেওয়া হলো:';
                }
                if (trim((string) ($finding['risk'] ?? '')) === '') {
                    $finding['risk'] = "সঠিক সময়ে আদায়যোগ্য না দেখানোর কারণে কর্মী মাঠ হতে টাকা আদায় করে আর্থিক অনিয়ম করার সুযোগ সৃষ্টি হতে পারে।\nসঠিক সময়ে আদায়যোগ্য না দেখানোর ফলে প্রকৃত বকেয়া না দেখানোর কারণে কর্মসূচির প্রকৃত চিত্র প্রদর্শিত হবে না।";
                }
            }

            $stats = $this->rowsFromFinding($finding, 'statsRows', fn () => [$this->blankObservationRow()]);
            $passbookRows = $this->rowsFromFinding(
                $finding,
                'passbookRows',
                fn () => (($finding['detail_type'] ?? 'none') === 'passbook_installment') ? $this->defaultPassbookInstallmentRows() : []
            );
            $sufolonRows = $this->rowsFromFinding(
                $finding,
                'sufolonRows',
                fn () => (($finding['detail_type'] ?? 'none') === 'sufolon_term') ? $this->defaultSufolonTermRows() : []
            );
            $this->page14Findings[$i] = array_merge($this->blankPage14Finding(), $finding, [
                'statsRows' => $stats,
                'passbookRows' => $passbookRows,
                'sufolonRows' => $sufolonRows,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function blankPage14Finding(array $overrides = []): array
    {
        $stats = $overrides['stats'] ?? null;
        unset($overrides['stats']);

        $detailType = (string) ($overrides['detail_type'] ?? 'none');
        $passbookRows = $detailType === 'passbook_installment' ? $this->defaultPassbookInstallmentRows() : [];
        $sufolonRows = $detailType === 'sufolon_term' ? $this->defaultSufolonTermRows() : [];

        return array_merge([
            'serial' => '',
            'title' => 'শিরোনাম',
            'body' => '',
            'indicator_id' => null,
            'indicator_code' => null,
            'amount' => '',
            'rating' => '',
            'criteria' => '',
            'observation' => '',
            'detail_type' => 'none',
            'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
            'statsRows' => [$stats ? array_merge($this->blankObservationRow(), $stats) : $this->blankObservationRow()],
            'passbookRows' => $passbookRows,
            'sufolonRows' => $sufolonRows,
            'risk' => '',
            'root_cause' => '',
            'recommendation' => '',
            'bm_reply' => '',
            'responsible' => '',
            'resolution_date' => '',
        ], $overrides);
    }

    /**
     * @return list<array<string, string>>
     */
    protected function defaultPassbookInstallmentRows(): array
    {
        return array_fill(0, 10, $this->blankPassbookInstallmentRow());
    }

    /**
     * @return array{samity_no:string,member_name_id:string,date:string,savings_amount:string,installment_amount:string,savings_adjustment:string}
     */
    protected function blankPassbookInstallmentRow(): array
    {
        return [
            'samity_no' => '',
            'member_name_id' => '',
            'date' => '',
            'savings_amount' => '',
            'installment_amount' => '',
            'savings_adjustment' => '',
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    protected function defaultSufolonTermRows(): array
    {
        return array_fill(0, 12, $this->blankSufolonTermRow());
    }

    /**
     * @return array{sl_no:string,samity_member_id:string,member_name:string,disbursement_sector:string,disbursement_date:string,actual_term:string,software_last_date:string,software_term:string,disbursed_amount:string,excess_service_charge:string}
     */
    protected function blankSufolonTermRow(): array
    {
        return [
            'sl_no' => '',
            'samity_member_id' => '',
            'member_name' => '',
            'disbursement_sector' => '',
            'disbursement_date' => '',
            'actual_term' => '',
            'software_last_date' => '',
            'software_term' => '',
            'disbursed_amount' => '',
            'excess_service_charge' => '',
        ];
    }

    public function addPage14StatsRow(int $findingIndex): void
    {
        $this->ensurePage14Defaults();
        if (! isset($this->page14Findings[$findingIndex])) {
            return;
        }
        $this->page14Findings[$findingIndex]['statsRows'][] = $this->blankObservationRow();
    }

    public function removePage14StatsRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page14Findings[$findingIndex]['statsRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page14Findings[$findingIndex]['statsRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page14Findings[$findingIndex]['statsRows'] = array_values($rows);
    }

    public function addPage14PassbookRow(int $findingIndex): void
    {
        $this->ensurePage14Defaults();
        if (! isset($this->page14Findings[$findingIndex])) {
            return;
        }
        $this->page14Findings[$findingIndex]['passbookRows'][] = $this->blankPassbookInstallmentRow();
    }

    public function removePage14PassbookRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page14Findings[$findingIndex]['passbookRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page14Findings[$findingIndex]['passbookRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page14Findings[$findingIndex]['passbookRows'] = array_values($rows);
    }

    public function addPage14SufolonRow(int $findingIndex): void
    {
        $this->ensurePage14Defaults();
        if (! isset($this->page14Findings[$findingIndex])) {
            return;
        }
        $this->page14Findings[$findingIndex]['sufolonRows'][] = $this->blankSufolonTermRow();
    }

    public function removePage14SufolonRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page14Findings[$findingIndex]['sufolonRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page14Findings[$findingIndex]['sufolonRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page14Findings[$findingIndex]['sufolonRows'] = array_values($rows);
    }

    public function updatedPage14Findings(mixed $value, ?string $key = null): void
    {
        if (! is_string($key) || ! preg_match('/^(\d+)\.detail_type$/', $key, $matches)) {
            return;
        }

        $index = (int) $matches[1];
        if (! isset($this->page14Findings[$index])) {
            return;
        }

        if (($this->page14Findings[$index]['detail_type'] ?? '') === 'passbook_installment'
            && empty($this->page14Findings[$index]['passbookRows'])) {
            $this->page14Findings[$index]['passbookRows'] = $this->defaultPassbookInstallmentRows();
        }

        if (($this->page14Findings[$index]['detail_type'] ?? '') === 'sufolon_term'
            && empty($this->page14Findings[$index]['sufolonRows'])) {
            $this->page14Findings[$index]['sufolonRows'] = $this->defaultSufolonTermRows();
        }
    }

    protected function syncPage14FindingsToToc(): void
    {
        $this->ensureTocDefaults();

        foreach ($this->page14Findings as $finding) {
            $serial = trim((string) ($finding['serial'] ?? ''));
            $body = trim((string) ($finding['body'] ?? ''));
            if ($serial === '' || $body === '') {
                continue;
            }

            $updated = false;
            foreach ($this->tocRows as $i => $row) {
                if (($row['type'] ?? 'item') !== 'item') {
                    continue;
                }
                if (($row['serial'] ?? '') !== $serial) {
                    continue;
                }

                $this->tocRows[$i]['finding'] = $body;
                if (filled($finding['rating'] ?? null)) {
                    $this->tocRows[$i]['rating'] = (string) $finding['rating'];
                }
                if (filled($finding['amount'] ?? null)) {
                    $this->tocRows[$i]['amount'] = (string) $finding['amount'];
                }
                $updated = true;
                break;
            }

            if ($updated) {
                continue;
            }

            array_splice($this->tocRows, $this->tocInsertIndexForSerial($serial), 0, [[
                'type' => 'item',
                'serial' => $serial,
                'finding' => $body,
                'amount' => (string) ($finding['amount'] ?? ''),
                'rating' => (string) ($finding['rating'] ?? ''),
                'status' => '',
                'page_no' => '',
                'preview_page' => 3,
            ]]);
        }
    }

    protected function page14Payload(): array
    {
        return [
            'page14Findings' => $this->page14Findings,
        ];
    }

    protected function ensurePage15Defaults(): void
    {
        $criteria = 'মাইক্রোফিন৩৬০ সফটওয়্যার অনুযায়ী মাসিক আদায়যোগ্য ঋণের ক্ষেত্রে কোন ঋণ বিতরণ হতে ৩০ দিনের মধ্যে সমিতি কালেকশনবার পরলে আদায়যোগ্য প্রদর্শিত হবে।';

        $risk46 = "ভুল আদায়যোগ্য সময় দেখানোর কারণে প্রকৃত বকেয়া লুকানোর সুযোগ সৃষ্টি হতে পারে।\nআর্থিক অনিয়ম ও কর্মসূচির প্রকৃত চিত্র না দেখানোর আশংকা।";

        if ($this->page15Findings === []) {
            $this->page15Findings = [
                $this->blankPage15Finding([
                    'serial' => '৪.৫',
                    'body' => 'সফটওয়্যার আদায়যোগ্যর সমস্যার কারণে ঋণ বকেয়া হলেও তা বকেয়া না দেখানো ৬০,০০০ টাকা',
                    'amount' => '৬০,০০০',
                    'rating' => 'Medium (C)',
                    'criteria' => $criteria,
                    'detail_type' => 'none',
                    'stats' => [
                        'total_population' => '৩৫',
                        'sample_size' => '৩৫',
                        'instances_found' => '২',
                        'percentage' => '০৬%',
                    ],
                ]),
                $this->blankPage15Finding([
                    'serial' => '৪.৬',
                    'body' => 'বাহ্যিক কোন যৌক্তিক কারণ ব্যতীত সফটওয়্যার কর্তৃক ঋণ বিতরণের ৫৪ দিন পরে আদায়যোগ্য দেখানো ০১ জন সদস্য যার আদায়যোগ্য ৪০,০০০ টাকা।',
                    'amount' => '৪০,০০০',
                    'rating' => 'Medium (C)',
                    'criteria' => $criteria,
                    'detail_type' => 'arrears_compare',
                    'detail_intro' => 'নিম্নে বিস্তারিত দেওয়া হলো:',
                    'risk' => $risk46,
                    'stats' => [
                        'total_population' => '৩৫',
                        'sample_size' => '৩৫',
                        'instances_found' => '১',
                        'percentage' => '০৩%',
                    ],
                ]),
            ];

            return;
        }

        foreach ($this->page15Findings as $i => $finding) {
            if (($finding['serial'] ?? '') === '৪.৬' && ($finding['detail_type'] ?? 'none') === 'none') {
                $finding['detail_type'] = 'arrears_compare';
                if (trim((string) ($finding['detail_intro'] ?? '')) === '') {
                    $finding['detail_intro'] = 'নিম্নে বিস্তারিত দেওয়া হলো:';
                }
                if (trim((string) ($finding['risk'] ?? '')) === '') {
                    $finding['risk'] = $risk46;
                }
            }

            $stats = $this->rowsFromFinding($finding, 'statsRows', fn () => [$this->blankObservationRow()]);
            $arrearsRows = $this->rowsFromFinding(
                $finding,
                'arrearsRows',
                fn () => (($finding['detail_type'] ?? 'none') === 'arrears_compare') ? $this->defaultArrearsCompareRows() : []
            );
            $this->page15Findings[$i] = array_merge($this->blankPage15Finding(), $finding, [
                'statsRows' => $stats,
                'arrearsRows' => $arrearsRows,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function blankPage15Finding(array $overrides = []): array
    {
        $stats = $overrides['stats'] ?? null;
        unset($overrides['stats']);

        $detailType = (string) ($overrides['detail_type'] ?? 'none');
        $arrearsRows = $detailType === 'arrears_compare' ? $this->defaultArrearsCompareRows() : [];

        return array_merge([
            'serial' => '',
            'title' => 'শিরোনাম',
            'body' => '',
            'indicator_id' => null,
            'indicator_code' => null,
            'amount' => '',
            'rating' => '',
            'criteria' => '',
            'observation' => '',
            'detail_type' => 'none',
            'detail_intro' => '',
            'statsRows' => [$stats ? array_merge($this->blankObservationRow(), $stats) : $this->blankObservationRow()],
            'arrearsRows' => $arrearsRows,
            'risk' => '',
            'root_cause' => '',
            'recommendation' => '',
            'bm_reply' => '',
            'responsible' => '',
            'resolution_date' => '',
        ], $overrides);
    }

    /**
     * @return list<array<string, string>>
     */
    protected function defaultArrearsCompareRows(): array
    {
        return array_fill(0, 3, $this->blankArrearsCompareRow());
    }

    /**
     * @return array{samity_no:string,member_name_id:string,disbursement_date:string,loan_amount:string,actual_due_date:string,software_due_date:string,installment_date:string,actual_arrears:string,software_arrears:string}
     */
    protected function blankArrearsCompareRow(): array
    {
        return [
            'samity_no' => '',
            'member_name_id' => '',
            'disbursement_date' => '',
            'loan_amount' => '',
            'actual_due_date' => '',
            'software_due_date' => '',
            'installment_date' => '',
            'actual_arrears' => '',
            'software_arrears' => '',
        ];
    }

    public function addPage15StatsRow(int $findingIndex): void
    {
        $this->ensurePage15Defaults();
        if (! isset($this->page15Findings[$findingIndex])) {
            return;
        }
        $this->page15Findings[$findingIndex]['statsRows'][] = $this->blankObservationRow();
    }

    public function removePage15StatsRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page15Findings[$findingIndex]['statsRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page15Findings[$findingIndex]['statsRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page15Findings[$findingIndex]['statsRows'] = array_values($rows);
    }

    public function addPage15ArrearsRow(int $findingIndex): void
    {
        $this->ensurePage15Defaults();
        if (! isset($this->page15Findings[$findingIndex])) {
            return;
        }
        $this->page15Findings[$findingIndex]['arrearsRows'][] = $this->blankArrearsCompareRow();
    }

    public function removePage15ArrearsRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page15Findings[$findingIndex]['arrearsRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page15Findings[$findingIndex]['arrearsRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page15Findings[$findingIndex]['arrearsRows'] = array_values($rows);
    }

    public function updatedPage15Findings(mixed $value, ?string $key = null): void
    {
        if (! is_string($key) || ! preg_match('/^(\d+)\.detail_type$/', $key, $matches)) {
            return;
        }

        $index = (int) $matches[1];
        if (! isset($this->page15Findings[$index])) {
            return;
        }

        if (($this->page15Findings[$index]['detail_type'] ?? '') === 'arrears_compare'
            && empty($this->page15Findings[$index]['arrearsRows'])) {
            $this->page15Findings[$index]['arrearsRows'] = $this->defaultArrearsCompareRows();
        }
    }

    protected function syncPage15FindingsToToc(): void
    {
        $this->ensureTocDefaults();

        foreach ($this->page15Findings as $finding) {
            $serial = trim((string) ($finding['serial'] ?? ''));
            $body = trim((string) ($finding['body'] ?? ''));
            if ($serial === '' || $body === '') {
                continue;
            }

            $updated = false;
            foreach ($this->tocRows as $i => $row) {
                if (($row['type'] ?? 'item') !== 'item') {
                    continue;
                }
                if (($row['serial'] ?? '') !== $serial) {
                    continue;
                }

                $this->tocRows[$i]['finding'] = $body;
                if (filled($finding['rating'] ?? null)) {
                    $this->tocRows[$i]['rating'] = (string) $finding['rating'];
                }
                if (filled($finding['amount'] ?? null)) {
                    $this->tocRows[$i]['amount'] = (string) $finding['amount'];
                }
                $updated = true;
                break;
            }

            if ($updated) {
                continue;
            }

            array_splice($this->tocRows, $this->tocInsertIndexForSerial($serial), 0, [[
                'type' => 'item',
                'serial' => $serial,
                'finding' => $body,
                'amount' => (string) ($finding['amount'] ?? ''),
                'rating' => (string) ($finding['rating'] ?? ''),
                'status' => '',
                'page_no' => '',
                'preview_page' => 3,
            ]]);
        }
    }

    protected function page15Payload(): array
    {
        return [
            'page15Findings' => $this->page15Findings,
        ];
    }

    protected function ensurePage16Defaults(): void
    {
        $criteria = "চিঠি-৪৮ (২৬/০১/২০২০) সিদ্ধান্ত-১১: একই স্থানে একাধিক সমিতি পরিচালনা ডিএসকে মাইক্রোক্রেডিট নীতিমালার পরিপন্থী।\nচিঠি-৪৫ (২৫/১০/২০১৯) সিদ্ধান্ত-২, ৩ ও ৪: এক সমিতির সদস্য অন্য সমিতির প্রধান হিসেবে দায়িত্ব পালন ও তদারকির ঘাটতি নিষিদ্ধ।";

        $risk48 = 'পাসবই উপস্থিত না করায় সদস্যের হিসাব যাচাই করা যায় না এবং আর্থিক অনিয়ম লুকানোর সুযোগ সৃষ্টি হতে পারে।';

        if ($this->page16Findings === []) {
            $this->page16Findings = [
                $this->blankPage16Finding([
                    'serial' => '৪.৭',
                    'body' => 'সমিতি পরিদর্শনে প্রাপ্ত ঘাটতি; একটি সমিতি একাধিক স্পটে বসানো। সমিতির সমিতি প্রধান সময় না দেওয়া। পাসবই পোস্টিং ব্যতীত সঞ্চয় কিস্তি আদায়। সমিতিতে কান আলোচনা না করা। ভাতিজাকে অভিভাবক করে ঋণ বিতরণ।',
                    'rating' => 'Medium (C)',
                    'criteria' => $criteria,
                    'detail_type' => 'none',
                ]),
                $this->blankPage16Finding([
                    'serial' => '৪.৮',
                    'body' => 'নিরীক্ষাকালে ক্রসচেকের প্রয়োজনে পাসবই উপস্থিত না করা ২৮টি যে সকল পাসবই এ আর্থিক অনিয়ম থাকার আশংকা আছে।',
                    'amount' => '২৮',
                    'rating' => 'Medium (C)',
                    'criteria' => $criteria,
                    'detail_type' => 'passbook_absent',
                    'risk' => $risk48,
                ]),
            ];

            return;
        }

        foreach ($this->page16Findings as $i => $finding) {
            if (($finding['serial'] ?? '') === '৪.৮' && ($finding['detail_type'] ?? 'none') === 'none') {
                $finding['detail_type'] = 'passbook_absent';
                if (trim((string) ($finding['risk'] ?? '')) === '') {
                    $finding['risk'] = $risk48;
                }
            }

            $stats = $this->rowsFromFinding($finding, 'statsRows', fn () => [$this->blankObservationRow()]);
            $passbookAbsentRows = $this->rowsFromFinding(
                $finding,
                'passbookAbsentRows',
                fn () => (($finding['detail_type'] ?? 'none') === 'passbook_absent') ? $this->defaultPassbookAbsentRows() : []
            );
            $this->page16Findings[$i] = array_merge($this->blankPage16Finding(), $finding, [
                'statsRows' => $stats,
                'passbookAbsentRows' => $passbookAbsentRows,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function blankPage16Finding(array $overrides = []): array
    {
        $stats = $overrides['stats'] ?? null;
        unset($overrides['stats']);

        $detailType = (string) ($overrides['detail_type'] ?? 'none');
        $passbookAbsentRows = $detailType === 'passbook_absent' ? $this->defaultPassbookAbsentRows() : [];

        return array_merge([
            'serial' => '',
            'title' => 'শিরোনাম',
            'body' => '',
            'indicator_id' => null,
            'indicator_code' => null,
            'amount' => '',
            'rating' => '',
            'criteria' => '',
            'observation' => '',
            'detail_type' => 'none',
            'detail_intro' => '',
            'statsRows' => [$stats ? array_merge($this->blankObservationRow(), $stats) : $this->blankObservationRow()],
            'passbookAbsentRows' => $passbookAbsentRows,
            'risk' => '',
            'root_cause' => '',
            'recommendation' => '',
            'bm_reply' => '',
            'responsible' => '',
            'resolution_date' => '',
        ], $overrides);
    }

    /**
     * @return list<array<string, string>>
     */
    protected function defaultPassbookAbsentRows(): array
    {
        return array_fill(0, 4, $this->blankPassbookAbsentRow());
    }

    /**
     * @return array{staff_name:string,samity_no:string,total_members:string,passbooks_received:string,passbooks_absent:string,officer_comment:string}
     */
    protected function blankPassbookAbsentRow(): array
    {
        return [
            'staff_name' => '',
            'samity_no' => '',
            'total_members' => '',
            'passbooks_received' => '',
            'passbooks_absent' => '',
            'officer_comment' => '',
        ];
    }

    public function addPage16PassbookAbsentRow(int $findingIndex): void
    {
        $this->ensurePage16Defaults();
        if (! isset($this->page16Findings[$findingIndex])) {
            return;
        }
        $this->page16Findings[$findingIndex]['passbookAbsentRows'][] = $this->blankPassbookAbsentRow();
    }

    public function removePage16PassbookAbsentRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page16Findings[$findingIndex]['passbookAbsentRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page16Findings[$findingIndex]['passbookAbsentRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page16Findings[$findingIndex]['passbookAbsentRows'] = array_values($rows);
    }

    public function updatedPage16Findings(mixed $value, ?string $key = null): void
    {
        if (! is_string($key) || ! preg_match('/^(\d+)\.detail_type$/', $key, $matches)) {
            return;
        }

        $index = (int) $matches[1];
        if (! isset($this->page16Findings[$index])) {
            return;
        }

        if (($this->page16Findings[$index]['detail_type'] ?? '') === 'passbook_absent'
            && empty($this->page16Findings[$index]['passbookAbsentRows'])) {
            $this->page16Findings[$index]['passbookAbsentRows'] = $this->defaultPassbookAbsentRows();
        }
    }

    public function addPage16StatsRow(int $findingIndex): void
    {
        $this->ensurePage16Defaults();
        if (! isset($this->page16Findings[$findingIndex])) {
            return;
        }
        $this->page16Findings[$findingIndex]['statsRows'][] = $this->blankObservationRow();
    }

    public function removePage16StatsRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page16Findings[$findingIndex]['statsRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page16Findings[$findingIndex]['statsRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page16Findings[$findingIndex]['statsRows'] = array_values($rows);
    }

    protected function syncPage16FindingsToToc(): void
    {
        $this->ensureTocDefaults();

        foreach ($this->page16Findings as $finding) {
            $serial = trim((string) ($finding['serial'] ?? ''));
            $body = trim((string) ($finding['body'] ?? ''));
            if ($serial === '' || $body === '') {
                continue;
            }

            $updated = false;
            foreach ($this->tocRows as $i => $row) {
                if (($row['type'] ?? 'item') !== 'item') {
                    continue;
                }
                if (($row['serial'] ?? '') !== $serial) {
                    continue;
                }

                $this->tocRows[$i]['finding'] = $body;
                if (filled($finding['rating'] ?? null)) {
                    $this->tocRows[$i]['rating'] = (string) $finding['rating'];
                }
                if (filled($finding['amount'] ?? null)) {
                    $this->tocRows[$i]['amount'] = (string) $finding['amount'];
                }
                $updated = true;
                break;
            }

            if ($updated) {
                continue;
            }

            array_splice($this->tocRows, $this->tocInsertIndexForSerial($serial), 0, [[
                'type' => 'item',
                'serial' => $serial,
                'finding' => $body,
                'amount' => (string) ($finding['amount'] ?? ''),
                'rating' => (string) ($finding['rating'] ?? ''),
                'status' => '',
                'page_no' => '',
                'preview_page' => 3,
            ]]);
        }
    }

    protected function page16Payload(): array
    {
        return [
            'page16Findings' => $this->page16Findings,
        ];
    }

    protected function ensurePage17Defaults(): void
    {
        $criteria = '২০/০১/২০১৮ তারিখের নীতিমালা অনুযায়ী ১ম ঋণের বাধ্যতামূলক সঞ্চয় হতে আংশিক সমন্বয় করে কিস্তি আদায় করা যাবে না; কিস্তি নগদ আদায় করতে হবে।';

        $risk = 'সঞ্চয় হতে কিস্তি সমন্বয় করায় নগদ আদায় না হওয়ায় আর্থিক অনিয়মের সুযোগ সৃষ্টি হতে পারে এবং সদস্যের সঞ্চয় সুরক্ষা দুর্বল হয়।';

        if ($this->page17Findings === []) {
            $this->page17Findings = [
                $this->blankPage17Finding([
                    'serial' => '৪.৯',
                    'body' => 'নীতিমালা বহির্ভূত ১ম ঋণের বাধ্যতামূলক সঞ্চয় আংশিক সমন্বয় করে কিস্তি আদায় ২১৫০০ টাকা',
                    'amount' => '২১,৫০০',
                    'rating' => 'Medium (C)',
                    'criteria' => $criteria,
                    'detail_type' => 'savings_partial_adjust',
                    'detail_intro' => 'বিস্তারিত নিম্নে দেওয়া হল:',
                    'risk' => $risk,
                ]),
            ];

            return;
        }

        foreach ($this->page17Findings as $i => $finding) {
            $stats = $this->rowsFromFinding($finding, 'statsRows', fn () => [$this->blankObservationRow()]);
            $savingsAdjustRows = $this->rowsFromFinding(
                $finding,
                'savingsAdjustRows',
                fn () => (($finding['detail_type'] ?? 'none') === 'savings_partial_adjust') ? $this->defaultSavingsAdjustRows() : []
            );
            $this->page17Findings[$i] = array_merge($this->blankPage17Finding(), $finding, [
                'statsRows' => $stats,
                'savingsAdjustRows' => $savingsAdjustRows,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function blankPage17Finding(array $overrides = []): array
    {
        $stats = $overrides['stats'] ?? null;
        unset($overrides['stats']);

        $detailType = (string) ($overrides['detail_type'] ?? 'none');
        $savingsAdjustRows = $detailType === 'savings_partial_adjust' ? $this->defaultSavingsAdjustRows() : [];

        return array_merge([
            'serial' => '',
            'title' => 'শিরোনাম',
            'body' => '',
            'indicator_id' => null,
            'indicator_code' => null,
            'amount' => '',
            'rating' => '',
            'criteria' => '',
            'observation' => '',
            'detail_type' => 'none',
            'detail_intro' => '',
            'statsRows' => [$stats ? array_merge($this->blankObservationRow(), $stats) : $this->blankObservationRow()],
            'savingsAdjustRows' => $savingsAdjustRows,
            'risk' => '',
            'root_cause' => '',
            'recommendation' => '',
            'bm_reply' => '',
            'responsible' => '',
            'resolution_date' => '',
        ], $overrides);
    }

    /**
     * @return list<array<string, string>>
     */
    protected function defaultSavingsAdjustRows(): array
    {
        return array_fill(0, 7, $this->blankSavingsAdjustRow());
    }

    /**
     * @return array{samity_no:string,member_name_id:string,adjust_date:string,adjust_amount:string}
     */
    protected function blankSavingsAdjustRow(): array
    {
        return [
            'samity_no' => '',
            'member_name_id' => '',
            'adjust_date' => '',
            'adjust_amount' => '',
        ];
    }

    public function addPage17StatsRow(int $findingIndex): void
    {
        $this->ensurePage17Defaults();
        if (! isset($this->page17Findings[$findingIndex])) {
            return;
        }
        $this->page17Findings[$findingIndex]['statsRows'][] = $this->blankObservationRow();
    }

    public function removePage17StatsRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page17Findings[$findingIndex]['statsRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page17Findings[$findingIndex]['statsRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page17Findings[$findingIndex]['statsRows'] = array_values($rows);
    }

    public function addPage17SavingsAdjustRow(int $findingIndex): void
    {
        $this->ensurePage17Defaults();
        if (! isset($this->page17Findings[$findingIndex])) {
            return;
        }
        $this->page17Findings[$findingIndex]['savingsAdjustRows'][] = $this->blankSavingsAdjustRow();
    }

    public function removePage17SavingsAdjustRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page17Findings[$findingIndex]['savingsAdjustRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page17Findings[$findingIndex]['savingsAdjustRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page17Findings[$findingIndex]['savingsAdjustRows'] = array_values($rows);
    }

    public function updatedPage17Findings(mixed $value, ?string $key = null): void
    {
        if (! is_string($key) || ! preg_match('/^(\d+)\.detail_type$/', $key, $matches)) {
            return;
        }

        $index = (int) $matches[1];
        if (! isset($this->page17Findings[$index])) {
            return;
        }

        if (($this->page17Findings[$index]['detail_type'] ?? '') === 'savings_partial_adjust'
            && empty($this->page17Findings[$index]['savingsAdjustRows'])) {
            $this->page17Findings[$index]['savingsAdjustRows'] = $this->defaultSavingsAdjustRows();
        }
    }

    protected function syncPage17FindingsToToc(): void
    {
        $this->ensureTocDefaults();

        foreach ($this->page17Findings as $finding) {
            $serial = trim((string) ($finding['serial'] ?? ''));
            $body = trim((string) ($finding['body'] ?? ''));
            if ($serial === '' || $body === '') {
                continue;
            }

            $updated = false;
            foreach ($this->tocRows as $i => $row) {
                if (($row['type'] ?? 'item') !== 'item') {
                    continue;
                }
                if (($row['serial'] ?? '') !== $serial) {
                    continue;
                }

                $this->tocRows[$i]['finding'] = $body;
                if (filled($finding['rating'] ?? null)) {
                    $this->tocRows[$i]['rating'] = (string) $finding['rating'];
                }
                if (filled($finding['amount'] ?? null)) {
                    $this->tocRows[$i]['amount'] = (string) $finding['amount'];
                }
                $updated = true;
                break;
            }

            if ($updated) {
                continue;
            }

            array_splice($this->tocRows, $this->tocInsertIndexForSerial($serial), 0, [[
                'type' => 'item',
                'serial' => $serial,
                'finding' => $body,
                'amount' => (string) ($finding['amount'] ?? ''),
                'rating' => (string) ($finding['rating'] ?? ''),
                'status' => '',
                'page_no' => '',
                'preview_page' => 3,
            ]]);
        }
    }

    protected function page17Payload(): array
    {
        return [
            'page17Findings' => $this->page17Findings,
        ];
    }

    protected function ensurePage18Defaults(): void
    {
        $criteria410 = 'প্রতিষ্ঠান ত্যাগকারী সদস্যদের সঞ্চয় ফেরতের সময় এমআরএ (MRA) নির্দেশনা অনুযায়ী সঞ্চয়ের উপর প্রাপ্য লাভ/মুনাফা প্রদান করতে হবে।';
        $risk410 = 'এমআরএ নিয়ম না মানায় প্রতিষ্ঠানের সুনাম ক্ষুণ্ন হতে পারে এবং নিয়ন্ত্রক কর্তৃপক্ষ কর্তৃক ব্যবস্থা নেওয়ার আশংকা রয়েছে।';
        $criteria411 = 'মাইক্রোফিন৩৬০ সফটওয়্যারে সঞ্চয় স্থানান্তরের মাধ্যমে ঋণ সমন্বয় করতে হয়; সঞ্চয় নগদে উত্তোলন করে ঋণ আদায় দেখানো যাবে না।';

        if ($this->page18Findings === []) {
            $this->page18Findings = [
                $this->blankPage18Finding([
                    'serial' => '৪.১০',
                    'body' => 'সদস্যদের লাভবিহীন সম্পূর্ণ সঞ্চয় ফেরত দিয়ে ড্রপআউট করা ৩৮,৯৪৪ টাকা।',
                    'amount' => '৩৮,৯৪৪',
                    'rating' => 'Medium (C)',
                    'criteria' => $criteria410,
                    'detail_type' => 'dropout_savings_refund',
                    'detail_intro' => 'বিস্তারিত নিম্নে দেওয়া হল:',
                    'stats' => [
                        'total_population' => '৯৩',
                        'sample_size' => '৩৫',
                        'instances_found' => '১৩',
                        'percentage' => '২৯%',
                    ],
                    'risk' => $risk410,
                ]),
                $this->blankPage18Finding([
                    'serial' => '৪.১১',
                    'body' => 'সঞ্চয় সমন্বয় তালিকায় লেখার পরেও সদস্যের সঞ্চয় নগদে উত্তোলন করে ঋণ আদায় দেখানোর কারণে ম্যানুয়াল সঞ্চয় সমন্বয় অনুমোদনের সাথে সফটওয়্যার এর সঞ্চয় সমন্বয়ের মিল পাওয়া যায়নি ৬,১২,২৯৬ টাকা।',
                    'amount' => '৬,১২,২৯৬',
                    'rating' => 'Medium (C)',
                    'criteria' => $criteria411,
                    'detail_type' => 'savings_adjust_compare',
                    'detail_intro' => 'বিস্তারিত নিম্নে দেওয়া হল:',
                    'risk' => 'আর্থিক অনিয়ম সৃষ্টির আশংকা',
                ]),
            ];

            return;
        }

        foreach ($this->page18Findings as $i => $finding) {
            $stats = $this->rowsFromFinding($finding, 'statsRows', fn () => [$this->blankObservationRow()]);
            $detailType = (string) ($finding['detail_type'] ?? 'none');
            $serial = trim((string) ($finding['serial'] ?? ''));
            if ($serial === '৪.১১' && $detailType === 'none') {
                $detailType = 'savings_adjust_compare';
            }
            $dropoutRefundRows = $this->rowsFromFinding(
                $finding,
                'dropoutRefundRows',
                fn () => ($detailType === 'dropout_savings_refund') ? $this->defaultDropoutSavingsRefundRows() : []
            );
            $savingsAdjustCompareRows = $this->rowsFromFinding(
                $finding,
                'savingsAdjustCompareRows',
                fn () => ($detailType === 'savings_adjust_compare') ? $this->defaultSavingsAdjustCompareRows() : []
            );
            $this->page18Findings[$i] = array_merge($this->blankPage18Finding(), $finding, [
                'detail_type' => $detailType,
                'statsRows' => $stats,
                'dropoutRefundRows' => $dropoutRefundRows,
                'savingsAdjustCompareRows' => $savingsAdjustCompareRows,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function blankPage18Finding(array $overrides = []): array
    {
        $stats = $overrides['stats'] ?? null;
        unset($overrides['stats']);

        $detailType = (string) ($overrides['detail_type'] ?? 'none');
        $dropoutRefundRows = $detailType === 'dropout_savings_refund' ? $this->defaultDropoutSavingsRefundRows() : [];
        $savingsAdjustCompareRows = $detailType === 'savings_adjust_compare' ? $this->defaultSavingsAdjustCompareRows() : [];

        return array_merge([
            'serial' => '',
            'title' => 'শিরোনাম',
            'body' => '',
            'indicator_id' => null,
            'indicator_code' => null,
            'amount' => '',
            'rating' => '',
            'criteria' => '',
            'observation' => '',
            'detail_type' => 'none',
            'detail_intro' => '',
            'statsRows' => [$stats ? array_merge($this->blankObservationRow(), $stats) : $this->blankObservationRow()],
            'dropoutRefundRows' => $dropoutRefundRows,
            'savingsAdjustCompareRows' => $savingsAdjustCompareRows,
            'risk' => '',
            'root_cause' => '',
            'recommendation' => '',
            'bm_reply' => '',
            'responsible' => '',
            'resolution_date' => '',
        ], $overrides);
    }

    /**
     * @return list<array<string, string>>
     */
    protected function defaultDropoutSavingsRefundRows(): array
    {
        return array_fill(0, 10, $this->blankDropoutSavingsRefundRow());
    }

    /**
     * @return array{date:string,samity_member_no:string,member_name:string,refund_amount:string}
     */
    protected function blankDropoutSavingsRefundRow(): array
    {
        return [
            'date' => '',
            'samity_member_no' => '',
            'member_name' => '',
            'refund_amount' => '',
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    protected function defaultSavingsAdjustCompareRows(): array
    {
        return array_fill(0, 5, $this->blankSavingsAdjustCompareRow());
    }

    /**
     * @return array{month_name:string,manual_adjust:string,software_adjust:string,difference:string}
     */
    protected function blankSavingsAdjustCompareRow(): array
    {
        return [
            'month_name' => '',
            'manual_adjust' => '',
            'software_adjust' => '',
            'difference' => '',
        ];
    }

    public function addPage18StatsRow(int $findingIndex): void
    {
        $this->ensurePage18Defaults();
        if (! isset($this->page18Findings[$findingIndex])) {
            return;
        }
        $this->page18Findings[$findingIndex]['statsRows'][] = $this->blankObservationRow();
    }

    public function removePage18StatsRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page18Findings[$findingIndex]['statsRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page18Findings[$findingIndex]['statsRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page18Findings[$findingIndex]['statsRows'] = array_values($rows);
    }

    public function addPage18DropoutRefundRow(int $findingIndex): void
    {
        $this->ensurePage18Defaults();
        if (! isset($this->page18Findings[$findingIndex])) {
            return;
        }
        $this->page18Findings[$findingIndex]['dropoutRefundRows'][] = $this->blankDropoutSavingsRefundRow();
    }

    public function removePage18DropoutRefundRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page18Findings[$findingIndex]['dropoutRefundRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page18Findings[$findingIndex]['dropoutRefundRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page18Findings[$findingIndex]['dropoutRefundRows'] = array_values($rows);
    }

    public function addPage18SavingsAdjustCompareRow(int $findingIndex): void
    {
        $this->ensurePage18Defaults();
        if (! isset($this->page18Findings[$findingIndex])) {
            return;
        }
        $this->page18Findings[$findingIndex]['savingsAdjustCompareRows'][] = $this->blankSavingsAdjustCompareRow();
    }

    public function removePage18SavingsAdjustCompareRow(int $findingIndex, int $rowIndex): void
    {
        if (! isset($this->page18Findings[$findingIndex]['savingsAdjustCompareRows'][$rowIndex])) {
            return;
        }
        $rows = $this->page18Findings[$findingIndex]['savingsAdjustCompareRows'];
        if (count($rows) <= 1) {
            return;
        }
        unset($rows[$rowIndex]);
        $this->page18Findings[$findingIndex]['savingsAdjustCompareRows'] = array_values($rows);
    }

    public function updatedPage18Findings(mixed $value, ?string $key = null): void
    {
        if (! is_string($key) || ! preg_match('/^(\d+)\.detail_type$/', $key, $matches)) {
            return;
        }

        $index = (int) $matches[1];
        if (! isset($this->page18Findings[$index])) {
            return;
        }

        if (($this->page18Findings[$index]['detail_type'] ?? '') === 'dropout_savings_refund'
            && empty($this->page18Findings[$index]['dropoutRefundRows'])) {
            $this->page18Findings[$index]['dropoutRefundRows'] = $this->defaultDropoutSavingsRefundRows();
        }

        if (($this->page18Findings[$index]['detail_type'] ?? '') === 'savings_adjust_compare'
            && empty($this->page18Findings[$index]['savingsAdjustCompareRows'])) {
            $this->page18Findings[$index]['savingsAdjustCompareRows'] = $this->defaultSavingsAdjustCompareRows();
        }
    }

    protected function syncPage18FindingsToToc(): void
    {
        $this->ensureTocDefaults();

        foreach ($this->page18Findings as $finding) {
            $serial = trim((string) ($finding['serial'] ?? ''));
            $body = trim((string) ($finding['body'] ?? ''));
            if ($serial === '' || $body === '') {
                continue;
            }

            $updated = false;
            foreach ($this->tocRows as $i => $row) {
                if (($row['type'] ?? 'item') !== 'item') {
                    continue;
                }
                if (($row['serial'] ?? '') !== $serial) {
                    continue;
                }

                $this->tocRows[$i]['finding'] = $body;
                if (filled($finding['rating'] ?? null)) {
                    $this->tocRows[$i]['rating'] = (string) $finding['rating'];
                }
                if (filled($finding['amount'] ?? null)) {
                    $this->tocRows[$i]['amount'] = (string) $finding['amount'];
                }
                $updated = true;
                break;
            }

            if ($updated) {
                continue;
            }

            array_splice($this->tocRows, $this->tocInsertIndexForSerial($serial), 0, [[
                'type' => 'item',
                'serial' => $serial,
                'finding' => $body,
                'amount' => (string) ($finding['amount'] ?? ''),
                'rating' => (string) ($finding['rating'] ?? ''),
                'status' => '',
                'page_no' => '',
                'preview_page' => 3,
            ]]);
        }
    }

    protected function page18Payload(): array
    {
        return [
            'page18Findings' => $this->page18Findings,
        ];
    }

    protected function ensurePage19Defaults(): void
    {
        if ($this->page19ComplianceRows === []) {
            $this->page19ComplianceRows = $this->defaultPage19ComplianceRows();
        } else {
            $this->page19ComplianceRows = array_values(array_map(
                fn ($row) => array_merge($this->blankPage19ComplianceRow(), (array) $row),
                $this->page19ComplianceRows
            ));
        }
    }

    protected function ensurePage20Defaults(): void
    {
        if ($this->page20ItChecklistRows === []) {
            $this->page20ItChecklistRows = $this->defaultPage20ItChecklistRows();
        } else {
            $this->page20ItChecklistRows = array_values(array_map(
                function ($row, $index) {
                    $defaults = $this->defaultPage20ItChecklistRows();
                    $prefill = $defaults[$index] ?? $this->blankPage20ItChecklistRow();

                    return array_merge($prefill, $this->blankPage20ItChecklistRow(), (array) $row);
                },
                $this->page20ItChecklistRows,
                array_keys($this->page20ItChecklistRows)
            ));
        }

        if ($this->page20_it_branch === '' && $this->shakha_display_name !== '') {
            $this->page20_it_branch = $this->shakha_display_name;
        }

        $legacyReCheckDescription = 'শাখা অফিসে সিসি ক্যামেরা গুলো ঠিক আছে কিনা তা নিয়মিত রি-চেক করা হয় কিনা?';
        if (count($this->page20ItChecklistRows) === 22) {
            $lastRow = $this->page20ItChecklistRows[21] ?? [];
            if (($lastRow['description'] ?? '') === $legacyReCheckDescription) {
                $this->page20ItChecklistRows[] = array_merge($this->blankPage20ItChecklistRow(), [
                    'sl_no' => '২৩',
                    'description' => 'শাখা অফিসে সিসি ক্যামেরা গুলোর নিয়ন্ত্রণ নির্ধারিত সুপারভাইজারের কাছে আছে কিনা?',
                ]);
            }
        }
    }

    protected function ensurePage21Defaults(): void
    {
        if ($this->page21ExternalAuditRows === []) {
            $this->page21ExternalAuditRows = $this->defaultPage21ExternalAuditRows();
        } else {
            $this->page21ExternalAuditRows = array_values(array_map(
                fn ($row) => array_merge($this->blankPage21ExternalAuditRow(), (array) $row),
                $this->page21ExternalAuditRows
            ));
        }

        if ($this->page21_branch_name === '' && $this->shakha_display_name !== '') {
            $this->page21_branch_name = $this->shakha_display_name;
        }

        if ($this->page21_sign_name === '') {
            $this->page21_sign_name = $this->sign_auditor_name !== ''
                ? $this->sign_auditor_name
                : $this->auditor_name;
        }

        if ($this->page21_sign_designation === '') {
            $this->page21_sign_designation = $this->sign_auditor_designation !== ''
                ? $this->sign_auditor_designation
                : $this->auditor_designation;
        }
    }

    /**
     * @return list<array<string, string>>
     */
    protected function defaultPage19ComplianceRows(): array
    {
        return array_fill(0, 10, $this->blankPage19ComplianceRow());
    }

    /**
     * @return array{prev_para_no:string,findings:string,first_discovery_period:string,management_reply:string,current_status:string,current_para_no:string}
     */
    protected function blankPage19ComplianceRow(): array
    {
        return [
            'prev_para_no' => '',
            'findings' => '',
            'first_discovery_period' => '',
            'management_reply' => '',
            'current_status' => '',
            'current_para_no' => '',
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    protected function defaultPage20ItChecklistRows(): array
    {
        $descriptions = [
            'শাখার ল্যাপটপ গুলোতে নির্দিষ্ট সময় পর পর Anti Virus দেওয়া হয় কিনা?',
            'Original Microsoft Window Software দিয়ে শাখার ল্যাপটপ গুলো পরিচালনা হচ্ছে কিনা?',
            'অধিক পুরাতন/ব্যবহার অযোগ্য ল্যাপটপ শাখায় ব্যবহার করা হচ্ছে কিনা?',
            'ব্যবস্থাপক,সহকারী ব্যবস্থাপকের কম্পিউটার/ল্যাপটপ পরিচালনার ন্যূনতম জ্ঞান (বাংলা ও ইংরেজি টাইপকরণ, এমএস ওয়ার্ড, এক্সেল, ই-মেইল, পাওয়ার পয়েন্ট ইত্যাদি) আছে কিনা?',
            'ব্যবস্থাপক,সহকারী ব্যবস্থাপকের কম্পিউটার/ল্যাপটপ পরিচালনার ন্যূনতম প্রশিক্ষণ পেয়েছে কিনা?',
            'ব্যবস্থাপক,সহকারী ব্যবস্থাপকের কম্পিউটার/ল্যাপটপ পরিচালনার ন্যূনতম আইটি বিষয়ক সচেতনতার প্রশিক্ষণ পেয়েছে কিনা (অবাঞ্ছিত মেসেজেস পরিহার করা বিষয়ক)?',
            'Strong Unique Password ব্যবহার করা ও অন্যদের সাথে শেয়ার করা থেকে বিরত থাকে কিনা?',
            'নির্দিষ্ট সময় পর পর Strong Unique Password পরিবর্তন করা হয় কিনা?',
            'একই Strong Unique Password ব্যবস্থাপক ও সহকারী ব্যবস্থাপক মিলে ০২জনেই ব্যবহার করে কিনা?',
            'শাখার ল্যাপটপ গুলোতে ব্যক্তিগত ফাইল, ছবি ভিডিও রাখা হয় কিনা?',
            'শাখার ল্যাপটপ গুলোতে ভিন্ন ভিন্ন সুপারভাইজার কর্তৃক প্রাথমিক এন্ট্রি Strong Unique Password আলাদা আলাদা ব্যবহার করা হয় কিনা?',
            'নির্দিষ্ট সময় পর পর laptop Auto lock/Auto Screen off হয় কিনা?',
            'ব্যবস্থাপক ও সহকারী ব্যবস্থাপক বর্তমান মাইক্রোফিন৩৬০ সফটওয়্যার অপারেটিং কার্যক্রম (সদস্য ভর্তি, ঋণ বিতরণ, সঞ্চয় ও ঋণ আদায়, সঞ্চয় ফেরত, ভাউচার পোস্টিং, সদস্য ও ঋণী স্থানান্তর করা ইত্যাদি) করতে পারে কিনা?',
            'ব্যবস্থাপক ও সহকারী ব্যবস্থাপক বর্তমান মাইক্রোফিন৩৬০ সফটওয়্যার এর সকল রিপোর্ট সম্পর্কে ধারণা ও দেখতে এবং প্রিন্ট করতে পারে কিনা?',
            'শাখা পর্যায়ের সকল কর্মকর্তার ওয়াইফাই ছাড়া মোবাইল ব্যক্তিগত ডাটা থাকে কিনা?',
            'শাখা পর্যায়ের সকল কর্মকর্তা মাইক্রোফিন৩৬০ সফটওয়্যার এর এ্যাপ (App) ব্যবহার প্রশিক্ষণ পেয়েছে কিনা?',
            'শাখা পর্যায়ের সকল মাঠকর্মীবৃন্দ মাইক্রোফিন৩৬০ সফটওয়্যার এর এ্যাপ (App) ব্যবহার করে অপারেটিং কার্যক্রম/পোস্টিং এর কাজ (সদস্য ভর্তি, ঋণ বিতরণ, সঞ্চয় ও ঋণ আদায়) করে কিনা?',
            'দিন শেষে laptop নিরাপদ জায়গায় সংরক্ষণ করে কিনা?',
            'শাখা অফিসে সিসি ক্যামেরা আছে কিনা এবং সক্রিয় আছে কিনা?',
            'শাখা অফিসে সিসি ক্যামেরার নিয়মিত ডাটা ব্যাকআপ রাখা হয় কিনা?',
            'শাখা অফিসে সিসি ক্যামেরার তথ্য পর্যবেক্ষণের জন্য আলাদা মনিটর আছে কিনা?',
            'শাখা অফিসে সিসি ক্যামেরা গুলো ঠিক আছে কিনা তা নিয়মিত রি-চেক করা হয় কিনা?',
            'শাখা অফিসে সিসি ক্যামেরা গুলোর নিয়ন্ত্রণ নির্ধারিত সুপারভাইজারের কাছে আছে কিনা?',
        ];

        $bnDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        $rows = [];

        foreach ($descriptions as $index => $description) {
            $n = $index + 1;
            $sl = ($n < 10 ? '০' : '')
                .strtr((string) $n, array_combine(range('0', '9'), $bnDigits));

            $rows[] = array_merge($this->blankPage20ItChecklistRow(), [
                'sl_no' => $sl,
                'description' => $description,
            ]);
        }

        return $rows;
    }

    /**
     * @return array{sl_no:string,description:string,compliance:string,action_owner:string,management_comments:string,recommendation:string}
     */
    protected function blankPage20ItChecklistRow(): array
    {
        return [
            'sl_no' => '',
            'description' => '',
            'compliance' => '',
            'action_owner' => '',
            'management_comments' => '',
            'recommendation' => '',
        ];
    }

    public function addPage19ComplianceRow(): void
    {
        $this->ensurePage19Defaults();
        $this->page19ComplianceRows[] = $this->blankPage19ComplianceRow();
    }

    public function removePage19ComplianceRow(int $rowIndex): void
    {
        if (! isset($this->page19ComplianceRows[$rowIndex])) {
            return;
        }
        if (count($this->page19ComplianceRows) <= 1) {
            return;
        }
        unset($this->page19ComplianceRows[$rowIndex]);
        $this->page19ComplianceRows = array_values($this->page19ComplianceRows);
    }

    public function addPage20ItChecklistRow(): void
    {
        $this->ensurePage20Defaults();
        $this->page20ItChecklistRows[] = $this->blankPage20ItChecklistRow();
    }

    public function removePage20ItChecklistRow(int $rowIndex): void
    {
        if (! isset($this->page20ItChecklistRows[$rowIndex])) {
            return;
        }
        if (count($this->page20ItChecklistRows) <= 1) {
            return;
        }
        unset($this->page20ItChecklistRows[$rowIndex]);
        $this->page20ItChecklistRows = array_values($this->page20ItChecklistRows);
    }

    /**
     * @return list<array<string, string>>
     */
    protected function defaultPage21ExternalAuditRows(): array
    {
        return array_fill(0, 4, $this->blankPage21ExternalAuditRow());
    }

    /**
     * @return array{area_of_observation:string,compliance_area:string,year_of_reporting:string,external_observation:string,compliance:string,internal_index_no:string}
     */
    protected function blankPage21ExternalAuditRow(): array
    {
        return [
            'area_of_observation' => '',
            'compliance_area' => '',
            'year_of_reporting' => '',
            'external_observation' => '',
            'compliance' => '',
            'internal_index_no' => '',
        ];
    }

    public function addPage21ExternalAuditRow(): void
    {
        $this->ensurePage21Defaults();
        $this->page21ExternalAuditRows[] = $this->blankPage21ExternalAuditRow();
    }

    public function removePage21ExternalAuditRow(int $rowIndex): void
    {
        if (! isset($this->page21ExternalAuditRows[$rowIndex])) {
            return;
        }
        if (count($this->page21ExternalAuditRows) <= 1) {
            return;
        }
        unset($this->page21ExternalAuditRows[$rowIndex]);
        $this->page21ExternalAuditRows = array_values($this->page21ExternalAuditRows);
    }

    protected function syncPage19SectionsToToc(): void
    {
        $this->ensureTocDefaults();

        $complianceFinding = 'বিগত অভ্যন্তরীণ নিরীক্ষা প্রতিবেদনের জবাবের কমপ্লায়েন্স (Compliance of Previous Internal Audit Report Reply)';
        $itFinding = 'আইটি (সফটওয়্যার) সংক্রান্ত চেকলিস্ট';

        foreach ($this->tocRows as $i => $row) {
            if (($row['type'] ?? 'item') !== 'section') {
                continue;
            }
            if (($row['serial'] ?? '') === '৫.০০') {
                $this->tocRows[$i]['finding'] = $complianceFinding;
            }
            if (($row['serial'] ?? '') === '৬.০০') {
                $this->tocRows[$i]['finding'] = $itFinding;
            }
        }
    }

    protected function syncPage21SectionsToToc(): void
    {
        $this->ensureTocDefaults();

        $externalFinding = 'Compliance of Previous External Audit Report';

        foreach ($this->tocRows as $i => $row) {
            if (($row['type'] ?? 'item') !== 'section') {
                continue;
            }
            if (($row['serial'] ?? '') === '৭.০০') {
                $this->tocRows[$i]['finding'] = $externalFinding;
            }
        }
    }

    protected function page19Payload(): array
    {
        return [
            'page19_compliance_title' => $this->page19_compliance_title,
            'page19_compliance_period' => $this->page19_compliance_period,
            'page19_compliance_followup_date' => $this->page19_compliance_followup_date,
            'page19ComplianceRows' => $this->page19ComplianceRows,
        ];
    }

    protected function page20Payload(): array
    {
        return [
            'page20_it_title' => $this->page20_it_title,
            'page20_it_org_line1' => $this->page20_it_org_line1,
            'page20_it_org_line2' => $this->page20_it_org_line2,
            'page20_it_org_line3' => $this->page20_it_org_line3,
            'page20_it_program' => $this->page20_it_program,
            'page20_it_branch' => $this->page20_it_branch,
            'page20_it_instruction' => $this->page20_it_instruction,
            'page20ItChecklistRows' => $this->page20ItChecklistRows,
        ];
    }

    protected function page21Payload(): array
    {
        return [
            'page21_section_title' => $this->page21_section_title,
            'page21_year_of_reporting' => $this->page21_year_of_reporting,
            'page21_branch_name' => $this->page21_branch_name,
            'page21ExternalAuditRows' => $this->page21ExternalAuditRows,
            'page21_sign_label' => $this->page21_sign_label,
            'page21_sign_name' => $this->page21_sign_name,
            'page21_sign_designation' => $this->page21_sign_designation,
        ];
    }

    protected function page3Payload(): array
    {
        return [
            'sign_auditor_name' => $this->sign_auditor_name,
            'sign_auditor_designation' => $this->sign_auditor_designation,
            'sign_auditor_date' => $this->sign_auditor_date,
            'sign_bm_name' => $this->sign_bm_name,
            'sign_bm_date' => $this->sign_bm_date,
            'sign_abm_name' => $this->sign_abm_name,
            'sign_abm_date' => $this->sign_abm_date,
        ];
    }

    protected function selectedShakhaLabel(): string
    {
        if (! $this->shakha_id) {
            return '';
        }

        $shakha = Shakha::query()->with('area')->find($this->shakha_id);

        return $shakha ? $this->formatShakhaLabel($shakha) : '';
    }

    protected function formatShakhaLabel(Shakha $shakha): string
    {
        return trim($shakha->name.($shakha->code ? ' ('.$shakha->code.')' : '').($shakha->area ? ' — '.$shakha->area->name : ''));
    }

    protected function calcWorkingDays(string $start, string $end): int
    {
        try {
            $a = $this->parseFlexibleDate($start);
            $b = $this->parseFlexibleDate($end);
            if (! $a || ! $b || $b->lt($a)) {
                return 0;
            }

            return $a->diffInDays($b) + 1;
        } catch (\Throwable) {
            return 0;
        }
    }

    protected function formatBnRange(string $start, string $end): string
    {
        try {
            $a = $this->parseFlexibleDate($start);
            $b = $this->parseFlexibleDate($end);
            if (! $a || ! $b) {
                return '';
            }

            return $a->format('d/m/Y').' হতে '.$b->format('d/m/Y');
        } catch (\Throwable) {
            return '';
        }
    }

    protected function parseFlexibleDate(?string $value): ?Carbon
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'j/n/Y'] as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $value);

                return $parsed?->startOfDay();
            } catch (\Throwable) {
                // try next
            }
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Normalize typed/picked dates to Y-m-d when parseable; otherwise keep original text.
     */
    protected function normalizeDateInput(?string $value): ?string
    {
        $parsed = $this->parseFlexibleDate($value);

        return $parsed?->toDateString();
    }

    protected function resolveLogoUrl(): ?string
    {
        if ($this->logoUpload) {
            try {
                return $this->logoUpload->temporaryUrl();
            } catch (\Throwable) {
                // Fall through to stored path.
            }
        }

        if (! $this->logo_path) {
            return null;
        }

        return asset('storage/'.$this->logo_path);
    }

    public function render()
    {
        $shakhas = app(UserAccessService::class)->accessibleShakhas(auth()->user());

        $branchOptions = $shakhas->values()->map(function ($shakha, $index) {
            return [
                'id' => (string) $shakha->id,
                'serial' => $index + 1,
                'name' => $shakha->name,
                'code' => (string) ($shakha->code ?: ''),
                'area' => (string) ($shakha->area?->name ?: ''),
                'division' => (string) ($shakha->area?->division ?: ''),
                'focal' => (string) ($shakha->focal_person_name ?: ''),
                'active' => $shakha->isActive(),
                'opening' => optional($shakha->opening_date ?? $shakha->opened_at)->format('d M Y') ?: '',
            ];
        })->values();

        $this->ensurePage2Defaults();
        $this->ensureTocDefaults();
        $this->ensureSignatureDefaults();
        $this->ensureFinancialAuditDefaults();
        $document = $this->stampedDocument();

        $userId = (int) (auth()->id() ?? 0);
        $ongoingReports = collect();
        $completedReports = collect();
        $ongoingCount = 0;
        $completedCount = 0;
        $pendingSlots = AuditReport::MAX_CONCURRENT_DRAFTS;

        if ($userId > 0) {
            $ongoingReports = AuditReport::query()
                ->ownedBy($userId)
                ->drafts()
                ->with('shakha.area')
                ->latest('last_saved_at')
                ->latest('updated_at')
                ->get();

            $completedReports = AuditReport::query()
                ->ownedBy($userId)
                ->completed()
                ->with('shakha.area')
                ->latest('completed_at')
                ->limit(8)
                ->get();

            $ongoingCount = $ongoingReports->count();
            $completedCount = AuditReport::query()->ownedBy($userId)->completed()->count();
            $pendingSlots = max(0, AuditReport::MAX_CONCURRENT_DRAFTS - $ongoingCount);
        }

        $financialIndicatorOptions = AuditIndicator::query()
            ->active()
            ->orderBy('category')
            ->orderBy('indicator_code')
            ->get(['id', 'indicator_code', 'title', 'category', 'risk_rating'])
            ->map(fn (AuditIndicator $indicator) => [
                'id' => $indicator->id,
                'code' => (string) $indicator->indicator_code,
                'title' => (string) $indicator->title,
                'category' => (string) ($indicator->category ?: ''),
                'risk' => (string) ($indicator->risk_rating ?: ''),
            ])
            ->values()
            ->all();

        return view('livewire.make-audit-report', [
            'branchOptions' => $branchOptions,
            'shakhaCount' => $shakhas->count(),
            'selectedShakhaLabel' => $this->selectedShakhaLabel(),
            'ratingColor' => AuditReport::ratingColor($this->control_rating),
            'monthLabel' => Carbon::create(null, $this->report_month, 1)->format('F'),
            'logoUrl' => $this->resolveLogoUrl(),
            'documentSheets' => $document['sheets'],
            'documentSheetCount' => count($document['sheets']),
            'tabs' => [
                ['id' => 'cover', 'num' => 1, 'label' => 'Cover Page', 'ready' => true],
                ['id' => 'page2', 'num' => 2, 'label' => 'এক নজরে + সূচিপত্র', 'ready' => true],
                ['id' => 'page3', 'num' => 3, 'label' => 'সূচিপত্র + শ্রেণীবিন্যাস', 'ready' => true],
                ['id' => 'page4', 'num' => 4, 'label' => 'আর্থিক নিরীক্ষা', 'ready' => true],
            ],
            'outlineNav' => $this->outlineNavItems(),
            'findingRatings' => $this->findingRatings,
            'financial_section_title' => $this->financial_section_title,
            'financialFindings' => $this->financialFindings,
            'reportSections' => $this->reportSections,
            'financial_criteria' => $this->financial_criteria,
            'vatObservationRows' => $this->vatObservationRows,
            'taxObservationRows' => $this->taxObservationRows,
            'financialIndicatorOptions' => $financialIndicatorOptions,
            'indicatorOptions' => $financialIndicatorOptions,
            'ongoingReports' => $ongoingReports,
            'completedReports' => $completedReports,
            'ongoingCount' => $ongoingCount,
            'completedCount' => $completedCount,
            'pendingSlots' => $pendingSlots,
            'maxConcurrentDrafts' => AuditReport::MAX_CONCURRENT_DRAFTS,
            'canStartNewReport' => $pendingSlots > 0,
        ]);
    }
}
