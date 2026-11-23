<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AstechBetLog;
use App\Models\User;
use App\Services\GameCatalog;
use Illuminate\Http\Request;

class GameBetController extends Controller
{
    public function index(Request $request)
    {
        $q = AstechBetLog::query();

        if ($u = trim((string) $request->get('user'))) {
            $ids = User::where('email', 'like', "%{$u}%")->orWhere('name', 'like', "%{$u}%")
                ->orWhere('phone', 'like', "%{$u}%")->pluck('id')->map(fn ($id) => (string) $id)->all();
            if (ctype_digit($u)) {
                $ids[] = $u;
            }
            $q->whereIn('user_id', $ids ?: ['-']);
        }
        if ($g = trim((string) $request->get('game'))) {
            $q->where('game_id', 'like', "%{$g}%");
        }
        if ($from = $request->get('from')) {
            $q->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $q->whereDate('created_at', '<=', $to);
        }
        if ($request->get('status') === 'refunded') {
            $q->where('rolled_back', true);
        } elseif ($request->get('status') === 'settled') {
            $q->where('rolled_back', false);
        }

        $totals = \App\Services\BetRounds::totals($q);
        $players = \App\Services\BetRounds::perPlayer($q, 25);
        $bets = \App\Services\BetRounds::paginate($q, 50);
        $users = User::whereIn('id', collect($bets->items())->pluck('user_id')->merge($players->pluck('user_id'))->unique()->all() ?: [0])
            ->get(['id', 'name', 'email'])->keyBy('id');
        $games = ($bets->isEmpty()) ? [] : GameCatalog::lookup();

        return view('admin.game-bets.index', compact('bets', 'totals', 'users', 'games', 'players'));
    }
}
