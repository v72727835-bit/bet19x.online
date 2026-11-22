<?php

namespace App\Http\Controllers;

use App\Models\AstechBetLog;
use App\Services\GameCatalog;
use Illuminate\Http\Request;

class BetHistoryController extends Controller
{
    public function index(Request $request)
    {
        $uid = (string) $request->user()->id;
        $base = AstechBetLog::where('user_id', $uid);

        $totals = (clone $base)->where('rolled_back', false)
            ->selectRaw('COUNT(*) as rounds, COALESCE(SUM(bet),0) as bet, COALESCE(SUM(win),0) as win')
            ->first();

        $bets = (clone $base)->latest('id')->paginate(25);
        $games = $bets->isEmpty() ? [] : GameCatalog::lookup();

        return view('bets.index', compact('bets', 'totals', 'games'));
    }
}
