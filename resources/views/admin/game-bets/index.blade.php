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

    @php($ggr = (float) $totals->bet - (float) $totals->win)
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-4">
        <div class="fx-card p-4"><p class="fx-muted text-xs">Rounds</p><p class="fx-heading text-2xl">{{ number_format($totals->rounds) }}</p></div>
        <div class="fx-card p-4"><p class="fx-muted text-xs">Players</p><p class="fx-heading text-2xl">{{ number_format($totals->players) }}</p></div>
        <div class="fx-card p-4"><p class="fx-muted text-xs">Total staked</p><p class="fx-heading text-2xl">₹{{ number_format($totals->bet, 2) }}</p></div>
        <div class="fx-card p-4"><p class="fx-muted text-xs">Total paid out</p><p class="fx-heading text-2xl">₹{{ number_format($totals->win, 2) }}</p></div>
        <div class="fx-card p-4"><p class="fx-muted text-xs">House profit (GGR)</p><p class="fx-heading text-2xl" style="color: {{ $ggr >= 0 ? 'var(--fx-green)' : 'var(--fx-red)' }};">₹{{ number_format($ggr, 2) }}</p></div>
    </div>

    <div class="fx-card overflow-x-auto">
        <table class="w-full fx-table">
            <thead>
                <tr><th>Time</th><th>Player</th><th>Game</th><th>Round</th><th>Bet</th><th>Win</th><th>Player result</th><th>Balance after</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($bets as $b)
                    @php($pu = $users[$b->user_id] ?? null)
                    @php($res = (float) $b->win - (float) $b->bet)
                    <tr>
                        <td class="fx-muted whitespace-nowrap">{{ $b->created_at?->format('d M Y H:i:s') }}</td>
                        <td>
                            @if($pu)
                                <a href="{{ route('admin.users.show', $pu) }}" style="color: var(--fx-amber);">{{ $pu->name }}</a>
                                <div class="fx-muted text-xs">#{{ $pu->id }} · {{ $pu->email }}</div>
                            @else
                                <span class="fx-muted">#{{ $b->user_id }} (deleted)</span>
                            @endif
                        </td>
                        <td>{{ $games[$b->game_id]['title'] ?? $b->game_id }}<div class="fx-muted text-xs">{{ $b->game_id }}</div></td>
                        <td class="fx-muted text-xs">{{ $b->round_id }}<div>{{ $b->transaction_id }}</div></td>
                        <td>₹{{ number_format($b->bet, 2) }}</td>
                        <td>₹{{ number_format($b->win, 2) }}</td>
                        <td style="color: {{ $res >= 0 ? 'var(--fx-green)' : 'var(--fx-red)' }};">{{ $res >= 0 ? '+' : '-' }}₹{{ number_format(abs($res), 2) }}</td>
                        <td>₹{{ number_format($b->balance_after, 2) }}</td>
                        <td>
                            <span class="fx-badge" style="background-color: var(--fx-card-hover); color: {{ $b->rolled_back ? 'var(--fx-amber)' : 'var(--fx-green)' }};">{{ $b->rolled_back ? 'Refunded' : 'Settled' }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center fx-muted py-6">Koi bet nahi mili.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $bets->links() }}</div>
</x-admin-layout>
