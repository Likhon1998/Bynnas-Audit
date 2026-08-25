<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MonthlyWorkItem extends Model
{
    public const SOURCE_YEARLY = 'yearly';

    public const SOURCE_SPECIAL = 'special';

    public const STATUS_UNASSIGNED = 'unassigned';

    public const STATUS_ASSIGNED = 'assigned';

    protected $fillable = [
        'audit_plan_id',
        'fy_label',
        'month_index',
        'category',
        'activity_type_id',
        'schedulable_type',
        'schedulable_id',
        'plan_schedule_id',
        'source',
        'status',
        'entity_label',
        'notes',
        'created_by',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(AuditPlan::class, 'audit_plan_id');
    }

    public function activityType(): BelongsTo
    {
        return $this->belongsTo(ActivityType::class);
    }

    public function schedulable(): MorphTo
    {
        return $this->morphTo();
    }

    public function planSchedule(): BelongsTo
    {
        return $this->belongsTo(PlanSchedule::class);
    }

    public function assignment(): HasOne
    {
        return $this->hasOne(MonthlyAssignment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isSpecial(): bool
    {
        return $this->source === self::SOURCE_SPECIAL;
    }

    public function isAssigned(): bool
    {
        return $this->status === self::STATUS_ASSIGNED;
    }
}
