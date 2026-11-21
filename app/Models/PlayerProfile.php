<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerProfile extends Model
{
    protected $connection = 'tenant';

    use HasFactory;

    protected $fillable = [
        'match_event_id', 'team_id', 'name', 'role', 'credit_value', 'points'
    ];

    public function matchEvent()
    {
        return $this->belongsTo(MatchEvent::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
