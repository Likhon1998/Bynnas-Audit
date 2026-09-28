<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditReviewerMonthAssignment extends Model
{
    protected $fillable = [
        'auditor_user_id',
        'reviewer_user_id',
        'year',
        'month',
        'assigned_by',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
    ];

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auditor_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }

    public function assignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
