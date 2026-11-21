@php
    $navWallet = auth()->check() ? \App\Models\Wallet::where('user_id', auth()->id())->first() : null;
    $navBalance = $navWallet ? (float) $navWallet->deposit_balance + (float) $navWallet->winnings_balance : 0;
@endphp

@if (!empty($siteSetting?->ticker_text))
    <div class="bx-ticker" aria-hidden="true"><span>{{ $siteSetting->ticker_text }}</span></div>
@endif

<header class="bx-header">
    <div class="bx-wrap bx-header-in">
        <a href="{{ route('dashboard') }}" class="bx-logo" aria-label="{{ $siteSetting->site_name ?? 'Home' }}">
            @if (!empty($siteSetting?->logo_path))
                <img src="{{ asset('storage/' . $siteSetting->logo_path) }}" alt="{{ $siteSetting->site_name ?? 'BET19X' }}">
            @else
                <span class="bx-logo-txt">{{ $siteSetting->site_name ?? 'BET19X' }}</span>
            @endif
        </a>

        @if (($menuItems ?? collect())->count())
            <nav class="bx-header-links" aria-label="Main">
                @foreach ($menuItems as $item)
                    <a href="{{ $item->url }}">{{ $item->label }}</a>
                @endforeach
            </nav>
        @endif

        <div class="bx-header-right">
            @auth
                <a href="{{ route('wallet.deposit') }}" class="bx-bal" aria-label="Balance, add money">
                    <span class="bx-bal-amt">&#8377;{{ number_format($navBalance, 0) }}</span>
                    <span class="bx-bal-plus">+</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="bx-btn bx-btn-ghost bx-btn-sm">Log in</a>
                <a href="{{ route('register') }}" class="bx-btn bx-btn-gold bx-btn-sm">Join now</a>
            @endauth
            <button type="button" class="bx-icon-btn" aria-label="Open menu" onclick="window.dispatchEvent(new CustomEvent('open-games-sidebar'))">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h10"/></svg>
            </button>
        </div>
    </div>
</header>
