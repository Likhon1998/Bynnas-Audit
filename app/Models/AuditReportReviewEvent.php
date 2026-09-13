<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditReportReviewEvent extends Model
{
    public const ACTION_SUBMITTED = 'submitted';

    public const ACTION_RETURNED = 'returned';

    public const ACTION_RESUBMITTED = 'resubmitted';

    public const ACTION_APPROVED = 'approved';

    public const ACTION_NOTE = 'note';

    protected $fillable = [
        'audit_report_id',
        'actor_user_id',
        'action',
        'review_round',
        'body',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'review_round' => 'integer',
            'meta' => 'array',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(AuditReport::class, 'audit_report_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            self::ACTION_SUBMITTED => 'Sent for 1st review',
            self::ACTION_RETURNED => 'Sent back for changes',
            self::ACTION_RESUBMITTED => 'Sent for re-review (after changes)',
            self::ACTION_APPROVED => 'Confirmed / locked',
            self::ACTION_NOTE => 'Note',
            default => (string) $this->action,
        };
    }
}
