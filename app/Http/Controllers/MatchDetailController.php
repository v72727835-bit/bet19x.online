<?php

namespace App\Http\Controllers;

use App\Models\MatchEvent;
use App\Models\Contest;

class MatchDetailController extends Controller
{
    public function show(MatchEvent $matchEvent)
    {
        $contests = Contest::where('match_event_id', $matchEvent->id)
            ->with(['entries' => function ($q) {
                $q->with('userTeam')->orderByRaw('points IS NULL, points DESC');
            }, 'entries.user'])
            ->get();

        return view('match-detail', compact('matchEvent', 'contests'));
    }
}
