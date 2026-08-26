<?php

namespace App\Livewire;

use App\Models\AuditReport;
use App\Models\Shakha;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
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

    /** @var list<string> */
    public array $findingRatings = [
        '',
        'Major (B)',
        'Medium (C)',
        'Minor (D)',
        'Unsatisfactory (F)',
    ];

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

        $shakha = Shakha::query()->with('area')->findOrFail($this->shakha_id);

        $this->shakha_display_name = trim($shakha->name.($shakha->code ? ' ('.$shakha->code.')' : ''));
        $this->area_display_name = (string) ($shakha->area?->name ?? '');
        $this->memo_no = 'অডিট/শাখা - '.($shakha->code ?: $shakha->id).'/'.$this->report_year;
        $this->branch_opening_date = optional($shakha->opening_date ?? $shakha->opened_at)?->toDateString() ?: '';
        $this->ensurePage2Defaults();
        $this->ensureTocDefaults();
        $this->ensureSignatureDefaults();
        $this->sign_auditor_name = $this->auditor_name;
        $this->sign_auditor_designation = $this->auditor_designation;

        try {
            $report = AuditReport::query()->create([
                'shakha_id' => $this->shakha_id,
                'report_month' => $this->report_month,
                'report_year' => $this->report_year,
                'status' => 'draft',
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
                    'page2' => $this->page2Payload(),
                    'toc' => $this->tocPayload(),
                    'page3' => $this->page3Payload(),
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
        $this->sign_auditor_name = $this->auditor_name;
        $this->sign_auditor_designation = $this->auditor_designation;
        $this->resetErrorBag();
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

        $report = AuditReport::query()->findOrFail($this->reportId);

        $report->update([
            'report_month' => $this->report_month,
            'report_year' => $this->report_year,
            'memo_no' => $this->memo_no,
            'report_date' => $this->report_date,
            'control_rating' => $this->control_rating,
            'shakha_display_name' => $this->shakha_display_name,
            'area_display_name' => $this->area_display_name,
            'audit_period_label' => $this->audit_period_label,
            'audit_start_date' => $this->audit_start_date,
            'audit_end_date' => $this->audit_end_date,
            'working_days' => $this->working_days,
            'period_scope' => $this->period_scope,
            'draft_sent_date' => $this->draft_sent_date ?: null,
            'comments_received_date' => $this->comments_received_date ?: null,
            'auditor_name' => $this->auditor_name,
            'auditor_designation' => $this->auditor_designation,
            'logo_path' => $this->logo_path,
            'pages_data' => array_merge((array) $report->pages_data, [
                'page2' => $this->page2Payload(),
                'toc' => $this->tocPayload(),
                'page3' => $this->page3Payload(),
            ]),
        ]);

        session()->flash('status', 'Cover page সংরক্ষণ হয়েছে।');
        $this->activeTab = 'page2';
    }

    public function savePage2(): void
    {
        $this->ensureTocDefaults();
        $report = AuditReport::query()->findOrFail($this->reportId);
        $pages = (array) $report->pages_data;
        $pages['page2'] = $this->page2Payload();
        $pages['toc'] = $this->tocPayload();
        $report->update(['pages_data' => $pages]);

        session()->flash('status', 'পৃষ্ঠা ২ সংরক্ষণ হয়েছে।');
        $this->activeTab = 'page3';
    }

    public function savePage3(): void
    {
        $this->ensureTocDefaults();
        $this->ensureSignatureDefaults();
        $report = AuditReport::query()->findOrFail($this->reportId);
        $pages = (array) $report->pages_data;
        $pages['toc'] = $this->tocPayload();
        $pages['page3'] = $this->page3Payload();
        $report->update(['pages_data' => $pages]);

        session()->flash('status', 'পৃষ্ঠা ৩ (সূচিপত্র ধারাবাহিকতা ও স্বাক্ষর) সংরক্ষণ হয়েছে।');
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
        $this->ensurePage2Defaults();
        $this->ensureTocDefaults();
        $this->ensureSignatureDefaults();

        $fontRegular = storage_path('fonts/NotoSansBengali-Regular.ttf');
        $fontBold = storage_path('fonts/NotoSansBengali-Bold.ttf');

        $logoDataUri = null;
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            $binary = Storage::disk('public')->get($this->logo_path);
            $mime = Storage::disk('public')->mimeType($this->logo_path) ?: 'image/png';
            $logoDataUri = 'data:'.$mime.';base64,'.base64_encode($binary);
        }

        $safeName = preg_replace('/[^\pL\pN\-]+/u', '-', trim($this->shakha_display_name ?: 'audit')) ?: 'audit';
        $filename = 'audit-report-'.$safeName.'-'.$this->report_year.'-'.$this->report_month.'.pdf';

        $pdf = Pdf::loadView('audits.pdf', [
            'fontRegular' => $fontRegular,
            'fontBold' => $fontBold,
            'logoDataUri' => $logoDataUri,
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
            'tocPage2Rows' => $this->tocRowsForPage(2),
            'tocPage3Rows' => $this->tocRowsForPage(3),
            'sign_auditor_name' => $this->sign_auditor_name,
            'sign_auditor_designation' => $this->sign_auditor_designation,
            'sign_auditor_date' => $this->sign_auditor_date,
            'sign_bm_name' => $this->sign_bm_name,
            'sign_bm_date' => $this->sign_bm_date,
            'sign_abm_name' => $this->sign_abm_name,
            'sign_abm_date' => $this->sign_abm_date,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            function () use ($pdf) {
                echo $pdf->output();
            },
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    public function backToSelect(): void
    {
        $this->step = 'select';
        $this->reportId = null;
        $this->activeTab = 'cover';
        $this->showPreview = false;
        $this->logoUpload = null;
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

    public static function findingRatingStyle(?string $rating): array
    {
        return match ($rating) {
            'Major (B)' => ['bg' => '#f8d5b8', 'color' => '#111111'],
            'Medium (C)' => ['bg' => '#e67e22', 'color' => '#111111'],
            'Minor (D)' => ['bg' => '#f5e6a3', 'color' => '#111111'],
            'Unsatisfactory (F)' => ['bg' => '#dc2626', 'color' => '#ffffff'],
            default => ['bg' => '#ffffff', 'color' => '#111111'],
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

    public function render()
    {
        $shakhas = Shakha::query()->with('area')->orderBy('name')->get();

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

        return view('livewire.make-audit-report', [
            'branchOptions' => $branchOptions,
            'shakhaCount' => $shakhas->count(),
            'selectedShakhaLabel' => $this->selectedShakhaLabel(),
            'ratingColor' => AuditReport::ratingColor($this->control_rating),
            'monthLabel' => Carbon::create(null, $this->report_month, 1)->format('F'),
            'logoUrl' => $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null,
            'tocPage2Rows' => $this->tocRowsForPage(2),
            'tocPage3Rows' => $this->tocRowsForPage(3),
            'tabs' => [
                ['id' => 'cover', 'num' => 1, 'label' => 'Cover Page', 'ready' => true],
                ['id' => 'page2', 'num' => 2, 'label' => 'এক নজরে + সূচিপত্র', 'ready' => true],
                ['id' => 'page3', 'num' => 3, 'label' => 'সূচিপত্র + স্বাক্ষর', 'ready' => true],
            ],
        ]);
    }
}
