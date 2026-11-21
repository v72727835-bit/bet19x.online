<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VipTier extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'name', 'min_points', 'cashback_percent', 'deposit_bonus_percent',
        'badge_color', 'perks', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'vip_tier_id');
    }
}
