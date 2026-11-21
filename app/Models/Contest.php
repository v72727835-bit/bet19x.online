<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contest extends Model
{
    protected $connection = 'tenant';

    use HasFactory;

    protected $fillable = [
        'match_event_id', 'name', 'entry_fee', 'total_prize_pool',
        'max_participants', 'current_participants', 'status'
    ];

    public function matchEvent()
    {
        return $this->belongsTo(MatchEvent::class);
    }

    public function entries()
    {
        return $this->hasMany(ContestEntry::class);
    }
}
