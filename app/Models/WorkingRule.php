<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingRule extends Model
{
    protected $fillable = [
        'type',
        'weekday',
        'date',
        'start_time',
        'end_time',
        'slot_interval',
        'active'
    ];

    protected $casts = [
        'date' => 'date',
        'active' => 'boolean'
    ];
}
