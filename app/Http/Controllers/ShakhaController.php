<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShakhaRequest;
use App\Models\Area;
use App\Models\Shakha;
use App\Services\AnnualPlanGenerator;
use App\Support\FinancialYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShakhaController extends Controller
{
    public function __construct(
        private AnnualPlanGenerator $planGenerator,
    ) {}

    public function index(): View
    {
        $fyLabel = FinancialYear::current()->label;

        $shakhas = Shakha::query()
            ->with([
                'area',
                'latestRiskAssessment',
                'annualKpis' => fn ($q) => $q->where('fy_label', $fyLabel),
            ])
            ->orderBy('name')
            ->get();

        $rows = $shakhas->values()->map(function (Shakha $shakha, int $index) use ($fyLabel) {
            $risk = $shakha->latestRiskAssessment;
            $kpiReady = $shakha->annualKpis->isNotEmpty();

            return [
                'serial' => $index + 1,
                'id' => $shakha->id,
                'name' => $shakha->name,
                'area' => (string) ($shakha->area?->name ?: ''),
                'division' => (string) ($shakha->area?->division ?: ''),
                'code' => (string) ($shakha->code ?: ''),
                'opening' => optional($shakha->opening_date ?? $shakha->opened_at)->format('d M Y') ?: '—',
                'status' => $shakha->status,
                'kpi_ready' => $kpiReady,
                'risk' => $risk?->risk_category ?: 'Not assessed',
                'risk_score' => $risk?->total_weighted_score,
                'risk_period' => $risk?->periodLabel(),
                'added_on' => $shakha->created_at?->format('d M Y') ?: '—',
                'edit_url' => route('shakhas.edit', $shakha),
                'risk_url' => route('shakhas.risk.create', $shakha),
                'kpi_url' => route('kpis.edit', ['shakha' => $shakha, 'fy' => $fyLabel]),
            ];
        })->values();

        $areas = $rows->pluck('area')->filter()->unique()->sort()->values();
        $divisions = $rows->pluck('division')->filter()->unique()->sort()->values();

        $riskCounts = [
            'all' => $rows->count(),
            'Low Risk' => $rows->where('risk', 'Low Risk')->count(),
            'Medium Risk' => $rows->where('risk', 'Medium Risk')->count(),
            'High Risk' => $rows->where('risk', 'High Risk')->count(),
            'Significant Risk' => $rows->where('risk', 'Significant Risk')->count(),
            'Not assessed' => $rows->where('risk', 'Not assessed')->count(),
        ];

        return view('shakhas.index', [
            'shakhas' => $shakhas,
            'rows' => $rows,
            'fyLabel' => $fyLabel,
            'areas' => $areas,
            'divisions' => $divisions,
            'riskCounts' => $riskCounts,
        ]);
    }

    public function create(): View
    {
        return $this->formView();
    }

    public function store(StoreShakhaRequest $request): RedirectResponse
    {
        $this->persistShakha($request);

        $syncNote = $this->planGenerator->includeInCurrentPlan();

        return redirect()
            ->route('shakhas.index')
            ->with('status', 'Shakha added successfully.'.($syncNote ? ' '.$syncNote : ' Generate or Sync new items on Annual Audit if this FY plan already exists.'));
    }

    public function edit(Shakha $shakha): View
    {
        return $this->formView($shakha);
    }

    public function update(StoreShakhaRequest $request, Shakha $shakha): RedirectResponse
    {
        $this->persistShakha($request, $shakha);

        return redirect()
            ->route('shakhas.index')
            ->with('status', 'Shakha updated successfully.');
    }

    protected function formView(?Shakha $shakha = null): View
    {
        $areas = Area::query()
            ->withCount('shakhas')
            ->orderBy('division')
            ->orderBy('name')
            ->get();

        return view('shakhas.form', [
            'shakha' => $shakha,
            'areas' => $areas,
            'areasByDivision' => $areas->groupBy('division'),
            'areaCount' => $areas->count(),
            'shakhaCount' => Shakha::query()->count(),
            'isEdit' => $shakha !== null,
        ]);
    }

    protected function persistShakha(StoreShakhaRequest $request, ?Shakha $shakha = null): Shakha
    {
        $data = $request->validated();
        $data['opened_at'] = $data['opening_date'];
        if (empty($data['focal_person_name'])) {
            $data['focal_person_name'] = null;
        }

        if ($shakha) {
            $shakha->update($data);

            return $shakha->fresh();
        }

        return Shakha::query()->create($data);
    }
}
