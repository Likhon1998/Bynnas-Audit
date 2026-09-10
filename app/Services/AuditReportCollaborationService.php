<?php

namespace App\Services;

use App\Models\AuditReport;
use App\Models\MonthlyWorkItem;
use App\Models\Shakha;
use App\Models\User;
use App\Support\FinancialYear;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AuditReportCollaborationService
{
    /**
     * User IDs linked to employees assigned as visitors on this shakha for the report month.
     *
     * @return list<int>
     */
    public function visitorUserIdsForShakhaPeriod(int $shakhaId, int $month, int $year): array
    {
        $asOf = Carbon::create($year, $month, 15)->startOfDay();
        $fy = FinancialYear::current($asOf);
        $monthMeta = collect($fy->months())->first(
            fn (array $m) => (int) $m['month'] === $month && (int) $m['year'] === $year
        );

        if (! $monthMeta) {
            return [];
        }

        $employeeIds = MonthlyWorkItem::query()
            ->where('fy_label', $fy->label)
            ->where('month_index', (int) $monthMeta['index'])
            ->where('schedulable_type', Shakha::class)
            ->where('schedulable_id', $shakhaId)
            ->whereHas('assignment')
            ->with(['assignment.visitors:id'])
            ->get()
            ->flatMap(function (MonthlyWorkItem $item) {
                $assignment = $item->assignment;
                if (! $assignment) {
                    return [];
                }

                $visitorIds = $assignment->relationLoaded('visitors')
                    ? $assignment->visitors->pluck('id')
                    : $assignment->visitors()->pluck('employees.id');

                if ($visitorIds->isEmpty() && $assignment->employee_id) {
                    return [(int) $assignment->employee_id];
                }

                return $visitorIds->map(fn ($id) => (int) $id)->all();
            })
            ->unique()
            ->values()
            ->all();

        if ($employeeIds === []) {
            return [];
        }

        return User::query()
            ->whereIn('employee_id', $employeeIds)
            ->where('is_active', true)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Existing draft for this shakha/period that the current user should join
     * (already a participant, or on the same monthly-visit team).
     */
    public function findJoinableDraft(int $shakhaId, int $month, int $year, User $user): ?AuditReport
    {
        $draft = AuditReport::query()
            ->where('shakha_id', $shakhaId)
            ->where('report_month', $month)
            ->where('report_year', $year)
            ->drafts()
            ->with(['collaborators:id,name', 'user:id,name'])
            ->latest('id')
            ->first();

        if (! $draft) {
            return null;
        }

        if ($draft->isAccessibleBy($user)) {
            return $draft;
        }

        $teamIds = $this->visitorUserIdsForShakhaPeriod($shakhaId, $month, $year);
        if ($teamIds === []) {
            return null;
        }

        $userId = (int) $user->id;
        if (! in_array($userId, $teamIds, true)) {
            return null;
        }

        $ownerId = (int) $draft->user_id;
        $participantIds = $draft->collaborators->pluck('id')->map(fn ($id) => (int) $id)->all();
        $participantIds[] = $ownerId;

        // Join only when the existing draft belongs to the same visit team.
        if (count(array_intersect($participantIds, $teamIds)) === 0) {
            return null;
        }

        return $draft;
    }

    /**
     * @param  list<int>  $userIds
     */
    public function syncCollaborators(AuditReport $report, array $userIds, ?User $actingUser = null): void
    {
        $ownerId = (int) $report->user_id;
        $ids = collect($userIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0 && $id !== $ownerId)
            ->unique()
            ->values()
            ->all();

        if ($actingUser && (int) $actingUser->id !== $ownerId) {
            $ids[] = (int) $actingUser->id;
            $ids = array_values(array_unique(array_filter($ids, fn (int $id) => $id !== $ownerId)));
        }

        $report->collaborators()->sync($ids);
        $this->refreshCoverAuditorNames($report);
    }

    public function refreshCoverAuditorNames(AuditReport $report): void
    {
        $report->loadMissing(['user:id,name', 'collaborators:id,name']);

        /** @var Collection<int, User> $people */
        $people = collect([$report->user])
            ->merge($report->collaborators)
            ->filter()
            ->unique('id')
            ->values();

        if ($people->isEmpty()) {
            return;
        }

        $names = $people->pluck('name')->map(fn ($n) => trim((string) $n))->filter()->values();
        if ($names->isEmpty()) {
            return;
        }

        $joined = $names->count() === 1
            ? $names->first()
            : $names->slice(0, -1)->implode(', ').' ও '.$names->last();

        $pages = (array) $report->pages_data;
        $cover = (array) ($pages['cover'] ?? []);
        $cover['auditor_name'] = $joined;
        $pages['cover'] = $cover;

        $report->forceFill([
            'auditor_name' => $joined,
            'pages_data' => $pages,
        ])->save();
    }
}
