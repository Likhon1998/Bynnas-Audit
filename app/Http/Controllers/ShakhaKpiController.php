<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShakhaAnnualKpiRequest;
use App\Models\KpiLaw;
use App\Models\Shakha;
use App\Models\ShakhaAnnualKpi;
use App\Services\KpiReportService;
use App\Services\ShakhaKpiExcelExporter;
use App\Support\FinancialYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

    public function laws(): View
    {
        $laws = KpiLaw::ordered();

        return view('kpis.laws', [
            'laws' => $laws,
            'operandOptions' => KpiLaw::operandOptions(),
            'categoryLabels' => [
                'increase' => 'Increases',
                'fund' => 'Funds',
                'percentage' => 'Percentages',
                'ratio' => 'Ratios',
            ],
        ]);
    }

    public function updateLaws(Request $request): RedirectResponse
    {
        KpiLaw::ensureDefaults();

        $operandKeys = array_keys(KpiLaw::operandOptions());
        $validated = $request->validate([
            'laws' => ['required', 'array'],
            'laws.*.id' => ['required', 'integer', 'exists:kpi_laws,id'],
            'laws.*.label' => ['required', 'string', 'max:120'],
            'laws.*.operation' => ['required', Rule::in([
                KpiLaw::OPERATION_ADD,
                KpiLaw::OPERATION_SUBTRACT,
                KpiLaw::OPERATION_DIVIDE,
            ])],
            'laws.*.left_operand' => ['required', Rule::in($operandKeys)],
            'laws.*.right_operand' => ['required', Rule::in($operandKeys)],
            'laws.*.description' => ['nullable', 'string', 'max:1000'],
            'laws.*.format' => ['required', Rule::in(['money', 'int', 'pct', 'ratio'])],
            'laws.*.is_active' => ['nullable', 'boolean'],
            'laws.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        foreach ($validated['laws'] as $row) {
            $law = KpiLaw::query()->findOrFail($row['id']);
            $law->fill([
                'label' => $row['label'],
                'operation' => $row['operation'],
                'left_operand' => $row['left_operand'],
                'right_operand' => $row['right_operand'],
                'description' => $row['description'] ?? null,
                'format' => $row['format'],
                'is_active' => (bool) ($row['is_active'] ?? false),
                'sort_order' => (int) ($row['sort_order'] ?? $law->sort_order),
            ]);
            $law->formula_display = $law->rebuildFormulaDisplay();
            $law->save();
        }

        return redirect()
            ->route('kpis.laws')
            ->with('status', 'KPI laws saved. Excel export and calculated ratios now use these rules.');
    }

    public function resetLaws(): RedirectResponse
    {
        KpiLaw::resetToDefaults();

        return redirect()
            ->route('kpis.laws')
            ->with('status', 'KPI laws restored to the standard formulas.');
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
