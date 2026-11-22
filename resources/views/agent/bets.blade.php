@extends('agent.layout')
@section('title', 'Bet history')
@section('content')
    @php
        $money = fn ($v, $dec = 2) => ($v < 0 ? '−' : '') . '₹' . number_format(abs($v), $dec);
        $hrTotal = (float) $totals->bet - (float) $totals->win;
        $onePlayer = request('player') ? ($users[(int) request('player')] ?? null) : null;
    @endphp
    <div class="bx-pagehead">
        <h1 class="bx-h1">Bet history</h1>
        <p class="bx-sub">
            @if (request('player'))
                Bets by {{ $onePlayer->name ?? 'player #' . request('player') }} — <a class="bx-link" href="{{ route('agent.bets', ['range' => $rangeKey]) }}">show all players</a>
            @else
                Every bet placed by players in your network.
            @endif
        </p>
    </div>

    <div class="bx-stats" style="margin-top:12px;">
        <div class="bx-stat"><b>{{ number_format($totals->rounds) }}</b><span>Bets</span></div>
        <div class="bx-stat"><b>₹{{ number_format($totals->bet, 0) }}</b><span>Staked</span></div>
        <div class="bx-stat"><b>₹{{ number_format($totals->win, 0) }}</b><span>Won by players</span></div>
        <div class="bx-stat"><b class="{{ $hrTotal >= 0 ? 'ag-pos' : 'ag-neg' }}">{{ $money($hrTotal * $rate) }}</b><span>Your share</span></div>
    </div>

    <div class="bx-panel ag-scroll" style="padding:4px; margin-top:14px;">
        @if ($bets->isEmpty())
            <p class="bx-empty">No bets in this period.</p>
        @else
            <table class="ag-table">
                <thead><tr><th>Time</th><th>Player</th><th>Game</th><th>Stake</th><th>Win</th><th>Player result</th><th>Your share</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach ($bets as $b)
                        @php
                            $bu = $users[(int) $b->user_id] ?? null;
                            $pr = (float) $b->win - (float) $b->bet;
                        @endphp
                        <tr>
                            <td class="ag-mut">{{ $b->created_at?->format('d M, h:i A') }}</td>
                            <td><b>{{ $bu->name ?? '#' . $b->user_id }}</b></td>
                            <td>{{ $games[$b->game_id]['title'] ?? $b->game_id }}</td>
                            <td>₹{{ number_format($b->bet, 2) }}</td>
                            <td>₹{{ number_format($b->win, 2) }}</td>
                            <td class="{{ $pr >= 0 ? 'ag-pos' : 'ag-neg' }}">{{ $money($pr) }}</td>
                            <td class="{{ $b->rolled_back ? 'ag-mut' : (-$pr >= 0 ? 'ag-pos' : 'ag-neg') }}">{{ $b->rolled_back ? '—' : $money(-$pr * $rate) }}</td>
                            <td>
                                @if ($b->rolled_back)
                                    <span class="bx-badge bx-badge-wait">Refunded</span>
                                @else
                                    <span class="bx-badge bx-badge-ok">Settled</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    @if ($bets->hasPages())
        <div style="margin-top:12px; display:flex; justify-content:space-between;">
            @if ($bets->previousPageUrl())<a class="bx-btn bx-btn-ghost bx-btn-sm" href="{{ $bets->previousPageUrl() }}">Newer</a>@else<span></span>@endif
            @if ($bets->nextPageUrl())<a class="bx-btn bx-btn-ghost bx-btn-sm" href="{{ $bets->nextPageUrl() }}">Older</a>@endif
        </div>
    @endif
@endsection
