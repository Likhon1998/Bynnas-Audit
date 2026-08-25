<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanExecution extends Model
{
    protected $fillable = [
        'plan_schedule_id',
        'actual_date',
        'status',
        'remarks',
        'completed_by',
    ];

    protected function casts(): array
    {
        return [
            'actual_date' => 'date',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(PlanSchedule::class, 'plan_schedule_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
