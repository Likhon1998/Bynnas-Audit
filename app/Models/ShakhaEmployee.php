<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ShakhaEmployee extends Model
{
    protected $fillable = [
        'shakha_id',
        'employee_code',
        'name',
        'designation',
        'phone',
        'email',
        'joined_organization_at',
        'joined_shakha_at',
        'status',
        'sort_order',
        'notes',
        'photo_path',
    ];

    protected function casts(): array
    {
        return [
            'joined_organization_at' => 'date',
            'joined_shakha_at' => 'date',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (self $employee): void {
            $employee->deleteStoredPhoto();
        });
    }

    public function shakha(): BelongsTo
    {
        return $this->belongsTo(Shakha::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function photoUrl(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        if (! Storage::disk('public')->exists($this->photo_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->photo_path);
    }

    public function hasPhoto(): bool
    {
        return $this->photoUrl() !== null;
    }

    public function deleteStoredPhoto(): void
    {
        if (! $this->photo_path) {
            return;
        }

        if (Storage::disk('public')->exists($this->photo_path)) {
            Storage::disk('public')->delete($this->photo_path);
        }

        $this->photo_path = null;
    }
}
