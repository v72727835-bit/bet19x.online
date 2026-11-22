@extends('agent.layout')
@section('title', 'Overview')
@section('content')
    @php
        $pct = rtrim(rtrim(number_format($agent->commission_rate, 2), '0'), '.');
        $money = fn ($v, $dec = 2) => ($v < 0 ? '−' : '') . '₹' . number_format(abs($v), $dec);
        $link = $agent->referralLink();
        $shareText = 'Join me on ' . ($siteSetting->site_name ?? 'BET19X') . ': ' . $link;
    @endphp

    <div class="bx-pagehead">
        <h1 class="bx-h1">Hello, {{ $agent->name }}</h1>
        <p class="bx-sub">{{ $agent->roleLabel() }} — {{ $agent->email }}@if ($agent->parent) — under {{ $agent->parent->name }} ({{ $agent->parent->roleLabel() }})@endif</p>
    </div>

    {{-- What the admin set for this agent --}}
    <div class="bx-marquee" style="margin-top:14px;">
        <div class="bx-marquee-in">
            <div>
                <div class="bx-marquee-label">Your share of player win / loss</div>
                <div class="bx-money bx-marquee-amt">{{ $pct }}%</div>
                <div class="bx-marquee-label" style="margin-top:4px;">Set by admin. You earn {{ $pct }}% when your players lose, and carry {{ $pct }}% when they win.</div>
            </div>
            <div style="margin-left:auto; text-align:right;">
                <div class="bx-marquee-label">Your share ({{ $ranges[$rangeKey] }})</div>
                <div class="bx-money {{ $stats['share'] >= 0 ? 'ag-pos' : 'ag-neg' }}" style="font-size:36px; line-height:1.05;">{{ $money($stats['share']) }}</div>
                <div class="bx-marquee-label">Credit given: ₹{{ number_format($agent->credit_limit, 0) }} — available ₹{{ number_format($agent->creditAvailable(), 0) }}</div>
            </div>
        </div>
    </div>

    {{-- Referral link --}}
    <section class="bx-section" x-data="{ copied: '' }">
        <div class="bx-panel">
            <div style="display:flex; flex-wrap:wrap; align-items:center; gap:10px;">
                <div class="bx-grow">
                    <b style="font-size:16px;">Your referral link</b>
                    <small style="color:var(--bx-muted);">Players who sign up with this link or code {{ $agent->referralCode() }} join your network automatically.</small>
                </div>
            </div>
            <div style="display:flex; gap:8px; margin-top:10px; flex-wrap:wrap;">
                <input class="bx-input" style="flex:1; min-width:220px; font-size:14px;" readonly value="{{ $link }}" onclick="this.select()" aria-label="Referral link">
                <button type="button" class="bx-btn bx-btn-gold" @click="navigator.clipboard.writeText(@js($link)); copied = 'link'; setTimeout(() => copied = '', 1500)" x-text="copied === 'link' ? 'Copied' : 'Copy link'">Copy link</button>
                <a class="bx-btn bx-btn-green" target="_blank" rel="noopener" href="https://wa.me/?text={{ urlencode($shareText) }}">Share on WhatsApp</a>
                <a class="bx-btn bx-btn-ghost" target="_blank" rel="noopener" href="https://t.me/share/url?url={{ urlencode($link) }}">Telegram</a>
            </div>
        </div>
    </section>

    <section class="bx-section">
        <div class="bx-stats">
            <div class="bx-stat"><b>{{ number_format($stats['players']) }}</b><span>Players in your network</span></div>
            <div class="bx-stat"><b>{{ number_format($stats['new_players']) }}</b><span>New sign-ups</span></div>
            <div class="bx-stat"><b>{{ number_format($stats['active_players']) }}</b><span>Players who bet</span></div>
            <div class="bx-stat"><b>{{ number_format($stats['rounds']) }}</b><span>Bets placed</span></div>
            <div class="bx-stat"><b>₹{{ number_format($stats['staked'], 0) }}</b><span>Total staked</span></div>
            <div class="bx-stat"><b>₹{{ number_format($stats['won'], 0) }}</b><span>Total won by players</span></div>
            <div class="bx-stat"><b class="{{ $stats['house_result'] >= 0 ? 'ag-pos' : 'ag-neg' }}">{{ $money($stats['house_result'], 0) }}</b><span>Players' loss (house result)</span></div>
            <div class="bx-stat"><b class="{{ $stats['share'] >= 0 ? 'ag-pos' : 'ag-neg' }}">{{ $money($stats['share']) }}</b><span>Your {{ $pct }}% share</span></div>
            <div class="bx-stat"><b>₹{{ number_format($stats['deposits'], 0) }}</b><span>Deposits approved @if ($stats['deposits_pending']) — {{ $stats['deposits_pending'] }} pending @endif</span></div>
            <div class="bx-stat"><b>₹{{ number_format($stats['withdrawals'], 0) }}</b><span>Withdrawals paid @if ($stats['withdrawals_pending']) — {{ $stats['withdrawals_pending'] }} pending @endif</span></div>
            <div class="bx-stat"><b>₹{{ number_format($stats['deposits'] - $stats['withdrawals'], 0) }}</b><span>Net deposits</span></div>
            <div class="bx-stat"><b>{{ $stats['sub_agents'] }}</b><span>Agents under you</span></div>
        </div>
    </section>

    <section class="bx-section">
        <div class="bx-section-head"><h2 class="bx-h2">Day by day</h2></div>
        <div class="bx-panel ag-scroll" style="padding:4px;">
            <table class="ag-table">
                <thead><tr><th>Date</th><th>Staked</th><th>Won</th><th>Players' loss</th><th>Your share</th><th>Deposits</th></tr></thead>
                <tbody>
                    @foreach ($daily as $day => $d)
                        @php
                            $hr = $d['bet'] - $d['win'];
                        @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($day)->format('d M, D') }}</td>
                            <td>₹{{ number_format($d['bet'], 0) }}</td>
                            <td>₹{{ number_format($d['win'], 0) }}</td>
                            <td class="{{ $hr >= 0 ? 'ag-pos' : 'ag-neg' }}">{{ $money($hr, 0) }}</td>
                            <td class="{{ $hr >= 0 ? 'ag-pos' : 'ag-neg' }}">{{ $money($hr * $rate) }}</td>
                            <td>₹{{ number_format($d['deposits'], 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="bx-section">
        <div class="bx-section-head"><h2 class="bx-h2">Top players</h2><a href="{{ route('agent.players') }}">All players</a></div>
        <div class="bx-panel ag-scroll" style="padding:4px;">
            @if ($top->isEmpty())
                <p class="bx-empty">No bets in this period yet. Share your referral link to bring players in.</p>
            @else
                <table class="ag-table">
                    <thead><tr><th>Player</th><th>Bets</th><th>Staked</th><th>Won</th><th>Your share</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($top as $t)
                            @php
                                $tu = $topUsers[$t->user_id] ?? null;
                                $hr = (float) $t->bet - (float) $t->win;
                            @endphp
                            <tr>
                                <td><b>{{ $tu->name ?? 'Player #' . $t->user_id }}</b><div class="ag-mut">{{ $tu->email ?? '' }}</div></td>
                                <td>{{ number_format($t->rounds) }}</td>
                                <td>₹{{ number_format($t->bet, 0) }}</td>
                                <td>₹{{ number_format($t->win, 0) }}</td>
                                <td class="{{ $hr >= 0 ? 'ag-pos' : 'ag-neg' }}">{{ $money($hr * $rate) }}</td>
                                <td><a class="bx-link" href="{{ route('agent.bets', ['player' => $t->user_id, 'range' => $rangeKey]) }}">Bets</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </section>
@endsection
