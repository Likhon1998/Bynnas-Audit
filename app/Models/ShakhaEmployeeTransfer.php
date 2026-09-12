<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShakhaEmployeeTransfer extends Model
{
    protected $fillable = [
        'shakha_employee_id',
        'from_shakha_id',
        'to_shakha_id',
        'transferred_at',
        'note',
        'transferred_by',
    ];

    protected function casts(): array
    {
        return [
            'transferred_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(ShakhaEmployee::class, 'shakha_employee_id');
    }

    public function fromShakha(): BelongsTo
    {
        return $this->belongsTo(Shakha::class, 'from_shakha_id');
    }

    public function toShakha(): BelongsTo
    {
        return $this->belongsTo(Shakha::class, 'to_shakha_id');
    }

    public function transferredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }
}
