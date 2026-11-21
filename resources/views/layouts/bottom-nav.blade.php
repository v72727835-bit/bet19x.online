@php
    $tabOn = fn ($name) => request()->routeIs($name) ? ' is-on' : '';
@endphp
<nav class="bx-tabbar" aria-label="Quick">
    <a href="{{ route('dashboard') }}" class="bx-tab{{ $tabOn('dashboard') }}">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 11l9-8 9 8M5 9.5V20h5v-6h4v6h5V9.5"/></svg>
        <span>Home</span>
    </a>
    <button type="button" class="bx-tab" onclick="window.dispatchEvent(new CustomEvent('open-games-sidebar'))">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="7" height="7" rx="2"/><rect x="13" y="4" width="7" height="7" rx="2"/><rect x="4" y="13" width="7" height="7" rx="2"/><rect x="13" y="13" width="7" height="7" rx="2"/></svg>
        <span>Casino</span>
    </button>
    <a href="{{ route('wallet.deposit') }}" class="bx-tab bx-tab-chip" aria-label="Deposit">
        <span class="bx-chip"><i>+</i></span>
        <span>Deposit</span>
    </a>
    <a href="{{ route('wallet.index') }}" class="bx-tab{{ $tabOn('wallet.index') }}">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="6" width="18" height="13" rx="3"/><path stroke-linecap="round" d="M3 10h18M16 14.5h2"/></svg>
        <span>Wallet</span>
    </a>
    <a href="{{ route('profile.edit') }}" class="bx-tab{{ $tabOn('profile.*') }}">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/></svg>
        <span>Account</span>
    </a>
</nav>
