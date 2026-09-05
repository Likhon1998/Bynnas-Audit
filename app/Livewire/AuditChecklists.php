<?php

namespace App\Livewire;

use App\Models\AuditChecklistFormat;
use App\Support\AuditChecklistCatalog;
use Livewire\Component;

class AuditChecklists extends Component
{
    public string $viewMode = 'catalog'; // catalog | preview

    public string $search = '';

    public ?int $formatId = null;

    public function mount(?string $format = null): void
    {
        $this->ensureFormatsSynced();

        if (is_string($format) && trim($format) !== '') {
            $this->openFormat(trim($format));
        }
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

    public function openFormat(string $code): void
    {
        $def = AuditChecklistCatalog::findByCode($code);
        abort_unless($def, 404);

        $format = AuditChecklistFormat::query()->where('code', $code)->firstOrFail();
        $this->formatId = $format->id;
        $this->viewMode = 'preview';
    }

    public function backToCatalog(): void
    {
        $this->viewMode = 'catalog';
        $this->formatId = null;
    }

    public function render()
    {
        $formats = AuditChecklistFormat::query()
            ->where('is_active', true)
            ->orderBy('format_number')
            ->get();

        $q = trim($this->search);
        if ($q !== '') {
            $formats = $formats->filter(function (AuditChecklistFormat $f) use ($q) {
                $hay = mb_strtolower($f->heading.' '.$f->format_number.' '.$f->code);

                return str_contains($hay, mb_strtolower($q));
            })->values();
        }

        $formatModel = $this->formatId
            ? AuditChecklistFormat::query()->find($this->formatId)
            : null;

        $definition = $formatModel
            ? AuditChecklistCatalog::findByCode($formatModel->code)
            : null;

        return view('livewire.audit-checklists', [
            'formats' => $formats,
            'definition' => $definition,
            'formatModel' => $formatModel,
        ]);
    }
}
