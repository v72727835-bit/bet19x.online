<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MatchEvent;
use App\Models\MatchMarket;
use Illuminate\Http\Request;

class OddsController extends Controller
{
    public function index(Request $request)
    {
        $matches = MatchEvent::whereIn('status', ['upcoming', 'live'])->orderBy('start_time')->get();

        $matchEventId = $request->get('match_event_id') ?: optional($matches->first())->id;
        $marketType = $request->get('market_type');

        $markets = collect();
        if ($matchEventId) {
            $query = MatchMarket::where('match_event_id', $matchEventId);
            if ($marketType) {
                $query->where('market_type', $marketType);
            }
            $markets = $query->orderBy('market_type')->orderBy('id')->get();
        }

        return view('admin.odds.index', compact('matches', 'matchEventId', 'marketType', 'markets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'match_event_id' => 'required|exists:match_events,id',
            'market_type' => 'required|in:match_winner,toss,fancy,session,custom',
            'name' => 'required|string|max:255',
            'selection_a' => 'nullable|string|max:255',
            'selection_b' => 'nullable|string|max:255',
            'odds_back' => 'nullable|numeric|min:1',
            'odds_lay' => 'nullable|numeric|min:1',
            'session_target' => 'nullable|integer',
            'session_yes_rate' => 'nullable|numeric',
            'session_no_rate' => 'nullable|numeric',
        ]);

        $market = MatchMarket::create($data);

        AuditLog::record('Odds Management', 'Create Market', "Created {$market->market_type} market: {$market->name}");

        return back()->with('status', 'Market/Odds add ho gaya.');
    }

    public function update(Request $request, MatchMarket $market)
    {
        $data = $request->validate([
            'odds_back' => 'nullable|numeric|min:1',
            'odds_lay' => 'nullable|numeric|min:1',
            'session_target' => 'nullable|integer',
            'session_yes_rate' => 'nullable|numeric',
            'session_no_rate' => 'nullable|numeric',
        ]);

        $data['is_manual_override'] = true;
        $market->update($data);

        AuditLog::record('Odds Management', 'Manual Odds Override', "Market #{$market->id} ({$market->name}) odds updated manually");

        return back()->with('status', 'Odds update ho gayi (manual override).');
    }

    public function bulkUpdate(Request $request)
    {
        $rows = $request->input('markets', []);

        foreach ($rows as $id => $row) {
            $market = MatchMarket::find($id);
            if (! $market) {
                continue;
            }
            $market->update([
                'odds_back' => $row['odds_back'] ?? $market->odds_back,
                'odds_lay' => $row['odds_lay'] ?? $market->odds_lay,
                'session_yes_rate' => $row['session_yes_rate'] ?? $market->session_yes_rate,
                'session_no_rate' => $row['session_no_rate'] ?? $market->session_no_rate,
                'is_manual_override' => true,
            ]);
        }

        AuditLog::record('Odds Management', 'Bulk Odds Update', count($rows).' markets updated in bulk');

        return back()->with('status', 'Bulk odds update ho gayi.');
    }

    public function toggleSuspend(MatchMarket $market)
    {
        $market->update(['status' => $market->status === 'suspended' ? 'active' : 'suspended']);

        AuditLog::record('Odds Management', 'Toggle Market Status', "Market #{$market->id} set to {$market->status}");

        return back()->with('status', 'Market status update ho gaya.');
    }

    public function destroy(MatchMarket $market)
    {
        AuditLog::record('Odds Management', 'Delete Market', $market->name);
        $market->delete();

        return back()->with('status', 'Market delete ho gaya.');
    }
}
