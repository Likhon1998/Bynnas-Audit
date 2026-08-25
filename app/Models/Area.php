<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'division',
        'status',
    ];

    public function shakhas(): HasMany
    {
        return $this->hasMany(Shakha::class);
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
