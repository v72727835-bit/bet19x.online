<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AstechBetLog;
use App\Models\DepositRequest;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Services\GameCatalog;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Agent panel (master distributor / distributor / retailer).
 *
 * Every query is limited to players whose agent_id is the signed-in agent or
 * any agent below it. The agent's share is the commission % the admin set,
 * applied to the house result of those players (stakes minus wins): positive
 * when players lose, negative when players win.
 */
class AgentPanelController extends Controller
{
    private const RANGES = ['today' => 'Today', '7d' => 'Last 7 days', '30d' => 'Last 30 days', 'all' => 'All time'];

    private function agent(Request $request): Agent
    {
        return $request->user('agent');
    }

    /** @return array{0: array<int>, 1: array<string>} player ids as ints and as strings (bet logs store strings) */
    private function playerIds(Agent $agent): array
    {
        $ids = User::whereIn('agent_id', $agent->scopeIds())->pluck('id')->all();

        return [$ids ?: [0], array_map('strval', $ids) ?: ['-']];
    }

    /** @return array{0: ?Carbon, 1: string} */
    private function range(Request $request, string $default = '30d'): array
    {
        $key = array_key_exists($request->get('range'), self::RANGES) ? $request->get('range') : $default;
        $from = match ($key) {
            'today' => now()->startOfDay(),
            '7d' => now()->subDays(6)->startOfDay(),
            '30d' => now()->subDays(29)->startOfDay(),
            default => null,
        };

        return [$from, $key];
    }

    private function common(Request $request, string $tab, string $rangeKey): array
    {
        return ['agent' => $this->agent($request), 'tab' => $tab, 'ranges' => self::RANGES, 'rangeKey' => $rangeKey];
    }

    public function dashboard(Request $request)
    {
        $agent = $this->agent($request);
        [$ids, $sids] = $this->playerIds($agent);
        [$from, $rangeKey] = $this->range($request);
        $rate = (float) $agent->commission_rate / 100;

        $bets = AstechBetLog::whereIn('user_id', $sids)->where('rolled_back', false)
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from));
        $betTotals = (clone $bets)->selectRaw('COUNT(*) as rounds, COUNT(DISTINCT user_id) as active, COALESCE(SUM(bet),0) as bet, COALESCE(SUM(win),0) as win')->first();
        $houseResult = (float) $betTotals->bet - (float) $betTotals->win;

        $dep = DepositRequest::whereIn('user_id', $ids)->when($from, fn ($q) => $q->where('created_at', '>=', $from));
        $wd = WithdrawalRequest::whereIn('user_id', $ids)->when($from, fn ($q) => $q->where('created_at', '>=', $from));

        $stats = [
            'players' => count(array_filter($ids)),
            'new_players' => User::whereIn('agent_id', $agent->scopeIds())->when($from, fn ($q) => $q->where('created_at', '>=', $from))->count(),
            'active_players' => (int) $betTotals->active,
            'rounds' => (int) $betTotals->rounds,
            'staked' => (float) $betTotals->bet,
            'won' => (float) $betTotals->win,
            'house_result' => $houseResult,
            'share' => $houseResult * $rate,
            'deposits' => (float) (clone $dep)->where('status', 'approved')->sum('amount'),
            'deposits_pending' => (clone $dep)->where('status', 'pending')->count(),
            'withdrawals' => (float) (clone $wd)->where('status', 'approved')->sum('amount'),
            'withdrawals_pending' => (clone $wd)->where('status', 'pending')->count(),
            'sub_agents' => count($agent->descendantIds()),
        ];

        // Day-by-day for the last 14 days (or the selected range if shorter)
        $days = $rangeKey === 'today' ? 1 : ($rangeKey === '7d' ? 7 : 14);
        $start = now()->subDays($days - 1)->startOfDay();
        $daily = [];
        for ($i = 0; $i < $days; $i++) {
            $d = $start->copy()->addDays($i)->toDateString();
            $daily[$d] = ['bet' => 0.0, 'win' => 0.0, 'deposits' => 0.0];
        }
        AstechBetLog::whereIn('user_id', $sids)->where('rolled_back', false)->where('created_at', '>=', $start)
            ->get(['bet', 'win', 'created_at'])->each(function ($r) use (&$daily) {
                $d = $r->created_at->toDateString();
                if (isset($daily[$d])) { $daily[$d]['bet'] += (float) $r->bet; $daily[$d]['win'] += (float) $r->win; }
            });
        DepositRequest::whereIn('user_id', $ids)->where('status', 'approved')->where('created_at', '>=', $start)
            ->get(['amount', 'created_at'])->each(function ($r) use (&$daily) {
                $d = $r->created_at->toDateString();
                if (isset($daily[$d])) { $daily[$d]['deposits'] += (float) $r->amount; }
            });
        $daily = array_reverse($daily, true);

        $top = (clone $bets)->groupBy('user_id')->selectRaw('user_id, COUNT(*) as rounds, SUM(bet) as bet, SUM(win) as win')
            ->orderByDesc('bet')->limit(5)->get();
        $topUsers = User::whereIn('id', $top->pluck('user_id')->all() ?: [0])->get(['id', 'name', 'email'])->keyBy('id');

        return view('agent.dashboard', $this->common($request, 'overview', $rangeKey) + compact('stats', 'daily', 'top', 'topUsers', 'rate'));
    }

    public function players(Request $request)
    {
        $agent = $this->agent($request);
        [$from, $rangeKey] = $this->range($request, 'all');

        $q = User::whereIn('agent_id', $agent->scopeIds())->with(['wallet', 'agent']);
        if ($s = trim((string) $request->get('q'))) {
            $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"));
        }
        $users = $q->latest()->paginate(25)->withQueryString();

        $sids = $users->pluck('id')->map(fn ($id) => (string) $id)->all() ?: ['-'];
        $perUser = AstechBetLog::whereIn('user_id', $sids)->where('rolled_back', false)
            ->when($from, fn ($w) => $w->where('created_at', '>=', $from))
            ->groupBy('user_id')->selectRaw('user_id, COUNT(*) as rounds, SUM(bet) as bet, SUM(win) as win, MAX(created_at) as last_bet')
            ->get()->keyBy('user_id');
        $deposits = DepositRequest::whereIn('user_id', $users->pluck('id')->all() ?: [0])->where('status', 'approved')
            ->when($from, fn ($w) => $w->where('created_at', '>=', $from))
            ->groupBy('user_id')->selectRaw('user_id, SUM(amount) as total')->pluck('total', 'user_id');

        return view('agent.players', $this->common($request, 'players', $rangeKey) + compact('users', 'perUser', 'deposits'));
    }

    public function bets(Request $request)
    {
        $agent = $this->agent($request);
        [$ids, $sids] = $this->playerIds($agent);
        [$from, $rangeKey] = $this->range($request, '7d');

        if ($pid = (int) $request->get('player')) {
            $sids = in_array((string) $pid, $sids, true) ? [(string) $pid] : ['-'];
        }
        $q = AstechBetLog::whereIn('user_id', $sids)->when($from, fn ($w) => $w->where('created_at', '>=', $from));
        $totals = (clone $q)->where('rolled_back', false)->selectRaw('COUNT(*) as rounds, COALESCE(SUM(bet),0) as bet, COALESCE(SUM(win),0) as win')->first();
        $bets = $q->latest('id')->paginate(40)->withQueryString();
        $users = User::whereIn('id', $bets->pluck('user_id')->unique()->all() ?: [0])->get(['id', 'name', 'email'])->keyBy('id');
        $games = $bets->isEmpty() ? [] : GameCatalog::lookup();
        $rate = (float) $agent->commission_rate / 100;

        return view('agent.bets', $this->common($request, 'bets', $rangeKey) + compact('bets', 'totals', 'users', 'games', 'rate'));
    }

    public function deposits(Request $request)
    {
        return $this->money($request, 'deposits');
    }

    public function withdrawals(Request $request)
    {
        return $this->money($request, 'withdrawals');
    }

    private function money(Request $request, string $kind)
    {
        $agent = $this->agent($request);
        [$ids] = $this->playerIds($agent);
        [$from, $rangeKey] = $this->range($request, '30d');

        $model = $kind === 'deposits' ? DepositRequest::class : WithdrawalRequest::class;
        $q = $model::with('user')->whereIn('user_id', $ids)->when($from, fn ($w) => $w->where('created_at', '>=', $from));
        if (in_array($request->get('status'), ['pending', 'approved', 'rejected'], true)) {
            $q->where('status', $request->get('status'));
        }
        $sum = [
            'approved' => (float) (clone $q)->where('status', 'approved')->sum('amount'),
            'pending' => (float) (clone $q)->where('status', 'pending')->sum('amount'),
            'count' => (clone $q)->count(),
        ];
        $rows = $q->latest()->paginate(30)->withQueryString();

        return view('agent.money', $this->common($request, $kind, $rangeKey) + compact('rows', 'sum', 'kind'));
    }

    public function agents(Request $request)
    {
        $agent = $this->agent($request);
        [$from, $rangeKey] = $this->range($request, '30d');

        $subAgents = Agent::whereIn('id', $agent->descendantIds())->with('parent')->withCount('users')->orderBy('role')->orderBy('name')->get();
        $results = [];
        foreach ($subAgents as $s) {
            $sids = User::whereIn('agent_id', $s->scopeIds())->pluck('id')->map(fn ($id) => (string) $id)->all() ?: ['-'];
            $t = AstechBetLog::whereIn('user_id', $sids)->where('rolled_back', false)->when($from, fn ($w) => $w->where('created_at', '>=', $from))
                ->selectRaw('COALESCE(SUM(bet),0) as bet, COALESCE(SUM(win),0) as win')->first();
            $results[$s->id] = (float) $t->bet - (float) $t->win;
        }

        return view('agent.agents', $this->common($request, 'agents', $rangeKey) + compact('subAgents', 'results'));
    }
}
