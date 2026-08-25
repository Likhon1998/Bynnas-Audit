<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProjectLocation extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'division',
        'status',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function schedules(): MorphMany
    {
        return $this->morphMany(PlanSchedule::class, 'schedulable');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
