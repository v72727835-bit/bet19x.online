<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchEvent extends Model
{
    protected $connection = 'tenant';

    use HasFactory;

    protected $fillable = [
        'sport_id', 'team_a_name', 'team_b_name',
        'start_time', 'lineup_lock_time', 'venue', 'status',
        'winner_team', 'result_summary',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'lineup_lock_time' => 'datetime',
    ];

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    public function playerProfiles()
    {
        return $this->hasMany(PlayerProfile::class);
    }

    public function contests()
    {
        return $this->hasMany(Contest::class);
    }

    public function userTeams()
    {
        return $this->hasMany(UserTeam::class);
    }
}
