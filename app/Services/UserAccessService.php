<?php

namespace App\Services;

use App\Models\MonthlyAssignment;
use App\Models\Shakha;
use App\Models\User;
use App\Support\FinancialYear;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Resolves which shakhas a user may access for reports / findings / officer dashboard.
 *
 * Sources (union):
 * 1. Explicit user_shakha assignments (superadmin-managed)
 * 2. Monthly visit assignments where the linked employee is lead or visitor
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

        if (Schema::hasTable('user_shakha')) {
            $ids = $ids->merge($user->assignedShakhas()->pluck('shakhas.id'));
        }

        if ($user->employee_id && Schema::hasTable('monthly_assignments')) {
            $ids = $ids->merge($this->visitAssignedShakhaIds((int) $user->employee_id));
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
     * @return list<int>
     */
    protected function visitAssignedShakhaIds(int $employeeId): array
    {
        $fy = FinancialYear::current(now('Asia/Dhaka'));

        $assignments = MonthlyAssignment::query()
            ->where(function ($q) use ($employeeId) {
                $q->where('employee_id', $employeeId)
                    ->orWhereHas('visitors', fn ($v) => $v->where('employees.id', $employeeId));
            })
            ->whereHas('workItem', function ($q) use ($fy) {
                $q->where('fy_label', $fy->label)
                    ->where('schedulable_type', Shakha::class);
            })
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
