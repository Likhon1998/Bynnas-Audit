<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditReport extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'audit_start_date' => 'date',
            'audit_end_date' => 'date',
            'draft_sent_date' => 'date',
            'comments_received_date' => 'date',
            'pages_data' => 'array',
            'working_days' => 'integer',
        ];
    }

    public function shakha(): BelongsTo
    {
        return $this->belongsTo(Shakha::class);
    }

    public static function ratingColor(?string $rating): string
    {
        return match ($rating) {
            'Satisfactory' => '#22c55e',
            'Minor' => '#eab308',
            'Medium' => '#f97316',
            'Major' => '#ef4444',
            'Unsatisfactory' => '#b91c1c',
            default => '#f97316',
        };
    }
}
