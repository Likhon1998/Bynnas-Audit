<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class HqDepartment extends Model
{
    protected $fillable = [
        'name',
        'status',
        'sort_order',
    ];

    public function schedules(): MorphMany
    {
        return $this->morphMany(PlanSchedule::class, 'schedulable');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
