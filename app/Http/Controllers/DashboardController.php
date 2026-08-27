<?php

namespace App\Http\Controllers;

use App\Models\AuditReport;
use App\Services\DashboardOpsService;
use App\Services\UserAccessService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardOpsService $ops, UserAccessService $access): View
    {
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
            ]);
        }

        $shakhas = $access->accessibleShakhas($user);
        $myDrafts = AuditReport::query()
            ->ownedBy((int) $user->id)
            ->drafts()
            ->with('shakha:id,name')
            ->latest('last_saved_at')
            ->limit(8)
            ->get();

        return view('dashboard', [
            'mode' => 'officer',
            'assignedShakhas' => $shakhas,
            'myDrafts' => $myDrafts,
            'slotsLeft' => max(0, AuditReport::MAX_CONCURRENT_DRAFTS - $myDrafts->count()),
        ]);
    }
}
