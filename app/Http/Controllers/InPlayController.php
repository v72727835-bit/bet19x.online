<?php

namespace App\Http\Controllers;

use App\Models\ContestEntry;
use Illuminate\Http\Request;

class InPlayController extends Controller
{
    /**
     * Shows all contests the logged-in user has entered where the
     * linked match is currently live (in progress).
     *
     * ASSUMPTIONS (adjust if your actual schema differs):
     * - contest_entries: user_id, contest_id, user_team_id, rank (nullable), points (nullable)
     * - contests: belongsTo MatchEvent via matchEvent()
     * - match_events: has a `status` column with value 'live' when in progress
     * - contests: title/entry_fee/total_prize_pool columns (per project doc)
     */
    public function index(Request $request)
    {
        $entries = ContestEntry::with(['contest.matchEvent', 'userTeam'])
            ->where('user_id', auth()->id())
            ->whereHas('contest.matchEvent', function ($q) {
                $q->where('status', 'live');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('in-play', compact('entries'));
    }
}
