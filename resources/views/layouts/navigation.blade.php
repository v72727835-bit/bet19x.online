<nav x-data="{ open: false }" style="background-color:#0D0D0D;" class="border-b border-[#24254A] fx-sticky-header">
    <style>
        @keyframes fxTickerScroll {
            0%   { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .fx-ticker-track {
            animation: fxTickerScroll 16s linear infinite;
            width: max-content;
        }
    </style>
    <div style="background-color:#1D1E3D;" class="overflow-hidden py-1.5">
        <div class="flex justify-center">
            <div class="flex fx-ticker-track">
                <div class="flex items-center gap-2 text-xs px-8 shrink-0">
                    <span class="relative flex h-2 w-2 shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" style="background-color:var(--fx-coral);"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2" style="background-color:var(--fx-coral);"></span>
                    </span>
                    <span style="color:var(--fx-amber); font-family:'Rajdhani',sans-serif; font-weight:600; letter-spacing:0.05em;">LIVE</span>
                    <span style="color:#F4F3FA;" class="whitespace-nowrap">{{ $siteSetting->ticker_text ?? 'IND vs AUS · contests close in 02:14:33 · build your XI now' }}</span>
                </div>
                <div class="flex items-center gap-2 text-xs px-8 shrink-0" aria-hidden="true">
                    <span class="relative flex h-2 w-2 shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" style="background-color:var(--fx-coral);"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2" style="background-color:var(--fx-coral);"></span>
                    </span>
                    <span style="color:var(--fx-amber); font-family:'Rajdhani',sans-serif; font-weight:600; letter-spacing:0.05em;">LIVE</span>
                    <span style="color:#F4F3FA;" class="whitespace-nowrap">{{ $siteSetting->ticker_text ?? 'IND vs AUS · contests close in 02:14:33 · build your XI now' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-[74px] items-center justify-between sm:hidden">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                @if (!empty($siteSetting?->logo_path))
                    <img src="{{ asset('storage/' . $siteSetting->logo_path) }}" alt="{{ $siteSetting->site_name ?? 'FantasyXI' }}" class="h-[72px] w-[72px] rounded-full object-cover">
                @else
                    <span class="flex h-[72px] w-[72px] items-center justify-center rounded-full" style="background-color:var(--fx-amber);">
                        <span style="color:#14152B; font-family:'Rajdhani',sans-serif; font-weight:700; font-size:28px;">FX</span>
                    </span>
                @endif
            </a>
            <div class="flex items-center gap-2">
                @guest
                    <a href="{{ route('login') }}" style="background-color:var(--fx-amber); color:#0D0D0D; font-family:'Rajdhani',sans-serif; font-weight:700;" class="rounded-full px-4 py-2 text-xs fx-btn-glow">Log in</a>
                    <a href="{{ route('register') }}" style="background-color:var(--fx-coral); color:#FFFFFF; font-family:'Rajdhani',sans-serif; font-weight:700;" class="rounded-full px-4 py-2 text-xs fx-btn-glow">Sign up</a>
                @endguest
                @auth
                <button @click="open = false; $dispatch('open-games-sidebar')" style="color:#F4F3FA;" class="p-2" aria-label="All games">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5h6v6H4V5zm10 0h6v6h-6V5zM4 15h6v6H4v-6zm10 0h6v6h-6v-6z"/></svg>
                </button>
                @endauth
                <button @click="open = !open" style="color:#F4F3FA;" class="p-2">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />

                    <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                </button>
            </div>
        </div>

        <div class="hidden sm:grid h-[74px] grid-cols-3 items-center">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 justify-self-start">
                @if (!empty($siteSetting?->logo_path))
                    <img src="{{ asset('storage/' . $siteSetting->logo_path) }}" alt="{{ $siteSetting->site_name ?? 'FantasyXI' }}" class="h-[72px] w-[72px] rounded-full object-cover">
                @else
                    <span class="flex h-[72px] w-[72px] items-center justify-center rounded-full" style="background-color:var(--fx-amber);">
                        <span style="color:#14152B; font-family:'Rajdhani',sans-serif; font-weight:700; font-size:28px;">FX</span>
                    </span>
                @endif
            </a>

            <div class="flex gap-8 justify-self-center">
                @foreach ($menuItems ?? [] as $item)
                    <a href="{{ $item->url }}" style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:600;" class="text-lg hover:text-[var(--fx-amber)] transition">{{ $item->label }}</a>
                @endforeach
            </div>

            <div class="flex items-center gap-4 justify-self-end">
                @auth
                    <a href="{{ route('wallet.index') }}" class="flex items-center gap-1.5 rounded-full px-3 py-1.5" style="background-color:#1D1E3D;text-decoration:none;cursor:pointer;">
                        <span style="color:var(--fx-amber); font-family:'Rajdhani',sans-serif; font-weight:700; font-size:14px;">₹{{ number_format((float) optional(\App\Models\Wallet::where('user_id', Auth::id())->first(), fn ($w) => $w->deposit_balance + $w->winnings_balance), 0) }}</span>
                        <span style="color:#8A8CB3; font-size:12px;">wallet</span>
                    </a>
                    @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button style="background-color:var(--fx-amber); color:#14152B; font-family:'Rajdhani',sans-serif; font-weight:700;" class="flex h-10 w-10 items-center justify-center rounded-full text-base hover:opacity-90 transition">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                    @endauth
                    @guest
                    <a href="{{ route('login') }}" style="background-color:var(--fx-amber); color:#14152B; font-family:'Rajdhani',sans-serif; font-weight:700;" class="rounded-full px-5 py-2 text-sm hover:opacity-90 transition fx-btn-glow">Login</a>
                    @endguest
                @else
                    <a href="{{ route('login') }}" style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:600;" class="text-sm hover:text-[var(--fx-amber)] transition">Log in</a>
                    <a href="{{ route('register') }}" style="background-color:var(--fx-coral); color:#4A190B; font-family:'Rajdhani',sans-serif; font-weight:700;" class="rounded-full px-4 py-2 text-sm hover:opacity-90 transition fx-btn-glow">Sign up</a>
                @endauth
            </div>
        </div>
    </div>

    <div x-show="open" x-cloak style="background-color:#1D1E3D; border-top:1px solid #24254A;" class="sm:hidden px-4 py-4 space-y-3">
        @foreach ($menuItems ?? [] as $item)
            <a href="{{ $item->url }}" style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:600;" class="block text-sm">{{ $item->label }}</a>
                @endforeach
        <div style="border-top:1px solid #24254A;" class="pt-3">
            @auth
                <a href="{{ route('wallet.index') }}" class="flex items-center gap-1.5 rounded-full px-3 py-1.5 w-fit mb-3" style="background-color:#14152B;text-decoration:none;cursor:pointer;">
                    <span style="color:var(--fx-amber); font-family:'Rajdhani',sans-serif; font-weight:700; font-size:14px;">₹{{ number_format((float) optional(\App\Models\Wallet::where('user_id', Auth::id())->first(), fn ($w) => $w->deposit_balance + $w->winnings_balance), 0) }}</span>
                    <span style="color:#8A8CB3; font-size:12px;">wallet</span>
                </a>
                <p style="color:#8A8CB3;" class="text-xs mb-2">{{ Auth::user()->name }}</p>
                <a href="{{ route('profile.edit') }}" style="color:#F4F3FA;" class="block text-sm mb-2">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" style="color:var(--fx-coral);" class="block text-sm">Log out</a>
                </form>
            @else
                <a href="{{ route('login') }}" style="color:#F4F3FA;" class="block text-sm mb-3">Log in</a>
                <a href="{{ route('register') }}" style="background-color:var(--fx-coral); color:#4A190B; font-family:'Rajdhani',sans-serif; font-weight:700;" class="inline-block rounded-full px-4 py-2 text-sm">Sign up</a>
            @endauth
        </div>
    </div>
</nav>
