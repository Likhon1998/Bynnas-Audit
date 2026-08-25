<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAreaRequest;
use App\Models\Area;
use App\Support\Divisions;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AreaController extends Controller
{
    public function index(): View
    {
        $areas = Area::query()
            ->withCount('shakhas')
            ->orderBy('division')
            ->orderBy('name')
            ->get();

        return view('areas.index', compact('areas'));
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

        return redirect()
            ->route('areas.index')
            ->with('status', 'Area added successfully.');
    }
}
