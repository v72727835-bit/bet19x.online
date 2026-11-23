<x-admin-layout title="Game Bet History">
    <form method="GET" class="fx-card p-4 mb-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="fx-muted text-xs block mb-1">Player</label>
            <input type="text" name="user" value="{{ request('user') }}" placeholder="User ID, name, email, phone" class="fx-input text-sm">
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">Game ID</label>
            <input type="text" name="game" value="{{ request('game') }}" placeholder="e.g. aviator" class="fx-input text-sm">
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">From</label>
            <input type="date" name="from" value="{{ request('from') }}" class="fx-input text-sm">
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">To</label>
            <input type="date" name="to" value="{{ request('to') }}" class="fx-input text-sm">
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">Status</label>
            <select name="status" class="fx-input text-sm">
                <option value="">All</option>
                <option value="settled" @selected(request('status')==='settled')>Settled</option>
                <option value="refunded" @selected(request('status')==='refunded')>Refunded (rolled back)</option>
            </select>
        </div>
        <button class="fx-btn text-sm">Filter</button>
        <a href="{{ route('admin.game-bets.index') }}" class="fx-btn-outline text-sm">Reset</a>
    </form>

    @php
        $ggr = $totals->bet - $totals->win;
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <div class="fx-card p-4"><p class="fx-muted text-xs">Rounds played</p><p class="fx-heading text-2xl">{{ number_format($totals->rounds) }}</p><p class="fx-muted text-xs">{{ number_format($totals->rounds_won) }} won · {{ number_format($totals->rounds_lost) }} lost</p></div>
        <div class="fx-card p-4"><p class="fx-muted text-xs">Players</p><p class="fx-heading text-2xl">{{ number_format($totals->players) }}</p></div>
        <div class="fx-card p-4"><p class="fx-muted text-xs">Total staked</p><p class="fx-heading text-2xl">₹{{ number_format($totals->bet, 2) }}</p></div>
        <div class="fx-card p-4"><p class="fx-muted text-xs">Total paid out</p><p class="fx-heading text-2xl">₹{{ number_format($totals->win, 2) }}</p></div>
        <div class="fx-card p-4"><p class="fx-muted text-xs">Players' profit (rounds they won)</p><p class="fx-heading text-2xl" style="color: var(--fx-green);">+₹{{ number_format($totals->profit, 2) }}</p></div>
        <div class="fx-card p-4"><p class="fx-muted text-xs">Players' loss (rounds they lost)</p><p class="fx-heading text-2xl" style="color: var(--fx-red);">−₹{{ number_format($totals->loss, 2) }}</p></div>
        <div class="fx-card p-4"><p class="fx-muted text-xs">Players' net</p><p class="fx-heading text-2xl" style="color: {{ $totals->net >= 0 ? 'var(--fx-green)' : 'var(--fx-red)' }};">{{ $totals->net >= 0 ? '+' : '−' }}₹{{ number_format(abs($totals->net), 2) }}</p></div>
        <div class="fx-card p-4"><p class="fx-muted text-xs">House profit (GGR)</p><p class="fx-heading text-2xl" style="color: {{ $ggr >= 0 ? 'var(--fx-green)' : 'var(--fx-red)' }};">{{ $ggr >= 0 ? '+' : '−' }}₹{{ number_format(abs($ggr), 2) }}</p></div>
    </div>

    <div class="fx-card overflow-x-auto mb-4">
        <p class="fx-heading text-base px-4 pt-4">Who is betting how much</p>
        <table class="w-full fx-table">
            <thead><tr><th>Player</th><th>Rounds</th><th>Staked</th><th>Won</th><th>Profit</th><th>Loss</th><th>Net</th><th>Last played</th><th></th></tr></thead>
            <tbody>
                @forelse($players as $p)
                    @php
                        $pu = $users[$p->user_id] ?? null;
                    @endphp
                    <tr>
                        <td>
                            @if($pu)
                                <a href="{{ route('admin.users.show', $pu) }}" style="color: var(--fx-amber);">{{ $pu->name }}</a>
                                <div class="fx-muted text-xs">#{{ $pu->id }} · {{ $pu->email }}</div>
                            @else
                                <span class="fx-muted">#{{ $p->user_id }}</span>
                            @endif
                        </td>
                        <td>{{ number_format($p->rounds) }}</td>
                        <td>₹{{ number_format($p->bet, 2) }}</td>
                        <td>₹{{ number_format($p->win, 2) }}</td>
                        <td style="color: var(--fx-green);">+₹{{ number_format($p->profit, 2) }}</td>
                        <td style="color: var(--fx-red);">−₹{{ number_format($p->loss, 2) }}</td>
                        <td style="color: {{ $p->net >= 0 ? 'var(--fx-green)' : 'var(--fx-red)' }}; font-weight:600;">{{ $p->net >= 0 ? '+' : '−' }}₹{{ number_format(abs($p->net), 2) }}</td>
                        <td class="fx-muted whitespace-nowrap">{{ $p->last_played ? \Illuminate\Support\Carbon::parse($p->last_played)->diffForHumans() : '' }}</td>
                        <td><a href="{{ route('admin.game-bets.index', array_merge(request()->except('page'), ['user' => $p->user_id])) }}" class="text-xs" style="color: var(--fx-amber);">Rounds</a></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center fx-muted py-6">Koi bet nahi mili.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="fx-card overflow-x-auto">
        <p class="fx-heading text-base px-4 pt-4">Rounds <span class="fx-muted text-xs" style="font-family:inherit;">— stake and payout of each round joined into one line</span></p>
        <table class="w-full fx-table">
            <thead>
                <tr><th>Time</th><th>Player</th><th>Game</th><th>Round</th><th>Bet</th><th>Win</th><th>Profit / Loss</th><th>Balance after</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($bets as $r)
                    @include('admin.game-bets._round-row', ['r' => $r, 'games' => $games, 'pu' => $users[$r->user_id] ?? null, 'showPlayer' => true])
                @empty
                    <tr><td colspan="9" class="text-center fx-muted py-6">Koi bet nahi mili.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $bets->links() }}</div>
</x-admin-layout>
