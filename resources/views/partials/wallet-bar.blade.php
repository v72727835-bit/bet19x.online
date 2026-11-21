{{-- The signature marquee panel: balance for players, a plain welcome for visitors (login lives in the header only). --}}
<div class="bx-marquee" style="margin-top:16px;">
    <div class="bx-marquee-in">
        @auth
            @php
                $wbWallet = \App\Models\Wallet::where('user_id', Auth::id())->first();
                $wbMain = (float) ($wbWallet->deposit_balance ?? 0) + (float) ($wbWallet->winnings_balance ?? 0);
                $wbWin = (float) ($wbWallet->winnings_balance ?? 0);
            @endphp
            <div>
                <div class="bx-marquee-label">Your balance</div>
                <div class="bx-money bx-marquee-amt">&#8377;{{ number_format($wbMain, 2) }}</div>
                <div class="bx-marquee-label" style="margin-top:4px;">Winnings you can withdraw: <span style="color:var(--bx-win); font-weight:800;">&#8377;{{ number_format($wbWin, 2) }}</span></div>
            </div>
            <div class="bx-marquee-actions">
                <a href="{{ route('wallet.deposit') }}" class="bx-btn bx-btn-gold">Deposit</a>
                <a href="{{ route('wallet.withdraw') }}" class="bx-btn bx-btn-ghost">Withdraw</a>
            </div>
        @else
            <div>
                <div class="bx-money bx-marquee-amt" style="font-size:34px;"><span id="bx-gamecount">Thousands of</span> games</div>
                <div class="bx-marquee-label" style="margin-top:6px;">Slots, live tables, crash and card games from top studios. Browse freely — log in when you want to play.</div>
            </div>
        @endauth
    </div>
</div>
