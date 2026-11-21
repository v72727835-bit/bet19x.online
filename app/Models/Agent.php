<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'parent_id', 'name', 'email', 'phone', 'password', 'role',
        'commission_rate', 'credit_limit', 'credit_used', 'wallet_balance',
        'is_active', 'last_login_at',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function parent()
    {
        return $this->belongsTo(Agent::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Agent::class, 'parent_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'agent_id');
    }

    public function creditAvailable(): float
    {
        return (float) $this->credit_limit - (float) $this->credit_used;
    }
}
