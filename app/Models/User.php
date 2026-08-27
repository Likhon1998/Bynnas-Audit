<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_superadmin',
        'is_active',
        'employee_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_superadmin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function assignedShakhas(): BelongsToMany
    {
        return $this->belongsToMany(Shakha::class, 'user_shakha')->withTimestamps();
    }

    public function auditReports(): HasMany
    {
        return $this->hasMany(AuditReport::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin') || (bool) $this->is_superadmin;
    }

    public function isActiveAccount(): bool
    {
        return (bool) ($this->is_active ?? true);
    }

    public function roleLabel(): string
    {
        return $this->getRoleNames()->first() ?: ($this->is_superadmin ? 'superadmin' : '—');
    }
}
