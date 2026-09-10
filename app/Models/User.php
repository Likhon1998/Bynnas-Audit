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
        'mail_from_email',
        'password',
        'is_superadmin',
        'access_profile',
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
        $role = $this->access_profile
            ?: $this->getRoleNames()->first()
            ?: ($this->is_superadmin ? 'superadmin' : null);

        if ($role && \App\Support\RoleAccess::isPersonalAccessRole((string) $role)) {
            $role = $this->access_profile ?: null;
        }

        return $role ? \App\Support\RoleAccess::label((string) $role) : '—';
    }

    public function roleKey(): string
    {
        if ($this->access_profile) {
            return (string) $this->access_profile;
        }

        $role = (string) ($this->getRoleNames()->first() ?: ($this->is_superadmin ? 'superadmin' : ''));
        if ($role !== '' && \App\Support\RoleAccess::isPersonalAccessRole($role)) {
            return '';
        }

        return $role;
    }

    /**
     * Effective permission names (role + direct).
     *
     * @return list<string>
     */
    public function grantedPermissionNames(): array
    {
        return $this->getAllPermissions()
            ->pluck('name')
            ->map(fn ($n) => (string) $n)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Email used as sender when this user emails audit reports.
     */
    public function mailSenderAddress(): string
    {
        $mail = trim((string) ($this->mail_from_email ?? ''));

        return $mail !== '' ? $mail : (string) $this->email;
    }
}
