<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShakhaRequest;
use App\Models\Area;
use App\Models\Shakha;
use App\Services\AnnualPlanGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShakhaController extends Controller
{
    public function __construct(
        private AnnualPlanGenerator $planGenerator,
    ) {}

    public function index(): View
    {
        $shakhas = Shakha::query()
            ->with('area')
            ->latest()
            ->get();

        return view('shakhas.index', compact('shakhas'));
    }

    public function create(): View
    {
        $areas = Area::query()
            ->withCount('shakhas')
            ->orderBy('division')
            ->orderBy('name')
            ->get();

        $areasByDivision = $areas->groupBy('division');

        return view('shakhas.create', [
            'areas' => $areas,
            'areasByDivision' => $areasByDivision,
            'areaCount' => $areas->count(),
            'shakhaCount' => Shakha::query()->count(),
        ]);
    }

    public function store(StoreShakhaRequest $request): RedirectResponse
    {
        Shakha::query()->create($request->validated());

        $syncNote = $this->planGenerator->includeInCurrentPlan();

        return redirect()
            ->route('shakhas.index')
            ->with('status', 'Shakha added successfully.'.($syncNote ? ' '.$syncNote : ' Generate or Sync new items on Annual Audit if this FY plan already exists.'));
    }
}
