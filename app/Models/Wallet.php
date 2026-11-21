<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $connection = 'tenant';

    use HasFactory;

    protected $fillable = ['user_id', 'deposit_balance', 'winnings_balance', 'bonus_balance'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
