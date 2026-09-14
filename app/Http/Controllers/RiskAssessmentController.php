<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShakhaRiskAssessmentRequest;
use App\Models\RiskLaw;
use App\Models\Shakha;
use App\Services\RiskAnalysisExcelExporter;
use App\Services\RiskAssessmentService;
use App\Support\FinancialYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class RiskAssessmentController extends Controller
{
    public function __construct(
        private RiskAssessmentService $riskAssessments,
        private RiskAnalysisExcelExporter $excelExporter,
    ) {}

    public function create(Request $request, Shakha $shakha): View
    {
        $latest = $shakha->latestRiskAssessment;
        $month = $request->filled('month')
            ? (int) $request->integer('month')
            : (int) ($latest?->assessment_month ?: now()->month);
        $year = $request->filled('year')
            ? (int) $request->integer('year')
            : (int) ($latest?->assessment_year ?: now()->year);

        $fy = $this->riskAssessments->fyForPeriod($month, $year);
        $kpi = $this->riskAssessments->findAnnualKpi($shakha, $fy->label);

        $existing = $shakha->riskAssessments()
            ->where('assessment_month', $month)
            ->where('assessment_year', $year)
            ->first();

        $otr = null;
        $dr = null;
        $nplr = null;
        $surplus = null;
        $totalOdTaka = null;
        if ($kpi) {
            $ratios = $this->riskAssessments->syncedRatios($kpi);
            $otr = $ratios['otr'];
            $dr = $ratios['dr'];
            $nplr = $ratios['nplr'];
            $surplus = $ratios['surplus'];
            $totalOdTaka = $ratios['total_od_taka'];
        }

        $preview = null;
        if ($kpi?->isReadyForRisk()) {
            try {
                $hasBoth = old('has_both_bm_and_abm');
                $hasBoth = $hasBoth === null
                    ? (bool) ($existing?->has_both_bm_and_abm ?? true)
                    : (string) $hasBoth === '1';

                $special = old('special_audit_last_two_years');
                $special = $special === null
                    ? (bool) ($existing?->special_audit_last_two_years ?? true)
                    : (string) $special === '1';

                $preview = $this->riskAssessments->previewScore($shakha, $month, $year, [
                    'total_income' => old('total_income', $existing?->total_income ?? 0),
                    'total_expenditure' => old('total_expenditure', $existing?->total_expenditure ?? 0),
                    'write_off_principal_amount' => old('write_off_principal_amount', $existing?->write_off_principal_amount ?? 0),
                    'savings_adjustment_amount' => old('savings_adjustment_amount', $existing?->savings_adjustment_amount ?? 0),
                    'distance_from_area_office_km' => old(
                        'distance_from_area_office_km',
                        (($existing?->distance_from_area_office_km ?? 0) > 0) ? 1 : 0
                    ),
                    'has_both_bm_and_abm' => $hasBoth,
                    'special_audit_last_two_years' => $special,
                ]);
            } catch (RuntimeException) {
                $preview = null;
            }
        }

        return view('shakhas.risk.create', [
            'shakha' => $shakha->load('area'),
            'month' => $month,
            'year' => $year,
            'fy' => $fy,
            'kpi' => $kpi,
            'kpiReady' => $kpi?->isReadyForRisk() ?? false,
            'existing' => $existing,
            'latest' => $latest,
            'preview' => $preview,
            'otr' => $otr,
            'dr' => $dr,
            'nplr' => $nplr,
            'surplus' => $surplus,
            'totalOdTaka' => $totalOdTaka,
        ]);
    }

    public function store(StoreShakhaRiskAssessmentRequest $request, Shakha $shakha): RedirectResponse
    {
        $data = $request->validated();
        $month = (int) $data['assessment_month'];
        $year = (int) $data['assessment_year'];

        try {
            $assessment = $this->riskAssessments->calculateRiskScore(
                $shakha,
                $month,
                $year,
                $data
            );
        } catch (RuntimeException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['kpi' => $e->getMessage()]);
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['kpi' => 'Unable to save risk assessment. Please try again.']);
        }

        return redirect()
            ->route('shakhas.index')
            ->with(
                'status',
                $shakha->name.' assessed as '.$assessment->risk_category
                .' (score '.$assessment->total_weighted_score.') for '
                .$assessment->periodLabel().'.'
            );
    }

    public function export(Request $request): StreamedResponse
    {
        $fyLabel = $request->string('fy')->toString();
        if (! $fyLabel || ! preg_match('/^\d{4}-\d{4}$/', $fyLabel)) {
            $fyLabel = FinancialYear::current()->label;
        }

        return $this->excelExporter->download($fyLabel);
    }

    public function laws(): View
    {
        $laws = RiskLaw::ordered();

        return view('shakhas.risk.laws', [
            'laws' => $laws,
            'groupLabels' => [
                RiskLaw::GROUP_RATIOS => 'Ratio scoring',
                RiskLaw::GROUP_FLAGS => 'Yes / No factors',
                RiskLaw::GROUP_CATEGORY => 'Final category',
            ],
        ]);
    }

    public function updateLaws(Request $request): RedirectResponse
    {
        RiskLaw::ensureDefaults();

        $validated = $request->validate([
            'laws' => ['required', 'array'],
            'laws.*.id' => ['required', 'integer', 'exists:risk_laws,id'],
            'laws.*.label' => ['required', 'string', 'max:120'],
            'laws.*.description' => ['nullable', 'string', 'max:1000'],
            'laws.*.is_active' => ['nullable', 'boolean'],
            'laws.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'laws.*.bands' => ['nullable', 'array'],
            'laws.*.bands.*.op' => ['nullable', 'string', 'max:16'],
            'laws.*.bands.*.value' => ['nullable', 'numeric'],
            'laws.*.bands.*.points' => ['nullable', 'integer', 'min:0', 'max:100'],
            'laws.*.bands.*.label' => ['nullable', 'string', 'max:64'],
            'laws.*.true_points' => ['nullable', 'integer', 'min:0', 'max:100'],
            'laws.*.false_points' => ['nullable', 'integer', 'min:0', 'max:100'],
            'laws.*.true_label' => ['nullable', 'string', 'max:120'],
            'laws.*.false_label' => ['nullable', 'string', 'max:120'],
        ]);

        foreach ($validated['laws'] as $row) {
            $law = RiskLaw::query()->findOrFail($row['id']);
            $bands = $law->bands;

            if ($law->unit === 'boolean') {
                $bands = [
                    'true_points' => (int) ($row['true_points'] ?? ($bands['true_points'] ?? 0)),
                    'false_points' => (int) ($row['false_points'] ?? ($bands['false_points'] ?? 0)),
                    'true_label' => (string) ($row['true_label'] ?? ($bands['true_label'] ?? 'Yes')),
                    'false_label' => (string) ($row['false_label'] ?? ($bands['false_label'] ?? 'No')),
                ];
            } elseif (isset($row['bands']) && is_array($row['bands'])) {
                $bands = [];
                foreach ($row['bands'] as $band) {
                    $op = (string) ($band['op'] ?? 'default');
                    if ($law->unit === 'category') {
                        $bands[] = $op === 'default'
                            ? ['op' => 'default', 'label' => (string) ($band['label'] ?? 'Significant Risk')]
                            : [
                                'op' => $op,
                                'value' => (float) ($band['value'] ?? 0),
                                'label' => (string) ($band['label'] ?? ''),
                            ];
                    } else {
                        $bands[] = $op === 'default'
                            ? ['op' => 'default', 'points' => (int) ($band['points'] ?? 0)]
                            : [
                                'op' => $op,
                                'value' => (float) ($band['value'] ?? 0),
                                'points' => (int) ($band['points'] ?? 0),
                            ];
                    }
                }
            }

            $law->update([
                'label' => $row['label'],
                'description' => $row['description'] ?? null,
                'is_active' => (bool) ($row['is_active'] ?? false),
                'sort_order' => (int) ($row['sort_order'] ?? $law->sort_order),
                'bands' => $bands,
            ]);
        }

        return redirect()
            ->route('shakhas.risk.laws')
            ->with('status', 'Risk analysis laws saved. New assessments use these rules.');
    }

    public function resetLaws(): RedirectResponse
    {
        RiskLaw::resetToDefaults();

        return redirect()
            ->route('shakhas.risk.laws')
            ->with('status', 'Risk analysis laws restored to the standard scoring matrix.');
    }
}
