<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MatchEvent;
use App\Models\Sport;
use Illuminate\Http\Request;

class MatchManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = MatchEvent::with('sport')->withCount('contests');

        if ($sportId = $request->get('sport_id')) {
            $query->where('sport_id', $sportId);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $matches = $query->latest('start_time')->paginate(20)->withQueryString();
        $sports = Sport::orderBy('name')->get();

        return view('admin.matches.index', compact('matches', 'sports'));
    }

    public function create()
    {
        $sports = Sport::where('is_active', true)->orderBy('name')->get();

        return view('admin.matches.create', compact('sports'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sport_id' => 'required|exists:sports,id',
            'team_a_name' => 'required|string|max:255',
            'team_b_name' => 'required|string|max:255',
            'start_time' => 'required|date',
            'lineup_lock_time' => 'required|date',
            'venue' => 'nullable|string|max:255',
            'status' => 'required|in:upcoming,live,completed,suspended,cancelled',
        ]);

        $match = MatchEvent::create($data);

        AuditLog::record('Match Management', 'Create Match', "Created match #{$match->id}: {$match->team_a_name} vs {$match->team_b_name}");

        return redirect()->route('admin.matches.index')->with('status', 'Match create ho gaya.');
    }

    public function edit(MatchEvent $match)
    {
        $sports = Sport::orderBy('name')->get();

        return view('admin.matches.edit', compact('match', 'sports'));
    }

    public function update(Request $request, MatchEvent $match)
    {
        $data = $request->validate([
            'sport_id' => 'required|exists:sports,id',
            'team_a_name' => 'required|string|max:255',
            'team_b_name' => 'required|string|max:255',
            'start_time' => 'required|date',
            'lineup_lock_time' => 'required|date',
            'venue' => 'nullable|string|max:255',
            'status' => 'required|in:upcoming,live,completed,suspended,cancelled',
        ]);

        $match->update($data);

        AuditLog::record('Match Management', 'Update Match', "Updated match #{$match->id}");

        return redirect()->route('admin.matches.index')->with('status', 'Match update ho gaya.');
    }

    public function toggleSuspend(MatchEvent $match)
    {
        $match->update(['status' => $match->status === 'suspended' ? 'upcoming' : 'suspended']);

        AuditLog::record('Match Management', 'Toggle Suspend', "Match #{$match->id} status: {$match->status}");

        return back()->with('status', 'Match status update ho gaya.');
    }

    public function declareResult(Request $request, MatchEvent $match)
    {
        $request->validate([
            'winner_team' => 'required|string|max:255',
            'result_summary' => 'nullable|string|max:1000',
        ]);

        $match->update([
            'status' => 'completed',
            'winner_team' => $request->winner_team,
            'result_summary' => $request->result_summary,
        ]);

        AuditLog::record('Match Management', 'Declare Result', "Match #{$match->id} winner: {$request->winner_team}");

        return back()->with('status', 'Result declare ho gaya.');
    }

    public function destroy(MatchEvent $match)
    {
        $match->delete();

        AuditLog::record('Match Management', 'Delete Match', "Deleted match #{$match->id}");

        return back()->with('status', 'Match delete ho gaya.');
    }
}
