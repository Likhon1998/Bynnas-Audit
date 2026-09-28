<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceMark extends Model
{
    protected $fillable = [
        'user_id',
        'performance_rule_id',
        'label',
        'points',
        'note',
        'awarded_on',
        'awarded_by',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'float',
            'awarded_on' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(PerformanceRule::class, 'performance_rule_id');
    }

    public function awardedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }
}
