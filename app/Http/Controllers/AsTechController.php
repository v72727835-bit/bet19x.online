<?php
namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Services\AsTechApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AsTechController extends Controller
{
    public function games(AsTechApi $api)
    {
        $error = null;
        try {
            $games = Cache::remember('astech_games', 1800, fn () => $api->games());
        } catch (\Throwable $e) {
            $games = [];
            $error = $e->getMessage();
        }
        $pv = (string) request()->query('provider', '');
        if ($pv !== '') {
            $want = array_map('strtolower', array_filter(explode('|', $pv)));
            $games = array_values(array_filter($games, function ($g) use ($want) {
                $k = strtolower((string) ($g['provider'] ?? ''));
                if (strpos($k, 'hd_') === 0) {
                    $k = substr($k, 3);
                }
                return in_array($k, $want, true);
            }));
        }
        return view('astech.games', compact('games', 'error'));
    }

    private function inrGames(AsTechApi $api): array
    {
        $all = Cache::remember('astech_games', 1800, fn () => $api->games());
        $cur = config('astech.currency', 'INR');
        return array_values(array_filter($all, fn ($g) => empty($g['currencies']) || in_array($cur, $g['currencies'], true)));
    }

    public function gameList(Request $request, AsTechApi $api)
    {
        try {
            $all = $this->inrGames($api);
        } catch (\Throwable $e) {
            return response()->json(['data' => [], 'total' => 0, 'error' => $e->getMessage()]);
        }
        $q = mb_strtolower(trim((string) $request->query('q', '')));
        $cat = (string) $request->query('category', '');
        $prov = (string) $request->query('provider', '');
        $f = array_values(array_filter($all, function ($g) use ($q, $cat, $prov) {
            if ($cat !== '' && ($g['category'] ?? '') !== $cat) return false;
            if ($prov !== '' && ($g['provider'] ?? '') !== $prov) return false;
            if ($q !== '' && !str_contains(mb_strtolower(($g['title'] ?? '') . ' ' . ($g['provider'] ?? '')), $q)) return false;
            return true;
        }));
        $page = max(1, (int) $request->query('page', 1));
        $per = 36;
        $rows = array_map(fn ($g) => [
            'id' => $g['gameId'], 'title' => $g['title'] ?? '', 'provider' => $g['provider'] ?? '',
            'category' => $g['category'] ?? '', 'img' => $g['imageUrl'] ?? null,
        ], array_slice($f, ($page - 1) * $per, $per));
        return response()->json(['data' => $rows, 'total' => count($f), 'page' => $page, 'per' => $per]);
    }

    public function gameMeta(AsTechApi $api)
    {
        try {
            $all = $this->inrGames($api);
        } catch (\Throwable $e) {
            return response()->json(['categories' => [], 'providers' => []]);
        }
        $cats = []; $provs = [];
        foreach ($all as $g) {
            $c = $g['category'] ?? ''; $p = $g['provider'] ?? '';
            if ($c !== '') $cats[$c] = ($cats[$c] ?? 0) + 1;
            if ($p !== '') $provs[$p] = ($provs[$p] ?? 0) + 1;
        }
        arsort($cats); arsort($provs);
        return response()->json(['categories' => $cats, 'providers' => array_slice($provs, 0, 60, true)]);
    }
    public function launch(Request $request, AsTechApi $api, string $gameId)
    {
        $uid = (string) $request->user()->id;
        Wallet::firstOrCreate(['user_id' => $uid], [
            'deposit_balance' => 0, 'winnings_balance' => 0, 'bonus_balance' => 0,
        ]);
        try {
            $s = $api->open($uid, $gameId);
        } catch (\Throwable $e) {
            return redirect('/games')->with('error', 'Game launch failed: ' . $e->getMessage());
        }
        return redirect()->away($s['gameUrl'])
            ->withHeaders(['Cache-Control' => 'no-store, no-cache, must-revalidate']);
    }

    private function ok(float $cash)
    {
        return response()->json(['status' => 'success', 'error' => '',
            'content' => ['cash' => number_format($cash, 2, '.', '')]], 200);
    }

    private function fail(string $err)
    {
        return response()->json(['status' => 'fail', 'error' => $err, 'content' => null], 200);
    }

    public function callback(Request $request, AsTechApi $api)
    {
        $raw = $request->getContent();
        if (!$api->verify($raw, (string) $request->header('X-Signature'))) {
            return $this->fail('Invalid signature');
        }
        $p = json_decode($raw, true);
        if (!is_array($p)) return $this->fail('Invalid JSON');

        $cmd   = (string) ($p['cmd'] ?? '');
        $login = (string) ($p['user_login'] ?? ($p['login'] ?? ''));
        $txn   = (string) ($p['transaction_id'] ?? ($p['transactionId'] ?? ''));
        if ($login === '') return $this->fail('user_login required');

        $db = DB::connection((new Wallet)->getConnectionName());

        try {
            if ($cmd === 'getBalance') {
                $w = Wallet::where('user_id', $login)->first();
                if (!$w) return $this->fail('Player not found');
                return $this->ok((float) $w->deposit_balance + (float) $w->winnings_balance);
            }

            if ($cmd === 'writeBet') {
                if ($txn === '') return $this->fail('transaction_id required');
                $bet   = round((float) ($p['bet'] ?? 0), 2);
                $win   = round((float) ($p['win'] ?? 0), 2);
                $round = (string) ($p['round_id'] ?? ($p['roundId'] ?? ''));
                $game  = (string) ($p['game_id'] ?? ($p['gameId'] ?? ''));
                $cur   = strtoupper((string) ($p['currency'] ?? 'INR'));

                if (! is_numeric($p['bet'] ?? 0) || ! is_numeric($p['win'] ?? 0)) return $this->fail('Invalid amount');
                if ($bet < 0 || $win < 0) return $this->fail('Negative amount not allowed');
                if ($bet > 10000000 || $win > 10000000) return $this->fail('Amount too large');
                if ($cur !== strtoupper((string) config('astech.currency', 'INR'))) return $this->fail('Currency mismatch');

                $r = $db->transaction(function () use ($db, $login, $txn, $bet, $win, $round, $game, $cur) {
                    $dup = $db->table('astech_bet_logs')->where('transaction_id', $txn)->first();
                    if ($dup) return ['ok', (float) $dup->balance_after];

                    $w = Wallet::where('user_id', $login)->lockForUpdate()->first();
                    if (!$w) return ['fail', 'Player not found'];

                    $dep = (float) $w->deposit_balance;
                    $wn  = (float) $w->winnings_balance;
                    $bal = round($dep + $wn, 2);
                    if ($bet <= 0 && $win <= 0) return ['fail', 'bet or win must be greater than 0'];
                    if ($bet > $bal + 0.00001) return ['fail', 'INSUFFICIENT_FUNDS'];

                    $fromDep = round(min($dep, $bet), 2);
                    $fromWin = round($bet - $fromDep, 2);
                    $w->deposit_balance  = round($dep - $fromDep, 2);
                    $w->winnings_balance = round($wn - $fromWin + $win, 2);
                    $w->save();
                    $new = round((float) $w->deposit_balance + (float) $w->winnings_balance, 2);

                    $db->table('astech_bet_logs')->insert([
                        'transaction_id' => $txn, 'user_id' => $login, 'game_id' => $game,
                        'round_id' => $round, 'bet' => $bet, 'win' => $win,
                        'from_deposit' => $fromDep, 'from_winnings' => $fromWin,
                        'balance_before' => $bal, 'balance_after' => $new,
                        'currency' => $cur, 'rolled_back' => 0,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                    return ['ok', $new];
                });
                return $r[0] === 'ok' ? $this->ok($r[1]) : $this->fail($r[1]);
            }

            if ($cmd === 'rollback') {
                $r = $db->transaction(function () use ($db, $login, $txn) {
                    $w = Wallet::where('user_id', $login)->lockForUpdate()->first();
                    if (!$w) return ['fail', 'Player not found'];
                    $log = $db->table('astech_bet_logs')->where('transaction_id', $txn)->lockForUpdate()->first();
                    if ($log && !$log->rolled_back) {
                        $w->deposit_balance  = round((float) $w->deposit_balance + (float) $log->from_deposit, 2);
                        $w->winnings_balance = round(max(0, (float) $w->winnings_balance + (float) $log->from_winnings - (float) $log->win), 2);
                        $w->save();
                        $db->table('astech_bet_logs')->where('id', $log->id)->update(['rolled_back' => 1, 'updated_at' => now()]);
                    }
                    return ['ok', round((float) $w->deposit_balance + (float) $w->winnings_balance, 2)];
                });
                return $r[0] === 'ok' ? $this->ok($r[1]) : $this->fail($r[1]);
            }

            return $this->fail('Unknown cmd: ' . $cmd);
        } catch (\Throwable $e) {
            report($e);
            return $this->fail('Server error');
        }
    }
}
