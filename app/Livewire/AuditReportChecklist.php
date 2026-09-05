<?php

namespace App\Livewire;

use App\Models\AuditChecklistFormat;
use App\Models\AuditChecklistSubmission;
use App\Models\AuditReport;
use App\Models\AuditReportChecklistFile;
use App\Support\AuditChecklistCatalog;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
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
        abort_unless($userId > 0 && (int) $report->user_id === $userId, 403);

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

        session()->flash('status', count($validIds).' heading(s) selected for this report.');
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

    public function saveDraft(): void
    {
        $this->persist('draft');
        session()->flash('status', 'Draft saved. You can edit anytime.');
    }

    public function saveEvidence(): void
    {
        $this->persist('evidence');
        session()->flash('status', 'Saved as evidence for this report.');
        $this->backHome();
    }

    protected function persist(string $status): void
    {
        $format = AuditChecklistFormat::query()->findOrFail((int) $this->formatId);

        if (! $this->report->checklistFormats()->where('audit_checklist_formats.id', $format->id)->exists()) {
            $this->report->checklistFormats()->attach($format->id);
            $this->report->unsetRelation('checklistFormats');
            $this->syncPickedFromReport();
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

        return view('livewire.audit-report-checklist', [
            'files' => $this->report->checklistFiles()->get(),
            'pickerFormats' => $pickerFormats,
            'selectedFormats' => $selectedFormats,
            'submissionsByFormat' => $submissionsByFormat,
            'definition' => $this->currentDefinition(),
            'formatModel' => $this->formatId ? AuditChecklistFormat::query()->find($this->formatId) : null,
        ]);
    }
}
