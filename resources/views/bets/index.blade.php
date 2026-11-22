<x-app-layout>
    <div class="bx-wrap" style="max-width:760px;">
        <div class="bx-pagehead">
            <h1 class="bx-h1">Bet history</h1>
            <p class="bx-sub">Every round you have played, newest first.</p>
        </div>

        <div class="bx-stats" style="margin-top:12px;">
            <div class="bx-stat"><b>{{ number_format($totals->rounds ?? 0) }}</b><span>Rounds played</span></div>
            <div class="bx-stat"><b>&#8377;{{ number_format((float) ($totals->bet ?? 0), 2) }}</b><span>Total staked</span></div>
            <div class="bx-stat"><b style="color:var(--bx-win);">&#8377;{{ number_format((float) ($totals->win ?? 0), 2) }}</b><span>Total won</span></div>
            @php
                $net = (float) ($totals->win ?? 0) - (float) ($totals->bet ?? 0);
            @endphp
            <div class="bx-stat"><b style="color:{{ $net >= 0 ? 'var(--bx-win)' : '#ff8a9a' }};">{{ $net >= 0 ? '+' : '−' }}&#8377;{{ number_format(abs($net), 2) }}</b><span>Net result</span></div>
        </div>

        <section class="bx-section">
            <div class="bx-panel" style="padding:4px 16px;">
                @if ($bets->isEmpty())
                    <p class="bx-empty" style="padding:24px 0;">No bets yet. Pick a game on the home page and your rounds will appear here.</p>
                @else
                    <ul class="bx-list">
                        @foreach ($bets as $b)
                            @php
                                $g = $games[$b->game_id] ?? null;
                                $result = (float) $b->win - (float) $b->bet;
                            @endphp
                            <li>
                                <div class="bx-grow">
                                    <b>{{ $g['title'] ?? ($b->game_id ?: 'Game') }}</b>
                                    <small>
                                        {{ $b->created_at?->format('d M Y, h:i A') }}
                                        — staked &#8377;{{ number_format($b->bet, 2) }}, won &#8377;{{ number_format($b->win, 2) }}
                                    </small>
                                </div>
                                @if ($b->rolled_back)
                                    <span class="bx-badge bx-badge-wait">Refunded</span>
                                @else
                                    <span class="bx-money {{ $result >= 0 ? 'bx-plus' : 'bx-minus' }}" style="font-size:19px;">{{ $result >= 0 ? '+' : '−' }}&#8377;{{ number_format(abs($result), 2) }}</span>
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
