<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectLocationRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Models\Area;
use App\Models\AuditPlan;
use App\Models\AuditPolicy;
use App\Models\HqDepartment;
use App\Models\PlanSchedule;
use App\Models\Project;
use App\Models\ProjectLocation;
use App\Services\AnnualAuditReportBuilder;
use App\Services\AnnualPlanGenerator;
use App\Services\ProjectWorkPlanExcelExporter;
use App\Support\Divisions;
use App\Support\FinancialYear;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnnualAuditController extends Controller
{
    public function __construct(
        private AnnualPlanGenerator $generator,
        private ProjectWorkPlanExcelExporter $excelExporter,
    ) {}

    public function index(Request $request): View
    {
        $plan = $this->resolvePlan($request);
        $builder = new AnnualAuditReportBuilder($plan);
        $tab = $request->string('tab', 'total')->toString();
        $allowed = [
            'total',
            'shakha',
            'area',
            'pksf',
            'hq',
            'project_audit',
            'project_monitoring',
            'strategic',
            'policies',
        ];
        if (! in_array($tab, $allowed, true)) {
            $tab = 'total';
        }

        $division = $request->string('division')->toString() ?: null;
        $areaId = $request->integer('area_id') ?: null;

        $availablePlans = AuditPlan::query()->orderByDesc('start_date')->get(['id', 'fy_label', 'status', 'start_date']);
        $nextFyLabel = FinancialYear::fromLabel($plan->fy_label)->next()->label;
        $nextPlanExists = $availablePlans->contains(fn ($p) => $p->fy_label === $nextFyLabel);

        $data = [
            'plan' => $plan,
            'tab' => $tab,
            'months' => $builder->months(),
            'kpis' => $builder->kpis(),
            'divisions' => Divisions::all(),
            'areas' => Area::query()->where('status', 'active')->orderBy('name')->get(),
            'filters' => [
                'division' => $division,
                'area_id' => $areaId,
            ],
            'canEditSchedule' => true,
            'availablePlans' => $availablePlans,
            'nextFyLabel' => $nextFyLabel,
            'nextPlanExists' => $nextPlanExists,
            'canDeletePlan' => (bool) $request->user()?->isSuperAdmin(),
        ];

        return match ($tab) {
            'shakha' => view('annual-audit.index', $data + [
                'shakhaGroups' => ($shakhaGroups = $builder->shakhaGroups()),
                'rows' => ($shakhaRows = $shakhaGroups->flatMap(fn ($g) => $g['rows'])),
                'shakhaTotals' => $builder->shakhaTotals($shakhaRows),
                'categoryTotals' => null,
            ]),
            'area' => view('annual-audit.index', $data + [
                'rows' => ($areaRows = $builder->areaMatrix()),
                'areaTotals' => $builder->areaTotals($areaRows),
                'categoryTotals' => null,
            ]),
            'pksf' => view('annual-audit.index', $data + [
                'rows' => ($pksfRows = $builder->pksfMatrix()),
                'pksfTotals' => $builder->pksfTotals($pksfRows),
                'categoryTotals' => null,
            ]),
            'hq' => view('annual-audit.index', $data + [
                'rows' => ($hqRows = $builder->hqMatrix()),
                'hqTotals' => $builder->hqTotals($hqRows),
                'categoryTotals' => null,
            ]),
            'project_audit' => view('annual-audit.index', $data + [
                'projectGroups' => $builder->projectAuditGroups(),
                'rows' => collect(),
                'categoryTotals' => null,
            ]),
            'project_monitoring' => view('annual-audit.index', $data + [
                'projectGroups' => $builder->projectMonitoringGroups(),
                'rows' => collect(),
                'categoryTotals' => null,
            ]),
            'strategic' => view('annual-audit.index', $data + [
                'strategicItems' => $builder->strategicItems(),
                'rows' => collect(),
                'categoryTotals' => null,
            ]),
            'policies' => view('annual-audit.index', $data + [
                'policies' => $plan->policies()->orderBy('category')->get(),
                'rows' => collect(),
                'categoryTotals' => null,
            ]),
            default => view('annual-audit.index', $data + [
                'categoryTotals' => $builder->totalsByCategory(),
                'rows' => collect(),
            ]),
        };
    }

    public function createYear(Request $request): RedirectResponse
    {
        $current = $this->resolvePlan($request);
        $plan = $this->generator->createNextPlan($current, $request->user()?->id);

        return redirect()
            ->route('annual-audit.index', $this->fyParams($plan, ['tab' => 'total']))
            ->with('status', 'Created FY '.$plan->fy_label.'. Policies copied from FY '.$current->fy_label.'. Click Generate when ready.');
    }

    public function destroyYear(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $plan = $this->resolvePlan($request);
        $deletedLabel = $plan->fy_label;

        DB::transaction(function () use ($plan) {
            $plan->delete();
        });

        $fallback = AuditPlan::query()->orderByDesc('start_date')->first();

        if ($fallback) {
            return redirect()
                ->route('annual-audit.index', $this->fyParams($fallback, ['tab' => 'total']))
                ->with('status', 'Deleted entire FY '.$deletedLabel.' report (schedules & policies removed).');
        }

        $fresh = $this->generator->ensurePlan(
            FinancialYear::current()->label,
            $request->user()?->id
        );

        return redirect()
            ->route('annual-audit.index', $this->fyParams($fresh, ['tab' => 'total']))
            ->with('status', 'Deleted FY '.$deletedLabel.'. Opened FY '.$fresh->fy_label.' instead.');
    }

    public function generate(Request $request): RedirectResponse
    {
        $plan = $this->resolvePlan($request);
        $this->generator->generate($plan, preserveManual: true);

        return redirect()
            ->route('annual-audit.index', $this->fyParams($plan, ['tab' => 'total']))
            ->with('status', 'Annual plan generated for FY '.$plan->fy_label.'. Manual month picks were kept.');
    }

    public function publish(Request $request): RedirectResponse
    {
        $plan = $this->resolvePlan($request);
        $plan->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return redirect()
            ->route('annual-audit.index', $this->fyParams($plan, ['tab' => 'total']))
            ->with('status', 'Plan published for FY '.$plan->fy_label.'.');
    }

    public function updatePolicies(Request $request): RedirectResponse
    {
        $plan = $this->resolvePlan($request);

        $validated = $request->validate([
            'policies' => ['required', 'array'],
            'policies.*.frequency_per_year' => ['required', 'integer', 'min:1', 'max:12'],
            'policies.*.interval_months' => ['nullable', 'integer', 'min:1', 'max:12'],
            'policies.*.pattern' => ['nullable', 'string', 'max:40'],
            'policies.*.custom_month_indexes' => ['nullable', 'string', 'max:80'],
            'policies.*.notes' => ['nullable', 'string', 'max:500'],
            'regenerate' => ['nullable', 'boolean'],
        ]);

        $this->generator->updatePolicies($plan, $validated['policies']);

        if ($request->boolean('regenerate')) {
            $this->generator->generate($plan->fresh('policies'), preserveManual: true);
        }

        return redirect()
            ->route('annual-audit.index', $this->fyParams($plan, ['tab' => 'policies']))
            ->with('status', $request->boolean('regenerate')
                ? 'Policies saved and plan regenerated (manual picks kept).'
                : 'Policies saved. Generate the plan when you are ready.');
    }

    public function toggleMonth(Request $request): RedirectResponse|JsonResponse
    {
        $plan = $this->resolvePlan($request);

        $validated = $request->validate([
            'category' => ['required', 'string', Rule::in([
                AuditPolicy::CATEGORY_SHAKHA,
                AuditPolicy::CATEGORY_AREA,
                AuditPolicy::CATEGORY_PKSF,
                AuditPolicy::CATEGORY_HQ,
                AuditPolicy::CATEGORY_PROJECT_AUDIT,
                AuditPolicy::CATEGORY_PROJECT_MONITORING,
            ])],
            'schedulable_type' => ['required', 'string', Rule::in(AnnualPlanGenerator::SCHEDULABLE_TYPES)],
            'schedulable_id' => ['required', 'integer', 'min:1'],
            'month_index' => ['required', 'integer', 'min:0', 'max:11'],
            'tab' => ['nullable', 'string'],
            'division' => ['nullable', 'string'],
            'area_id' => ['nullable', 'integer'],
            'fy' => ['nullable', 'string'],
        ]);

        $result = $this->generator->toggleMonth(
            $plan,
            $validated['category'],
            $validated['schedulable_type'],
            (int) $validated['schedulable_id'],
            (int) $validated['month_index']
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'result' => $result,
                'active' => $result === 'added',
            ]);
        }

        return redirect()
            ->route('annual-audit.index', $this->fyParams($plan, [
                'tab' => $validated['tab'] ?? 'shakha',
                'division' => $validated['division'] ?? null,
                'area_id' => $validated['area_id'] ?? null,
            ]))
            ->with('status', $result === 'added'
                ? 'Month scheduled (admin override).'
                : 'Month removed.');
    }

    public function export(Request $request): StreamedResponse
    {
        $mode = $request->string('mode', 'monitoring')->toString();
        $plan = $this->resolvePlan($request);

        if ($mode === 'all') {
            return $this->excelExporter->downloadAll($plan);
        }

        if (! in_array($mode, ['audit', 'monitoring', 'hq', 'area', 'pksf', 'shakha'], true)) {
            $mode = 'monitoring';
        }

        return $this->excelExporter->download($plan, $mode);
    }

    public function storeHqDepartment(Request $request): RedirectResponse
    {
        $plan = $this->resolvePlan($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:hq_departments,name'],
        ]);

        $maxOrder = (int) HqDepartment::query()->max('sort_order');

        HqDepartment::query()->create([
            'name' => $validated['name'],
            'status' => 'active',
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()
            ->route('annual-audit.index', $this->fyParams($plan, ['tab' => 'hq']))
            ->with('status', 'HQ department added. Click months to schedule visits.');
    }

    public function destroyHqDepartment(Request $request, HqDepartment $department): RedirectResponse
    {
        $plan = $this->resolvePlan($request);

        DB::transaction(function () use ($department) {
            PlanSchedule::query()
                ->where('schedulable_type', HqDepartment::class)
                ->where('schedulable_id', $department->id)
                ->delete();

            $department->delete();
        });

        return redirect()
            ->route('annual-audit.index', $this->fyParams($plan, ['tab' => 'hq']))
            ->with('status', 'HQ department removed.');
    }

    public function storeProject(StoreProjectRequest $request): RedirectResponse
    {
        $plan = $this->resolvePlan($request);
        $data = $request->validated();
        $locations = $data['locations'] ?? [];
        unset($data['locations']);

        $tab = $request->string('return_tab', 'project_monitoring')->toString();
        if (! in_array($tab, ['project_audit', 'project_monitoring'], true)) {
            $tab = 'project_monitoring';
        }

        if ($tab === 'project_audit') {
            $data['has_project_audit'] = true;
            $data['has_project_monitoring'] = $data['has_project_monitoring'] ?? false;
        } else {
            $data['has_project_monitoring'] = true;
            $data['has_project_audit'] = $data['has_project_audit'] ?? false;
        }

        DB::transaction(function () use ($data, $locations) {
            $project = Project::query()->create($data);

            foreach ($locations as $location) {
                if (blank($location['name'] ?? null)) {
                    continue;
                }

                $project->locations()->create([
                    'name' => $location['name'],
                    'division' => $location['division'] ?? null,
                    'status' => $location['status'] ?? 'active',
                ]);
            }
        });

        return redirect()
            ->route('annual-audit.index', $this->fyParams($plan, ['tab' => $tab]))
            ->with('status', 'Project added. Click months to schedule visits.');
    }

    public function storeProjectLocation(StoreProjectLocationRequest $request, Project $project): RedirectResponse
    {
        $plan = $this->resolvePlan($request);
        $tab = $request->string('return_tab', 'project_monitoring')->toString();
        if (! in_array($tab, ['project_audit', 'project_monitoring'], true)) {
            $tab = 'project_monitoring';
        }

        $ok = $tab === 'project_audit'
            ? ($project->has_project_audit && $project->isActive())
            : ($project->has_project_monitoring && $project->isActive());

        abort_unless($ok, 404);

        $project->locations()->create($request->validated());

        return redirect()
            ->route('annual-audit.index', $this->fyParams($plan, ['tab' => $tab]))
            ->with('status', 'Location added. Click months to schedule.');
    }

    public function destroyProject(Request $request, Project $project): RedirectResponse
    {
        $plan = $this->resolvePlan($request);
        $tab = $request->string('return_tab', 'project_monitoring')->toString();
        if (! in_array($tab, ['project_audit', 'project_monitoring'], true)) {
            $tab = 'project_monitoring';
        }

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

        return redirect()
            ->route('annual-audit.index', $this->fyParams($plan, ['tab' => $tab]))
            ->with('status', 'Project removed.');
    }

    public function destroyProjectLocation(Request $request, Project $project, ProjectLocation $location): RedirectResponse
    {
        $plan = $this->resolvePlan($request);
        abort_unless($location->project_id === $project->id, 404);

        $tab = $request->string('return_tab', 'project_monitoring')->toString();
        if (! in_array($tab, ['project_audit', 'project_monitoring'], true)) {
            $tab = 'project_monitoring';
        }

        DB::transaction(function () use ($location) {
            PlanSchedule::query()
                ->where('schedulable_type', ProjectLocation::class)
                ->where('schedulable_id', $location->id)
                ->delete();

            $location->delete();
        });

        return redirect()
            ->route('annual-audit.index', $this->fyParams($plan, ['tab' => $tab]))
            ->with('status', 'Location removed.');
    }

    protected function resolvePlan(Request $request): AuditPlan
    {
        $fy = $request->string('fy')->toString();

        if ($fy !== '') {
            try {
                return $this->generator->ensurePlan($fy, $request->user()?->id);
            } catch (\InvalidArgumentException) {
                abort(422, 'Invalid financial year.');
            }
        }

        $latest = AuditPlan::query()->orderByDesc('start_date')->first();
        if ($latest) {
            return $this->generator->ensurePlan($latest->fy_label, $request->user()?->id);
        }

        return $this->generator->ensurePlan(
            FinancialYear::current()->label,
            $request->user()?->id
        );
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    protected function fyParams(AuditPlan $plan, array $extra = []): array
    {
        return array_filter(
            ['fy' => $plan->fy_label] + $extra,
            fn ($v) => $v !== null && $v !== ''
        );
    }
}
