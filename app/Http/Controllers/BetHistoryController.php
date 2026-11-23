<?php

namespace App\Http\Controllers;

use App\Models\AstechBetLog;
use App\Services\BetRounds;
use App\Services\GameCatalog;
use Illuminate\Http\Request;

class BetHistoryController extends Controller
{
    public function index(Request $request)
    {
        $base = AstechBetLog::where('user_id', (string) $request->user()->id);

        $totals = BetRounds::totals($base);
        $bets = BetRounds::paginate($base, 25);
        $games = $bets->isEmpty() ? [] : GameCatalog::lookup();

        return view('bets.index', compact('bets', 'totals', 'games'));
    }
}
