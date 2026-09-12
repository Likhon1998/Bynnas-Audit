<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ShakhaEmployee extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_TRANSFERRED = 'transferred';

    public const STATUS_FIRED = 'fired';

    /** @var list<string> */
    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_TRANSFERRED,
        self::STATUS_FIRED,
    ];

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

    public function transfers(): HasMany
    {
        return $this->hasMany(ShakhaEmployeeTransfer::class)->orderByDesc('transferred_at');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isFired(): bool
    {
        return $this->status === self::STATUS_FIRED;
    }

    public function isTransferred(): bool
    {
        return $this->status === self::STATUS_TRANSFERRED;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Active (কর্মরত)',
            self::STATUS_TRANSFERRED => 'Transferred (স্থানান্তরিত)',
            self::STATUS_FIRED => 'Fired (চাকরিচ্যুত)',
            self::STATUS_INACTIVE => 'Inactive (নিষ্ক্রিয়)',
            default => (string) $this->status,
        };
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
