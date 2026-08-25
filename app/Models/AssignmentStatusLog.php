<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentStatusLog extends Model
{
    protected $fillable = [
        'monthly_assignment_id',
        'from_status',
        'to_status',
        'reason',
        'meta',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(MonthlyAssignment::class, 'monthly_assignment_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
