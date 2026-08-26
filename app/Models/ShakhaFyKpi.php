<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShakhaFyKpi extends Model
{
    protected $table = 'shakha_fy_kpis';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'field_officer_count' => 'integer',
            'total_samities' => 'integer',
            'total_members' => 'integer',
            'total_borrowers' => 'integer',
            'total_od_borrowers' => 'integer',
            'fy_savings_collection' => 'decimal:2',
            'fy_savings_withdrawal' => 'decimal:2',
            'savings_balance' => 'decimal:2',
            'fy_members_admitted' => 'integer',
            'fy_members_dropout' => 'integer',
            'fy_disbursement_borrowers' => 'integer',
            'fy_fully_repayment_borrowers' => 'integer',
            'fy_disbursement_amount' => 'decimal:2',
            'fy_loan_recovery' => 'decimal:2',
            'loan_outstanding' => 'decimal:2',
            'recoverable' => 'decimal:2',
            'current_recovery' => 'decimal:2',
            'total_od_taka' => 'decimal:2',
            'due_loanee_loan_outstanding' => 'decimal:2',
            'own_fund_until_prior_june' => 'decimal:2',
            'surplus_deficit_fy' => 'decimal:2',
            'new_due' => 'decimal:2',
            'due_increase_this_month' => 'decimal:2',
        ];
    }

    public function shakha(): BelongsTo
    {
        return $this->belongsTo(Shakha::class);
    }

    /** @return list<string> */
    public static function inputFields(): array
    {
        return [
            'field_officer_count',
            'total_samities',
            'total_members',
            'total_borrowers',
            'total_od_borrowers',
            'fy_savings_collection',
            'fy_savings_withdrawal',
            'savings_balance',
            'fy_members_admitted',
            'fy_members_dropout',
            'fy_disbursement_borrowers',
            'fy_fully_repayment_borrowers',
            'fy_disbursement_amount',
            'fy_loan_recovery',
            'loan_outstanding',
            'recoverable',
            'current_recovery',
            'total_od_taka',
            'due_loanee_loan_outstanding',
            'own_fund_until_prior_june',
            'surplus_deficit_fy',
            'new_due',
            'due_increase_this_month',
            'focal_person_name',
        ];
    }
}
