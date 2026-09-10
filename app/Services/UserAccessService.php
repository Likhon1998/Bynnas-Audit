<?php

namespace App\Services;

use App\Models\MonthlyAssignment;
use App\Models\MonthlyWorkItem;
use App\Models\Shakha;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Resolves which shakhas a user may access for reports / findings / officer dashboard.
 *
 * Officers (no view-all):
 *  1. Automatic — shakhas from Monthly Visits where linked employee is allocated
 *  2. Extra — optional explicit user_shakha grants from Users & Access
 *
 * Managers / superadmin see all.
 */
class UserAccessService
{
    public function canAccessAllShakhas(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['superadmin', 'audit_manager'])
            || $user->can('shakhas.view_all');
    }

    /**
     * Field staff scoped to visit allocations (+ optional extras).
     */
    public function isAllocationScoped(?User $user): bool
    {
        return $user !== null && ! $this->canAccessAllShakhas($user);
    }

    /**
     * @return list<int>|null  null = all shakhas
     */
    public function accessibleShakhaIds(?User $user): ?array
    {
        if (! $user) {
            return [];
        }

        if ($this->canAccessAllShakhas($user)) {
            return null;
        }

        $ids = collect();

        // 1) Automatic: monthly visit allocations for the linked employee
        if ($user->employee_id && Schema::hasTable('monthly_assignments')) {
            $ids = $ids->merge($this->visitAssignedShakhaIds((int) $user->employee_id));
        }

        // 2) Extra: admin-granted shakhas on Users & Access (optional)
        if (Schema::hasTable('user_shakha')) {
            $ids = $ids->merge($user->assignedShakhas()->pluck('shakhas.id'));
        }

        return $ids->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    /**
     * @return Collection<int, Shakha>
     */
    public function accessibleShakhas(?User $user): Collection
    {
        $query = Shakha::query()->with('area')->orderBy('name');

        $ids = $this->accessibleShakhaIds($user);
        if ($ids === null) {
            return $query->get();
        }

        if ($ids === []) {
            return collect();
        }

        return $query->whereIn('id', $ids)->get();
    }

    public function canAccessShakha(?User $user, int $shakhaId): bool
    {
        $ids = $this->accessibleShakhaIds($user);
        if ($ids === null) {
            return true;
        }

        return in_array($shakhaId, $ids, true);
    }

    /**
     * Who may browse every branch when starting an audit report.
     * Only Super Admin — field managers/officers use their monthly allocations.
     */
    public function canBrowseAllShakhasForReports(?User $user): bool
    {
        return $user !== null && $user->isSuperAdmin();
    }

    /**
     * Shakhas available in the Audit Reports branch picker.
     * Super Admin: all. Everyone else: monthly-visit allocations for the report month (+ optional user_shakha).
     *
     * @return Collection<int, Shakha>
     */
    public function reportableShakhas(?User $user, ?int $month = null, ?int $year = null): Collection
    {
        $query = Shakha::query()->with('area')->orderBy('name');

        $ids = $this->reportableShakhaIds($user, $month, $year);
        if ($ids === null) {
            return $query->get();
        }

        if ($ids === []) {
            return collect();
        }

        return $query->whereIn('id', $ids)->get();
    }

    /**
     * @return list<int>|null  null = all shakhas
     */
    public function reportableShakhaIds(?User $user, ?int $month = null, ?int $year = null): ?array
    {
        if (! $user) {
            return [];
        }

        if ($this->canBrowseAllShakhasForReports($user)) {
            return null;
        }

        $ids = collect();

        if ($user->employee_id && Schema::hasTable('monthly_assignments')) {
            $ids = $ids->merge($this->visitAssignedShakhaIds(
                (int) $user->employee_id,
                $month,
                $year,
            ));
        }

        // Admin extras still allow an exception grant outside the visit plan.
        if (Schema::hasTable('user_shakha')) {
            $ids = $ids->merge($user->assignedShakhas()->pluck('shakhas.id'));
        }

        return $ids->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    public function canStartReportForShakha(
        ?User $user,
        int $shakhaId,
        ?int $month = null,
        ?int $year = null,
    ): bool {
        $ids = $this->reportableShakhaIds($user, $month, $year);
        if ($ids === null) {
            return true;
        }

        return in_array($shakhaId, $ids, true);
    }

    public function employeeIsOnAssignment(int $employeeId, MonthlyAssignment $assignment): bool
    {
        if ((int) $assignment->employee_id === $employeeId) {
            return true;
        }

        $visitors = $assignment->relationLoaded('visitors')
            ? $assignment->visitors
            : $assignment->visitors()->get();

        return $visitors->contains(fn ($e) => (int) $e->id === $employeeId);
    }

    public function userCanAccessAssignment(?User $user, MonthlyAssignment $assignment): bool
    {
        if (! $user) {
            return false;
        }

        if (! $this->isAllocationScoped($user)) {
            return true;
        }

        if (! $user->employee_id) {
            return false;
        }

        return $this->employeeIsOnAssignment((int) $user->employee_id, $assignment);
    }

    /**
     * Keep only work items where the user's linked employee is allocated.
     *
     * @param  Collection<int, MonthlyWorkItem>  $items
     * @return Collection<int, MonthlyWorkItem>
     */
    public function filterWorkItemsForUser(Collection $items, ?User $user): Collection
    {
        if (! $user || ! $this->isAllocationScoped($user)) {
            return $items;
        }

        if (! $user->employee_id) {
            return collect();
        }

        $employeeId = (int) $user->employee_id;

        return $items
            ->filter(function (MonthlyWorkItem $item) use ($employeeId) {
                $assignment = $item->assignment;
                if (! $assignment) {
                    return false;
                }

                return $this->employeeIsOnAssignment($employeeId, $assignment);
            })
            ->values();
    }

    /**
     * Shakha IDs from monthly visits where this employee is allocated.
     * When month/year are set, only visits overlapping that calendar month count.
     *
     * @return list<int>
     */
    protected function visitAssignedShakhaIds(int $employeeId, ?int $month = null, ?int $year = null): array
    {
        $query = MonthlyAssignment::query()
            ->where(function ($q) use ($employeeId) {
                $q->where('employee_id', $employeeId)
                    ->orWhereHas('visitors', fn ($v) => $v->where('employees.id', $employeeId));
            })
            ->whereHas('workItem', function ($q) {
                $q->where('schedulable_type', Shakha::class);
            })
            ->whereNotNull('start_date')
            ->whereNotNull('end_date');

        if ($month !== null && $year !== null && $month >= 1 && $month <= 12 && $year >= 2000) {
            $monthStart = sprintf('%04d-%02d-01', $year, $month);
            $monthEnd = date('Y-m-t', strtotime($monthStart));
            $query
                ->whereDate('start_date', '<=', $monthEnd)
                ->whereDate('end_date', '>=', $monthStart);
        }

        $assignments = $query
            ->with('workItem:id,schedulable_id,schedulable_type')
            ->get();

        return $assignments
            ->map(fn (MonthlyAssignment $a) => (int) ($a->workItem?->schedulable_id ?? 0))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
