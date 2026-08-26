<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShakhaAnnualKpi extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'fo_count' => 'integer',
            'total_samities' => 'integer',
            'total_members' => 'integer',
            'total_borrowers' => 'integer',
            'total_od_borrowers' => 'integer',
            'fy_members_admission' => 'integer',
            'fy_members_dropout' => 'integer',
            'fy_disbursement_borrowers' => 'integer',
            'fy_fully_repayment_borrowers' => 'integer',
            'fy_savings_collection' => 'decimal:2',
            'fy_savings_withdrawal' => 'decimal:2',
            'savings_balance' => 'decimal:2',
            'fy_disbursement_amount' => 'decimal:2',
            'fy_loan_recovery' => 'decimal:2',
            'loan_outstanding' => 'decimal:2',
            'recoverable' => 'decimal:2',
            'current_recovery' => 'decimal:2',
            'due_recovery' => 'decimal:2',
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
}
