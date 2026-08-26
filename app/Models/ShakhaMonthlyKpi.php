<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShakhaMonthlyKpi extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'report_month' => 'integer',
            'report_year' => 'integer',
            'total_samities' => 'integer',
            'total_members' => 'integer',
            'total_borrowers' => 'integer',
            'total_od_borrowers' => 'integer',
            'monthly_members_admitted' => 'integer',
            'monthly_members_dropout' => 'integer',
            'field_officer_count' => 'integer',
            'savings_balance' => 'decimal:2',
            'loan_outstanding' => 'decimal:2',
            'total_od_taka' => 'decimal:2',
            'monthly_savings_collection' => 'decimal:2',
            'monthly_savings_withdrawal' => 'decimal:2',
            'monthly_disbursement_amount' => 'decimal:2',
            'monthly_loan_recovery' => 'decimal:2',
            'monthly_current_recovery' => 'decimal:2',
            'monthly_recoverable' => 'decimal:2',
            'due_loanee_loan_outstanding' => 'decimal:2',
        ];
    }

    public function shakha(): BelongsTo
    {
        return $this->belongsTo(Shakha::class);
    }

    public function periodLabel(): string
    {
        $month = str_pad((string) $this->report_month, 2, '0', STR_PAD_LEFT);

        return date('F', mktime(0, 0, 0, (int) $this->report_month, 1)).'-'.$this->report_year;
    }
}
