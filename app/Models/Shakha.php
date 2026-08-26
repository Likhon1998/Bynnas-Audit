<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Shakha extends Model
{
    use HasFactory;

    protected $fillable = [
        'area_id',
        'name',
        'code',
        'status',
        'opening_date',
        'opened_at',
        'focal_person_name',
    ];

    protected function casts(): array
    {
        return [
            'opening_date' => 'date',
            'opened_at' => 'date',
        ];
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function monthlyKpis(): HasMany
    {
        return $this->hasMany(ShakhaMonthlyKpi::class);
    }

    public function annualKpis(): HasMany
    {
        return $this->hasMany(ShakhaAnnualKpi::class);
    }

    public function riskAssessments(): HasMany
    {
        return $this->hasMany(ShakhaRiskAssessment::class);
    }

    public function auditReports(): HasMany
    {
        return $this->hasMany(AuditReport::class);
    }

    public function latestRiskAssessment(): HasOne
    {
        return $this->hasOne(ShakhaRiskAssessment::class)->latestOfMany([
            'assessment_year',
            'assessment_month',
        ]);
    }

    public function schedules(): MorphMany
    {
        return $this->morphMany(PlanSchedule::class, 'schedulable');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
