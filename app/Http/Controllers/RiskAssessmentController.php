<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShakhaRiskAssessmentRequest;
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
        $month = (int) $request->integer('month', now()->month);
        $year = (int) $request->integer('year', now()->year);
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

        return view('shakhas.risk.create', [
            'shakha' => $shakha->load('area'),
            'month' => $month,
            'year' => $year,
            'fy' => $fy,
            'kpi' => $kpi,
            'existing' => $existing,
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
}
