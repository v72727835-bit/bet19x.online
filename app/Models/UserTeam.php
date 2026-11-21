<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTeam extends Model
{
    protected $connection = 'tenant';

    use HasFactory;

    protected $fillable = ['user_id', 'match_event_id', 'team_name', 'total_points'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function matchEvent()
    {
        return $this->belongsTo(MatchEvent::class);
    }

    public function players()
    {
        return $this->hasMany(UserTeamPlayer::class);
    }
}
