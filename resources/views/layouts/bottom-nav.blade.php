<nav class="sm:hidden fixed bottom-0 inset-x-0 z-50" style="background-color:#2A0A0A; border-top:1px solid #4A190B; border-radius:18px 18px 0 0; box-shadow: 0 -8px 24px -8px rgba(0,0,0,.5);">
    <div class="grid grid-cols-5">
        @php
            $navItems = [
                ['label' => 'Home', 'route' => 'dashboard', 'icon' => 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h4a1 1 0 001-1v-4h2v4a1 1 0 001 1h4a1 1 0 001-1V10'],
                ['label' => 'Live', 'route' => 'in-play.index', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                ['label' => 'Wallet', 'route' => 'wallet.index', 'icon' => 'M3 7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7zM16 12a1 1 0 100-2 1 1 0 000 2z'],
                ['label' => 'Deposit', 'route' => 'wallet.deposit', 'icon' => 'M12 4v16m8-8H4'],
                ['label' => 'Account', 'route' => 'profile.edit', 'icon' => 'M5.121 17.804A9 9 0 1118.879 6.196 9 9 0 015.12 17.804zM12 11a3 3 0 100-6 3 3 0 000 6z'],
            ];
        @endphp
        @foreach ($navItems as $item)
            @php $isActive = request()->routeIs($item['route']); @endphp
            <a href="{{ route($item['route']) }}"
               class="flex flex-col items-center justify-center gap-1 py-2.5 transition-all duration-200"
               style="{{ $isActive ? 'background-color:var(--fx-amber); border-radius:14px; margin:6px; box-shadow:0 0 14px 1px rgba(230,0,18,.55);' : '' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="{{ $isActive ? '#FFFFFF' : '#C9A0A0' }}" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                </svg>
                <span class="text-[10px]" style="color: {{ $isActive ? '#FFFFFF' : '#C9A0A0' }}; font-family:'Rajdhani',sans-serif; font-weight:600;">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
