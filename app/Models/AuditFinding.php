<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditFinding extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'audit_month' => 'integer',
            'audit_year' => 'integer',
            'amount' => 'decimal:2',
            'sample_size_checked' => 'integer',
            'irregularity_count' => 'integer',
        ];
    }

    public function shakha(): BelongsTo
    {
        return $this->belongsTo(Shakha::class);
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(AuditIndicator::class, 'audit_indicator_id');
    }

    public function hasIrregularity(): bool
    {
        return (int) ($this->irregularity_count ?? 0) > 0
            || (float) ($this->amount ?? 0) > 0
            || filled($this->observation);
    }
}
