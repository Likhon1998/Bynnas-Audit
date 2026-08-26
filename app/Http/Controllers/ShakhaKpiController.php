<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShakhaAnnualKpiRequest;
use App\Models\Shakha;
use App\Models\ShakhaAnnualKpi;
use App\Services\KpiReportService;
use App\Services\ShakhaKpiExcelExporter;
use App\Support\FinancialYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShakhaKpiController extends Controller
{
    public function __construct(
        private KpiReportService $kpiReports,
        private ShakhaKpiExcelExporter $excelExporter,
    ) {}

    public function index(Request $request): View
    {
        $fyLabel = $this->resolveFy($request->string('fy')->toString() ?: null);
        $shakhas = $this->kpiReports->shakhasForFy($fyLabel);
        $progress = $this->kpiReports->progress($fyLabel);
        $grouped = $shakhas->groupBy(fn (Shakha $s) => $s->area?->name ?? 'Unassigned');

        return view('kpis.index', [
            'fyLabel' => $fyLabel,
            'fyOptions' => $this->fyOptions(),
            'shakhas' => $shakhas,
            'grouped' => $grouped,
            'progress' => $progress,
        ]);
    }

    public function edit(Request $request, Shakha $shakha): View
    {
        $fyLabel = $this->resolveFy($request->string('fy')->toString() ?: null);
        $existing = $this->kpiReports->getForFy($shakha, $fyLabel);

        return view('kpis.edit', [
            'shakha' => $shakha->load('area'),
            'fyLabel' => $fyLabel,
            'fyOptions' => $this->fyOptions(),
            'existing' => $existing,
            'fy' => FinancialYear::fromLabel($fyLabel),
        ]);
    }

    public function store(StoreShakhaAnnualKpiRequest $request, Shakha $shakha): RedirectResponse
    {
        $data = $request->validated();
        $fyLabel = $data['fy_label'];

        $opening = $data['opening_date'] ?? null;
        $shakha->update([
            'opening_date' => $opening,
            'opened_at' => $opening,
            'focal_person_name' => $data['focal_person_name'] ?? $shakha->focal_person_name,
        ]);

        ShakhaAnnualKpi::query()->updateOrCreate(
            [
                'shakha_id' => $shakha->id,
                'fy_label' => $fyLabel,
            ],
            collect($data)->except(['fy_label', 'opening_date', 'focal_person_name'])->all()
        );

        return redirect()
            ->route('kpis.index', ['fy' => $fyLabel])
            ->with('status', "KPI saved for {$shakha->name} ({$fyLabel}).");
    }

    public function export(Request $request): StreamedResponse
    {
        $fyLabel = $this->resolveFy($request->string('fy')->toString() ?: null);

        return $this->excelExporter->download($fyLabel);
    }

    protected function resolveFy(?string $fyLabel): string
    {
        if ($fyLabel && preg_match('/^\d{4}-\d{4}$/', $fyLabel)) {
            try {
                return FinancialYear::fromLabel($fyLabel)->label;
            } catch (\Throwable) {
                // fall through
            }
        }

        return FinancialYear::current()->label;
    }

    /**
     * @return list<string>
     */
    protected function fyOptions(): array
    {
        $current = FinancialYear::current();

        return [
            $current->previous()->label,
            $current->label,
            $current->next()->label,
        ];
    }
}
