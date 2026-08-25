<?php

namespace App\Services;

use App\Models\Area;
use App\Models\AuditPlan;
use App\Models\AuditPolicy;
use App\Models\HqDepartment;
use App\Models\PlanSchedule;
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
                    'interval_months' => $policy->interval_months,
                    'pattern' => $policy->pattern,
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
            AuditPolicy::CATEGORY_SHAKHA => [
                'frequency_per_year' => 3,
                'interval_months' => 4,
                'pattern' => 'rotated_interval',
                'notes' => 'Default suggestion only. Admin can change frequency (e.g. 3 or 4) and pick months per shakha.',
            ],
            AuditPolicy::CATEGORY_AREA => [
                'frequency_per_year' => 4,
                'interval_months' => 3,
                'pattern' => 'quarterly',
                'custom_month_indexes' => [0, 3, 6, 9],
                'notes' => 'Default quarterly months. Admin can override per area office.',
            ],
            AuditPolicy::CATEGORY_PKSF => [
                'frequency_per_year' => 2,
                'interval_months' => 6,
                'pattern' => 'interval',
                'custom_month_indexes' => [0, 6],
                'notes' => 'Default twice yearly. Admin can override per location.',
            ],
            AuditPolicy::CATEGORY_HQ => [
                'frequency_per_year' => 2,
                'interval_months' => 6,
                'pattern' => 'interval',
                'custom_month_indexes' => [5, 11],
                'notes' => 'Default twice yearly (e.g. Dec & Jun). Admin can set any months per HQ department like Excel.',
            ],
            AuditPolicy::CATEGORY_PROJECT_AUDIT => [
                'frequency_per_year' => 4,
                'interval_months' => 3,
                'pattern' => 'quarterly',
                'custom_month_indexes' => [0, 3, 6, 9],
                'notes' => 'Default quarterly (Jul/Oct/Jan/Apr) matching Excel Project Audit work plan.',
            ],
            AuditPolicy::CATEGORY_PROJECT_MONITORING => [
                'frequency_per_year' => 4,
                'interval_months' => 3,
                'pattern' => 'quarterly',
                'custom_month_indexes' => [0, 3, 6, 9],
                'notes' => 'Default quarterly. Admin can override per location.',
            ],
        ];

        foreach ($defaults as $category => $data) {
            AuditPolicy::firstOrCreate(
                ['audit_plan_id' => $plan->id, 'category' => $category],
                $data
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
     * @param  array<int, array{frequency_per_year?:int,interval_months?:int|null,pattern?:string,notes?:string|null,custom_month_indexes?:array|null}>  $policies
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
            $interval = isset($data['interval_months']) && $data['interval_months'] !== ''
                ? max(1, min(12, (int) $data['interval_months']))
                : (int) max(1, floor(12 / $frequency));

            $custom = $data['custom_month_indexes'] ?? null;
            if (is_string($custom)) {
                $custom = collect(explode(',', $custom))
                    ->map(fn ($v) => trim($v))
                    ->filter(fn ($v) => $v !== '' && is_numeric($v))
                    ->map(fn ($v) => (int) $v)
                    ->filter(fn ($v) => $v >= 0 && $v <= 11)
                    ->values()
                    ->all();
                $custom = $custom === [] ? null : $custom;
            }

            $policy->update([
                'frequency_per_year' => $frequency,
                'interval_months' => $interval,
                'pattern' => $data['pattern'] ?? $policy->pattern,
                'custom_month_indexes' => $custom,
                'notes' => $data['notes'] ?? $policy->notes,
            ]);
        }
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
        if (! empty($policy->custom_month_indexes) && is_array($policy->custom_month_indexes)) {
            return array_values(array_map('intval', $policy->custom_month_indexes));
        }

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
