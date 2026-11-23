<x-app-layout>
    <div class="bx-wrap" style="max-width:760px;">
        <div class="bx-pagehead">
            <h1 class="bx-h1">Bet history</h1>
            <p class="bx-sub">Every round you played, newest first. Live games send the stake and the payout separately; here they are joined into one line per round.</p>
        </div>

        <div class="bx-stats" style="margin-top:12px;">
            <div class="bx-stat"><b>{{ number_format($totals->rounds) }}</b><span>Rounds played</span></div>
            <div class="bx-stat"><b>&#8377;{{ number_format($totals->bet, 2) }}</b><span>Total staked</span></div>
            <div class="bx-stat"><b style="color:var(--bx-win);">+&#8377;{{ number_format($totals->profit, 2) }}</b><span>Profit — {{ $totals->rounds_won }} rounds won</span></div>
            <div class="bx-stat"><b style="color:#ff8a9a;">−&#8377;{{ number_format($totals->loss, 2) }}</b><span>Loss — {{ $totals->rounds_lost }} rounds lost</span></div>
            <div class="bx-stat"><b>&#8377;{{ number_format($totals->win, 2) }}</b><span>Total won</span></div>
            <div class="bx-stat"><b style="color:{{ $totals->net >= 0 ? 'var(--bx-win)' : '#ff8a9a' }};">{{ $totals->net >= 0 ? '+' : '−' }}&#8377;{{ number_format(abs($totals->net), 2) }}</b><span>Net result</span></div>
        </div>

        <section class="bx-section">
            <div class="bx-panel" style="padding:4px 16px;">
                @if ($bets->isEmpty())
                    <p class="bx-empty" style="padding:24px 0;">No bets yet. Pick a game on the home page and your rounds will appear here.</p>
                @else
                    <ul class="bx-list">
                        @foreach ($bets as $r)
                            <li>
                                <div class="bx-grow">
                                    <b>{{ $games[$r->game_id]['title'] ?? ($r->game_id ?: 'Game') }}</b>
                                    <small>
                                        {{ $r->started_at->format('d M Y, h:i A') }}
                                        — staked &#8377;{{ number_format($r->bet, 2) }}, won &#8377;{{ number_format($r->win, 2) }}
                                    </small>
                                </div>
                                @if ($r->refunded)
                                    <span class="bx-badge bx-badge-wait">Refunded</span>
                                @elseif ($r->result > 0)
                                    <span style="text-align:right;"><span class="bx-money bx-plus" style="font-size:19px;">+&#8377;{{ number_format($r->result, 2) }}</span><small style="display:block; color:var(--bx-win); font-size:11px; font-weight:700;">Profit</small></span>
                                @elseif ($r->result < 0)
                                    <span style="text-align:right;"><span class="bx-money bx-minus" style="font-size:19px;">−&#8377;{{ number_format(abs($r->result), 2) }}</span><small style="display:block; color:#ff8a9a; font-size:11px; font-weight:700;">Loss</small></span>
                                @else
                                    <span class="bx-badge bx-badge-wait">Even</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            @if ($bets->hasPages())
                <div style="margin-top:12px; display:flex; justify-content:space-between;">
                    @if ($bets->previousPageUrl())<a class="bx-btn bx-btn-ghost bx-btn-sm" href="{{ $bets->previousPageUrl() }}">Newer</a>@else<span></span>@endif
                    @if ($bets->nextPageUrl())<a class="bx-btn bx-btn-ghost bx-btn-sm" href="{{ $bets->nextPageUrl() }}">Older</a>@endif
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
