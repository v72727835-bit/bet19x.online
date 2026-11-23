@extends('agent.layout')
@section('title', 'Bet history')
@section('content')
    @php
        $signed = fn ($v, $dec = 2) => ($v > 0 ? '+' : ($v < 0 ? '−' : '')) . '₹' . number_format(abs($v), $dec);
        $houseResult = $totals->bet - $totals->win;
        $onePlayer = request('player') ? ($users[(int) request('player')] ?? null) : null;
    @endphp
    <div class="bx-pagehead">
        <h1 class="bx-h1">Bet history</h1>
        <p class="bx-sub">
            @if (request('player'))
                Rounds played by {{ $onePlayer->name ?? 'player #' . request('player') }} — <a class="bx-link" href="{{ route('agent.bets', ['range' => $rangeKey]) }}">show all players</a>
            @else
                Who is betting how much in your network, and the profit or loss of every round.
            @endif
        </p>
    </div>

    <div class="bx-stats" style="margin-top:12px;">
        <div class="bx-stat"><b>{{ number_format($totals->rounds) }}</b><span>Rounds — {{ $totals->rounds_won }} won, {{ $totals->rounds_lost }} lost</span></div>
        <div class="bx-stat"><b>₹{{ number_format($totals->bet, 0) }}</b><span>Staked</span></div>
        <div class="bx-stat"><b class="ag-pos">+₹{{ number_format($totals->profit, 0) }}</b><span>Players' profit</span></div>
        <div class="bx-stat"><b class="ag-neg">−₹{{ number_format($totals->loss, 0) }}</b><span>Players' loss</span></div>
        <div class="bx-stat"><b class="{{ $totals->net >= 0 ? 'ag-pos' : 'ag-neg' }}">{{ $signed($totals->net, 0) }}</b><span>Players' net</span></div>
        <div class="bx-stat"><b class="{{ $houseResult >= 0 ? 'ag-pos' : 'ag-neg' }}">{{ $signed($houseResult * $rate) }}</b><span>Your share</span></div>
    </div>

    @unless (request('player'))
        <section class="bx-section">
            <div class="bx-section-head"><h2 class="bx-h2">Who is betting how much</h2></div>
            <div class="bx-panel ag-scroll" style="padding:4px;">
                @if ($players->isEmpty())
                    <p class="bx-empty">No bets in this period.</p>
                @else
                    <table class="ag-table">
                        <thead><tr><th>Player</th><th>Rounds</th><th>Staked</th><th>Won</th><th>Profit</th><th>Loss</th><th>Net</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($players as $p)
                                @php
                                    $pu = $users[(int) $p->user_id] ?? null;
                                @endphp
                                <tr>
                                    <td><b>{{ $pu->name ?? '#' . $p->user_id }}</b><div class="ag-mut">{{ $pu->email ?? '' }}</div></td>
                                    <td>{{ number_format($p->rounds) }}</td>
                                    <td>₹{{ number_format($p->bet, 0) }}</td>
                                    <td>₹{{ number_format($p->win, 0) }}</td>
                                    <td class="ag-pos">+₹{{ number_format($p->profit, 0) }}</td>
                                    <td class="ag-neg">−₹{{ number_format($p->loss, 0) }}</td>
                                    <td class="{{ $p->net >= 0 ? 'ag-pos' : 'ag-neg' }}">{{ $signed($p->net, 0) }}</td>
                                    <td><a class="bx-link" href="{{ route('agent.bets', ['player' => $p->user_id, 'range' => $rangeKey]) }}">Rounds</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </section>
    @endunless

    <section class="bx-section">
        <div class="bx-section-head"><h2 class="bx-h2">Rounds</h2></div>
        <div class="bx-panel ag-scroll" style="padding:4px;">
            @if ($bets->isEmpty())
                <p class="bx-empty">No bets in this period.</p>
            @else
                <table class="ag-table">
                    <thead><tr><th>Time</th><th>Player</th><th>Game</th><th>Stake</th><th>Win</th><th>Profit / Loss</th><th>Your share</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach ($bets as $r)
                            @php
                                $bu = $users[(int) $r->user_id] ?? null;
                            @endphp
                            <tr>
                                <td class="ag-mut">{{ $r->started_at->format('d M, h:i A') }}</td>
                                <td><b>{{ $bu->name ?? '#' . $r->user_id }}</b></td>
                                <td>{{ $games[$r->game_id]['title'] ?? $r->game_id }}</td>
                                <td>₹{{ number_format($r->bet, 2) }}</td>
                                <td>₹{{ number_format($r->win, 2) }}</td>
                                <td>
                                    @if ($r->refunded)
                                        <span class="ag-mut">—</span>
                                    @elseif ($r->result > 0)
                                        <span class="ag-pos">Profit +₹{{ number_format($r->result, 2) }}</span>
                                    @elseif ($r->result < 0)
                                        <span class="ag-neg">Loss −₹{{ number_format(abs($r->result), 2) }}</span>
                                    @else
                                        <span class="ag-mut">Even</span>
                                    @endif
                                </td>
                                <td class="{{ $r->refunded ? 'ag-mut' : (-$r->result >= 0 ? 'ag-pos' : 'ag-neg') }}">{{ $r->refunded ? '—' : $signed(-$r->result * $rate) }}</td>
                                <td>
                                    @if ($r->refunded)
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
    </section>
@endsection
