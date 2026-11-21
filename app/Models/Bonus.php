<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'name', 'type', 'value_type', 'value', 'max_bonus', 'min_deposit',
        'is_active', 'valid_from', 'valid_to', 'description',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
    ];
}
