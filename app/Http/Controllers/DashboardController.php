<?php

namespace App\Http\Controllers;

use App\Services\DashboardOpsService;
use App\Services\OfficerDashboardService;
use App\Support\RoleAccess;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(
        Request $request,
        DashboardOpsService $ops,
        OfficerDashboardService $officerDashboard,
    ): View {
        $user = $request->user();

        if ($user && $user->can('dashboard.ops')) {
            $fyLabel = $request->string('fy')->toString() ?: null;
            if ($fyLabel && ! preg_match('/^\d{4}-\d{4}$/', $fyLabel)) {
                $fyLabel = null;
            }

            $monthIndex = $request->has('month')
                ? (int) $request->integer('month')
                : null;

            $pulse = $ops->build(
                fyLabel: $fyLabel,
                monthIndex: $monthIndex,
                userId: $user->id,
            );

            return view('dashboard', [
                'mode' => 'ops',
                'pulse' => $pulse,
                'roleCatalog' => RoleAccess::catalog(),
            ]);
        }

        $fyLabel = $request->string('fy')->toString() ?: null;
        if ($fyLabel && ! preg_match('/^\d{4}-\d{4}$/', $fyLabel)) {
            $fyLabel = null;
        }

        $monthIndex = $request->has('month')
            ? (int) $request->integer('month')
            : null;

        $board = $officerDashboard->build($user, $fyLabel, $monthIndex);

        return view('dashboard', array_merge($board, [
            'mode' => 'officer',
            'roleInfo' => RoleAccess::catalog()[$user->roleKey()] ?? null,
            'slotsLeft' => $board['stats']['slots_left'],
        ]));
    }
}
