<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteStat extends Model
{
    protected $connection = 'tenant';

    use HasFactory;

    protected $fillable = ['icon', 'value', 'label', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
