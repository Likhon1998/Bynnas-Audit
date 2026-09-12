<?php

namespace App\Services;

use App\Models\MonthlyAssignment;
use App\Models\MonthlyWorkItem;
use App\Models\ProjectLocation;
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
        $query = Shakha::query()->with(['area', 'latestRiskAssessment'])->orderBy('name');

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
        $query = Shakha::query()->with(['area', 'latestRiskAssessment'])->orderBy('name');

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

    /**
     * @return list<int>|null  null = all project locations (superadmin)
     */
    public function reportableProjectLocationIds(?User $user, ?int $month = null, ?int $year = null): ?array
    {
        if (! $user) {
            return [];
        }

        if ($this->canBrowseAllShakhasForReports($user)) {
            return null;
        }

        if (! $user->employee_id || ! Schema::hasTable('monthly_assignments')) {
            return [];
        }

        return $this->visitAssignedProjectLocationIds(
            (int) $user->employee_id,
            $month,
            $year,
        );
    }

    public function canStartReportForProjectLocation(
        ?User $user,
        int $locationId,
        ?int $month = null,
        ?int $year = null,
    ): bool {
        $ids = $this->reportableProjectLocationIds($user, $month, $year);
        if ($ids === null) {
            return true;
        }

        return in_array($locationId, $ids, true);
    }

    /**
     * Project locations available in the Audit Reports entity picker.
     *
     * @return Collection<int, ProjectLocation>
     */
    public function reportableProjectLocations(?User $user, ?int $month = null, ?int $year = null): Collection
    {
        $query = ProjectLocation::query()->with('project')->orderBy('name');

        $ids = $this->reportableProjectLocationIds($user, $month, $year);
        if ($ids === null) {
            // Superadmin: only locations that appear on the month's visit plan keep the picker usable.
            if ($month !== null && $year !== null && Schema::hasTable('monthly_assignments')) {
                $plannedIds = MonthlyAssignment::query()
                    ->whereNotNull('start_date')
                    ->whereNotNull('end_date')
                    ->whereDate('start_date', '<=', date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $year, $month))))
                    ->whereDate('end_date', '>=', sprintf('%04d-%02d-01', $year, $month))
                    ->whereHas('workItem', fn ($q) => $q->where('schedulable_type', ProjectLocation::class))
                    ->with('workItem:id,schedulable_id,schedulable_type')
                    ->get()
                    ->map(fn (MonthlyAssignment $a) => (int) ($a->workItem?->schedulable_id ?? 0))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                if ($plannedIds === []) {
                    return collect();
                }

                return $query->whereIn('id', $plannedIds)->get();
            }

            return $query->limit(200)->get();
        }

        if ($ids === []) {
            return collect();
        }

        return $query->whereIn('id', $ids)->get();
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
     * Labels for visit types that still cannot open an audit report (not shakha / project location).
     *
     * @return list<string>
     */
    public function visitAssignedUnsupportedLabels(?User $user, ?int $month = null, ?int $year = null): array
    {
        if (! $user?->employee_id || ! Schema::hasTable('monthly_assignments')) {
            return [];
        }

        $query = $this->visitAssignmentsQuery((int) $user->employee_id, $month, $year)
            ->whereHas('workItem', function ($q) {
                $q->whereNotIn('schedulable_type', [Shakha::class, ProjectLocation::class]);
            });

        return $query
            ->with(['workItem'])
            ->get()
            ->map(function (MonthlyAssignment $a) {
                $item = $a->workItem;
                if (! $item) {
                    return '';
                }

                return trim((string) ($item->entity_label ?: 'Other visit'));
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @deprecated Use visitAssignedUnsupportedLabels — project locations are reportable.
     *
     * @return list<string>
     */
    public function visitAssignedNonShakhaLabels(?User $user, ?int $month = null, ?int $year = null): array
    {
        return $this->visitAssignedUnsupportedLabels($user, $month, $year);
    }

    /**
     * Shakha IDs from monthly visits where this employee is allocated.
     * When month/year are set, only visits overlapping that calendar month count.
     *
     * @return list<int>
     */
    protected function visitAssignedShakhaIds(int $employeeId, ?int $month = null, ?int $year = null): array
    {
        return $this->visitAssignedSchedulableIds($employeeId, Shakha::class, $month, $year);
    }

    /**
     * @return list<int>
     */
    protected function visitAssignedProjectLocationIds(int $employeeId, ?int $month = null, ?int $year = null): array
    {
        return $this->visitAssignedSchedulableIds($employeeId, ProjectLocation::class, $month, $year);
    }

    /**
     * @return list<int>
     */
    protected function visitAssignedSchedulableIds(
        int $employeeId,
        string $schedulableType,
        ?int $month = null,
        ?int $year = null,
    ): array {
        $assignments = $this->visitAssignmentsQuery($employeeId, $month, $year)
            ->whereHas('workItem', function ($q) use ($schedulableType) {
                $q->where('schedulable_type', $schedulableType);
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

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\MonthlyAssignment>
     */
    protected function visitAssignmentsQuery(int $employeeId, ?int $month = null, ?int $year = null)
    {
        $query = MonthlyAssignment::query()
            ->where(function ($q) use ($employeeId) {
                $q->where('employee_id', $employeeId)
                    ->orWhereHas('visitors', fn ($v) => $v->where('employees.id', $employeeId));
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

        return $query;
    }
}
