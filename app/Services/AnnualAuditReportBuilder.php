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
use App\Models\StrategicPlanItem;
use App\Support\FinancialYear;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnnualAuditReportBuilder
{
    public function __construct(public readonly AuditPlan $plan) {}

    public function months(): array
    {
        return FinancialYear::fromLabel($this->plan->fy_label)->months();
    }

    /**
     * @return array<string, array{label:string,planned:int,by_month:array<int,int>}>
     */
    public function totalsByCategory(): array
    {
        $months = range(0, 11);
        $categories = [
            AuditPolicy::CATEGORY_SHAKHA => 'Shakha Audit',
            AuditPolicy::CATEGORY_AREA => 'Area Office',
            AuditPolicy::CATEGORY_PKSF => 'PKSF & Maternity',
            AuditPolicy::CATEGORY_HQ => 'HQ Concern',
            AuditPolicy::CATEGORY_PROJECT_AUDIT => 'Project Audit',
            AuditPolicy::CATEGORY_PROJECT_MONITORING => 'Project Monitoring',
        ];

        $counts = PlanSchedule::query()
            ->select('category', 'month_index', DB::raw('count(*) as total'))
            ->where('audit_plan_id', $this->plan->id)
            ->where('month_index', '<=', 11)
            ->groupBy('category', 'month_index')
            ->get()
            ->groupBy('category');

        $result = [];
        foreach ($categories as $key => $label) {
            $byMonth = array_fill_keys($months, 0);
            $group = $counts->get($key, collect());
            foreach ($group as $row) {
                $byMonth[(int) $row->month_index] = (int) $row->total;
            }
            $result[$key] = [
                'label' => $label,
                'planned' => array_sum($byMonth),
                'by_month' => $byMonth,
            ];
        }

        return $result;
    }

    public function kpis(): array
    {
        $totals = $this->totalsByCategory();
        $planned = array_sum(array_column($totals, 'planned'));
        $completed = PlanSchedule::query()
            ->where('audit_plan_id', $this->plan->id)
            ->where('month_index', '<=', 11)
            ->where('status', 'completed')
            ->count();

        return [
            'planned' => $planned,
            'completed' => $completed,
            'pending' => max(0, $planned - $completed),
            'shakha' => $totals[AuditPolicy::CATEGORY_SHAKHA]['planned'] ?? 0,
            'area' => $totals[AuditPolicy::CATEGORY_AREA]['planned'] ?? 0,
            'project_audit' => $totals[AuditPolicy::CATEGORY_PROJECT_AUDIT]['planned'] ?? 0,
            'project_monitoring' => $totals[AuditPolicy::CATEGORY_PROJECT_MONITORING]['planned'] ?? 0,
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function shakhaMatrix(?string $division = null, ?int $areaId = null): Collection
    {
        $shakhas = Shakha::query()
            ->with('area')
            ->where('status', 'active')
            ->when($areaId, fn ($q) => $q->where('area_id', $areaId))
            ->when($division, fn ($q) => $q->whereHas('area', fn ($a) => $a->where('division', $division)))
            ->get()
            ->sortBy([
                fn (Shakha $s) => $s->area?->division ?? '',
                fn (Shakha $s) => $s->area?->name ?? '',
                fn (Shakha $s) => sprintf('%010s', (string) ($s->code ?? '')),
                fn (Shakha $s) => $s->name,
            ])
            ->values();

        $schedules = PlanSchedule::query()
            ->where('audit_plan_id', $this->plan->id)
            ->where('category', AuditPolicy::CATEGORY_SHAKHA)
            ->where('schedulable_type', Shakha::class)
            ->where('month_index', '<=', 11)
            ->get()
            ->groupBy('schedulable_id');

        return $shakhas->map(function (Shakha $shakha) use ($schedules) {
            $months = array_fill(0, 12, false);
            $manual = array_fill(0, 12, false);
            foreach ($schedules->get($shakha->id, collect()) as $schedule) {
                $months[(int) $schedule->month_index] = true;
                if ($schedule->is_manual) {
                    $manual[(int) $schedule->month_index] = true;
                }
            }

            return [
                'id' => $shakha->id,
                'area_id' => $shakha->area_id,
                'name' => $shakha->name,
                'code' => $shakha->code,
                'area' => $shakha->area?->name,
                'division' => $shakha->area?->division,
                'category' => AuditPolicy::CATEGORY_SHAKHA,
                'schedulable_type' => Shakha::class,
                'months' => $months,
                'manual' => $manual,
                'total' => count(array_filter($months)),
            ];
        });
    }

    /**
     * Shakhas grouped by area (Excel branch-office sheet layout).
     *
     * @return Collection<int, array{area_id:int|null,area:?string,division:?string,rows:Collection<int, array<string, mixed>>}>
     */
    public function shakhaGroups(?string $division = null, ?int $areaId = null): Collection
    {
        $sl = 0;

        return $this->shakhaMatrix($division, $areaId)
            ->groupBy('area_id')
            ->map(function (Collection $areaRows) use (&$sl) {
                $first = $areaRows->first();

                return [
                    'area_id' => $first['area_id'] ?? null,
                    'area' => $first['area'] ?? null,
                    'division' => $first['division'] ?? null,
                    'rows' => $areaRows->values()->map(function (array $row) use (&$sl) {
                        $sl++;
                        $row['sl'] = $sl;

                        return $row;
                    }),
                ];
            })
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array{by_month: array<int,int>, grand: int}
     */
    public function shakhaTotals(Collection $rows): array
    {
        $byMonth = array_fill(0, 12, 0);
        foreach ($rows as $row) {
            foreach ($row['months'] as $index => $active) {
                if ($active) {
                    $byMonth[$index]++;
                }
            }
        }

        return [
            'by_month' => $byMonth,
            'grand' => array_sum($byMonth),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function areaMatrix(?string $division = null): Collection
    {
        $areas = Area::query()
            ->where('status', 'active')
            ->when($division, fn ($q) => $q->where('division', $division))
            ->orderBy('division')
            ->orderBy('name')
            ->get();

        $schedules = PlanSchedule::query()
            ->where('audit_plan_id', $this->plan->id)
            ->where('category', AuditPolicy::CATEGORY_AREA)
            ->where('schedulable_type', Area::class)
            ->where('month_index', '<=', 11)
            ->get()
            ->groupBy('schedulable_id');

        return $areas->values()->map(function (Area $area, int $index) use ($schedules) {
            $months = array_fill(0, 12, false);
            $manual = array_fill(0, 12, false);
            foreach ($schedules->get($area->id, collect()) as $schedule) {
                $months[(int) $schedule->month_index] = true;
                if ($schedule->is_manual) {
                    $manual[(int) $schedule->month_index] = true;
                }
            }

            return [
                'id' => $area->id,
                'sl' => $index + 1,
                'name' => $area->name,
                'division' => $area->division,
                'category' => AuditPolicy::CATEGORY_AREA,
                'schedulable_type' => Area::class,
                'months' => $months,
                'manual' => $manual,
                'total' => count(array_filter($months)),
            ];
        });
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array{by_month: array<int,int>, by_quarter: array<string,int>, grand: int}
     */
    public function areaTotals(Collection $rows): array
    {
        return $this->hqTotals($rows);
    }

    /**
     * Excel-style Project Audit / Monitoring groups (project → locations).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function projectGroups(string $flag, string $category): Collection
    {
        $projects = Project::query()
            ->with(['locations' => fn ($q) => $q->where('status', 'active')->orderBy('name')])
            ->where('status', 'active')
            ->where($flag, true)
            ->orderBy('name')
            ->get();

        $locationIds = $projects->flatMap(fn ($p) => $p->locations->pluck('id'));

        $schedules = PlanSchedule::query()
            ->where('audit_plan_id', $this->plan->id)
            ->where('category', $category)
            ->where('schedulable_type', ProjectLocation::class)
            ->when($locationIds->isNotEmpty(), fn ($q) => $q->whereIn('schedulable_id', $locationIds))
            ->where('month_index', '<=', 11)
            ->get()
            ->groupBy('schedulable_id');

        $sl = 0;

        return $projects->map(function (Project $project) use ($schedules, $category, &$sl) {
            $sl++;

            $rows = $project->locations->map(function (ProjectLocation $location) use ($schedules, $category, $project) {
                $months = array_fill(0, 12, false);
                $manual = array_fill(0, 12, false);
                foreach ($schedules->get($location->id, collect()) as $schedule) {
                    $months[(int) $schedule->month_index] = true;
                    if ($schedule->is_manual) {
                        $manual[(int) $schedule->month_index] = true;
                    }
                }

                return [
                    'id' => $location->id,
                    'project_id' => $project->id,
                    'project' => $project->name,
                    'donor' => $project->donor,
                    'location' => $location->name,
                    'division' => $location->division,
                    'category' => $category,
                    'schedulable_type' => ProjectLocation::class,
                    'months' => $months,
                    'manual' => $manual,
                    'total' => count(array_filter($months)),
                ];
            });

            return [
                'sl' => $sl,
                'project_id' => $project->id,
                'project' => $project->name,
                'donor' => $project->donor,
                'rows' => $rows->values(),
            ];
        });
    }

    public function projectMonitoringGroups(): Collection
    {
        return $this->projectGroups('has_project_monitoring', AuditPolicy::CATEGORY_PROJECT_MONITORING);
    }

    public function projectAuditGroups(): Collection
    {
        return $this->projectGroups('has_project_audit', AuditPolicy::CATEGORY_PROJECT_AUDIT);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function locationMatrix(string $category, ?callable $projectFilter = null): Collection
    {
        $locations = ProjectLocation::query()
            ->with('project')
            ->where('status', 'active')
            ->whereHas('project', function ($q) use ($projectFilter) {
                $q->where('status', 'active');
                if ($projectFilter) {
                    $projectFilter($q);
                }
            })
            ->orderBy('project_id')
            ->orderBy('name')
            ->get();

        $schedules = PlanSchedule::query()
            ->where('audit_plan_id', $this->plan->id)
            ->where('category', $category)
            ->where('schedulable_type', ProjectLocation::class)
            ->where('month_index', '<=', 11)
            ->get()
            ->groupBy('schedulable_id');

        return $locations->values()->map(function (ProjectLocation $location, int $index) use ($schedules, $category) {
            $months = array_fill(0, 12, false);
            $manual = array_fill(0, 12, false);
            foreach ($schedules->get($location->id, collect()) as $schedule) {
                $months[(int) $schedule->month_index] = true;
                if ($schedule->is_manual) {
                    $manual[(int) $schedule->month_index] = true;
                }
            }

            return [
                'id' => $location->id,
                'sl' => $index + 1,
                'project' => $location->project?->name,
                'location' => $location->name,
                'division' => $location->division,
                'is_pksf' => (bool) $location->project?->is_pksf,
                'is_maternity' => (bool) $location->project?->is_maternity,
                'category' => $category,
                'schedulable_type' => ProjectLocation::class,
                'months' => $months,
                'manual' => $manual,
                'total' => count(array_filter($months)),
            ];
        });
    }

    /**
     * PKSF projects + DSK Hospital & Maternity (Excel sheet order).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function pksfMatrix(): Collection
    {
        $order = [
            'RMTP',
            'RAISE',
            'PPEPP',
            'DSK NFPE',
            'ENRICH',
            'Adolescent Program',
            'DSK-Hospital Dhaka',
            'DSK Maternity Hospital',
            'DSK Gajaria Matri Sadan',
        ];

        $rows = $this->locationMatrix(
            AuditPolicy::CATEGORY_PKSF,
            fn ($q) => $q->where(fn ($inner) => $inner->where('is_pksf', true)->orWhere('is_maternity', true))
        );

        return $rows
            ->sortBy(function (array $row) use ($order) {
                $idx = array_search($row['project'], $order, true);

                return $idx === false ? 1000 + ($row['sl'] ?? 0) : $idx;
            })
            ->values()
            ->map(function (array $row, int $index) {
                $row['sl'] = $index + 1;

                return $row;
            });
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array{by_month: array<int,int>, by_quarter: array<string,int>, grand: int}
     */
    public function pksfTotals(Collection $rows): array
    {
        return $this->hqTotals($rows);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function hqMatrix(): Collection
    {
        $departments = HqDepartment::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $schedules = PlanSchedule::query()
            ->where('audit_plan_id', $this->plan->id)
            ->where('category', AuditPolicy::CATEGORY_HQ)
            ->where('schedulable_type', HqDepartment::class)
            ->where('month_index', '<=', 11)
            ->get()
            ->groupBy('schedulable_id');

        return $departments->values()->map(function (HqDepartment $department, int $index) use ($schedules) {
            $months = array_fill(0, 12, false);
            $manual = array_fill(0, 12, false);
            foreach ($schedules->get($department->id, collect()) as $schedule) {
                $months[(int) $schedule->month_index] = true;
                if ($schedule->is_manual) {
                    $manual[(int) $schedule->month_index] = true;
                }
            }

            return [
                'sl' => $index + 1,
                'id' => $department->id,
                'name' => $department->name,
                'category' => AuditPolicy::CATEGORY_HQ,
                'schedulable_type' => HqDepartment::class,
                'months' => $months,
                'manual' => $manual,
                'total' => count(array_filter($months)),
            ];
        });
    }

    /**
     * @return array{by_month: array<int,int>, by_quarter: array<string,int>, grand: int}
     */
    public function hqTotals(Collection $rows): array
    {
        $byMonth = array_fill(0, 12, 0);
        foreach ($rows as $row) {
            foreach ($row['months'] as $index => $active) {
                if ($active) {
                    $byMonth[$index]++;
                }
            }
        }

        return [
            'by_month' => $byMonth,
            'by_quarter' => [
                'q1' => $byMonth[0] + $byMonth[1] + $byMonth[2],
                'q2' => $byMonth[3] + $byMonth[4] + $byMonth[5],
                'q3' => $byMonth[6] + $byMonth[7] + $byMonth[8],
                'q4' => $byMonth[9] + $byMonth[10] + $byMonth[11],
            ],
            'grand' => array_sum($byMonth),
        ];
    }

    public function strategicItems(): Collection
    {
        return StrategicPlanItem::query()->orderBy('sl_no')->get();
    }
}
