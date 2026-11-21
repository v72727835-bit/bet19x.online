<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MatchEvent;
use App\Models\Sport;
use Illuminate\Http\Request;

class LiveMatchController extends Controller
{
    public function index()
    {
        $matches = MatchEvent::with('sport')->orderBy('start_time', 'desc')->get();
        $sports = Sport::where('is_active', true)->get();
        return view('admin.live-matches', compact('matches', 'sports'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sport_id' => 'required|exists:sports,id',
            'team_a_name' => 'required|string|max:100',
            'team_b_name' => 'required|string|max:100',
            'start_time' => 'required|date',
            'venue' => 'nullable|string|max:150',
        ]);

        MatchEvent::create([
            'sport_id' => $request->sport_id,
            'team_a_name' => $request->team_a_name,
            'team_b_name' => $request->team_b_name,
            'start_time' => $request->start_time,
            'lineup_lock_time' => $request->start_time,
            'venue' => $request->venue,
            'status' => 'upcoming',
        ]);

        return back()->with('status', 'Match add ho gaya.');
    }

    public function updateScore(Request $request, MatchEvent $matchEvent)
    {
        $request->validate([
            'team_a_score' => 'nullable|string|max:50',
            'team_b_score' => 'nullable|string|max:50',
            'current_over' => 'nullable|string|max:50',
            'live_summary' => 'nullable|string|max:255',
            'status' => 'required|in:upcoming,live,completed',
        ]);

        $matchEvent->update($request->only([
            'team_a_score', 'team_b_score', 'current_over', 'live_summary', 'status',
        ]));

        return back()->with('status', 'Score update ho gaya.');
    }

    public function destroy(MatchEvent $matchEvent)
    {
        $matchEvent->delete();
        return back()->with('status', 'Match delete ho gaya.');
    }
}
