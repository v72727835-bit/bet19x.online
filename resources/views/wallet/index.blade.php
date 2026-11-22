<x-app-layout>
    <div class="bx-wrap" style="max-width:720px;">
        <div class="bx-pagehead">
            <h1 class="bx-h1">Wallet</h1>
        </div>

        @if (session('status'))
            <div class="bx-alert bx-alert-ok">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="bx-alert bx-alert-err">{{ session('error') }}</div>
        @endif

        <div class="bx-panel" style="margin-top:10px;">
            <div class="bx-marquee-label">Playable balance</div>
            <div class="bx-money" style="font-size:44px; color:var(--bx-gold); line-height:1;">&#8377;{{ number_format((float) $wallet->deposit_balance + (float) $wallet->winnings_balance, 2) }}</div>
            <ul class="bx-list" style="margin-top:10px;">
                <li><div class="bx-grow"><b>Deposited</b><small>Used first when you play</small></div><span class="bx-money" style="font-size:20px;">&#8377;{{ number_format($wallet->deposit_balance, 2) }}</span></li>
                <li><div class="bx-grow"><b>Winnings</b><small>Can be withdrawn to your bank</small></div><span class="bx-money bx-plus" style="font-size:20px;">&#8377;{{ number_format($wallet->winnings_balance, 2) }}</span></li>
                <li><div class="bx-grow"><b>Bonus</b><small>Promotional credit</small></div><span class="bx-money" style="font-size:20px; color:#f7a8c4;">&#8377;{{ number_format($wallet->bonus_balance, 2) }}</span></li>
            </ul>
            <div style="display:flex; gap:10px; margin-top:12px;">
                <a href="{{ route('wallet.deposit') }}" class="bx-btn bx-btn-gold" style="flex:1;">Deposit</a>
                <a href="{{ route('wallet.withdraw') }}" class="bx-btn bx-btn-ghost" style="flex:1;">Withdraw</a>
            </div>
        </div>

        @if ($depositRequests->isNotEmpty())
            <section class="bx-section">
                <div class="bx-section-head"><h2 class="bx-h2">Deposit requests</h2></div>
                <div class="bx-panel" style="padding:4px 16px;">
                    <ul class="bx-list">
                        @foreach ($depositRequests as $dr)
                            <li>
                                <div class="bx-grow">
                                    <b>&#8377;{{ number_format($dr->amount, 2) }} by {{ strtoupper($dr->method) }}</b>
                                    <small>{{ $dr->created_at->format('d M Y, h:i A') }}@if ($dr->status === 'rejected' && $dr->admin_remarks) — {{ $dr->admin_remarks }}@endif</small>
                                </div>
                                @if ($dr->status === 'approved')
                                    <span class="bx-badge bx-badge-ok">Added</span>
                                @elseif ($dr->status === 'rejected')
                                    <span class="bx-badge bx-badge-no">Rejected</span>
                                @else
                                    <span class="bx-badge bx-badge-wait">Waiting for approval</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif

        <section class="bx-section">
            <div class="bx-section-head"><h2 class="bx-h2">History</h2><a href="{{ route('bets.index') }}">Bet history</a></div>
            <div class="bx-panel" style="padding:4px 16px;">
                @if ($transactions->isEmpty())
                    <p class="bx-empty" style="padding:22px 0;">No transactions yet. Your deposits, withdrawals and refunds will show here.</p>
                @else
                    <ul class="bx-list">
                        @foreach ($transactions as $t)
                            @php($in = in_array($t->type, ['deposit', 'credit', 'refund']))
                            <li>
                                <div class="bx-grow">
                                    <b>{{ $t->description ?: ucfirst($t->type) }}</b>
                                    <small>{{ $t->created_at->format('d M Y, h:i A') }}@if ($t->reference_id) — {{ $t->reference_id }}@endif</small>
                                </div>
                                <span class="bx-money {{ $in ? 'bx-plus' : 'bx-minus' }}" style="font-size:19px;">{{ $in ? '+' : '−' }}&#8377;{{ number_format($t->amount, 2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            @if ($transactions->hasPages())
                <div style="margin-top:12px; display:flex; justify-content:space-between;">
                    @if ($transactions->previousPageUrl())<a class="bx-btn bx-btn-ghost bx-btn-sm" href="{{ $transactions->previousPageUrl() }}">Newer</a>@else<span></span>@endif
                    @if ($transactions->nextPageUrl())<a class="bx-btn bx-btn-ghost bx-btn-sm" href="{{ $transactions->nextPageUrl() }}">Older</a>@endif
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
