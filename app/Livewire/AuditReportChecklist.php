<?php

namespace App\Livewire;

use App\Models\AuditChecklistFormat;
use App\Models\AuditChecklistSubmission;
use App\Models\AuditReport;
use App\Models\AuditReportChecklistFile;
use App\Services\ChecklistAiSummaryService;
use App\Services\ChecklistReportInfluenceService;
use App\Support\AuditChecklistCatalog;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditReportChecklist extends Component
{
    use WithFileUploads;

    public AuditReport $report;

    /** @var mixed */
    public $upload = null;

    public string $viewMode = 'home'; // home | editor

    public string $search = '';

    public bool $choosingHeadings = false;

    /** @var list<int|string> */
    public array $pickedFormatIds = [];

    public ?int $formatId = null;

    public ?int $submissionId = null;

    public string $shakha_name = '';

    public string $audit_period = '';

    public string $summary = '';

    /** @var array{sections?: array<string, list<array<string, mixed>>>} */
    public array $payload = [];

    public function mount(AuditReport $report): void
    {
        $userId = (int) (auth()->id() ?? 0);
        abort_unless($userId > 0 && $report->isAccessibleBy(auth()->user()), 403);

        $this->report = $report->load(['shakha.area']);
        $this->ensureFormatsSynced();
        $this->syncPickedFromReport();
    }

    public function ensureFormatsSynced(): void
    {
        foreach (AuditChecklistCatalog::all() as $def) {
            AuditChecklistFormat::query()->updateOrCreate(
                ['code' => $def['code']],
                [
                    'format_number' => $def['number'],
                    'heading' => $def['heading'],
                    'org_name' => $def['org_name'],
                    'dept_name' => $def['dept_name'],
                    'is_active' => true,
                ]
            );
        }
    }

    public function syncPickedFromReport(): void
    {
        $this->pickedFormatIds = $this->report->checklistFormats()
            ->pluck('audit_checklist_formats.id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function openHeadingPicker(): void
    {
        $this->syncPickedFromReport();
        $this->choosingHeadings = true;
        $this->search = '';
    }

    public function closeHeadingPicker(): void
    {
        $this->choosingHeadings = false;
        $this->syncPickedFromReport();
    }

    public function togglePickFormat(int $formatId): void
    {
        $ids = array_map('intval', $this->pickedFormatIds);
        if (in_array($formatId, $ids, true)) {
            $this->pickedFormatIds = array_values(array_filter($ids, fn ($id) => $id !== $formatId));
        } else {
            $ids[] = $formatId;
            $this->pickedFormatIds = array_values($ids);
        }
    }

    public function saveHeadingSelection(): void
    {
        $ids = collect($this->pickedFormatIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $validIds = AuditChecklistFormat::query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->report->checklistFormats()->sync($validIds);
        $this->report->unsetRelation('checklistFormats');
        $this->choosingHeadings = false;
        $this->syncPickedFromReport();

        session()->flash('status', count($validIds).' checklist heading(s) selected for this visit. Save each as evidence when done.');
    }

    public function removeHeading(int $formatId): void
    {
        $this->report->checklistFormats()->detach($formatId);
        $this->report->unsetRelation('checklistFormats');
        $this->syncPickedFromReport();
        session()->flash('status', 'Heading removed from this report.');
    }

    public function updatedUpload(): void
    {
        if ($this->upload) {
            $this->saveUpload();
        }
    }

    public function saveUpload(): void
    {
        $this->resetErrorBag('upload');

        $this->validate([
            'upload' => [
                'required',
                'file',
                'max:10240',
                'extensions:pdf,doc,docx',
            ],
        ], [
            'upload.required' => 'Choose a PDF or DOC file.',
            'upload.extensions' => 'Only PDF, DOC, or DOCX files are allowed.',
            'upload.max' => 'File must be 10MB or smaller.',
        ]);

        $file = $this->upload;
        $path = $file->store('audit-checklists/'.$this->report->id, 'public');

        $this->report->checklistFiles()->create([
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'mime_type' => $file->getMimeType() ?: null,
            'size_bytes' => (int) $file->getSize(),
        ]);

        $this->upload = null;
    }

    public function downloadFile(int $fileId): StreamedResponse
    {
        $file = $this->ownedFile($fileId);
        abort_unless($file->stored_path && Storage::disk('public')->exists($file->stored_path), 404);

        return Storage::disk('public')->download($file->stored_path, $file->original_name);
    }

    protected function ownedFile(int $fileId): AuditReportChecklistFile
    {
        return AuditReportChecklistFile::query()
            ->where('audit_report_id', $this->report->id)
            ->findOrFail($fileId);
    }

    public function workOnFormat(int $formatId): void
    {
        $attached = $this->report->checklistFormats()->where('audit_checklist_formats.id', $formatId)->exists();
        abort_unless($attached, 403);

        $format = AuditChecklistFormat::query()->findOrFail($formatId);
        $def = AuditChecklistCatalog::findByCode($format->code);
        abort_unless($def, 404);

        $existing = AuditChecklistSubmission::query()
            ->where('audit_report_id', $this->report->id)
            ->where('audit_checklist_format_id', $formatId)
            ->latest('id')
            ->first();

        if ($existing) {
            $this->openSubmission($existing->id);

            return;
        }

        $this->formatId = $format->id;
        $this->submissionId = null;
        $this->shakha_name = (string) ($this->report->shakha_display_name ?: ($this->report->shakha?->name ?? ''));
        $this->audit_period = (string) $this->report->periodLabel();
        $this->summary = '';
        $this->payload = AuditChecklistCatalog::blankPayload($def);
        $this->normalizeSectionSummaries($def);
        $this->viewMode = 'editor';
    }

    public function openSubmission(int $id): void
    {
        $row = AuditChecklistSubmission::query()
            ->where('user_id', auth()->id())
            ->where('audit_report_id', $this->report->id)
            ->with('format')
            ->findOrFail($id);

        $this->submissionId = $row->id;
        $this->formatId = $row->audit_checklist_format_id;
        $this->shakha_name = (string) ($row->shakha_name ?? '');
        $this->audit_period = (string) ($row->audit_period ?? '');
        $this->summary = (string) ($row->summary ?? '');
        $this->payload = is_array($row->payload) ? $row->payload : [];
        $def = AuditChecklistCatalog::findByCode((string) ($row->format?->code ?? ''))
            ?? AuditChecklistCatalog::findByNumber((int) ($row->format?->format_number ?? 0));
        if ($def) {
            $this->normalizeSectionSummaries($def);
        }
        $this->viewMode = 'editor';
    }

    public function backHome(): void
    {
        $this->viewMode = 'home';
        $this->submissionId = null;
        $this->formatId = null;
        $this->payload = [];
        $this->summary = '';
        $this->resetErrorBag();
    }

    public function addRow(?string $sectionKey = null): void
    {
        $def = $this->currentDefinition();
        if (! $def) {
            return;
        }

        $layout = $def['layout'] ?? '';

        if ($layout === AuditChecklistCatalog::LAYOUT_SOCIETY_MANAGEMENT) {
            if (! isset($this->payload['stats_rows']) || ! is_array($this->payload['stats_rows'])) {
                $this->payload['stats_rows'] = [];
            }
            $this->payload['stats_rows'][] = AuditChecklistCatalog::blankManagementStatsRow();

            return;
        }

        if ($layout === AuditChecklistCatalog::LAYOUT_MEMBER_ADMISSION
            || $layout === AuditChecklistCatalog::LAYOUT_SAVINGS_LOAN_COLLECTION
            || $layout === AuditChecklistCatalog::LAYOUT_SAVINGS_REFUND) {
            $checks = (int) ($def['check_count'] ?? match ($layout) {
                AuditChecklistCatalog::LAYOUT_SAVINGS_REFUND => 17,
                AuditChecklistCatalog::LAYOUT_SAVINGS_LOAN_COLLECTION => 12,
                default => 11,
            });
            if (! isset($this->payload['rows']) || ! is_array($this->payload['rows'])) {
                $this->payload['rows'] = [];
            }
            $this->payload['rows'][] = match ($layout) {
                AuditChecklistCatalog::LAYOUT_SAVINGS_REFUND => AuditChecklistCatalog::blankSavingsRefundRow($checks),
                AuditChecklistCatalog::LAYOUT_SAVINGS_LOAN_COLLECTION => AuditChecklistCatalog::blankSavingsLoanRow($checks),
                default => AuditChecklistCatalog::blankMemberRow($checks),
            };

            return;
        }

        if ($sectionKey === null || ! isset($def['sections'][$sectionKey])) {
            return;
        }

        $checks = (int) ($def['sections'][$sectionKey]['check_count'] ?? 0);
        $this->payload['sections'][$sectionKey][] = AuditChecklistCatalog::blankSocietyRow($checks);
    }

    public function removeRow(int $index, ?string $sectionKey = null): void
    {
        $def = $this->currentDefinition();
        if (! $def) {
            return;
        }

        $layout = $def['layout'] ?? '';

        if ($layout === AuditChecklistCatalog::LAYOUT_SOCIETY_MANAGEMENT) {
            if (! isset($this->payload['stats_rows'][$index])) {
                return;
            }
            $rows = $this->payload['stats_rows'];
            if (count($rows) <= 1) {
                return;
            }
            unset($rows[$index]);
            $this->payload['stats_rows'] = array_values($rows);

            return;
        }

        if ($layout === AuditChecklistCatalog::LAYOUT_MEMBER_ADMISSION
            || $layout === AuditChecklistCatalog::LAYOUT_SAVINGS_LOAN_COLLECTION
            || $layout === AuditChecklistCatalog::LAYOUT_SAVINGS_REFUND) {
            if (! isset($this->payload['rows'][$index])) {
                return;
            }
            $rows = $this->payload['rows'];
            if (count($rows) <= 1) {
                return;
            }
            unset($rows[$index]);
            $this->payload['rows'] = array_values($rows);

            return;
        }

        if ($sectionKey === null || ! isset($this->payload['sections'][$sectionKey][$index])) {
            return;
        }

        $rows = $this->payload['sections'][$sectionKey];
        if (count($rows) <= 1) {
            return;
        }

        unset($rows[$index]);
        $this->payload['sections'][$sectionKey] = array_values($rows);
    }

    public function generateSectionSummary(string $sectionKey): void
    {
        $definition = $this->currentDefinition();
        if (! $definition || ! isset($definition['sections'][$sectionKey])) {
            session()->flash('error', 'Invalid checklist section.');

            return;
        }

        $this->normalizeSectionSummaries($definition);

        try {
            $text = app(ChecklistAiSummaryService::class)->summarizeSection(
                $definition,
                $this->payload,
                $sectionKey,
                $this->shakha_name,
                $this->audit_period,
            );
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        $this->payload['section_summaries'][$sectionKey] = $text;
        session()->flash('status', 'AI সারসংক্ষেপ generated — review and edit before saving evidence.');
    }

    public function generateAllSectionSummaries(): void
    {
        $definition = $this->currentDefinition();
        $keys = array_keys((array) ($definition['sections'] ?? []));
        if ($definition === null || $keys === []) {
            session()->flash('error', 'This format has no section summaries.');

            return;
        }

        $this->normalizeSectionSummaries($definition);
        $ai = app(ChecklistAiSummaryService::class);
        $ok = 0;
        $lastError = null;

        foreach ($keys as $key) {
            try {
                $this->payload['section_summaries'][$key] = $ai->summarizeSection(
                    $definition,
                    $this->payload,
                    (string) $key,
                    $this->shakha_name,
                    $this->audit_period,
                );
                $ok++;
            } catch (RuntimeException $e) {
                $lastError = $e->getMessage();
            }
        }

        if ($ok > 0) {
            session()->flash('status', "AI সারসংক্ষেপ generated for {$ok} head(s). Unusual (✗) points are emphasized for the report.");
        } else {
            session()->flash('error', $lastError ?: 'Could not generate সারসংক্ষেপ.');
        }
    }

    public function generateFormatSummary(): void
    {
        $definition = $this->currentDefinition();
        if (! $definition) {
            session()->flash('error', 'Open a checklist format first.');

            return;
        }

        try {
            $text = app(ChecklistAiSummaryService::class)->summarizeFormat(
                $definition,
                $this->payload,
                $this->shakha_name,
                $this->audit_period,
            );
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        $this->summary = $text;
        session()->flash('status', 'AI সারসংক্ষেপ generated — review and edit before saving evidence.');
    }

    public function saveDraft(): void
    {
        $this->persist('draft');
        session()->flash('status', 'Draft saved. You can edit anytime.');
    }

    public function saveEvidence(): void
    {
        $this->persist('evidence');

        $progress = app(\App\Services\VisitAuditWorkService::class)->checklistProgress($this->report);
        $msg = 'Saved as evidence for this report. Use সারসংক্ষেপ → Add to report to place observations in findings (optional).';
        if ($progress['needs_selection'] ?? false) {
            $msg .= ' Select the checklist headings that apply to this visit.';
        } elseif (($progress['required'] ?? 0) > 0) {
            $msg .= ' Selected checklist progress: '.$progress['done'].'/'.$progress['required'].'.';
        }

        session()->flash('status', $msg);
        $this->backHome();
    }

    public function addSectionSummaryToReport(string $sectionKey, bool $withAi = false): void
    {
        $definition = $this->currentDefinition();
        $format = AuditChecklistFormat::query()->find((int) $this->formatId);
        if (! $definition || ! $format || ! isset($definition['sections'][$sectionKey])) {
            session()->flash('error', 'Invalid checklist section.');

            return;
        }

        $this->normalizeSectionSummaries($definition);
        $text = trim((string) data_get($this->payload, 'section_summaries.'.$sectionKey, ''));
        $label = (string) ($definition['sections'][$sectionKey]['label'] ?? $sectionKey);
        $seedKey = ChecklistReportInfluenceService::sectionSummarySeedKey((string) $format->code, $sectionKey);

        $this->addSummaryObservationToReport($format, $text, $seedKey, $label, $withAi);
    }

    public function addFormatSummaryToReport(bool $withAi = false): void
    {
        $format = AuditChecklistFormat::query()->find((int) $this->formatId);
        if (! $format) {
            session()->flash('error', 'Open a checklist format first.');

            return;
        }

        $text = trim($this->summary);
        $seedKey = ChecklistReportInfluenceService::formatSummarySeedKey((string) $format->code);
        $this->addSummaryObservationToReport($format, $text, $seedKey, (string) $format->heading, $withAi);
    }

    protected function addSummaryObservationToReport(
        AuditChecklistFormat $format,
        string $text,
        string $seedKey,
        string $sourceLabel,
        bool $withAi,
    ): void {
        $this->persist('draft');

        if (! $this->report->checklistFormats()->where('audit_checklist_formats.id', $format->id)->exists()) {
            $this->report->checklistFormats()->attach($format->id);
            $this->report->unsetRelation('checklistFormats');
            $this->syncPickedFromReport();
        }

        try {
            app(ChecklistReportInfluenceService::class)->addSummaryToReport(
                $this->report->fresh() ?? $this->report,
                $format,
                $text,
                $seedKey,
                $sourceLabel,
                $withAi,
            );
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        $this->report->refresh();
        $mode = $withAi ? 'AI সহ' : 'AI ছাড়া';
        session()->flash('status', "পর্যবেক্ষণ রিপোর্টে যোগ হয়েছে ({$mode})। বিভাগ/শিরোনাম/প্রচলিত নিয়ম ও জবাব আপনি পূরণ করবেন।");
    }

    protected function persist(string $status): void
    {
        $format = AuditChecklistFormat::query()->findOrFail((int) $this->formatId);

        if (! $this->report->checklistFormats()->where('audit_checklist_formats.id', $format->id)->exists()) {
            $this->report->checklistFormats()->attach($format->id);
            $this->report->unsetRelation('checklistFormats');
            $this->syncPickedFromReport();
        }

        $def = AuditChecklistCatalog::findByCode((string) $format->code)
            ?? AuditChecklistCatalog::findByNumber((int) $format->format_number);
        if ($def) {
            $this->normalizeSectionSummaries($def);
        }

        $combinedSummary = $this->combinedSectionSummaryText($def);
        if ($combinedSummary !== '') {
            $this->summary = $combinedSummary;
        }

        $data = [
            'user_id' => auth()->id(),
            'audit_report_id' => $this->report->id,
            'audit_checklist_format_id' => $format->id,
            'heading' => $format->heading,
            'shakha_name' => trim($this->shakha_name) !== '' ? trim($this->shakha_name) : null,
            'audit_period' => trim($this->audit_period) !== '' ? trim($this->audit_period) : null,
            'payload' => $this->payload,
            'summary' => trim($this->summary) !== '' ? trim($this->summary) : null,
            'status' => $status,
            'saved_at' => now(),
        ];

        if ($this->submissionId) {
            $row = AuditChecklistSubmission::query()
                ->where('user_id', auth()->id())
                ->where('audit_report_id', $this->report->id)
                ->findOrFail($this->submissionId);
            $row->update($data);
            $this->submissionId = $row->id;
        } else {
            $existing = AuditChecklistSubmission::query()
                ->where('audit_report_id', $this->report->id)
                ->where('audit_checklist_format_id', $format->id)
                ->latest('id')
                ->first();

            if ($existing) {
                $existing->update($data);
                $this->submissionId = $existing->id;
            } else {
                $row = AuditChecklistSubmission::query()->create($data);
                $this->submissionId = $row->id;
            }
        }
    }

    public function deleteSubmission(int $id): void
    {
        AuditChecklistSubmission::query()
            ->where('user_id', auth()->id())
            ->where('audit_report_id', $this->report->id)
            ->whereKey($id)
            ->delete();

        if ($this->submissionId === $id) {
            $this->backHome();
        }
    }

    /**
     * @param  array<string, mixed>|null  $definition
     */
    protected function normalizeSectionSummaries(?array $definition): void
    {
        $keys = array_keys((array) ($definition['sections'] ?? []));
        if ($keys === []) {
            return;
        }

        if (! isset($this->payload['section_summaries']) || ! is_array($this->payload['section_summaries'])) {
            $this->payload['section_summaries'] = [];
        }

        foreach ($keys as $key) {
            if (! array_key_exists($key, $this->payload['section_summaries'])) {
                $this->payload['section_summaries'][$key] = '';
            } else {
                $this->payload['section_summaries'][$key] = (string) $this->payload['section_summaries'][$key];
            }
        }
    }

    /**
     * @param  array<string, mixed>|null  $definition
     */
    protected function combinedSectionSummaryText(?array $definition): string
    {
        $sections = (array) ($definition['sections'] ?? []);
        $summaries = (array) ($this->payload['section_summaries'] ?? []);
        if ($sections === [] || $summaries === []) {
            return trim($this->summary);
        }

        $parts = [];
        foreach ($sections as $key => $section) {
            $text = trim((string) ($summaries[$key] ?? ''));
            if ($text === '') {
                continue;
            }
            $label = (string) ($section['label'] ?? $key);
            $parts[] = $label.":\n".$text;
        }

        return implode("\n\n", $parts);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function currentDefinition(): ?array
    {
        if (! $this->formatId) {
            return null;
        }

        $format = AuditChecklistFormat::query()->find($this->formatId);

        return $format ? AuditChecklistCatalog::findByCode($format->code) : null;
    }

    public function render()
    {
        $allFormats = AuditChecklistFormat::query()
            ->where('is_active', true)
            ->orderBy('format_number')
            ->get();

        $q = trim($this->search);
        $pickerFormats = $allFormats;
        if ($q !== '' && $this->choosingHeadings) {
            $pickerFormats = $allFormats->filter(function (AuditChecklistFormat $f) use ($q) {
                $hay = mb_strtolower($f->heading.' '.$f->format_number.' '.$f->code);

                return str_contains($hay, mb_strtolower($q));
            })->values();
        }

        $selectedFormats = $this->report->checklistFormats()->get();

        $submissionsByFormat = AuditChecklistSubmission::query()
            ->where('audit_report_id', $this->report->id)
            ->orderByDesc('id')
            ->get()
            ->unique('audit_checklist_format_id')
            ->keyBy('audit_checklist_format_id');

        $reportBlocks = (array) data_get($this->report->fresh()?->pages_data ?? $this->report->pages_data, 'page4.reportBlocks', []);
        $addedSummaryKeys = app(ChecklistReportInfluenceService::class)->existingSeedKeys($reportBlocks);

        return view('livewire.audit-report-checklist', [
            'files' => $this->report->checklistFiles()->get(),
            'pickerFormats' => $pickerFormats,
            'selectedFormats' => $selectedFormats,
            'submissionsByFormat' => $submissionsByFormat,
            'definition' => $this->currentDefinition(),
            'formatModel' => $this->formatId ? AuditChecklistFormat::query()->find($this->formatId) : null,
            'aiSummaryReady' => app(ChecklistAiSummaryService::class)->isConfigured(),
            'addedSummaryKeys' => $addedSummaryKeys,
        ]);
    }
}
