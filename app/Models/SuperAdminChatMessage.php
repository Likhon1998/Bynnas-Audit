<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuperAdminChatMessage extends Model
{
    protected $fillable = [
        'user_id',
        'thread_uuid',
        'intent',
        'question',
        'answer',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'question' => 'encrypted',
            'answer' => 'encrypted',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
