<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'code', 'value_type', 'value', 'usage_limit', 'used_count',
        'expires_at', 'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}
