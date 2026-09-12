<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Shakha;
use App\Models\ShakhaEmployee;
use App\Models\ShakhaEmployeeTransfer;
use App\Services\StaffFinancialOccurrenceService;
use App\Support\Divisions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShakhaEmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $division = trim((string) $request->input('division', ''));
        if ($division !== '' && ! in_array($division, Divisions::OPTIONS, true)) {
            $division = '';
        }

        $areaId = $request->integer('area_id') ?: null;
        $shakhaId = $request->integer('shakha_id') ?: null;
        $status = (string) $request->input('status', 'all');
        $q = trim((string) $request->input('q', ''));

        if ($areaId) {
            $area = Area::query()->find($areaId);
            if (! $area || ($division !== '' && $area->division !== $division)) {
                $areaId = null;
                $shakhaId = null;
            } elseif ($division === '') {
                $division = (string) $area->division;
            }
        }

        if ($shakhaId) {
            $shakha = Shakha::query()->with('area')->find($shakhaId);
            if (! $shakha || ($areaId && (int) $shakha->area_id !== $areaId)) {
                $shakhaId = null;
            } elseif (! $areaId) {
                $areaId = (int) $shakha->area_id;
                $division = (string) ($shakha->area?->division ?: $division);
            }
        }

        $areas = Area::query()
            ->with(['shakhas' => fn ($query) => $query->orderBy('name')])
            ->when($division !== '', fn ($query) => $query->where('division', $division))
            ->orderBy('division')
            ->orderBy('name')
            ->get();

        $byShakha = Shakha::query()
            ->with('area')
            ->withCount('employees')
            ->when($division !== '', fn ($query) => $query->whereHas('area', fn ($q) => $q->where('division', $division)))
            ->when($areaId, fn ($query) => $query->where('area_id', $areaId))
            ->orderByDesc('employees_count')
            ->orderBy('name')
            ->get();

        $shakhas = $byShakha;

        $employees = collect();
        if ($shakhaId) {
            $employees = ShakhaEmployee::query()
                ->with(['shakha.area'])
                ->where('shakha_id', $shakhaId)
                ->when(in_array($status, ShakhaEmployee::STATUSES, true), fn ($query) => $query->where('status', $status))
                ->when($q !== '', function ($query) use ($q) {
                    $query->where(function ($inner) use ($q) {
                        $inner->where('employee_code', 'like', "%{$q}%")
                            ->orWhere('name', 'like', "%{$q}%")
                            ->orWhere('designation', 'like', "%{$q}%");
                    });
                })
                ->orderBy('name')
                ->get();
        } elseif ($q !== '') {
            // Org-wide search by fixed ID / name so authority can open a dossier without knowing current shakha.
            $employees = ShakhaEmployee::query()
                ->with(['shakha.area'])
                ->when(in_array($status, ShakhaEmployee::STATUSES, true), fn ($query) => $query->where('status', $status))
                ->where(function ($inner) use ($q) {
                    $inner->where('employee_code', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%")
                        ->orWhere('designation', 'like', "%{$q}%");
                })
                ->orderBy('name')
                ->limit(100)
                ->get();
        }

        $reportCounts = app(StaffFinancialOccurrenceService::class)
            ->lifetimeVisitCounts($employees->pluck('id')->all());

        return view('shakha-employees.index', [
            'areas' => $areas,
            'shakhas' => $shakhas,
            'employees' => $employees,
            'byShakha' => $byShakha,
            'divisions' => Divisions::OPTIONS,
            'transferTargets' => $shakhaId
                ? $this->transferTargets(Shakha::query()->findOrFail($shakhaId))
                : collect(),
            'reportCounts' => $reportCounts,
            'filters' => [
                'division' => $division,
                'area_id' => $areaId,
                'shakha_id' => $shakhaId,
                'status' => $status,
                'q' => $q,
            ],
            'canManage' => auth()->user()?->can('shakhas.manage') ?? false,
        ]);
    }

    public function manage(Shakha $shakha): View
    {
        $shakha->load(['area', 'employees']);
        $employees = $shakha->employees;
        $reportCounts = app(StaffFinancialOccurrenceService::class)
            ->lifetimeVisitCounts($employees->pluck('id')->all());

        return view('shakha-employees.manage', [
            'shakha' => $shakha,
            'employees' => $employees,
            'employee' => null,
            'transferTargets' => $this->transferTargets($shakha),
            'reportCounts' => $reportCounts,
            'canManage' => auth()->user()?->can('shakhas.manage') ?? false,
        ]);
    }

    public function store(Request $request, Shakha $shakha): RedirectResponse
    {
        $data = $this->validated($request, $shakha);
        $photo = $request->file('photo');

        if ($photo instanceof UploadedFile) {
            $data['photo_path'] = $photo->store('shakha-employees/'.$shakha->id, 'public');
        }

        $shakha->employees()->create($data);

        return redirect()
            ->route('shakha-employees.manage', $shakha)
            ->with('status', 'Employee added to '.$shakha->name.'.');
    }

    public function edit(ShakhaEmployee $shakhaEmployee): View
    {
        $shakhaEmployee->load(['shakha.area']);
        $employees = $shakhaEmployee->shakha->employees()->orderBy('sort_order')->orderBy('name')->get();
        $reportCounts = app(StaffFinancialOccurrenceService::class)
            ->lifetimeVisitCounts($employees->pluck('id')->all());

        return view('shakha-employees.manage', [
            'shakha' => $shakhaEmployee->shakha,
            'employees' => $employees,
            'employee' => $shakhaEmployee,
            'transferTargets' => $this->transferTargets($shakhaEmployee->shakha),
            'reportCounts' => $reportCounts,
            'canManage' => auth()->user()?->can('shakhas.manage') ?? false,
        ]);
    }

    public function dossier(ShakhaEmployee $shakhaEmployee, StaffFinancialOccurrenceService $occurrence): View
    {
        $shakhaEmployee->load(['shakha.area']);
        $payload = $occurrence->dossierPayload($shakhaEmployee);

        return view('shakha-employees.dossier', [
            'employee' => $shakhaEmployee,
            'reportCount' => $payload['report_count'],
            'findingCount' => $payload['finding_count'],
            'shakhaCount' => $payload['shakha_count'],
            'transferCount' => $payload['transfer_count'],
            'findings' => $payload['findings'],
            'transfers' => $payload['transfers'],
            'reportLinks' => $payload['report_links'],
        ]);
    }

    public function update(Request $request, ShakhaEmployee $shakhaEmployee): RedirectResponse
    {
        $data = $this->validated($request, $shakhaEmployee->shakha, $shakhaEmployee);
        $photo = $request->file('photo');
        $removePhoto = $request->boolean('remove_photo');

        if ($photo instanceof UploadedFile) {
            $shakhaEmployee->deleteStoredPhoto();
            $data['photo_path'] = $photo->store('shakha-employees/'.$shakhaEmployee->shakha_id, 'public');
        } elseif ($removePhoto) {
            $shakhaEmployee->deleteStoredPhoto();
            $data['photo_path'] = null;
        }

        $shakhaEmployee->update($data);

        return redirect()
            ->route('shakha-employees.manage', $shakhaEmployee->shakha)
            ->with('status', 'Employee updated.');
    }

    public function transfer(Request $request, ShakhaEmployee $shakhaEmployee): RedirectResponse
    {
        $from = $shakhaEmployee->shakha;
        $data = $request->validate([
            'target_shakha_id' => [
                'required',
                'integer',
                'exists:shakhas,id',
                Rule::notIn([(int) $shakhaEmployee->shakha_id]),
            ],
            'joined_shakha_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
            'return_to' => ['nullable', 'string', 'max:2000'],
        ]);

        $target = Shakha::query()->findOrFail((int) $data['target_shakha_id']);
        $fixedCode = (string) $shakhaEmployee->employee_code;
        $joined = $data['joined_shakha_at'] ?? now()->toDateString();
        $note = trim((string) ($data['note'] ?? ''));
        $line = 'Transferred from '.($from?->name ?: 'previous shakha')
            .' ('.($from?->code ?: '—').') on '.now()->toDateString()
            .' → '.$target->name.' ('.($target->code ?: '—').')';
        if ($note !== '') {
            $line .= ' — '.$note;
        }

        $notes = trim((string) ($shakhaEmployee->notes ?? ''));
        $notes = $notes !== '' ? $notes."\n".$line : $line;

        DB::transaction(function () use ($shakhaEmployee, $from, $target, $joined, $notes, $note, $fixedCode, $request) {
            ShakhaEmployeeTransfer::query()->create([
                'shakha_employee_id' => $shakhaEmployee->id,
                'from_shakha_id' => $from->id,
                'to_shakha_id' => $target->id,
                'transferred_at' => now(),
                'note' => $note !== '' ? $note : null,
                'transferred_by' => $request->user()?->id,
            ]);

            // Fixed identity: never change id or employee_code on transfer.
            $shakhaEmployee->update([
                'shakha_id' => $target->id,
                'employee_code' => $fixedCode,
                'joined_shakha_at' => $joined,
                'status' => ShakhaEmployee::STATUS_ACTIVE,
                'notes' => $notes,
            ]);
        });

        return $this->redirectAfterStaffAction(
            $request,
            route('shakha-employees.manage', $target),
            $shakhaEmployee->name.' transferred to '.$target->name.' (ID '.$fixedCode.' unchanged).'
        );
    }

    public function fire(Request $request, ShakhaEmployee $shakhaEmployee): RedirectResponse
    {
        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
            'return_to' => ['nullable', 'string', 'max:2000'],
        ]);

        $line = 'Fired (চাকরিচ্যুত) on '.now()->toDateString();
        if (trim((string) ($data['note'] ?? '')) !== '') {
            $line .= ' — '.trim((string) $data['note']);
        }

        $notes = trim((string) ($shakhaEmployee->notes ?? ''));
        $notes = $notes !== '' ? $notes."\n".$line : $line;

        $shakha = $shakhaEmployee->shakha;
        $shakhaEmployee->update([
            'status' => ShakhaEmployee::STATUS_FIRED,
            'notes' => $notes,
        ]);

        return $this->redirectAfterStaffAction(
            $request,
            route('shakha-employees.manage', $shakha),
            $shakhaEmployee->name.' marked as fired (চাকরিচ্যুত).'
        );
    }

    public function destroy(ShakhaEmployee $shakhaEmployee): RedirectResponse
    {
        $shakha = $shakhaEmployee->shakha;
        $shakhaEmployee->delete();

        return redirect()
            ->route('shakha-employees.manage', $shakha)
            ->with('status', 'Employee removed.');
    }

    private function redirectAfterStaffAction(Request $request, string $fallback, string $status): RedirectResponse
    {
        $returnTo = trim((string) $request->input('return_to', ''));
        $appUrl = rtrim((string) config('app.url'), '/');

        if ($returnTo !== '' && (str_starts_with($returnTo, $appUrl.'/') || str_starts_with($returnTo, '/'))) {
            return redirect()->to($returnTo)->with('status', $status);
        }

        return redirect()->to($fallback)->with('status', $status);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, Shakha $shakha, ?ShakhaEmployee $employee = null): array
    {
        $data = $request->validate([
            'employee_code' => [
                'required',
                'string',
                'max:80',
                Rule::unique('shakha_employees', 'employee_code')->ignore($employee?->id),
            ],
            'name' => ['required', 'string', 'max:160'],
            'designation' => ['required', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:160'],
            'joined_organization_at' => ['nullable', 'date'],
            'joined_shakha_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in(ShakhaEmployee::STATUSES)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ]);

        unset($data['photo'], $data['remove_photo']);

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Shakha>
     */
    private function transferTargets(Shakha $shakha)
    {
        return Shakha::query()
            ->with('area')
            ->where('id', '!=', $shakha->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'area_id']);
    }
}
