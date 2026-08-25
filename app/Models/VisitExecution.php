<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitExecution extends Model
{
    public const STATUS_PLANNED = 'planned';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_DELAYED = 'delayed';

    public const STATUS_RESCHEDULED = 'rescheduled';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'monthly_assignment_id',
        'status',
        'actual_start_date',
        'actual_end_date',
        'actual_duration_days',
        'actual_employee_id',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'actual_start_date' => 'date',
            'actual_end_date' => 'date',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(MonthlyAssignment::class, 'monthly_assignment_id');
    }

    public function actualEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'actual_employee_id');
    }
}
