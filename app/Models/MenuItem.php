<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $connection = 'tenant';

    protected $fillable = ['label', 'url', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
