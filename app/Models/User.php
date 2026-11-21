<?php

namespace App\Models;

use App\Notifications\UserResetPasswordNotification;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    protected $connection = 'tenant';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_blocked',
        'blocked_reason',
        'blocked_at',
        'kyc_status',
        'kyc_id_proof_path',
        'kyc_address_proof_path',
        'kyc_remarks',
        'daily_deposit_limit',
        'max_contest_entry_limit',
        'agent_id',
        'loyalty_points',
        'vip_tier_id',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function vipTier()
    {
        return $this->belongsTo(VipTier::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_blocked' => 'boolean',
            'blocked_at' => 'datetime',
        ];
    }

    /**
     * Get the user's wallet.
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function userTeams()
    {
        return $this->hasMany(UserTeam::class);
    }

    public function contestEntries()
    {
        return $this->hasMany(ContestEntry::class);
    }

    public function depositRequests()
    {
        return $this->hasMany(DepositRequest::class);
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    public function loginLogs()
    {
        return $this->hasMany(LoginLog::class);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new UserResetPasswordNotification($token));
    }
}
