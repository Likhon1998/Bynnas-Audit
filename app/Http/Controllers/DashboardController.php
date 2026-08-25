<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Shakha;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $officerCount = Employee::query()->count();
        $rankCount = Position::query()->count();
        $shakhaCount = Schema::hasTable('shakhas') ? Shakha::query()->count() : 0;
        $areaCount = Schema::hasTable('areas') ? Area::query()->count() : 0;
        $activeShakhaCount = Schema::hasTable('shakhas')
            ? Shakha::query()->where('status', 'active')->count()
            : 0;
        $inactiveShakhaCount = max($shakhaCount - $activeShakhaCount, 0);
        $activeAreaCount = Schema::hasTable('areas')
            ? Area::query()->where('status', 'active')->count()
            : 0;
        $divisionCount = Schema::hasTable('areas')
            ? (int) Area::query()->distinct('division')->count('division')
            : 0;

        $stats = [
            [
                'label' => 'Total Shakhas',
                'value' => (string) $shakhaCount,
                'meta' => $activeShakhaCount.' active · '.$inactiveShakhaCount.' inactive',
                'href' => route('shakhas.index'),
                'tone' => 'violet',
            ],
            [
                'label' => 'Areas',
                'value' => (string) $areaCount,
                'meta' => $divisionCount.' divisions · '.$activeAreaCount.' active',
                'href' => route('areas.index'),
                'tone' => 'emerald',
            ],
            [
                'label' => 'Audit Officers',
                'value' => (string) $officerCount,
                'meta' => $rankCount.' ranks in organogram',
                'href' => route('organogram'),
                'tone' => 'sky',
            ],
            [
                'label' => 'Active Shakhas',
                'value' => (string) $activeShakhaCount,
                'meta' => $shakhaCount > 0
                    ? number_format(($activeShakhaCount / $shakhaCount) * 100, 0).'% of total branches'
                    : 'No branches yet',
                'href' => route('shakhas.index'),
                'tone' => 'orange',
            ],
        ];

        return view('dashboard', [
            'stats' => $stats,
        ]);
    }
}
