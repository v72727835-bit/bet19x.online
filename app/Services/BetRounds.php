<?php

namespace App\Services;

use App\Models\AstechBetLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Bet logs store one row per provider callback. Live games send the stake and
 * the payout of the same round as two rows (bet 4,400 / win 0, then bet 0 /
 * win 90,000). These helpers join them back into one line per round so each
 * line shows what the player staked, won, and the round's profit or loss.
 */
class BetRounds
{
    private const KEY = "COALESCE(NULLIF(round_id, ''), transaction_id)";

    /**
     * One row per (player, round) for the filtered bet-log query.
     *
     * The round key is computed in an inner derived table and grouped on as a
     * plain column: MariaDB's ONLY_FULL_GROUP_BY rejects grouping on the
     * expression itself (error 1055), while every engine accepts this form.
     */
    public static function roundsSub(Builder $logs)
    {
        $k = self::KEY;
        $conn = (new AstechBetLog)->getConnectionName();

        $rows = (clone $logs)->toBase()
            ->select(['user_id', 'game_id', 'created_at', 'id', 'bet', 'win', 'rolled_back'])
            ->selectRaw("{$k} as round_key");

        return DB::connection($conn)->query()->fromSub($rows, 'l')
            ->select(['user_id', 'round_key'])
            ->selectRaw('MAX(game_id) as game_id, MIN(created_at) as started_at, MAX(created_at) as ended_at, MAX(id) as last_id, COUNT(*) as entries')
            ->selectRaw('SUM(CASE WHEN rolled_back = 0 THEN bet ELSE 0 END) as bet')
            ->selectRaw('SUM(CASE WHEN rolled_back = 0 THEN win ELSE 0 END) as win')
            ->selectRaw('SUM(CASE WHEN rolled_back = 1 THEN 1 ELSE 0 END) as rolled')
            ->groupBy('user_id', 'round_key');
    }

    /** Paginated rounds, newest first, each with ->result (win - bet), ->refunded and ->balance_after. */
    public static function paginate(Builder $logs, int $perPage = 40): LengthAwarePaginator
    {
        $conn = (new AstechBetLog)->getConnectionName();
        $page = DB::connection($conn)->query()->fromSub(self::roundsSub($logs), 'r')
            ->orderByDesc('last_id')->paginate($perPage)->withQueryString();

        $balances = AstechBetLog::whereIn('id', collect($page->items())->pluck('last_id')->all() ?: [0])->pluck('balance_after', 'id');
        $page->getCollection()->transform(function ($r) use ($balances) {
            $r->bet = (float) $r->bet;
            $r->win = (float) $r->win;
            $r->result = $r->win - $r->bet;
            $r->refunded = (int) $r->rolled >= (int) $r->entries;
            $r->balance_after = (float) ($balances[$r->last_id] ?? 0);
            $r->started_at = \Illuminate\Support\Carbon::parse($r->started_at);
            $r->ended_at = \Illuminate\Support\Carbon::parse($r->ended_at);

            return $r;
        });

        return $page;
    }

    /** Totals over all rounds: rounds, players, staked, won, profit (rounds won), loss (rounds lost). */
    public static function totals(Builder $logs): object
    {
        $conn = (new AstechBetLog)->getConnectionName();
        $t = DB::connection($conn)->query()->fromSub(self::roundsSub($logs), 'r')
            ->selectRaw('COUNT(*) as rounds, COUNT(DISTINCT user_id) as players, COALESCE(SUM(bet),0) as bet, COALESCE(SUM(win),0) as win')
            ->selectRaw('COALESCE(SUM(CASE WHEN win > bet THEN win - bet ELSE 0 END),0) as profit')
            ->selectRaw('COALESCE(SUM(CASE WHEN win < bet THEN bet - win ELSE 0 END),0) as loss')
            ->selectRaw('COALESCE(SUM(CASE WHEN win > bet THEN 1 ELSE 0 END),0) as rounds_won')
            ->selectRaw('COALESCE(SUM(CASE WHEN win < bet THEN 1 ELSE 0 END),0) as rounds_lost')
            ->first();

        foreach (['bet', 'win', 'profit', 'loss'] as $f) {
            $t->$f = (float) $t->$f;
        }
        $t->net = $t->win - $t->bet; // players' net (+ = players ahead)

        return $t;
    }

    /** Per-player: rounds, staked, won, profit, loss, net — ordered by stake. */
    public static function perPlayer(Builder $logs, int $limit = 50)
    {
        $conn = (new AstechBetLog)->getConnectionName();

        return DB::connection($conn)->query()->fromSub(self::roundsSub($logs), 'r')
            ->groupBy('user_id')
            ->selectRaw('user_id, COUNT(*) as rounds, COALESCE(SUM(bet),0) as bet, COALESCE(SUM(win),0) as win, MAX(ended_at) as last_played')
            ->selectRaw('COALESCE(SUM(CASE WHEN win > bet THEN win - bet ELSE 0 END),0) as profit')
            ->selectRaw('COALESCE(SUM(CASE WHEN win < bet THEN bet - win ELSE 0 END),0) as loss')
            ->orderByDesc('bet')->limit($limit)->get()
            ->map(function ($p) {
                foreach (['bet', 'win', 'profit', 'loss'] as $f) {
                    $p->$f = (float) $p->$f;
                }
                $p->net = $p->win - $p->bet;

                return $p;
            });
    }
}
