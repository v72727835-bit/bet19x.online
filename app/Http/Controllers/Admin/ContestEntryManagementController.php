<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Contest;
use App\Models\ContestEntry;
use App\Models\MatchEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContestEntryManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = ContestEntry::with(['user', 'contest.matchEvent']);

        if ($matchId = $request->get('match_event_id')) {
            $query->whereHas('contest', fn ($q) => $q->where('match_event_id', $matchId));
        }

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $entries = $query->latest()->paginate(25)->withQueryString();
        $matches = MatchEvent::orderByDesc('start_time')->take(50)->get();

        return view('admin.bets.index', compact('entries', 'matches'));
    }

    public function settle(Request $request, ContestEntry $contestEntry)
    {
        $request->validate([
            'status' => 'required|in:won,lost,void,cancelled',
            'prize_won' => 'nullable|numeric|min:0',
            'admin_remarks' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $contestEntry) {
            if ($request->status === 'won' && $request->filled('prize_won')) {
                $wallet = $contestEntry->user->wallet;
                if ($wallet) {
                    $wallet->increment('winnings_balance', $request->prize_won);
                    $wallet->transactions()->create([
                        'type' => 'credit',
                        'amount' => $request->prize_won,
                        'description' => "Contest prize - Entry #{$contestEntry->id}",
                        'reference_id' => (string) $contestEntry->id,
                    ]);
                }
            }

            if (in_array($request->status, ['void', 'cancelled'])) {
                $wallet = $contestEntry->user->wallet;
                if ($wallet && $contestEntry->entry_fee_paid > 0) {
                    $wallet->increment('deposit_balance', $contestEntry->entry_fee_paid);
                    $wallet->transactions()->create([
                        'type' => 'credit',
                        'amount' => $contestEntry->entry_fee_paid,
                        'description' => "Entry fee refund ({$request->status}) - Entry #{$contestEntry->id}",
                        'reference_id' => (string) $contestEntry->id,
                    ]);
                }
            }

            $contestEntry->update([
                'status' => $request->status,
                'prize_won' => $request->prize_won ?? $contestEntry->prize_won,
                'admin_remarks' => $request->admin_remarks,
            ]);
        });

        AuditLog::record('Bet Management', 'Settle Entry', "Entry #{$contestEntry->id} set to {$request->status}");

        return back()->with('status', 'Entry settle ho gayi.');
    }
}
