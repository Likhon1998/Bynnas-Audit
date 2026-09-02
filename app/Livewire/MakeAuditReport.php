<?php

namespace App\Livewire;

use App\Models\AuditIndicator;
use App\Models\AuditReport;
use App\Models\Shakha;
use App\Services\AuditReportDocService;
use App\Services\AuditReportPdfService;
use App\Services\UserAccessService;
use App\Support\AuditReportPaginator;
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

    /** @var array<int, array{serial:string,title:string,body:string,rating:string}> */
    public array $financialFindings = [];

    public string $financial_criteria = '';

    /** @var array<int, array{total_population:string,sample_size:string,instances_found:string,percentage:string}> */
    public array $vatObservationRows = [];

    /** @var array<int, array{total_population:string,sample_size:string,instances_found:string,percentage:string}> */
    public array $taxObservationRows = [];

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
        $this->ensurePage2Defaults();
        $this->ensureTocDefaults();
        $this->ensureSignatureDefaults();
        $this->ensureFinancialAuditDefaults();
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
        $this->ensurePage2Defaults();
        $this->ensureTocDefaults();
        $this->ensureSignatureDefaults();
        $this->ensureFinancialAuditDefaults();
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
    }

    public function savePage4(): void
    {
        $this->ensureFinancialAuditDefaults();
        $this->syncAllFinancialFindingsToToc();
        $this->persistDraft(markTab: 'page4', flash: true, flashMessage: 'পৃষ্ঠা ৪ (আর্থিক নিরীক্ষা) সংরক্ষণ হয়েছে।');
    }

    public function updatedFinancialFindings(mixed $value, ?string $key = null): void
    {
        if (! is_string($key) || ! preg_match('/^(\d+)\.(body|rating)$/', $key, $matches)) {
            return;
        }

        $this->syncTocFindingFromFinancial((int) $matches[1]);
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

        $meta['tabs_done'] = $tabsDone;
        $meta['active_tab'] = $this->activeTab;

        $pages['meta'] = $meta;
        $pages['page2'] = $this->page2Payload();
        $pages['toc'] = $this->tocPayload();
        $pages['page3'] = $this->page3Payload();
        $pages['page4'] = $this->page4Payload();

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
        $this->financial_criteria = (string) ($page4['financial_criteria'] ?? '');
        $this->vatObservationRows = array_values((array) ($page4['vatObservationRows'] ?? []));
        $this->taxObservationRows = array_values((array) ($page4['taxObservationRows'] ?? []));

        $this->ensurePage2Defaults();
        $this->ensureTocDefaults();
        $this->ensureSignatureDefaults();
        $this->ensureFinancialAuditDefaults();

        $this->activeTab = (string) ($report->current_tab ?: ($meta['active_tab'] ?? 'cover'));
        if (! in_array($this->activeTab, ['cover', 'page2', 'page3', 'page4'], true)) {
            $this->activeTab = 'cover';
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

        // Page 2: glance + staff leave room for ~12 TOC rows on A4.
        // Page 3: remaining TOC + signature blocks.
        $this->tocRows = [
            $make('section', '১.০', 'অর্থ ও হিসাব নিরীক্ষা (Accounts and Financial Audit)', '', 2),
            $make('item', '১.১', 'ভ্যাট ও ট্যাক্স কর্তন না করা', 'Major (B)', 2),
            $make('item', '১.২', 'ভ্যাট ও ট্যাক্স পরিশোধ না করা', 'Major (B)', 2),
            $make('item', '১.৩', 'নগদ অর্থ স্থিতির ঘাটতি', 'Medium (C)', 2),
            $make('item', '১.৪', 'বাজেটের অতিরিক্ত ব্যয়', 'Major (B)', 2),
            $make('item', '১.৫', 'অনুমোদন ছাড়া ব্যয়', 'Major (B)', 2),
            $make('item', '১.৬', 'ভাউচার/বিল সংরক্ষণে ঘাটতি', 'Medium (C)', 2),
            $make('item', '১.৭', 'ব্যাংক সমন্বয় বিবরণীতে পার্থক্য', 'Medium (C)', 2),
            $make('item', '১.৮', 'অগ্রিম হিসাব নিষ্পত্তিতে বিলম্ব', 'Medium (C)', 2),
            $make('item', '১.৯', 'রসিদ বই ব্যবহার/হিসাবরক্ষণে দুর্বলতা', 'Minor (D)', 2),
            $make('item', '১.১০', 'খরচের সহায়ক দলিলাদি অসম্পূর্ণ', 'Medium (C)', 2),
            $make('item', '১.১১', 'হিসাব খাত শ্রেণিবিন্যাসে ত্রুটি', 'Medium (C)', 2),

            // Page 3 continuation
            $make('item', '১.১২', 'নগদ ও ব্যাংক রেজিস্টার হালনাগাদ না হওয়া', 'Major (B)', 3),

            $make('section', '২.০', 'স্থায়ী সম্পদ নিরীক্ষা (Fixed Asset Audit)', '', 3),
            $make('item', '২.১', 'স্থায়ী সম্পদ রেজিস্টার হালনাগাদ না হওয়া', 'Medium (C)', 3),
            $make('item', '২.২', 'সম্পদের কোডিং/ট্যাগিং না থাকা', 'Medium (C)', 3),
            $make('item', '২.৩', 'অবচয় হিসাবযোজনায় ঘাটতি', 'Minor (D)', 3),

            $make('section', '৩.০', 'স্টক ব্যবস্থাপনা নিরীক্ষা (Stock Management Audit)', '', 3),
            $make('item', '৩.১', 'স্টক রেজিস্টার সংরক্ষণ/হালনাগাদে ঘাটতি', 'Major (B)', 3),

            $make('section', '৪.০', 'কার্যক্রম/পরিচালন (Program and Operational Audit)', '', 3),
            $make('item', '৪.১', 'কার্যক্রম পরিচালনায় গুরুতর অনিয়ম', 'Unsatisfactory (F)', 3),
            $make('item', '৪.২', 'ঋণ বিতরণ/আদায় প্রক্রিয়ায় অনিয়ম', 'Major (B)', 3),
            $make('item', '৪.৩', 'সদস্য তথ্য/কেন্দ্র যাচাইয়ে ঘাটতি', 'Major (B)', 3),
            $make('item', '৪.৪', 'সফটওয়্যারে পোস্টিংয়ে বিলম্ব/ভুল', 'Medium (C)', 3),
            $make('item', '৪.৫', 'ঋণ বকেয়া ব্যবস্থাপনায় দুর্বলতা', 'Medium (C)', 3),
            $make('item', '৪.৬', 'সদস্য সঞ্চয় উত্তোলন প্রক্রিয়ায় ঘাটতি', 'Medium (C)', 3),
            $make('item', '৪.৭', 'কেন্দ্র সভা/উপস্থিতি রেকর্ডে অসম্পূর্ণতা', 'Medium (C)', 3),
            $make('item', '৪.৮', 'ঋণ অনুমোদন চেকলিস্ট অনুসরণ না করা', 'Medium (C)', 3),
            $make('item', '৪.৯', 'পুনঃতফসিল/পুনর্গঠন নীতিতে ব্যত্যয়', 'Medium (C)', 3),
            $make('item', '৪.১০', 'মাঠ পর্যায়ে তদারকি/ফলোআপে ঘাটতি', 'Medium (C)', 3),
            $make('item', '৪.১১', 'পরিচালন নীতিমালা অনুসরণে দুর্বলতা', 'Medium (C)', 3),

            $make('section', '৫.০০', 'বিগত নিরীক্ষা প্রতিবেদনের ফলোআপ', '', 3),
            $make('section', '৬.০০', 'আইটি (সফটওয়্যার) সংক্রান্ত চেকলিস্ট', '', 3),
            $make('section', '৭.০০', 'বহিঃ নিরীক্ষা প্রতিবেদনের ফলোআপ', '', 3),
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

        if ($this->financialFindings === []) {
            $this->syncFinancialFindingsFromToc();
        }

        if ($this->vatObservationRows === []) {
            $this->vatObservationRows = [$this->blankObservationRow()];
        }

        if ($this->taxObservationRows === []) {
            $this->taxObservationRows = [$this->blankObservationRow()];
        }
    }

    /**
     * Bind a finding row to an existing indicator, or create a new indicator from free text.
     */
    public function applyFinancialIndicator(int $index, ?int $indicatorId, string $title): void
    {
        if (! isset($this->financialFindings[$index])) {
            return;
        }

        $title = trim($title);
        if ($title === '') {
            return;
        }

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
                'category' => 'আর্থিক নিরীক্ষা (রিপোর্ট)',
                'sub_category' => null,
                'indicator_code' => $this->makeUniqueIndicatorCode(),
                'title' => $title,
                'risk_rating' => null,
                'is_active' => true,
            ]);
        }

        $this->financialFindings[$index]['title'] = 'শিরোনাম';
        $this->financialFindings[$index]['body'] = $indicator->title;
        $this->financialFindings[$index]['indicator_id'] = $indicator->id;
        $this->financialFindings[$index]['indicator_code'] = $indicator->indicator_code;

        if (
            blank($this->financialFindings[$index]['rating'] ?? null)
            && filled($indicator->risk_rating)
        ) {
            $this->financialFindings[$index]['rating'] = $this->mapRiskToFindingRating(
                (string) $indicator->risk_rating
            );
        }

        $this->syncTocFindingFromFinancial($index);
        $this->autoSaveHint = 'Indicator সংযুক্ত · সূচিপত্রে আপডেট';

        if ($this->reportId) {
            $this->autoSaveDraft();
        }
    }

    protected function syncAllFinancialFindingsToToc(): void
    {
        foreach (array_keys($this->financialFindings) as $index) {
            $this->syncTocFindingFromFinancial((int) $index);
        }
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
        $finding = $this->financialFindings[$index] ?? null;
        if (! is_array($finding)) {
            return;
        }

        $serial = trim((string) ($finding['serial'] ?? ''));
        $body = trim((string) ($finding['body'] ?? ''));
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
            if (filled($finding['rating'] ?? null)) {
                $this->tocRows[$i]['rating'] = (string) $finding['rating'];
            }

            return;
        }

        $newRow = [
            'type' => 'item',
            'serial' => $serial,
            'finding' => $body,
            'amount' => '',
            'rating' => (string) ($finding['rating'] ?? ''),
            'status' => '',
            'page_no' => '',
            'preview_page' => 2,
        ];

        array_splice($this->tocRows, $this->tocInsertIndexForSerial($serial), 0, [$newRow]);
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

        $serials = ['১.১', '১.২'];
        $findings = [];

        foreach ($this->tocRows as $row) {
            if (($row['type'] ?? 'item') !== 'item') {
                continue;
            }

            if (! in_array($row['serial'] ?? '', $serials, true)) {
                continue;
            }

            $findings[] = [
                'serial' => $row['serial'],
                'title' => 'শিরোনাম',
                'body' => $row['finding'] ?? '',
                'rating' => $row['rating'] ?? '',
                'indicator_id' => null,
                'indicator_code' => null,
            ];
        }

        if ($findings === []) {
            $findings = [
                [
                    'serial' => '১.১',
                    'title' => 'শিরোনাম',
                    'body' => 'ভ্যাট ও ট্যাক্স কর্তন না করা',
                    'rating' => 'Major (B)',
                    'indicator_id' => null,
                    'indicator_code' => null,
                ],
                [
                    'serial' => '১.২',
                    'title' => 'শিরোনাম',
                    'body' => 'ভ্যাট ও ট্যাক্স পরিশোধ না করা',
                    'rating' => 'Major (B)',
                    'indicator_id' => null,
                    'indicator_code' => null,
                ],
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
        return [
            'financial_section_title' => $this->financial_section_title,
            'financialFindings' => $this->financialFindings,
            'financial_criteria' => $this->financial_criteria,
            'vatObservationRows' => $this->vatObservationRows,
            'taxObservationRows' => $this->taxObservationRows,
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
            $a = Carbon::parse($start)->startOfDay();
            $b = Carbon::parse($end)->startOfDay();
            if ($b->lt($a)) {
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
            $a = Carbon::parse($start)->format('d/m/Y');
            $b = Carbon::parse($end)->format('d/m/Y');

            return $a.' হতে '.$b;
        } catch (\Throwable) {
            return '';
        }
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
            'findingRatings' => $this->findingRatings,
            'financial_section_title' => $this->financial_section_title,
            'financialFindings' => $this->financialFindings,
            'financial_criteria' => $this->financial_criteria,
            'vatObservationRows' => $this->vatObservationRows,
            'taxObservationRows' => $this->taxObservationRows,
            'financialIndicatorOptions' => $financialIndicatorOptions,
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
