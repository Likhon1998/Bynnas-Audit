<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditReportReviewAnnotation extends Model
{
    public const TYPE_TEXT = 'text';

    public const TYPE_AREA = 'area';

    public const COLORS = ['yellow', 'rose', 'sky', 'lime', 'orange'];

    protected $fillable = [
        'audit_report_id',
        'user_id',
        'review_round',
        'type',
        'color',
        'quote',
        'prefix',
        'suffix',
        'body',
        'rect_x',
        'rect_y',
        'rect_w',
        'rect_h',
        'snapshot_path',
        'addressed_at',
    ];

    protected function casts(): array
    {
        return [
            'review_round' => 'integer',
            'rect_x' => 'float',
            'rect_y' => 'float',
            'rect_w' => 'float',
            'rect_h' => 'float',
            'addressed_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(AuditReport::class, 'audit_report_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isArea(): bool
    {
        return $this->type === self::TYPE_AREA;
    }

    public function snapshotUrl(): ?string
    {
        if (! $this->snapshot_path) {
            return null;
        }

        return asset('storage/'.$this->snapshot_path);
    }

    public function cssBackground(): string
    {
        return match ($this->color) {
            'rose' => '#fecdd3',
            'sky' => '#bae6fd',
            'lime' => '#bef264',
            'orange' => '#fed7aa',
            default => '#fef08a',
        };
    }

    public function cssBorder(): string
    {
        return match ($this->color) {
            'rose' => '#e11d48',
            'sky' => '#0284c7',
            'lime' => '#65a30d',
            'orange' => '#ea580c',
            default => '#ca8a04',
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function toReviewPayload(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type ?: self::TYPE_TEXT,
            'color' => $this->color,
            'quote' => $this->quote,
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
            'body' => $this->body,
            'rect_x' => $this->rect_x,
            'rect_y' => $this->rect_y,
            'rect_w' => $this->rect_w,
            'rect_h' => $this->rect_h,
            'snapshot_url' => $this->snapshotUrl(),
            'author' => $this->user?->name ?: 'Reviewer',
            'bg' => $this->cssBackground(),
            'border' => $this->cssBorder(),
            'review_round' => (int) ($this->review_round ?: 1),
            'addressed' => $this->addressed_at !== null,
            'created_at' => \App\Support\AppTime::dateTime($this->created_at),
        ];
    }
}
