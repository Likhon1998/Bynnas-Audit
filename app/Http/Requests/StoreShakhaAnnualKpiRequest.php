<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShakhaAnnualKpiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $money = ['required', 'numeric'];
        $count = ['required', 'numeric', 'min:0'];

        return [
            'fy_label' => ['required', 'string', 'regex:/^\d{4}-\d{4}$/'],
            'opening_date' => ['nullable', 'date'],
            'focal_person_name' => ['nullable', 'string', 'max:255'],

            'fo_count' => $count,
            'total_samities' => $count,
            'total_members' => $count,
            'total_borrowers' => $count,
            'total_od_borrowers' => $count,
            'fy_members_admission' => $count,
            'fy_members_dropout' => $count,
            'fy_disbursement_borrowers' => $count,
            'fy_fully_repayment_borrowers' => $count,

            'fy_savings_collection' => $money,
            'fy_savings_withdrawal' => $money,
            'savings_balance' => $money,
            'fy_disbursement_amount' => $money,
            'fy_loan_recovery' => $money,
            'loan_outstanding' => $money,
            'recoverable' => $money,
            'current_recovery' => $money,
            'due_recovery' => $money,
            'total_od_taka' => $money,
            'due_loanee_loan_outstanding' => $money,
            'own_fund_until_prior_june' => $money,
            'surplus_deficit_fy' => $money,
            'new_due' => $money,
            'due_increase_this_month' => $money,
        ];
    }
}
