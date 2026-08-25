<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\StorePositionRequest;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrganogramController extends Controller
{
    public function index(): View
    {
        $positions = Position::query()
            ->with('employees')
            ->orderBy('serial')
            ->get();

        $nextSerial = ((int) Position::query()->max('serial')) + 1;

        return view('organogram', compact('positions', 'nextSerial'));
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $nextOrder = (int) Employee::query()
            ->where('position_id', $data['position_id'])
            ->max('sort_order');

        Employee::query()->create([
            'position_id' => $data['position_id'],
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'sort_order' => $nextOrder + 1,
        ]);

        return back()->with('status', 'Officer added to the audit organogram.');
    }

    public function storePosition(StorePositionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $title = trim($data['title']);
        $baseSlug = Str::slug($title) ?: 'position';
        $slug = $baseSlug;
        $suffix = 1;

        while (Position::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        Position::query()->create([
            'title' => $title,
            'slug' => $slug,
            'serial' => $data['serial'] ?? (((int) Position::query()->max('serial')) + 1),
            'color' => '#4C6FFF',
        ]);

        return back()->with('status', 'Position added to the audit organogram.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return back()->with('status', 'Officer removed from the audit organogram.');
    }
}
