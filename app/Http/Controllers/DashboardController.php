<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Position;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $positions = Position::query()
            ->withCount('employees')
            ->orderBy('serial')
            ->get();

        $officerCount = Employee::query()->count();
        $rankCount = $positions->count();

        $stats = [
            ['label' => 'Audit Officers', 'value' => (string) $officerCount, 'change' => 'Across the organogram'],
            ['label' => 'Audit Ranks', 'value' => (string) $rankCount, 'change' => 'Director to Audit Officer'],
            ['label' => 'Director Audit', 'value' => (string) ($positions->firstWhere('serial', 1)?->employees_count ?? 0), 'change' => 'Serial 1'],
            ['label' => 'Audit Officer', 'value' => (string) ($positions->firstWhere('serial', 7)?->employees_count ?? 0), 'change' => 'Serial 7'],
        ];

        return view('dashboard', compact('stats'));
    }
}
