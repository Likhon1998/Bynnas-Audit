<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditChecklistFormat extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'format_number' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AuditChecklistSubmission::class, 'audit_checklist_format_id');
    }
}
