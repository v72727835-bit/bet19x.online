@extends('agent.layout')
@section('title', 'Profit / Loss')
@section('content')
    @php
        $money = fn ($v, $dec = 2) => ($v < 0 ? '−' : '') . '₹' . number_format(abs($v), $dec);
        $signed = fn ($v, $dec = 2) => ($v > 0 ? '+' : ($v < 0 ? '−' : '')) . '₹' . number_format(abs($v), $dec);
        $pct = rtrim(rtrim(number_format($agent->commission_rate, 2), '0'), '.');
        $show = request('show');
    @endphp

    <div class="bx-pagehead">
        <h1 class="bx-h1">Profit / Loss</h1>
        <p class="bx-sub">How much each of your players won or lost in {{ strtolower($ranges[$rangeKey]) }}, and what that means for your {{ $pct }}% share.</p>
    </div>

    <div class="bx-stats" style="margin-top:12px;">
        <div class="bx-stat"><b class="ag-pos">{{ $summary['in_profit'] }}</b><span>Players in profit</span></div>
        <div class="bx-stat"><b class="ag-pos">{{ $signed($summary['profit'], 0) }}</b><span>Total won by them</span></div>
        <div class="bx-stat"><b class="ag-neg">{{ $summary['in_loss'] }}</b><span>Players in loss</span></div>
        <div class="bx-stat"><b class="ag-neg">−₹{{ number_format($summary['loss'], 0) }}</b><span>Total lost by them</span></div>
        <div class="bx-stat"><b>₹{{ number_format($summary['staked'], 0) }}</b><span>Total staked</span></div>
        <div class="bx-stat"><b class="{{ $summary['net_players'] >= 0 ? 'ag-pos' : 'ag-neg' }}">{{ $signed($summary['net_players'], 0) }}</b><span>Players' net result</span></div>
        <div class="bx-stat"><b class="{{ $summary['share'] >= 0 ? 'ag-pos' : 'ag-neg' }}">{{ $signed($summary['share']) }}</b><span>Your {{ $pct }}% share</span></div>
        <div class="bx-stat"><b>{{ $summary['players'] }}</b><span>Players who bet</span></div>
    </div>

    <div class="ag-bar" style="margin-top:16px;">
        @foreach (['' => 'All players', 'profit' => 'In profit', 'loss' => 'In loss'] as $val => $label)
            <a href="{{ route('agent.profit-loss', ['range' => $rangeKey, 'show' => $val ?: null]) }}" class="{{ (string) $show === $val ? 'is-on' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <section class="bx-section" style="margin-top:10px;">
        <div class="bx-panel ag-scroll" style="padding:4px;">
            @if ($list->isEmpty())
                <p class="bx-empty">No player results for this period.</p>
            @else
                <table class="ag-table">
                    <thead><tr><th>Player</th><th>Via</th><th>Bets</th><th>Staked</th><th>Won</th><th>Player profit / loss</th><th>Your share</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($list as $r)
                            @php
                                $u = $users[(int) $r->user_id] ?? null;
                            @endphp
                            <tr>
                                <td><b>{{ $u->name ?? 'Player #' . $r->user_id }}</b><div class="ag-mut">{{ $u->email ?? '' }}</div></td>
                                <td class="ag-mut">{{ $u && $u->agent_id === $agent->id ? 'You' : ($u->agent->name ?? '—') }}</td>
                                <td>{{ number_format($r->rounds) }}</td>
                                <td>₹{{ number_format($r->bet, 0) }}</td>
                                <td>₹{{ number_format($r->win, 0) }}</td>
                                <td>
                                    <span class="{{ $r->pl >= 0 ? 'ag-pos' : 'ag-neg' }}">{{ $signed($r->pl) }}</span>
                                    <span class="bx-badge {{ $r->pl > 0 ? 'bx-badge-ok' : ($r->pl < 0 ? 'bx-badge-no' : 'bx-badge-wait') }}" style="margin-left:6px;">{{ $r->pl > 0 ? 'Profit' : ($r->pl < 0 ? 'Loss' : 'Even') }}</span>
                                </td>
                                <td class="{{ -$r->pl >= 0 ? 'ag-pos' : 'ag-neg' }}">{{ $signed(-$r->pl * $rate) }}</td>
                                <td><a class="bx-link" href="{{ route('agent.bets', ['player' => $r->user_id, 'range' => $rangeKey]) }}">Bets</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </section>

    <section class="bx-section">
        <div class="bx-section-head"><h2 class="bx-h2">By game</h2></div>
        <div class="bx-panel ag-scroll" style="padding:4px;">
            @if ($byGame->isEmpty())
                <p class="bx-empty">No games played in this period.</p>
            @else
                <table class="ag-table">
                    <thead><tr><th>Game</th><th>Bets</th><th>Staked</th><th>Won</th><th>Players' profit / loss</th><th>Your share</th></tr></thead>
                    <tbody>
                        @foreach ($byGame as $g)
                            @php
                                $gpl = (float) $g->win - (float) $g->bet;
                            @endphp
                            <tr>
                                <td><b>{{ $games[$g->game_id]['title'] ?? $g->game_id }}</b></td>
                                <td>{{ number_format($g->rounds) }}</td>
                                <td>₹{{ number_format($g->bet, 0) }}</td>
                                <td>₹{{ number_format($g->win, 0) }}</td>
                                <td class="{{ $gpl >= 0 ? 'ag-pos' : 'ag-neg' }}">{{ $signed($gpl) }}</td>
                                <td class="{{ -$gpl >= 0 ? 'ag-pos' : 'ag-neg' }}">{{ $signed(-$gpl * $rate) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </section>
@endsection
