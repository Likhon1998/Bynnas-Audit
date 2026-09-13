<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditReportReviewSnapshot extends Model
{
    protected $fillable = [
        'audit_report_id',
        'review_round',
        'is_resubmit',
        'submitted_by',
        'pages_fingerprint',
        'pages_data',
        'maker_note',
        'addressed_annotation_ids',
        'change_summary',
    ];

    protected function casts(): array
    {
        return [
            'is_resubmit' => 'boolean',
            'review_round' => 'integer',
            'pages_data' => 'array',
            'addressed_annotation_ids' => 'array',
            'change_summary' => 'array',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(AuditReport::class, 'audit_report_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
