<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectLocationRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Models\Project;
use App\Models\ProjectLocation;
use App\Services\AnnualPlanGenerator;
use App\Support\Divisions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(
        private AnnualPlanGenerator $planGenerator,
    ) {}

    public function index(): View
    {
        $projects = Project::query()
            ->withCount('locations')
            ->orderBy('name')
            ->get();

        return view('projects.index', compact('projects'));
    }

    public function create(): View
    {
        return view('projects.create', [
            'divisions' => Divisions::all(),
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $locations = $data['locations'] ?? [];
        unset($data['locations']);

        $project = DB::transaction(function () use ($data, $locations) {
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

            return $project;
        });

        $syncNote = $this->planGenerator->includeInCurrentPlan();

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Project added.'.($syncNote ? ' '.$syncNote : ' Generate or Sync new items on Annual Audit if this FY plan already exists.'));
    }

    public function show(Project $project): View
    {
        $project->load(['locations' => fn ($q) => $q->orderBy('name')]);

        return view('projects.show', [
            'project' => $project,
            'divisions' => Divisions::all(),
        ]);
    }

    public function storeLocation(StoreProjectLocationRequest $request, Project $project): RedirectResponse
    {
        $project->locations()->create($request->validated());

        $syncNote = $this->planGenerator->includeInCurrentPlan();

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Location added.'.($syncNote ? ' '.$syncNote : ' Generate or Sync new items on Annual Audit if this FY plan already exists.'));
    }

    public function destroyLocation(Project $project, ProjectLocation $location): RedirectResponse
    {
        abort_unless($location->project_id === $project->id, 404);

        $this->planGenerator->deleteLocationWithSchedules($location);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Location removed from project and annual audit schedules.');
    }
}
