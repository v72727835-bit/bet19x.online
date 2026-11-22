<?php

namespace App\Models;

use App\Notifications\AdminResetPasswordNotification;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable implements CanResetPassword
{
    protected $connection = 'tenant';

    use HasFactory, Notifiable, CanResetPasswordTrait;

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active', 'permissions'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'permissions' => 'array',
        ];
    }

    /**
     * Admin panel modules that can be granted to a sub-admin.
     * key => label shown on the "create sub-admin" form.
     */
    public const MODULES = [
        'users' => 'Users (view, edit, login as, delete)',
        'bets' => 'Bet history',
        'wallet' => 'Deposits, withdrawals & transactions',
        'support' => 'Live support chat',
        'sports' => 'Matches, sports & odds',
        'risk' => 'Risk management',
        'agents' => 'Agents',
        'promotions' => 'Bonuses, promo codes & VIP',
        'reports' => 'Reports',
        'content' => 'Site content, banners, menu & notifications',
        'security' => 'Audit logs & blocked IPs',
        'settings' => 'Site settings',
    ];

    /** Modules each built-in role gets when no explicit permission list is saved. */
    public const ROLE_DEFAULTS = [
        'manager' => ['users', 'bets', 'wallet', 'support', 'sports', 'risk', 'agents', 'promotions', 'reports', 'content', 'settings'],
        'support' => ['users', 'support', 'content'],
    ];

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function modules(): array
    {
        if ($this->isSuperAdmin()) {
            return array_keys(self::MODULES);
        }

        return is_array($this->permissions) ? $this->permissions : (self::ROLE_DEFAULTS[$this->role] ?? []);
    }

    public function hasPermission(string $module): bool
    {
        return $this->isSuperAdmin() || in_array($module, $this->modules(), true);
    }

    public function roleLabel(): string
    {
        if ($this->isSuperAdmin()) {
            return 'Super admin';
        }

        return is_array($this->permissions) ? 'Sub-admin' : ucfirst($this->role);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new AdminResetPasswordNotification($token));
    }
}
