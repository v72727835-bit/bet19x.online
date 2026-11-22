<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Agent extends Authenticatable
{
    protected $connection = 'tenant';

    protected $fillable = [
        'parent_id', 'name', 'email', 'phone', 'password', 'role',
        'commission_rate', 'credit_limit', 'credit_used', 'wallet_balance',
        'is_active', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    public const ROLE_LABELS = ['master_agent' => 'Master agent', 'agent' => 'Agent', 'sub_agent' => 'Sub-agent'];

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

    public function roleLabel(): string
    {
        return self::ROLE_LABELS[$this->role] ?? ucfirst(str_replace('_', ' ', (string) $this->role));
    }

    /** Ids of every agent below this one (children, grandchildren, ...). */
    public function descendantIds(): array
    {
        $all = [];
        $level = [$this->id];
        for ($depth = 0; $depth < 5 && $level; $depth++) {
            $level = static::whereIn('parent_id', $level)->pluck('id')->all();
            $level = array_values(array_diff($level, $all, [$this->id]));
            $all = array_merge($all, $level);
        }

        return $all;
    }

    /** This agent plus everyone below it — the scope the agent may see. */
    public function scopeIds(): array
    {
        return array_merge([$this->id], $this->descendantIds());
    }

    public function creditAvailable(): float
    {
        return (float) $this->credit_limit - (float) $this->credit_used;
    }
}
