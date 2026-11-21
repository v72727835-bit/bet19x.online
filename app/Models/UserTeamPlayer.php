<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTeamPlayer extends Model
{
    protected $connection = 'tenant';

    use HasFactory;

    protected $fillable = ['user_team_id', 'player_profile_id', 'is_captain', 'is_vice_captain'];

    public function userTeam()
    {
        return $this->belongsTo(UserTeam::class);
    }

    public function playerProfile()
    {
        return $this->belongsTo(PlayerProfile::class);
    }
}
