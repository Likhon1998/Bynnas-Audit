<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShakhaMonthlyKpiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'report_month' => ['required', 'integer', 'min:1', 'max:12'],
            'report_year' => ['required', 'integer', 'min:2000', 'max:2100'],

            'total_samities' => ['required', 'numeric', 'min:0'],
            'total_members' => ['required', 'numeric', 'min:0'],
            'total_borrowers' => ['required', 'numeric', 'min:0'],
            'total_od_borrowers' => ['required', 'numeric', 'min:0'],
            'monthly_members_admitted' => ['required', 'numeric', 'min:0'],
            'monthly_members_dropout' => ['required', 'numeric', 'min:0'],
            'field_officer_count' => ['required', 'numeric', 'min:0'],

            'savings_balance' => ['required', 'numeric', 'min:0'],
            'loan_outstanding' => ['required', 'numeric', 'min:0'],
            'total_od_taka' => ['required', 'numeric', 'min:0'],
            'monthly_savings_collection' => ['required', 'numeric', 'min:0'],
            'monthly_savings_withdrawal' => ['required', 'numeric', 'min:0'],
            'monthly_disbursement_amount' => ['required', 'numeric', 'min:0'],
            'monthly_loan_recovery' => ['required', 'numeric', 'min:0'],
            'monthly_current_recovery' => ['required', 'numeric', 'min:0'],
            'monthly_recoverable' => ['required', 'numeric', 'min:0'],
            'due_loanee_loan_outstanding' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'report_month.min' => 'Report month must be between 1 and 12.',
            'report_month.max' => 'Report month must be between 1 and 12.',
            'report_year.min' => 'Enter a valid report year.',
            'report_year.max' => 'Enter a valid report year.',
        ];
    }
}
