<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchMarket extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'match_event_id', 'market_type', 'name', 'selection_a', 'selection_b',
        'odds_back', 'odds_lay', 'session_target', 'session_yes_rate', 'session_no_rate',
        'status', 'is_manual_override',
    ];

    protected $casts = [
        'is_manual_override' => 'boolean',
    ];

    public function matchEvent()
    {
        return $this->belongsTo(MatchEvent::class);
    }
}
