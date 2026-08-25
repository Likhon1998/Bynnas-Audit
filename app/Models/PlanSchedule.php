<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PlanSchedule extends Model
{
    protected $fillable = [
        'audit_plan_id',
        'category',
        'schedulable_type',
        'schedulable_id',
        'month_index',
        'planned_date',
        'occurrence',
        'status',
        'is_manual',
        'auditor_id',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'planned_date' => 'date',
            'is_manual' => 'boolean',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(AuditPlan::class, 'audit_plan_id');
    }

    public function schedulable(): MorphTo
    {
        return $this->morphTo();
    }

    public function execution(): HasOne
    {
        return $this->hasOne(PlanExecution::class);
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }
}
