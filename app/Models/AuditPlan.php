<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditPlan extends Model
{
    protected $fillable = [
        'name',
        'fy_label',
        'start_date',
        'end_date',
        'status',
        'generated_at',
        'published_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'generated_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function policies(): HasMany
    {
        return $this->hasMany(AuditPolicy::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(PlanSchedule::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
