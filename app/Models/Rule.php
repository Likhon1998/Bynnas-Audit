<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rule extends Model
{
    protected $fillable = [
        'serial',
        'title',
        'statement',
        'article',
        'reference_where',
        'reference_when',
        'reference_who',
        'source_name',
        'created_by',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function criteriaText(): string
    {
        return trim((string) $this->statement);
    }
}
