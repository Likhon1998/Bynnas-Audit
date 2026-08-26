<?php

namespace App\Services;

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
            ->with(['area', 'annualKpis' => fn ($q) => $q->where('fy_label', $fyLabel)])
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

        return [
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
            'fy_savings_increase' => $savingsColl - $savingsWd,
            'savings_balance' => $savingsBal,
            'fy_members_admission' => $admission,
            'fy_members_dropout' => $dropout,
            'fy_members_increase' => $admission - $dropout,
            'fy_disbursement_borrowers' => $disbBorrowers,
            'fy_fully_repayment_borrowers' => $repayBorrowers,
            'fy_borrowers_increase' => $disbBorrowers - $repayBorrowers,
            'fy_disbursement_amount' => $disbAmt,
            'fy_loan_recovery' => $loanRec,
            'fy_loan_outstanding_increase' => $disbAmt - $loanRec,
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
            'total_surplus_deficit' => $ownFund + $surplus,
            'new_due' => $newDue,
            'due_increase_this_month' => $dueInc,
            'otr' => $this->safeDivide($currentRec, $recoverable),
            'dr_borrowers' => $this->safeDivide($odBorrowers, $borrowers),
            'dr_taka' => $this->safeDivide($odTaka, $loanOs),
            'par' => $this->safeDivide($dueLoaneeOs, $loanOs),
            'overdue_growth_vs_outstanding' => $this->safeDivide($dueInc, $loanOs),
            'due_recovery_pct' => $this->safeDivide($dueRec, $odTaka),
            'member_loanee' => $this->safeDivide($borrowers, $members),
            'savings_loan' => $this->safeDivide($savingsBal, $loanOs),
            'dropout_pct' => $this->safeDivide($dropout, $admission),
            'savings_withdrawal_pct' => $this->safeDivide($savingsWd, $savingsColl),
            'samities_member' => $this->safeDivide($members, $samities),
            'samities_borrowers' => $this->safeDivide($borrowers, $samities),
            'fo_member' => $this->safeDivide($members, $fo),
            'fo_borrowers' => $this->safeDivide($borrowers, $fo),
            'fo_savings' => $this->safeDivide($savingsBal, $fo),
            'fo_loan' => $this->safeDivide($loanOs, $fo),
            'member_savings' => $this->safeDivide($savingsBal, $members),
            'borrowers_loan' => $this->safeDivide($loanOs, $borrowers),
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
