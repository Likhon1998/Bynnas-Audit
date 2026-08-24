<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'serial',
        'title',
        'slug',
        'color',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class)->orderBy('sort_order')->orderBy('name');
    }
}
