<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShakhaRequest;
use App\Models\Area;
use App\Models\Shakha;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShakhaController extends Controller
{
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

        return redirect()
            ->route('shakhas.index')
            ->with('status', 'Shakha added successfully.');
    }
}
