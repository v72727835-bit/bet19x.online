<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\UserTeam;
use App\Models\UserTeamPlayer;
use App\Models\ContestEntry;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContestController extends Controller
{
    const TEAM_SIZE = 11;
    const CREDIT_BUDGET = 100;

    public function index()
    {
        $contests = Contest::with('matchEvent.sport')
            ->where('status', 'open')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('contests.index', compact('contests'));
    }

    public function show(Contest $contest)
    {
        $contest->load('matchEvent.sport');
        $alreadyJoined = ContestEntry::where('contest_id', $contest->id)
            ->where('user_id', Auth::id())
            ->exists();

        return view('contests.show', compact('contest', 'alreadyJoined'));
    }

    public function buildTeam(Contest $contest)
    {
        $contest->load('matchEvent');
        $players = $contest->matchEvent->playerProfiles()->with('team')->orderBy('team_id')->orderBy('role')->get();

        return view('contests.build-team', [
            'contest' => $contest,
            'players' => $players,
            'teamSize' => self::TEAM_SIZE,
            'creditBudget' => self::CREDIT_BUDGET,
        ]);
    }

    public function join(Request $request, Contest $contest)
    {
        $request->validate([
            'team_name' => 'required|string|max:100',
            'players' => 'required|array|min:' . self::TEAM_SIZE . '|max:' . self::TEAM_SIZE,
            'players.*' => 'exists:player_profiles,id',
            'captain_id' => 'required|exists:player_profiles,id',
            'vice_captain_id' => 'required|different:captain_id|exists:player_profiles,id',
        ]);

        if (ContestEntry::where('contest_id', $contest->id)->where('user_id', Auth::id())->exists()) {
            return back()->with('error', 'Aap already is contest mein join kar chuke hain.');
        }

        if ($contest->current_participants >= $contest->max_participants) {
            return back()->with('error', 'Ye contest fully booked ho chuka hai.');
        }

        $selectedPlayers = \App\Models\PlayerProfile::whereIn('id', $request->players)->get();
        $totalCredits = $selectedPlayers->sum('credit_value');

        if ($totalCredits > self::CREDIT_BUDGET) {
            return back()->withInput()->with('error', 'Credit budget (' . self::CREDIT_BUDGET . ') exceed ho gaya hai. Aapne ' . $totalCredits . ' use kiye.');
        }

        if (!in_array($request->captain_id, $request->players) || !in_array($request->vice_captain_id, $request->players)) {
            return back()->withInput()->with('error', 'Captain aur Vice-Captain aapki selected team mein se hone chahiye.');
        }

        $user = Auth::user();
        $wallet = $user->wallet ?? Wallet::firstOrCreate(['user_id' => $user->id]);
        $entryFee = $contest->entry_fee;
        $availableBalance = $wallet->deposit_balance + $wallet->bonus_balance;

        if ($entryFee > 0 && $entryFee > $availableBalance) {
            return back()->withInput()->with('error', 'Wallet mein paryapt balance nahi hai. Available: ₹' . number_format($availableBalance, 2));
        }

        DB::beginTransaction();
        try {
            $userTeam = UserTeam::create([
                'user_id' => $user->id,
                'match_event_id' => $contest->match_event_id,
                'team_name' => $request->team_name,
                'total_points' => 0,
            ]);

            foreach ($request->players as $playerId) {
                UserTeamPlayer::create([
                    'user_team_id' => $userTeam->id,
                    'player_profile_id' => $playerId,
                    'is_captain' => $playerId == $request->captain_id,
                    'is_vice_captain' => $playerId == $request->vice_captain_id,
                ]);
            }

            if ($entryFee > 0) {
                $remaining = $entryFee;
                if ($wallet->deposit_balance >= $remaining) {
                    $wallet->deposit_balance -= $remaining;
                } else {
                    $remaining -= $wallet->deposit_balance;
                    $wallet->deposit_balance = 0;
                    $wallet->bonus_balance -= $remaining;
                }
                $wallet->save();

                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'contest_entry',
                    'amount' => $entryFee,
                    'description' => 'Entry fee for ' . $contest->name,
                    'reference_id' => 'CE_' . Str::upper(Str::random(8)),
                ]);
            }

            ContestEntry::create([
                'contest_id' => $contest->id,
                'user_team_id' => $userTeam->id,
                'user_id' => $user->id,
                'rank' => null,
                'points' => 0,
                'prize_won' => 0,
            ]);

            $contest->increment('current_participants');

            DB::commit();

            return redirect()->route('contests.show', $contest)->with('status', 'Team successfully bani aur contest join ho gaya!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Kuch galat ho gaya. Please try again. (' . $e->getMessage() . ')');
        }
    }
}
