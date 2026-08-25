<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'name',
        'donor',
        'is_pksf',
        'is_maternity',
        'has_project_audit',
        'has_project_monitoring',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_pksf' => 'boolean',
            'is_maternity' => 'boolean',
            'has_project_audit' => 'boolean',
            'has_project_monitoring' => 'boolean',
        ];
    }

    public function locations(): HasMany
    {
        return $this->hasMany(ProjectLocation::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Best Annual Audit tab for deep-linking this project.
     */
    public function preferredPlanTab(): string
    {
        if ($this->is_pksf || $this->is_maternity) {
            return 'pksf';
        }

        if ($this->has_project_audit) {
            return 'project_audit';
        }

        if ($this->has_project_monitoring) {
            return 'project_monitoring';
        }

        return 'project_audit';
    }
}
