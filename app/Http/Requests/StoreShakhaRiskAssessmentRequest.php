<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShakhaRiskAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'distance_from_area_office_km' => $this->boolean('distance_from_area_office_km'),
            'has_both_bm_and_abm' => $this->boolean('has_both_bm_and_abm'),
            'special_audit_last_two_years' => $this->boolean('special_audit_last_two_years'),
        ]);
    }

    public function rules(): array
    {
        $money = ['required', 'numeric', 'min:0'];

        return [
            'assessment_month' => ['required', 'integer', 'between:1,12'],
            'assessment_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            // Manual (not on KPI)
            'total_income' => $money,
            'total_expenditure' => $money,
            'write_off_principal_amount' => $money,
            'savings_adjustment_amount' => $money,
            // overdue_principal ← KPI total_od_taka (auto)
            'distance_from_area_office_km' => ['required', 'boolean'],
            'has_both_bm_and_abm' => ['required', 'boolean'],
            'special_audit_last_two_years' => ['required', 'boolean'],
        ];
    }
}
