<?php

namespace App\Services;

use App\Models\Area;
use App\Models\AuditPlan;
use App\Models\AuditPolicy;
use App\Models\HqDepartment;
use App\Models\PlanSchedule;
use App\Models\Project;
use App\Models\ProjectLocation;
use App\Models\Shakha;
use App\Support\FinancialYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AnnualPlanGenerator
{
    /** @var list<class-string<Model>> */
    public const SCHEDULABLE_TYPES = [
        Shakha::class,
        Area::class,
        ProjectLocation::class,
        HqDepartment::class,
    ];

    public function ensurePlan(string $fyLabel, ?int $userId = null, ?AuditPlan $copyPoliciesFrom = null): AuditPlan
    {
        $fy = FinancialYear::fromLabel($fyLabel);

        $plan = AuditPlan::firstOrCreate(
            ['fy_label' => $fy->label],
            [
                'name' => 'Annual Audit & Monitoring '.$fy->label,
                'start_date' => $fy->startDate->toDateString(),
                'end_date' => $fy->endDate->toDateString(),
                'status' => 'draft',
                'created_by' => $userId,
            ]
        );

        if ($plan->wasRecentlyCreated && $copyPoliciesFrom) {
            $this->copyPoliciesFrom($copyPoliciesFrom, $plan);
        } else {
            $this->ensureDefaultPolicies($plan);
        }

        return $plan->fresh('policies');
    }

    /** @deprecated Use ensurePlan() — kept for seeders/tests */
    public function ensureFy2026Plan(?int $userId = null): AuditPlan
    {
        return $this->ensurePlan(FinancialYear::for2026_2027()->label, $userId);
    }

    public function createNextPlan(AuditPlan $from, ?int $userId = null): AuditPlan
    {
        $next = FinancialYear::fromLabel($from->fy_label)->next();

        return $this->ensurePlan($next->label, $userId, $from);
    }

    public function copyPoliciesFrom(AuditPlan $source, AuditPlan $target): void
    {
        foreach ($source->policies as $policy) {
            AuditPolicy::query()->updateOrCreate(
                [
                    'audit_plan_id' => $target->id,
                    'category' => $policy->category,
                ],
                [
                    'frequency_per_year' => $policy->frequency_per_year,
                    'interval_months' => max(1, (int) floor(12 / max(1, (int) $policy->frequency_per_year))),
                    'pattern' => $policy->pattern ?: 'interval',
                    'custom_month_indexes' => $policy->custom_month_indexes,
                    'notes' => $policy->notes,
                ]
            );
        }

        $this->ensureDefaultPolicies($target);
    }

    public function ensureDefaultPolicies(AuditPlan $plan): void
    {
        $defaults = [
            AuditPolicy::CATEGORY_SHAKHA => ['frequency_per_year' => 3],
            AuditPolicy::CATEGORY_AREA => ['frequency_per_year' => 4],
            AuditPolicy::CATEGORY_PKSF => ['frequency_per_year' => 2],
            AuditPolicy::CATEGORY_HQ => ['frequency_per_year' => 2],
            AuditPolicy::CATEGORY_PROJECT_AUDIT => ['frequency_per_year' => 4],
            AuditPolicy::CATEGORY_PROJECT_MONITORING => ['frequency_per_year' => 4],
        ];

        foreach ($defaults as $category => $data) {
            $frequency = max(1, min(12, (int) $data['frequency_per_year']));
            AuditPolicy::firstOrCreate(
                ['audit_plan_id' => $plan->id, 'category' => $category],
                [
                    'frequency_per_year' => $frequency,
                    'interval_months' => max(1, (int) floor(12 / $frequency)),
                    'pattern' => 'interval',
                    'custom_month_indexes' => null,
                    'notes' => null,
                ]
            );
        }
    }

    public function generate(AuditPlan $plan, bool $preserveManual = true): AuditPlan
    {
        $fy = FinancialYear::fromLabel($plan->fy_label);
        $policies = $plan->policies()->get()->keyBy('category');

        DB::transaction(function () use ($plan, $fy, $policies, $preserveManual) {
            $query = PlanSchedule::query()
                ->where('audit_plan_id', $plan->id);

            if ($preserveManual) {
                $query->where('is_manual', false);
            }

            $query->delete();

            $this->generateShakhaSchedules($plan, $fy, $policies->get(AuditPolicy::CATEGORY_SHAKHA), $preserveManual);
            $this->generateAreaSchedules($plan, $fy, $policies->get(AuditPolicy::CATEGORY_AREA), $preserveManual);
            $this->generateProjectSchedules($plan, $fy, $policies, $preserveManual);
            $this->generateHqSchedules($plan, $fy, $policies->get(AuditPolicy::CATEGORY_HQ), $preserveManual);

            $plan->update([
                'generated_at' => now(),
                'status' => $plan->status === 'published' ? 'published' : 'generated',
            ]);
        });

        return $plan->fresh(['policies', 'schedules']);
    }

    public function toggleMonth(
        AuditPlan $plan,
        string $category,
        string $schedulableType,
        int $schedulableId,
        int $monthIndex
    ): string {
        if (! in_array($schedulableType, self::SCHEDULABLE_TYPES, true)) {
            throw new InvalidArgumentException('Invalid schedulable type.');
        }

        if ($monthIndex < 0 || $monthIndex > 11) {
            throw new InvalidArgumentException('Invalid month index.');
        }

        $fy = FinancialYear::fromLabel($plan->fy_label);

        return DB::transaction(function () use ($plan, $category, $schedulableType, $schedulableId, $monthIndex, $fy) {
            // Claim this entity for admin so regenerate will not overwrite it.
            PlanSchedule::query()
                ->where('audit_plan_id', $plan->id)
                ->where('category', $category)
                ->where('schedulable_type', $schedulableType)
                ->where('schedulable_id', $schedulableId)
                ->where('month_index', '<=', 11)
                ->update(['is_manual' => true]);

            PlanSchedule::query()
                ->where('audit_plan_id', $plan->id)
                ->where('category', $category)
                ->where('schedulable_type', $schedulableType)
                ->where('schedulable_id', $schedulableId)
                ->where('month_index', 255)
                ->delete();

            $existing = PlanSchedule::query()
                ->where('audit_plan_id', $plan->id)
                ->where('category', $category)
                ->where('schedulable_type', $schedulableType)
                ->where('schedulable_id', $schedulableId)
                ->where('month_index', $monthIndex)
                ->get();

            if ($existing->isNotEmpty()) {
                PlanSchedule::query()->whereIn('id', $existing->pluck('id'))->delete();

                $remaining = PlanSchedule::query()
                    ->where('audit_plan_id', $plan->id)
                    ->where('category', $category)
                    ->where('schedulable_type', $schedulableType)
                    ->where('schedulable_id', $schedulableId)
                    ->where('month_index', '<=', 11)
                    ->count();

                if ($remaining === 0) {
                    PlanSchedule::query()->create([
                        'audit_plan_id' => $plan->id,
                        'category' => $category,
                        'schedulable_type' => $schedulableType,
                        'schedulable_id' => $schedulableId,
                        'month_index' => 255,
                        'planned_date' => $fy->startDate->toDateString(),
                        'occurrence' => 0,
                        'status' => 'locked',
                        'is_manual' => true,
                        'remarks' => 'Admin cleared all months — do not auto-generate',
                    ]);
                }

                return 'removed';
            }

            $occurrence = PlanSchedule::query()
                ->where('audit_plan_id', $plan->id)
                ->where('category', $category)
                ->where('schedulable_type', $schedulableType)
                ->where('schedulable_id', $schedulableId)
                ->where('month_index', '<=', 11)
                ->count() + 1;

            PlanSchedule::query()->create([
                'audit_plan_id' => $plan->id,
                'category' => $category,
                'schedulable_type' => $schedulableType,
                'schedulable_id' => $schedulableId,
                'month_index' => $monthIndex,
                'planned_date' => $fy->dateForMonthIndex($monthIndex)->toDateString(),
                'occurrence' => $occurrence,
                'status' => 'planned',
                'is_manual' => true,
                'remarks' => 'Set by admin',
            ]);

            return 'added';
        });
    }

    /**
     * @param  array<int, array{frequency_per_year?:int}>  $policies
     */
    public function updatePolicies(AuditPlan $plan, array $policies): void
    {
        foreach ($policies as $policyId => $data) {
            $policy = AuditPolicy::query()
                ->where('audit_plan_id', $plan->id)
                ->where('id', $policyId)
                ->first();

            if (! $policy) {
                continue;
            }

            $frequency = max(1, min(12, (int) ($data['frequency_per_year'] ?? $policy->frequency_per_year)));

            $policy->update([
                'frequency_per_year' => $frequency,
                'interval_months' => max(1, (int) floor(12 / $frequency)),
                'pattern' => $data['pattern'] ?? $policy->pattern ?? 'interval',
                'custom_month_indexes' => array_key_exists('custom_month_indexes', $data)
                    ? ($data['custom_month_indexes'] === '' || $data['custom_month_indexes'] === null
                        ? null
                        : $data['custom_month_indexes'])
                    : $policy->custom_month_indexes,
                'notes' => $data['notes'] ?? $policy->notes,
            ]);
        }
    }

    /**
     * Add schedules for active entities that are missing from an already-generated plan
     * (e.g. a shakha opened mid-year). Does not touch existing rows.
     */
    public function syncMissing(AuditPlan $plan): int
    {
        $this->ensureDefaultPolicies($plan);
        $plan->load('policies');
        $fy = FinancialYear::fromLabel($plan->fy_label);
        $policies = $plan->policies->keyBy('category');
        $added = 0;

        $added += $this->syncMissingShakhas($plan, $fy, $policies->get(AuditPolicy::CATEGORY_SHAKHA));
        $added += $this->syncMissingAreas($plan, $fy, $policies->get(AuditPolicy::CATEGORY_AREA));
        $added += $this->syncMissingHq($plan, $fy, $policies->get(AuditPolicy::CATEGORY_HQ));
        $added += $this->syncMissingProjects($plan, $fy, $policies);

        if ($added > 0 && ! $plan->generated_at) {
            $plan->update([
                'generated_at' => now(),
                'status' => $plan->status === 'published' ? 'published' : 'generated',
            ]);
        }

        return $added;
    }

    /**
     * Schedule missing entities into the active (latest generated) FY plan —
     * same plan Annual Audit opens by default.
     */
    public function includeInCurrentPlan(): ?string
    {
        $plan = AuditPlan::query()
            ->whereNotNull('generated_at')
            ->orderByDesc('start_date')
            ->first();

        if (! $plan) {
            $plan = AuditPlan::query()
                ->where('fy_label', FinancialYear::current()->label)
                ->whereNotNull('generated_at')
                ->first();
        }

        if (! $plan) {
            return null;
        }

        $added = $this->syncMissing($plan);

        if ($added === 0) {
            return null;
        }

        return "Scheduled into FY {$plan->fy_label} ({$added} new row".($added === 1 ? '' : 's').').';
    }

    public function deleteLocationWithSchedules(ProjectLocation $location): void
    {
        DB::transaction(function () use ($location) {
            PlanSchedule::query()
                ->where('schedulable_type', ProjectLocation::class)
                ->where('schedulable_id', $location->id)
                ->delete();

            $location->delete();
        });
    }

    public function deleteProjectWithSchedules(Project $project): void
    {
        DB::transaction(function () use ($project) {
            $locationIds = $project->locations()->pluck('id');

            if ($locationIds->isNotEmpty()) {
                PlanSchedule::query()
                    ->where('schedulable_type', ProjectLocation::class)
                    ->whereIn('schedulable_id', $locationIds)
                    ->delete();
            }

            $project->locations()->delete();
            $project->delete();
        });
    }

    protected function generateShakhaSchedules(AuditPlan $plan, FinancialYear $fy, ?AuditPolicy $policy, bool $preserveManual): void
    {
        if (! $policy) {
            return;
        }

        $frequency = max(1, (int) $policy->frequency_per_year);
        $interval = max(1, (int) ($policy->interval_months ?: (int) floor(12 / $frequency)));
        $skipIds = $preserveManual
            ? $this->manualEntityIds($plan, AuditPolicy::CATEGORY_SHAKHA, Shakha::class)
            : [];

        $shakhas = Shakha::query()
            ->where('status', 'active')
            ->when($skipIds !== [], fn ($q) => $q->whereNotIn('id', $skipIds))
            ->orderBy('id')
            ->get();

        $rows = [];
        foreach ($shakhas as $offset => $shakha) {
            $startMonth = $offset % $interval;
            for ($i = 0; $i < $frequency; $i++) {
                $monthIndex = ($startMonth + ($i * $interval)) % 12;
                $rows[] = $this->scheduleRow(
                    $plan,
                    AuditPolicy::CATEGORY_SHAKHA,
                    $shakha,
                    $monthIndex,
                    $fy,
                    $i + 1
                );
            }
        }

        $this->insertChunks($rows);
    }

    protected function syncMissingShakhas(AuditPlan $plan, FinancialYear $fy, ?AuditPolicy $policy): int
    {
        if (! $policy) {
            return 0;
        }

        $existingIds = $this->scheduledEntityIds($plan, AuditPolicy::CATEGORY_SHAKHA, Shakha::class);
        $frequency = max(1, (int) $policy->frequency_per_year);
        $interval = max(1, (int) ($policy->interval_months ?: (int) floor(12 / $frequency)));

        $shakhas = Shakha::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->get();

        $rows = [];
        foreach ($shakhas as $offset => $shakha) {
            if (in_array((int) $shakha->id, $existingIds, true)) {
                continue;
            }

            $startMonth = $offset % $interval;
            for ($i = 0; $i < $frequency; $i++) {
                $monthIndex = ($startMonth + ($i * $interval)) % 12;
                $rows[] = $this->scheduleRow(
                    $plan,
                    AuditPolicy::CATEGORY_SHAKHA,
                    $shakha,
                    $monthIndex,
                    $fy,
                    $i + 1
                );
            }
        }

        $this->insertChunks($rows);

        return count($rows);
    }

    protected function syncMissingAreas(AuditPlan $plan, FinancialYear $fy, ?AuditPolicy $policy): int
    {
        if (! $policy) {
            return 0;
        }

        $existingIds = $this->scheduledEntityIds($plan, AuditPolicy::CATEGORY_AREA, Area::class);
        $months = $this->resolveMonthIndexes($policy);
        $areas = Area::query()
            ->where('status', 'active')
            ->whereNotIn('id', $existingIds ?: [0])
            ->orderBy('id')
            ->get();

        $rows = [];
        foreach ($areas as $area) {
            foreach ($months as $occurrence => $monthIndex) {
                $rows[] = $this->scheduleRow(
                    $plan,
                    AuditPolicy::CATEGORY_AREA,
                    $area,
                    $monthIndex,
                    $fy,
                    $occurrence + 1
                );
            }
        }

        $this->insertChunks($rows);

        return count($rows);
    }

    protected function syncMissingHq(AuditPlan $plan, FinancialYear $fy, ?AuditPolicy $policy): int
    {
        if (! $policy) {
            return 0;
        }

        $existingIds = $this->scheduledEntityIds($plan, AuditPolicy::CATEGORY_HQ, HqDepartment::class);
        $months = $this->resolveMonthIndexes($policy);
        $departments = HqDepartment::query()
            ->where('status', 'active')
            ->whereNotIn('id', $existingIds ?: [0])
            ->orderBy('sort_order')
            ->get();

        $rows = [];
        foreach ($departments as $department) {
            foreach ($months as $occurrence => $monthIndex) {
                $rows[] = $this->scheduleRow(
                    $plan,
                    AuditPolicy::CATEGORY_HQ,
                    $department,
                    $monthIndex,
                    $fy,
                    $occurrence + 1
                );
            }
        }

        $this->insertChunks($rows);

        return count($rows);
    }

    protected function syncMissingProjects(AuditPlan $plan, FinancialYear $fy, Collection $policies): int
    {
        $locations = ProjectLocation::query()
            ->with('project')
            ->where('status', 'active')
            ->whereHas('project', fn ($q) => $q->where('status', 'active'))
            ->get();

        $rows = [];

        foreach ($locations as $location) {
            $project = $location->project;

            if ($project->is_pksf || $project->is_maternity) {
                $this->appendMissingLocationRows(
                    $rows,
                    $plan,
                    $fy,
                    $policies->get(AuditPolicy::CATEGORY_PKSF),
                    AuditPolicy::CATEGORY_PKSF,
                    $location
                );
            }

            if ($project->has_project_audit) {
                $this->appendMissingLocationRows(
                    $rows,
                    $plan,
                    $fy,
                    $policies->get(AuditPolicy::CATEGORY_PROJECT_AUDIT),
                    AuditPolicy::CATEGORY_PROJECT_AUDIT,
                    $location
                );
            }

            if ($project->has_project_monitoring) {
                $this->appendMissingLocationRows(
                    $rows,
                    $plan,
                    $fy,
                    $policies->get(AuditPolicy::CATEGORY_PROJECT_MONITORING),
                    AuditPolicy::CATEGORY_PROJECT_MONITORING,
                    $location
                );
            }
        }

        $this->insertChunks($rows);

        return count($rows);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    protected function appendMissingLocationRows(
        array &$rows,
        AuditPlan $plan,
        FinancialYear $fy,
        ?AuditPolicy $policy,
        string $category,
        ProjectLocation $location
    ): void {
        if (! $policy) {
            return;
        }

        $alreadyScheduled = PlanSchedule::query()
            ->where('audit_plan_id', $plan->id)
            ->where('category', $category)
            ->where('schedulable_type', ProjectLocation::class)
            ->where('schedulable_id', $location->id)
            ->exists();

        if ($alreadyScheduled) {
            return;
        }

        foreach ($this->resolveMonthIndexes($policy) as $occurrence => $monthIndex) {
            $rows[] = $this->scheduleRow(
                $plan,
                $category,
                $location,
                $monthIndex,
                $fy,
                $occurrence + 1
            );
        }
    }

    /**
     * @return list<int>
     */
    protected function scheduledEntityIds(AuditPlan $plan, string $category, string $type): array
    {
        return PlanSchedule::query()
            ->where('audit_plan_id', $plan->id)
            ->where('category', $category)
            ->where('schedulable_type', $type)
            ->distinct()
            ->pluck('schedulable_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    protected function generateAreaSchedules(AuditPlan $plan, FinancialYear $fy, ?AuditPolicy $policy, bool $preserveManual): void
    {
        if (! $policy) {
            return;
        }

        $months = $this->resolveMonthIndexes($policy);
        $skipIds = $preserveManual
            ? $this->manualEntityIds($plan, AuditPolicy::CATEGORY_AREA, Area::class)
            : [];

        $areas = Area::query()
            ->where('status', 'active')
            ->when($skipIds !== [], fn ($q) => $q->whereNotIn('id', $skipIds))
            ->orderBy('id')
            ->get();

        $rows = [];
        foreach ($areas as $area) {
            foreach ($months as $occurrence => $monthIndex) {
                $rows[] = $this->scheduleRow(
                    $plan,
                    AuditPolicy::CATEGORY_AREA,
                    $area,
                    $monthIndex,
                    $fy,
                    $occurrence + 1
                );
            }
        }

        $this->insertChunks($rows);
    }

    protected function generateProjectSchedules(AuditPlan $plan, FinancialYear $fy, Collection $policies, bool $preserveManual): void
    {
        $locations = ProjectLocation::query()
            ->with('project')
            ->where('status', 'active')
            ->whereHas('project', fn ($q) => $q->where('status', 'active'))
            ->get();

        $rows = [];

        foreach ($locations as $location) {
            $project = $location->project;

            if ($project->is_pksf || $project->is_maternity) {
                $this->appendLocationRows(
                    $rows,
                    $plan,
                    $fy,
                    $policies->get(AuditPolicy::CATEGORY_PKSF),
                    AuditPolicy::CATEGORY_PKSF,
                    $location,
                    $preserveManual
                );
            }

            if ($project->has_project_audit) {
                $this->appendLocationRows(
                    $rows,
                    $plan,
                    $fy,
                    $policies->get(AuditPolicy::CATEGORY_PROJECT_AUDIT),
                    AuditPolicy::CATEGORY_PROJECT_AUDIT,
                    $location,
                    $preserveManual
                );
            }

            if ($project->has_project_monitoring) {
                $this->appendLocationRows(
                    $rows,
                    $plan,
                    $fy,
                    $policies->get(AuditPolicy::CATEGORY_PROJECT_MONITORING),
                    AuditPolicy::CATEGORY_PROJECT_MONITORING,
                    $location,
                    $preserveManual
                );
            }
        }

        $this->insertChunks($rows);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    protected function appendLocationRows(
        array &$rows,
        AuditPlan $plan,
        FinancialYear $fy,
        ?AuditPolicy $policy,
        string $category,
        ProjectLocation $location,
        bool $preserveManual
    ): void {
        if (! $policy) {
            return;
        }

        if ($preserveManual && $this->entityHasManual($plan, $category, ProjectLocation::class, $location->id)) {
            return;
        }

        foreach ($this->resolveMonthIndexes($policy) as $occurrence => $monthIndex) {
            $rows[] = $this->scheduleRow(
                $plan,
                $category,
                $location,
                $monthIndex,
                $fy,
                $occurrence + 1
            );
        }
    }

    protected function generateHqSchedules(AuditPlan $plan, FinancialYear $fy, ?AuditPolicy $policy, bool $preserveManual): void
    {
        if (! $policy) {
            return;
        }

        $months = $this->resolveMonthIndexes($policy);
        $skipIds = $preserveManual
            ? $this->manualEntityIds($plan, AuditPolicy::CATEGORY_HQ, HqDepartment::class)
            : [];

        $departments = HqDepartment::query()
            ->where('status', 'active')
            ->when($skipIds !== [], fn ($q) => $q->whereNotIn('id', $skipIds))
            ->orderBy('sort_order')
            ->get();

        $rows = [];
        foreach ($departments as $department) {
            foreach ($months as $occurrence => $monthIndex) {
                $rows[] = $this->scheduleRow(
                    $plan,
                    AuditPolicy::CATEGORY_HQ,
                    $department,
                    $monthIndex,
                    $fy,
                    $occurrence + 1
                );
            }
        }

        $this->insertChunks($rows);
    }

    /**
     * @return list<int>
     */
    protected function resolveMonthIndexes(AuditPolicy $policy): array
    {
        $frequency = max(1, (int) $policy->frequency_per_year);
        $interval = max(1, (int) ($policy->interval_months ?: (int) floor(12 / $frequency)));
        $indexes = [];

        for ($i = 0; $i < $frequency; $i++) {
            $indexes[] = ($i * $interval) % 12;
        }

        return $indexes;
    }

    /**
     * @return list<int>
     */
    protected function manualEntityIds(AuditPlan $plan, string $category, string $type): array
    {
        return PlanSchedule::query()
            ->where('audit_plan_id', $plan->id)
            ->where('category', $category)
            ->where('schedulable_type', $type)
            ->where('is_manual', true)
            ->distinct()
            ->pluck('schedulable_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    protected function entityHasManual(AuditPlan $plan, string $category, string $type, int $id): bool
    {
        return PlanSchedule::query()
            ->where('audit_plan_id', $plan->id)
            ->where('category', $category)
            ->where('schedulable_type', $type)
            ->where('schedulable_id', $id)
            ->where('is_manual', true)
            ->exists();
    }

    protected function scheduleRow(
        AuditPlan $plan,
        string $category,
        Model $schedulable,
        int $monthIndex,
        FinancialYear $fy,
        int $occurrence
    ): array {
        return [
            'audit_plan_id' => $plan->id,
            'category' => $category,
            'schedulable_type' => $schedulable::class,
            'schedulable_id' => $schedulable->id,
            'month_index' => $monthIndex,
            'planned_date' => $fy->dateForMonthIndex($monthIndex)->toDateString(),
            'occurrence' => $occurrence,
            'status' => 'planned',
            'is_manual' => false,
            'auditor_id' => null,
            'remarks' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    protected function insertChunks(array $rows): void
    {
        foreach (array_chunk($rows, 500) as $chunk) {
            PlanSchedule::insert($chunk);
        }
    }
}
