<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShakhaRiskAssessment extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'assessment_month' => 'integer',
            'assessment_year' => 'integer',
            'distance_from_area_office_km' => 'integer',
            'total_income' => 'decimal:2',
            'total_expenditure' => 'decimal:2',
            'write_off_principal_amount' => 'decimal:2',
            'savings_adjustment_amount' => 'decimal:2',
            'overdue_principal_31_365_days' => 'decimal:2',
            'has_both_bm_and_abm' => 'boolean',
            'special_audit_last_two_years' => 'boolean',
            'total_weighted_score' => 'integer',
        ];
    }

    public function shakha(): BelongsTo
    {
        return $this->belongsTo(Shakha::class);
    }

    public function periodLabel(): string
    {
        return date('F', mktime(0, 0, 0, (int) $this->assessment_month, 1)).' '.$this->assessment_year;
    }
}
