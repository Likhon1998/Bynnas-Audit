<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StrategicPlanItem extends Model
{
    protected $fillable = [
        'sl_no',
        'targeted_development',
        'year_1',
        'year_2',
        'year_3',
        'year_4',
        'year_5',
        'status',
        'remarks',
    ];
}
