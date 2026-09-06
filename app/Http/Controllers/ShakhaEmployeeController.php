<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Shakha;
use App\Models\ShakhaEmployee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShakhaEmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $areaId = $request->integer('area_id') ?: null;
        $shakhaId = $request->integer('shakha_id') ?: null;
        $status = (string) $request->input('status', 'all');
        $q = trim((string) $request->input('q', ''));

        $areas = Area::query()
            ->with(['shakhas' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get();

        $shakhas = Shakha::query()
            ->with('area')
            ->when($areaId, fn ($query) => $query->where('area_id', $areaId))
            ->orderBy('name')
            ->get();

        $employees = ShakhaEmployee::query()
            ->with(['shakha.area'])
            ->when($areaId, fn ($query) => $query->whereHas('shakha', fn ($q) => $q->where('area_id', $areaId)))
            ->when($shakhaId, fn ($query) => $query->where('shakha_id', $shakhaId))
            ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('status', $status))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('employee_code', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%")
                        ->orWhere('designation', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->get();

        $byShakha = Shakha::query()
            ->with('area')
            ->withCount('employees')
            ->when($areaId, fn ($query) => $query->where('area_id', $areaId))
            ->orderBy('name')
            ->get();

        return view('shakha-employees.index', [
            'areas' => $areas,
            'shakhas' => $shakhas,
            'employees' => $employees,
            'byShakha' => $byShakha,
            'filters' => [
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

        return view('shakha-employees.manage', [
            'shakha' => $shakha,
            'employees' => $shakha->employees,
            'employee' => null,
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

        return view('shakha-employees.manage', [
            'shakha' => $shakhaEmployee->shakha,
            'employees' => $shakhaEmployee->shakha->employees()->orderBy('sort_order')->orderBy('name')->get(),
            'employee' => $shakhaEmployee,
            'canManage' => auth()->user()?->can('shakhas.manage') ?? false,
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

    public function destroy(ShakhaEmployee $shakhaEmployee): RedirectResponse
    {
        $shakha = $shakhaEmployee->shakha;
        $shakhaEmployee->delete();

        return redirect()
            ->route('shakha-employees.manage', $shakha)
            ->with('status', 'Employee removed.');
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
                Rule::unique('shakha_employees', 'employee_code')
                    ->where(fn ($query) => $query->where('shakha_id', $shakha->id))
                    ->ignore($employee?->id),
            ],
            'name' => ['required', 'string', 'max:160'],
            'designation' => ['required', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:160'],
            'joined_organization_at' => ['nullable', 'date'],
            'joined_shakha_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ]);

        unset($data['photo'], $data['remove_photo']);

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
