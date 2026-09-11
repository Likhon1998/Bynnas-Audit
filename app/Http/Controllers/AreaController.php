<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAreaRequest;
use App\Models\Area;
use App\Services\AnnualPlanGenerator;
use App\Support\Divisions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AreaController extends Controller
{
    public function __construct(
        private AnnualPlanGenerator $planGenerator,
    ) {}

    public function index(Request $request): View
    {
        $division = trim((string) $request->input('division', ''));
        if ($division !== '' && ! in_array($division, Divisions::OPTIONS, true)) {
            $division = '';
        }

        $areas = Area::query()
            ->withCount('shakhas')
            ->when($division !== '', fn ($query) => $query->where('division', $division))
            ->orderBy('division')
            ->orderBy('name')
            ->get();

        return view('areas.index', [
            'areas' => $areas,
            'groupedAreas' => $areas->groupBy('division'),
            'divisions' => Divisions::OPTIONS,
            'selectedDivision' => $division,
        ]);
    }

    public function create(): View
    {
        return view('areas.create', [
            'divisions' => Divisions::OPTIONS,
        ]);
    }

    public function store(StoreAreaRequest $request): RedirectResponse
    {
        Area::query()->create($request->validated());

        $syncNote = $this->planGenerator->includeInCurrentPlan();

        return redirect()
            ->route('areas.index')
            ->with('status', 'Area added successfully.'.($syncNote ? ' '.$syncNote : ' Generate or Sync new items on Annual Audit if this FY plan already exists.'));
    }
}
