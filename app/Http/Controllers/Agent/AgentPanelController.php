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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
        $totals = \App\Services\BetRounds::totals($q);
        $players = \App\Services\BetRounds::perPlayer($q, 25);
        $bets = \App\Services\BetRounds::paginate($q, 40);
        $users = User::whereIn('id', collect($bets->items())->pluck('user_id')->merge($players->pluck('user_id'))->unique()->all() ?: [0])->get(['id', 'name', 'email'])->keyBy('id');
        $games = $bets->isEmpty() ? [] : GameCatalog::lookup();
        $rate = (float) $agent->commission_rate / 100;

        return view('agent.bets', $this->common($request, 'bets', $rangeKey) + compact('bets', 'totals', 'users', 'games', 'rate', 'players'));
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

    /** Profit / loss of every player in the network for the selected period. */
    public function profitLoss(Request $request)
    {
        $agent = $this->agent($request);
        [$ids, $sids] = $this->playerIds($agent);
        [$from, $rangeKey] = $this->range($request, '30d');
        $rate = (float) $agent->commission_rate / 100;

        $base = AstechBetLog::whereIn('user_id', $sids)->where('rolled_back', false)
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from));

        $rows = (clone $base)->groupBy('user_id')
            ->selectRaw('user_id, COUNT(*) as rounds, SUM(bet) as bet, SUM(win) as win')->get()
            ->map(function ($r) {
                $r->pl = (float) $r->win - (float) $r->bet; // player's own profit (+) or loss (-)
                return $r;
            });

        $filter = $request->get('show');
        if ($filter === 'profit') {
            $list = $rows->filter(fn ($r) => $r->pl > 0)->sortByDesc('pl');
        } elseif ($filter === 'loss') {
            $list = $rows->filter(fn ($r) => $r->pl < 0)->sortBy('pl');
        } else {
            $list = $rows->sortByDesc(fn ($r) => abs($r->pl));
        }
        $list = $list->values();

        $profitRows = $rows->filter(fn ($r) => $r->pl > 0);
        $lossRows = $rows->filter(fn ($r) => $r->pl < 0);
        $summary = [
            'players' => $rows->count(),
            'in_profit' => $profitRows->count(),
            'profit' => (float) $profitRows->sum('pl'),
            'in_loss' => $lossRows->count(),
            'loss' => abs((float) $lossRows->sum('pl')),
            'staked' => (float) $rows->sum('bet'),
            'won' => (float) $rows->sum('win'),
        ];
        $summary['net_players'] = $summary['profit'] - $summary['loss'];
        $summary['share'] = -$summary['net_players'] * $rate;

        $users = User::whereIn('id', $list->pluck('user_id')->all() ?: [0])->with('agent')->get(['id', 'name', 'email', 'agent_id'])->keyBy('id');

        $byGame = (clone $base)->groupBy('game_id')
            ->selectRaw('game_id, COUNT(*) as rounds, SUM(bet) as bet, SUM(win) as win')
            ->orderByDesc('bet')->limit(10)->get();
        $games = ($list->isEmpty() && $byGame->isEmpty()) ? [] : GameCatalog::lookup();

        return view('agent.profit-loss', $this->common($request, 'pl', $rangeKey) + compact('list', 'summary', 'users', 'byGame', 'games', 'rate'));
    }

    /** An agent creates a distributor / retailer under itself and sets its commission. */
    public function storeAgent(Request $request)
    {
        $me = $this->agent($request);
        abort_unless($me->canCreateAgents(), 403, 'Aap naye agent nahi bana sakte.');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique($me->getConnectionName() . '.agents', 'email')],
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|max:100',
            'role' => ['required', Rule::in($me->allowedChildRoles())],
            'commission_rate' => 'required|numeric|min:0|max:' . (float) $me->commission_rate,
            'credit_limit' => 'nullable|numeric|min:0|max:' . max(0, $me->creditAvailable()),
        ], [
            'commission_rate.max' => 'Commission aapke apne ' . (float) $me->commission_rate . '% se zyada nahi ho sakta.',
            'credit_limit.max' => 'Credit aapke bache hue credit (₹' . number_format(max(0, $me->creditAvailable()), 2) . ') se zyada nahi ho sakta.',
        ]);

        $credit = round((float) ($data['credit_limit'] ?? 0), 2);

        $child = DB::connection($me->getConnectionName())->transaction(function () use ($me, $data, $credit) {
            $fresh = Agent::lockForUpdate()->find($me->id);
            if ($credit > $fresh->creditAvailable() + 0.00001) {
                abort(422, 'Credit available nahi hai.');
            }
            $fresh->credit_used = round((float) $fresh->credit_used + $credit, 2);
            $fresh->save();

            return Agent::create([
                'parent_id' => $me->id,
                'name' => $data['name'],
                'email' => strtolower(trim($data['email'])),
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'commission_rate' => $data['commission_rate'],
                'credit_limit' => $credit,
                'is_active' => true,
            ]);
        });

        \App\Models\AuditLog::record('Agent Panel', 'Agent Created Agent', "{$me->name} (#{$me->id}) created {$child->role} {$child->email} at {$child->commission_rate}%");

        return redirect()->route('agent.agents')->with('status', "{$child->roleLabel()} {$child->email} ban gaya. Login link: " . route('agent.login'));
    }

    private function ownChild(Request $request, Agent $child): Agent
    {
        $me = $this->agent($request);
        abort_unless((int) $child->parent_id === (int) $me->id, 403, 'Ye agent aapke direct neeche nahi hai.');

        return $me;
    }

    public function updateAgent(Request $request, Agent $child)
    {
        $me = $this->ownChild($request, $child);

        $data = $request->validate([
            'commission_rate' => 'required|numeric|min:0|max:' . (float) $me->commission_rate,
            'credit_limit' => 'nullable|numeric|min:0',
            'new_password' => 'nullable|string|min:6|max:100',
        ], ['commission_rate.max' => 'Commission aapke apne ' . (float) $me->commission_rate . '% se zyada nahi ho sakta.']);

        $newCredit = array_key_exists('credit_limit', $data) && $data['credit_limit'] !== null ? round((float) $data['credit_limit'], 2) : (float) $child->credit_limit;
        $diff = round($newCredit - (float) $child->credit_limit, 2);
        if ($diff > 0 && $diff > $me->creditAvailable() + 0.00001) {
            return back()->withErrors(['credit_limit' => 'Itna credit aapke paas bacha nahi hai (₹' . number_format($me->creditAvailable(), 2) . ').']);
        }
        if ($newCredit < (float) $child->credit_used) {
            return back()->withErrors(['credit_limit' => 'Credit limit us agent ke use kiye hue credit se kam nahi ho sakti.']);
        }

        DB::connection($me->getConnectionName())->transaction(function () use ($me, $child, $data, $newCredit, $diff) {
            $fresh = Agent::lockForUpdate()->find($me->id);
            $fresh->credit_used = max(0, round((float) $fresh->credit_used + $diff, 2));
            $fresh->save();

            $child->commission_rate = $data['commission_rate'];
            $child->credit_limit = $newCredit;
            if (! empty($data['new_password'])) {
                $child->password = Hash::make($data['new_password']);
            }
            $child->save();
        });

        \App\Models\AuditLog::record('Agent Panel', 'Agent Updated Agent', "{$me->name} (#{$me->id}) set {$child->email} to {$child->commission_rate}%, credit ₹{$newCredit}");

        return back()->with('status', "{$child->name} update ho gaya.");
    }

    public function toggleAgent(Request $request, Agent $child)
    {
        $me = $this->ownChild($request, $child);
        $child->update(['is_active' => ! $child->is_active]);
        \App\Models\AuditLog::record('Agent Panel', $child->is_active ? 'Agent Activated Agent' : 'Agent Deactivated Agent', "{$me->name} (#{$me->id}) -> {$child->email}");

        return back()->with('status', $child->name . ($child->is_active ? ' active ho gaya.' : ' deactivate ho gaya.'));
    }

    /** Agent moves money between two players of its own network. */
    public function transferForm(Request $request)
    {
        $agent = $this->agent($request);
        [$from, $rangeKey] = $this->range($request, '30d');
        $players = User::whereIn('agent_id', $agent->scopeIds())->with('wallet')->orderBy('name')->limit(1000)->get(['id', 'name', 'email', 'agent_id']);
        $recent = \App\Models\WalletTransaction::with('wallet.user')->where('type', 'transfer_out')
            ->whereHas('wallet', fn ($q) => $q->whereIn('user_id', $players->pluck('id')->all() ?: [0]))
            ->latest()->take(20)->get();

        $agentHistory = \App\Services\AgentTransfer::history([$agent->id], 20);

        return view('agent.transfer', $this->common($request, 'transfer', $rangeKey) + compact('players', 'recent', 'agentHistory'));
    }

    public function transfer(Request $request)
    {
        $agent = $this->agent($request);
        $data = $request->validate([
            'from' => 'required|max:255',
            'to' => 'required|max:255',
            'amount' => 'required|numeric|min:' . \App\Services\WalletTransfer::MIN,
            'note' => 'nullable|string|max:120',
        ]);

        $scope = $agent->scopeIds();
        $from = \App\Services\WalletTransfer::findUser($data['from']);
        $to = \App\Services\WalletTransfer::findUser($data['to']);
        if (! $from || ! in_array((int) $from->agent_id, $scope, true) || ! $to || ! in_array((int) $to->agent_id, $scope, true)) {
            return back()->withInput()->withErrors(['from' => 'Dono players aapke network ke hone chahiye.']);
        }

        try {
            $ref = \App\Services\WalletTransfer::send($from, $to, (float) $data['amount'], "agent {$agent->name}", $data['note'] ?? null);
        } catch (\DomainException $e) {
            return back()->withInput()->withErrors(['amount' => $e->getMessage()]);
        }

        \App\Models\AuditLog::record('Agent Panel', 'Agent Transfer', "{$agent->name} (#{$agent->id}) moved ₹{$data['amount']} from #{$from->id} to #{$to->id} [{$ref}]");

        return back()->with('status', '₹' . number_format((float) $data['amount'], 2) . " {$from->name} se {$to->name} ko transfer ho gaye. Ref {$ref}");
    }

    /** Looks up an agent by its unique code so the sender can confirm who will receive the money. */
    public function agentLookup(Request $request)
    {
        $me = $this->agent($request);
        $to = Agent::findByReferralCode($request->query('code'));
        if (! $to || $to->id === $me->id) {
            return response()->json(['found' => false]);
        }

        return response()->json(['found' => true, 'name' => $to->name, 'type' => $to->roleLabel(), 'code' => $to->referralCode()]);
    }

    /** Any agent (master, distributor, retailer) sends credit or commission balance to another agent by its code. */
    public function transferToAgent(Request $request)
    {
        $me = $this->agent($request);

        $data = $request->validate([
            'code' => 'required|string|max:20',
            'kind' => 'required|in:' . implode(',', array_keys(\App\Services\AgentTransfer::KINDS)),
            'amount' => 'required|numeric|min:' . \App\Services\AgentTransfer::MIN,
            'note' => 'nullable|string|max:120',
        ]);

        $to = Agent::findByReferralCode($data['code']);
        if (! $to) {
            return back()->withInput()->withErrors(['code' => 'Is agent code ka koi active agent nahi mila. Code dobara check karein.']);
        }
        if ($to->id === $me->id) {
            return back()->withInput()->withErrors(['code' => 'Ye aapka apna code hai.']);
        }

        try {
            $ref = \App\Services\AgentTransfer::send($me, $to, (float) $data['amount'], $data['kind'], "{$me->roleLabel()} {$me->name}", $data['note'] ?? null);
        } catch (\DomainException $e) {
            return back()->withInput()->withErrors(['amount' => $e->getMessage()]);
        }

        return back()->with('status', '₹' . number_format((float) $data['amount'], 2) . ' ' . strtolower(\App\Services\AgentTransfer::KINDS[$data['kind']]) . " {$to->name} ({$to->roleLabel()}) ko bhej diya. Ref {$ref}");
    }
}
