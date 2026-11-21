<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContestEntry extends Model
{
    protected $connection = 'tenant';

    use HasFactory;

    protected $fillable = [
        'contest_id', 'user_team_id', 'user_id', 'rank', 'prize_won',
        'status', 'entry_fee_paid', 'admin_remarks',
    ];

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    public function userTeam()
    {
        return $this->belongsTo(UserTeam::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
