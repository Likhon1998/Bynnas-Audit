<?php

namespace App\Services;

use App\Models\KpiLaw;
use App\Models\Shakha;
use App\Models\ShakhaAnnualKpi;
use App\Support\FinancialYear;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class KpiReportService
{
    /**
     * @return Collection<int, Shakha>
     */
    public function shakhasForFy(string $fyLabel, bool $onlyEntered = false): Collection
    {
        $query = Shakha::query()
            ->join('areas', 'areas.id', '=', 'shakhas.area_id')
            ->with(['area', 'latestRiskAssessment', 'annualKpis' => fn ($q) => $q->where('fy_label', $fyLabel)])
            ->where('shakhas.status', 'active')
            ->orderBy('areas.division')
            ->orderBy('areas.name')
            ->orderBy('shakhas.code')
            ->orderBy('shakhas.name')
            ->select('shakhas.*');

        if ($onlyEntered) {
            $query->whereHas('annualKpis', fn ($q) => $q->where('fy_label', $fyLabel));
        }

        return $query->get();
    }

    public function getForFy(Shakha $shakha, string $fyLabel): ?ShakhaAnnualKpi
    {
        return ShakhaAnnualKpi::query()
            ->where('shakha_id', $shakha->id)
            ->where('fy_label', $fyLabel)
            ->first();
    }

    /**
     * Compile one export row (raw + calculated) matching the Excel template.
     *
     * @return array<string, mixed>
     */
    public function compileRow(Shakha $shakha, ?ShakhaAnnualKpi $kpi, string $fyLabel, int $serial, Carbon $asOf): array
    {
        $fy = FinancialYear::fromLabel($fyLabel);
        $n = fn ($v) => (float) ($v ?? 0);
        $i = fn ($v) => (int) ($v ?? 0);

        $fo = $i($kpi?->fo_count);
        $samities = $i($kpi?->total_samities);
        $members = $i($kpi?->total_members);
        $borrowers = $i($kpi?->total_borrowers);
        $odBorrowers = $i($kpi?->total_od_borrowers);
        $admission = $i($kpi?->fy_members_admission);
        $dropout = $i($kpi?->fy_members_dropout);
        $disbBorrowers = $i($kpi?->fy_disbursement_borrowers);
        $repayBorrowers = $i($kpi?->fy_fully_repayment_borrowers);

        $savingsColl = $n($kpi?->fy_savings_collection);
        $savingsWd = $n($kpi?->fy_savings_withdrawal);
        $savingsBal = $n($kpi?->savings_balance);
        $disbAmt = $n($kpi?->fy_disbursement_amount);
        $loanRec = $n($kpi?->fy_loan_recovery);
        $loanOs = $n($kpi?->loan_outstanding);
        $recoverable = $n($kpi?->recoverable);
        $currentRec = $n($kpi?->current_recovery);
        $dueRec = $n($kpi?->due_recovery);
        $odTaka = $n($kpi?->total_od_taka);
        $dueLoaneeOs = $n($kpi?->due_loanee_loan_outstanding);
        $ownFund = $n($kpi?->own_fund_until_prior_june);
        $surplus = $n($kpi?->surplus_deficit_fy);
        $newDue = $n($kpi?->new_due);
        $dueInc = $n($kpi?->due_increase_this_month);

        $opening = $shakha->opening_date ?? $shakha->opened_at;

        $row = [
            'serial' => $serial,
            'code' => $shakha->code,
            'area_name' => $shakha->area?->name,
            'branch_name' => $shakha->name,
            'opening_date' => $opening,
            'fo_count' => $fo,
            'total_samities' => $samities,
            'total_members' => $members,
            'fy_savings_collection' => $savingsColl,
            'fy_savings_withdrawal' => $savingsWd,
            'savings_balance' => $savingsBal,
            'fy_members_admission' => $admission,
            'fy_members_dropout' => $dropout,
            'fy_disbursement_borrowers' => $disbBorrowers,
            'fy_fully_repayment_borrowers' => $repayBorrowers,
            'fy_disbursement_amount' => $disbAmt,
            'fy_loan_recovery' => $loanRec,
            'total_borrowers' => $borrowers,
            'loan_outstanding' => $loanOs,
            'recoverable' => $recoverable,
            'current_recovery' => $currentRec,
            'due_recovery' => $dueRec,
            'total_od_borrowers' => $odBorrowers,
            'total_od_taka' => $odTaka,
            'due_loanee_loan_outstanding' => $dueLoaneeOs,
            'own_fund_until_prior_june' => $ownFund,
            'surplus_deficit_fy' => $surplus,
            'new_due' => $newDue,
            'due_increase_this_month' => $dueInc,
            'today_date' => $asOf->copy()->startOfDay(),
            'opening_year' => $opening ? (int) $opening->year : null,
            'opening_month' => $opening ? (int) $opening->month : null,
            'opening_day' => $opening ? (int) $opening->day : null,
            'focal_person_name' => $shakha->focal_person_name,
            'has_data' => $kpi !== null,
            'fy_label' => $fy->label,
            'prior_june_label' => 'June-'.$fy->startYear(),
            'end_june_label' => 'Jun-'.substr((string) $fy->endDate->year, -2),
        ];

        foreach (KpiLaw::activeOrdered() as $law) {
            $row[$law->key] = $this->applyLaw($law, $row);
        }

        return $row;
    }

    /**
     * Apply one editable KPI law against already-known row values.
     *
     * @param  array<string, mixed>  $row
     */
    protected function applyLaw(KpiLaw $law, array $row): float|int|null
    {
        $left = (float) ($row[$law->left_operand] ?? 0);
        $right = (float) ($row[$law->right_operand] ?? 0);

        $value = match ($law->operation) {
            KpiLaw::OPERATION_ADD => $left + $right,
            KpiLaw::OPERATION_SUBTRACT => $left - $right,
            KpiLaw::OPERATION_DIVIDE => $this->safeDivide($left, $right),
            default => null,
        };

        if ($value === null) {
            return null;
        }

        if ($law->format === 'int') {
            return (int) round($value);
        }

        return $value;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function compileAllRows(string $fyLabel, bool $onlyEntered = false): array
    {
        $fy = FinancialYear::fromLabel($fyLabel);
        $asOf = $fy->endDate->copy();
        $shakhas = $this->shakhasForFy($fyLabel, $onlyEntered);
        $rows = [];
        $serial = 1;

        foreach ($shakhas as $shakha) {
            $kpi = $shakha->annualKpis->first();
            if ($onlyEntered && ! $kpi) {
                continue;
            }
            $rows[] = $this->compileRow($shakha, $kpi, $fyLabel, $serial++, $asOf);
        }

        return $rows;
    }

    /**
     * @return array{entered:int,total:int,fy_label:string}
     */
    public function progress(string $fyLabel): array
    {
        $shakhas = $this->shakhasForFy($fyLabel);

        return [
            'entered' => $shakhas->filter(fn (Shakha $s) => $s->annualKpis->isNotEmpty())->count(),
            'total' => $shakhas->count(),
            'fy_label' => $fyLabel,
        ];
    }

    protected function safeDivide(float $numerator, float $denominator): ?float
    {
        if (abs($denominator) < 0.0000001) {
            return null;
        }

        return $numerator / $denominator;
    }
}
