<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $fxSetting = $siteSetting ?? \App\Models\SiteSetting::first();
        $fxAdmin = auth('admin')->user();
        try {
            $fxSupportUnread = $fxAdmin?->hasPermission('support') ? (int) \App\Models\SupportConversation::sum('admin_unread') : 0;
        } catch (\Throwable $e) {
            $fxSupportUnread = 0;
        }
    @endphp
    <title>{{ $title ?? 'Admin' }} - {{ $fxSetting->site_name ?? 'Admin Panel' }}</title>
    @if(!empty($fxSetting?->favicon_path))
        <link rel="icon" href="{{ asset('storage/'.$fxSetting->favicon_path) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function () {
            var theme = localStorage.getItem('fx_admin_theme') || 'dark';
            var collapsed = localStorage.getItem('fx_admin_sidebar') === '1';
            document.documentElement.classList.toggle('fx-light', theme === 'light');
            document.documentElement.classList.toggle('fx-collapsed', collapsed);
        })();
    </script>
    <style>
        :root {
            --fx-bg: {{ $fxSetting->bg_dark_color ?? '#0F1023' }}; --fx-surface: {{ $fxSetting->surface_dark_color ?? '#14152B' }}; --fx-card: #1D1E3D; --fx-card-hover: #24254A;
            --fx-border: #2B2C55; --fx-text: #F4F3FA; --fx-muted: #8A8CB3;
            --fx-amber: {{ $fxSetting->primary_color ?? '#FFB627' }}; --fx-coral: {{ $fxSetting->secondary_color ?? '#FF6B4A' }}; --fx-green: {{ $fxSetting->accent_color ?? '#34D399' }}; --fx-red: #F87171;
            --fx-sidebar-w: 272px; --fx-sidebar-w-collapsed: 76px;
        }
        html.fx-light {
            --fx-bg: #F4F5FA; --fx-surface: #FFFFFF; --fx-card: #FFFFFF; --fx-card-hover: #EEF0FA;
            --fx-border: #E1E3F0; --fx-text: #17182E; --fx-muted: #5C5E82;
            --fx-amber: {{ $fxSetting->primary_color ?? '#B8790E' }}; --fx-coral: {{ $fxSetting->secondary_color ?? '#D9482E' }}; --fx-green: {{ $fxSetting->accent_color ?? '#0F9D68' }}; --fx-red: #D6362A;
        }
        body { background-color: var(--fx-bg); color: var(--fx-text); font-family: 'Inter', sans-serif; }
        .fx-heading { font-family: 'Rajdhani', sans-serif; font-weight: 700; letter-spacing: 0.01em; }
        .fx-card { background-color: var(--fx-card); border: 1px solid var(--fx-border); border-radius: 0.9rem; }
        .fx-card.fx-hoverable:hover { background-color: var(--fx-card-hover); }
        .fx-muted { color: var(--fx-muted); }
        .fx-btn { background-color: var(--fx-amber); color: #14152B; font-weight: 600; padding: 0.5rem 1rem; border-radius: 0.6rem; transition: opacity .15s ease; }
        .fx-btn:hover { opacity: 0.85; }
        .fx-btn-outline { border: 1px solid var(--fx-border); color: var(--fx-text); padding: 0.5rem 1rem; border-radius: 0.6rem; transition: background-color .15s ease; }
        .fx-btn-outline:hover { background-color: var(--fx-card-hover); }
        .fx-btn-danger { background-color: var(--fx-red); color: #fff; padding: 0.4rem 0.8rem; border-radius: 0.5rem; font-size: 0.85rem; }
        .fx-btn-success { background-color: var(--fx-green); color: #0F1023; padding: 0.4rem 0.8rem; border-radius: 0.5rem; font-size: 0.85rem; }
        .fx-input { background-color: var(--fx-bg); border: 1px solid var(--fx-border); color: var(--fx-text); border-radius: 0.5rem; padding: 0.5rem 0.75rem; }
        .fx-input:focus { outline: none; border-color: var(--fx-amber); }
        .fx-table th { color: var(--fx-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.03em; text-align: left; padding: 0.6rem 0.9rem; border-bottom: 1px solid var(--fx-border); }
        .fx-table td { padding: 0.7rem 0.9rem; border-bottom: 1px solid var(--fx-border); font-size: 0.9rem; }
        .fx-badge { display: inline-block; padding: 0.15rem 0.6rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }

        /* Sidebar */
        .fx-sidebar { width: var(--fx-sidebar-w); }
        @media (min-width: 1024px) {
            .fx-sidebar { transition: width .2s ease; }
            html.fx-collapsed .fx-sidebar { width: var(--fx-sidebar-w-collapsed); }
            .fx-content-wrap { padding-left: var(--fx-sidebar-w); transition: padding-left .2s ease; }
            html.fx-collapsed .fx-content-wrap { padding-left: var(--fx-sidebar-w-collapsed); }
        }
        html.fx-collapsed .fx-sidebar .fx-label,
        html.fx-collapsed .fx-sidebar .fx-nav-group-title,
        html.fx-collapsed .fx-sidebar .fx-brand-text,
        html.fx-collapsed .fx-sidebar .fx-nav-chevron,
        html.fx-collapsed .fx-sidebar .fx-collapse-label { display: none !important; }
        html.fx-collapsed .fx-sidebar .fx-nav-link,
        html.fx-collapsed .fx-sidebar .fx-group-toggle { justify-content: center; }

        .fx-nav-link { color: var(--fx-muted); display: flex; align-items: center; gap: 0.7rem; padding: 0.5rem 0.9rem; border-radius: 0.55rem; font-size: 0.88rem; position: relative; transition: background-color .12s ease, color .12s ease; }
        .fx-nav-link:hover { background-color: var(--fx-card-hover); color: var(--fx-text); }
        .fx-nav-link.active { background-color: var(--fx-card-hover); color: var(--fx-amber); }
        .fx-nav-link.active::before { content: ''; position: absolute; left: -0.9rem; top: 0.3rem; bottom: 0.3rem; width: 3px; border-radius: 2px; background-color: var(--fx-amber); }
        .fx-nav-link svg { flex-shrink: 0; width: 18px; height: 18px; }
        .fx-nav-group-title { color: var(--fx-muted); font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.09em; padding: 1rem 0.9rem 0.35rem; font-weight: 600; }
        .fx-group-toggle { width: 100%; display: flex; align-items: center; justify-content: space-between; background: none; border: none; cursor: pointer; transition: color .12s ease; }
        .fx-group-toggle:hover { color: var(--fx-amber); }
        .fx-group-toggle .fx-nav-chevron { width: 12px; height: 12px; margin-left: 0; transition: transform .15s ease; }
        .fx-group-toggle .fx-nav-chevron.rotated { transform: rotate(90deg); }

        /* Tooltip for collapsed state */
        .fx-nav-link .fx-tooltip { display: none; }
        html.fx-collapsed .fx-sidebar .fx-nav-link .fx-tooltip { position: absolute; left: calc(100% + 10px); top: 50%; transform: translateY(-50%); background: var(--fx-card); border: 1px solid var(--fx-border); color: var(--fx-text); padding: 0.35rem 0.7rem; border-radius: 0.4rem; font-size: 0.8rem; white-space: nowrap; z-index: 50; box-shadow: 0 4px 14px rgba(0,0,0,.25); }
        html.fx-collapsed .fx-sidebar .fx-nav-link:hover .fx-tooltip { display: block; }

        ::-webkit-scrollbar { width: 9px; height: 9px; }
        ::-webkit-scrollbar-track { background-color: transparent; }
        ::-webkit-scrollbar-thumb { background-color: var(--fx-border); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background-color: var(--fx-muted); }
        .fx-sidebar { scrollbar-width: thin; scrollbar-color: var(--fx-muted) transparent; }
        .fx-sidebar::-webkit-scrollbar { width: 9px; }
        .fx-sidebar::-webkit-scrollbar-track { background-color: rgba(255,255,255,0.03); }
        .fx-sidebar::-webkit-scrollbar-thumb { background-color: var(--fx-muted); border-radius: 4px; }
        .fx-sidebar::-webkit-scrollbar-thumb:hover { background-color: var(--fx-amber); }

        .fx-dropdown { background-color: var(--fx-card); border: 1px solid var(--fx-border); border-radius: 0.75rem; box-shadow: 0 10px 30px rgba(0,0,0,.25); }
        .fx-dropdown-item { display: flex; align-items: center; gap: 0.6rem; padding: 0.55rem 0.9rem; font-size: 0.86rem; color: var(--fx-text); }
        .fx-dropdown-item:hover { background-color: var(--fx-card-hover); }
        .fx-dot { position: absolute; top: -2px; right: -2px; width: 8px; height: 8px; border-radius: 999px; background-color: var(--fx-coral); border: 2px solid var(--fx-surface); }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-screen overflow-hidden" x-data="{
        sidebarOpen: false,
        collapsed: localStorage.getItem('fx_admin_sidebar') === '1',
        theme: localStorage.getItem('fx_admin_theme') || 'dark',
        userMenu: false,
        notifMenu: false,
        groups: (function(){ try { return JSON.parse(localStorage.getItem('fx_admin_groups')) || {}; } catch(e) { return {}; } })(),
        toggleCollapse() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('fx_admin_sidebar', this.collapsed ? '1' : '0');
            document.documentElement.classList.toggle('fx-collapsed', this.collapsed);
        },
        toggleGroup(name) {
            this.groups[name] = !this.isGroupOpen(name, false);
            localStorage.setItem('fx_admin_groups', JSON.stringify(this.groups));
        },
        isGroupOpen(name, activeByDefault) {
            return name in this.groups ? this.groups[name] : !!activeByDefault;
        }
    }">

    <div class="flex h-screen">
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fx-sidebar fixed inset-y-0 left-0 z-40 h-screen overflow-y-auto transition-transform duration-200 flex flex-col"
               style="background-color: var(--fx-surface); border-right: 1px solid var(--fx-border);">
            <div class="px-5 py-5 flex items-center justify-between sticky top-0 z-10" style="background-color: var(--fx-surface); border-bottom:1px solid var(--fx-border);">
                <a href="{{ route('admin.dashboard') }}" class="fx-heading text-lg flex items-center gap-2 min-w-0" style="color: var(--fx-text);">
                    @if(!empty($fxSetting?->logo_path))
                        <img src="{{ asset('storage/'.$fxSetting->logo_path) }}" alt="{{ $fxSetting->site_name }}" class="h-8 w-8 rounded object-cover flex-shrink-0">
                    @endif
                    <span class="fx-brand-text truncate">{{ $fxSetting->site_name ?? 'Admin' }} <span style="color: var(--fx-amber);">Admin</span></span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden fx-muted text-xl">&times;</button>
            </div>

            <nav class="flex-1 py-2">
                <p class="fx-nav-group-title">Overview</p>
                <a href="{{ route('admin.dashboard') }}" class="fx-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13h8V3H3v10Zm0 8h8v-6H3v6Zm10 0h8V11h-8v10Zm0-18v6h8V3h-8Z"/></svg>
                    <span class="fx-label">Dashboard</span>
                    <span class="fx-tooltip">Dashboard</span>
                </a>

                @if($fxAdmin?->hasPermission('users'))
                <button @click="toggleGroup('users')" class="fx-nav-group-title fx-group-toggle">
                    <span>Users</span>
                    <svg class="fx-nav-chevron" :class="isGroupOpen('users', {{ (request()->routeIs('admin.users.*') || request()->routeIs('admin.security.login-history')) ? 'true' : 'false' }}) ? 'rotated' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6"/></svg>
                </button>
                <div x-show="collapsed || isGroupOpen('users', {{ (request()->routeIs('admin.users.*') || request()->routeIs('admin.security.login-history')) ? 'true' : 'false' }})">
                @if($fxAdmin?->hasPermission('users'))
                <a href="{{ route('admin.users.index') }}" class="fx-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM12 14c-4.4 0-8 2.2-8 5v1h16v-1c0-2.8-3.6-5-8-5Z"/></svg>
                    <span class="fx-label">All Users</span>
                    <span class="fx-tooltip">All Users</span>
                </a>
                @endif
                @if($fxAdmin?->hasPermission('users'))
                <a href="{{ route('admin.security.login-history') }}" class="fx-nav-link {{ request()->routeIs('admin.security.login-history') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <span class="fx-label">Login History</span>
                    <span class="fx-tooltip">Login History</span>
                </a>
                @endif
                </div>
                @endif

                @if($fxAdmin?->hasPermission('bets') || $fxAdmin?->hasPermission('sports') || $fxAdmin?->hasPermission('risk'))
                <button @click="toggleGroup('contests')" class="fx-nav-group-title fx-group-toggle">
                    <span>Contests</span>
                    <svg class="fx-nav-chevron" :class="isGroupOpen('contests', {{ (request()->routeIs('admin.bets.*') || request()->routeIs('admin.matches.*') || request()->routeIs('admin.sports.*') || request()->routeIs('admin.odds.*') || request()->routeIs('admin.risk.*')) ? 'true' : 'false' }}) ? 'rotated' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6"/></svg>
                </button>
                <div x-show="collapsed || isGroupOpen('contests', {{ (request()->routeIs('admin.bets.*') || request()->routeIs('admin.matches.*') || request()->routeIs('admin.sports.*') || request()->routeIs('admin.odds.*') || request()->routeIs('admin.risk.*')) ? 'true' : 'false' }})">
                @if($fxAdmin?->hasPermission('bets'))
                <a href="{{ route('admin.bets.index') }}" class="fx-nav-link {{ request()->routeIs('admin.bets.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m-6 4h6m-6 4h4M5 3h14a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z"/></svg>
                    <span class="fx-label">Bet / Entry Management</span>
                    <span class="fx-tooltip">Bet / Entry Management</span>
                </a>
                @endif
                @if($fxAdmin?->hasPermission('bets'))
                <a href="{{ route('admin.game-bets.index') }}" class="fx-nav-link {{ request()->routeIs('admin.game-bets.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="12" rx="3"/><path stroke-linecap="round" d="M8 12h2m-1-1v2M15 11h.01M17 13h.01"/></svg>
                    <span class="fx-label">Game bets</span>
                    <span class="fx-tooltip">Game bets</span>
                </a>
                @endif
                @if($fxAdmin?->hasPermission('sports'))
                <a href="{{ route('admin.matches.index') }}" class="fx-nav-link {{ request()->routeIs('admin.matches.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 3v18M3 12h18"/></svg>
                    <span class="fx-label">Match Management</span>
                    <span class="fx-tooltip">Match Management</span>
                </a>
                @endif
                @if($fxAdmin?->hasPermission('sports'))
                <a href="{{ route('admin.sports.index') }}" class="fx-nav-link {{ request()->routeIs('admin.sports.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <span class="fx-label">Sports</span>
                    <span class="fx-tooltip">Sports</span>
                </a>
                @endif
                @if($fxAdmin?->hasPermission('sports'))
                <a href="{{ route('admin.odds.index') }}" class="fx-nav-link {{ request()->routeIs('admin.odds.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m3 17 5-5 4 4 8-8m0 0h-5m5 0v5"/></svg>
                    <span class="fx-label">Odds Management</span>
                    <span class="fx-tooltip">Odds Management</span>
                </a>
                @endif
                @if($fxAdmin?->hasPermission('risk'))
                <a href="{{ route('admin.risk.index') }}" class="fx-nav-link {{ request()->routeIs('admin.risk.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 2.5 17a1.5 1.5 0 0 0 1.3 2.2h16.4A1.5 1.5 0 0 0 21.5 17L13.7 3.9a1.5 1.5 0 0 0-2.6 0Z"/></svg>
                    <span class="fx-label">Risk & Exposure</span>
                    <span class="fx-tooltip">Risk & Exposure</span>
                </a>
                @endif
                </div>
                @endif

                @if($fxAdmin?->hasPermission('agents'))
                <p class="fx-nav-group-title">Agents</p>
                @endif
                @if($fxAdmin?->hasPermission('agents'))
                <a href="{{ route('admin.agents.index') }}" class="fx-nav-link {{ request()->routeIs('admin.agents.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2m14 0v-2a4 4 0 0 0-3-3.87M13 3.13a4 4 0 0 1 0 7.75M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>
                    <span class="fx-label">Master Agent / Agent / Sub-Agent</span>
                    <span class="fx-tooltip">Agents</span>
                </a>
                @endif

                @if($fxAdmin?->hasPermission('wallet'))
                <button @click="toggleGroup('wallet')" class="fx-nav-group-title fx-group-toggle">
                    <span>Wallet</span>
                    <svg class="fx-nav-chevron" :class="isGroupOpen('wallet', {{ (request()->routeIs('admin.wallet.*')) ? 'true' : 'false' }}) ? 'rotated' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6"/></svg>
                </button>
                <div x-show="collapsed || isGroupOpen('wallet', {{ (request()->routeIs('admin.wallet.*')) ? 'true' : 'false' }})">
                @if($fxAdmin?->hasPermission('wallet'))
                <a href="{{ route('admin.wallet.deposits') }}" class="fx-nav-link {{ request()->routeIs('admin.wallet.deposits') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span class="fx-label">Deposit Requests</span>
                    <span class="fx-tooltip">Deposit Requests</span>
                </a>
                @endif
                @if($fxAdmin?->hasPermission('wallet'))
                <a href="{{ route('admin.wallet.withdrawals') }}" class="fx-nav-link {{ request()->routeIs('admin.wallet.withdrawals') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                    <span class="fx-label">Withdrawal Requests</span>
                    <span class="fx-tooltip">Withdrawal Requests</span>
                </a>
                @endif
                @if($fxAdmin?->hasPermission('wallet'))
                <a href="{{ route('admin.wallet.transactions') }}" class="fx-nav-link {{ request()->routeIs('admin.wallet.transactions') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path stroke-linecap="round" d="M3 10h18"/></svg>
                    <span class="fx-label">Transactions</span>
                    <span class="fx-tooltip">Transactions</span>
                </a>
                <a href="{{ route('admin.wallet.transfer') }}" class="fx-nav-link {{ request()->routeIs('admin.wallet.transfer*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8h13l-3-3m6 11H7l3 3"/></svg>
                    <span class="fx-label">Transfer between users</span>
                    <span class="fx-tooltip">Transfer between users</span>
                </a>
                @endif
                </div>
                @endif

                @if($fxAdmin?->hasPermission('support'))
                <p class="fx-nav-group-title">Support</p>
                <a href="{{ route('admin.support.index') }}" class="fx-nav-link {{ request()->routeIs('admin.support.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v11H8l-4 4V5z"/></svg>
                    <span class="fx-label">Live support</span>
                    @if(($fxSupportUnread ?? 0) > 0)<span class="fx-badge" style="margin-left:auto; background: var(--fx-coral); color:#fff;">{{ $fxSupportUnread }}</span>@endif
                    <span class="fx-tooltip">Live support</span>
                </a>
                @endif
                @if($fxAdmin?->hasPermission('promotions') || $fxAdmin?->hasPermission('content'))
                <button @click="toggleGroup('growth')" class="fx-nav-group-title fx-group-toggle">
                    <span>Growth</span>
                    <svg class="fx-nav-chevron" :class="isGroupOpen('growth', {{ (request()->routeIs('admin.bonuses.*') || request()->routeIs('admin.promo-codes.*') || request()->routeIs('admin.vip.*') || request()->routeIs('admin.notifications.*')) ? 'true' : 'false' }}) ? 'rotated' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6"/></svg>
                </button>
                <div x-show="collapsed || isGroupOpen('growth', {{ (request()->routeIs('admin.bonuses.*') || request()->routeIs('admin.promo-codes.*') || request()->routeIs('admin.vip.*') || request()->routeIs('admin.notifications.*')) ? 'true' : 'false' }})">
                @if($fxAdmin?->hasPermission('promotions'))
                <a href="{{ route('admin.bonuses.index') }}" class="fx-nav-link {{ request()->routeIs('admin.bonuses.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12v8H4v-8M2 7h20v5H2V7Zm10 13V7m0 0a3 3 0 1 0-3-3c0 1.7 3 3 3 3Zm0 0a3 3 0 1 1 3-3c0 1.7-3 3-3 3Z"/></svg>
                    <span class="fx-label">Bonuses</span>
                    <span class="fx-tooltip">Bonuses</span>
                </a>
                @endif
                @if($fxAdmin?->hasPermission('promotions'))
                <a href="{{ route('admin.promo-codes.index') }}" class="fx-nav-link {{ request()->routeIs('admin.promo-codes.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.6 12.6 12.6 20.6a2 2 0 0 1-2.8 0l-6.4-6.4a2 2 0 0 1 0-2.8L11.4 3.4a2 2 0 0 1 1.4-.6H19a2 2 0 0 1 2 2v6.6a2 2 0 0 1-.4 1.6ZM15.5 8.5h.01"/></svg>
                    <span class="fx-label">Promo Codes</span>
                    <span class="fx-tooltip">Promo Codes</span>
                </a>
                @endif
                @if($fxAdmin?->hasPermission('promotions'))
                <a href="{{ route('admin.vip.index') }}" class="fx-nav-link {{ request()->routeIs('admin.vip.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4 8 4 3 4-6 4 6 4-3-2 11H6L4 8Z"/></svg>
                    <span class="fx-label">VIP & Loyalty</span>
                    <span class="fx-tooltip">VIP & Loyalty</span>
                </a>
                @endif
                @if($fxAdmin?->hasPermission('content'))
                <a href="{{ route('admin.notifications.index') }}" class="fx-nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-4-5.7V5a2 2 0 1 0-4 0v.3A6 6 0 0 0 6 11v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9"/></svg>
                    <span class="fx-label">Notifications</span>
                    <span class="fx-tooltip">Notifications</span>
                </a>
                @endif
                </div>
                @endif

                @if($fxAdmin?->hasPermission('reports'))
                <p class="fx-nav-group-title">Reports</p>
                @endif
                @if($fxAdmin?->hasPermission('reports'))
                <a href="{{ route('admin.reports.index') }}" class="fx-nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17V9m4 8v-4m4 4V5M5 21h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2Z"/></svg>
                    <span class="fx-label">Reports & Export</span>
                    <span class="fx-tooltip">Reports & Export</span>
                </a>
                @endif

                @if($fxAdmin?->hasPermission('content'))
                <button @click="toggleGroup('cms')" class="fx-nav-group-title fx-group-toggle">
                    <span>CMS</span>
                    <svg class="fx-nav-chevron" :class="isGroupOpen('cms', {{ (request()->routeIs('admin.cms-pages.*') || request()->routeIs('admin.hero-banners.*') || request()->routeIs('admin.menu.*') || request()->routeIs('admin.sport-categories.*') || request()->routeIs('admin.featured-contests.*') || request()->routeIs('admin.top-winners.*') || request()->routeIs('admin.site-stats.*')) ? 'true' : 'false' }}) ? 'rotated' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6"/></svg>
                </button>
                <div x-show="collapsed || isGroupOpen('cms', {{ (request()->routeIs('admin.cms-pages.*') || request()->routeIs('admin.hero-banners.*') || request()->routeIs('admin.menu.*') || request()->routeIs('admin.sport-categories.*') || request()->routeIs('admin.featured-contests.*') || request()->routeIs('admin.top-winners.*') || request()->routeIs('admin.site-stats.*')) ? 'true' : 'false' }})">
                @if($fxAdmin?->hasPermission('content'))
                <a href="{{ route('admin.cms-pages.index') }}" class="fx-nav-link {{ request()->routeIs('admin.cms-pages.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3h8l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Zm8 0v5h5"/></svg>
                    <span class="fx-label">Pages / News / Rules</span>
                    <span class="fx-tooltip">Pages / News / Rules</span>
                </a>
                @endif
                @if($fxAdmin?->hasPermission('content'))
                <a href="{{ route('admin.hero-banners.index') }}" class="fx-nav-link {{ request()->routeIs('admin.hero-banners.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="m3 15 5-4 4 3 5-5 4 3"/></svg>
                    <span class="fx-label">Hero Banners</span>
                    <span class="fx-tooltip">Hero Banners</span>
                </a>
                @endif
                @if($fxAdmin?->hasPermission('content'))
                <a href="{{ route('admin.menu.index') }}" class="fx-nav-link {{ request()->routeIs('admin.menu.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <span class="fx-label">Menu Items</span>
                    <span class="fx-tooltip">Menu Items</span>
                </a>
                @endif
                @if($fxAdmin?->hasPermission('content'))
                <a href="{{ route('admin.sport-categories.index') }}" class="fx-nav-link {{ request()->routeIs('admin.sport-categories.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    <span class="fx-label">Sport Categories</span>
                    <span class="fx-tooltip">Sport Categories</span>
                </a>
                @endif
                @if($fxAdmin?->hasPermission('content'))
                <a href="{{ route('admin.featured-contests.index') }}" class="fx-nav-link {{ request()->routeIs('admin.featured-contests.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m12 2 2.9 6.3L21 9l-4.9 4.5L17.5 21 12 17.6 6.5 21l1.4-7.5L3 9l6.1-.7L12 2Z"/></svg>
                    <span class="fx-label">Featured Contests</span>
                    <span class="fx-tooltip">Featured Contests</span>
                </a>
                @endif
                @if($fxAdmin?->hasPermission('content'))
                <a href="{{ route('admin.top-winners.index') }}" class="fx-nav-link {{ request()->routeIs('admin.top-winners.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8M12 17v4M7 4h10v4a5 5 0 0 1-10 0V4Zm-3 0h3v2a3 3 0 0 1-3-3V4Zm16 0h-3v2a3 3 0 0 0 3-3V4Z"/></svg>
                    <span class="fx-label">Top Winners</span>
                    <span class="fx-tooltip">Top Winners</span>
                </a>
                @endif
                @if($fxAdmin?->hasPermission('content'))
                <a href="{{ route('admin.site-stats.index') }}" class="fx-nav-link {{ request()->routeIs('admin.site-stats.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7 15l4-5 3 3 5-7"/></svg>
                    <span class="fx-label">Site Stats</span>
                    <span class="fx-tooltip">Site Stats</span>
                </a>
                <a href="{{ route('admin.providers.index') }}" class="fx-nav-link {{ request()->routeIs('admin.providers.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h10M4 12h16M4 18h7M17 4v4m-2-2h4"/></svg>
                    <span class="fx-label">Provider order</span>
                    <span class="fx-tooltip">Provider order</span>
                </a>
                @endif
                </div>
                @endif

                @if($fxAdmin?->hasPermission('security'))
                <button @click="toggleGroup('security')" class="fx-nav-group-title fx-group-toggle">
                    <span>Security</span>
                    <svg class="fx-nav-chevron" :class="isGroupOpen('security', {{ (request()->routeIs('admin.security.audit-logs') || request()->routeIs('admin.security.blocked-ips')) ? 'true' : 'false' }}) ? 'rotated' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6"/></svg>
                </button>
                <div x-show="collapsed || isGroupOpen('security', {{ (request()->routeIs('admin.security.audit-logs') || request()->routeIs('admin.security.blocked-ips')) ? 'true' : 'false' }})">
                @if($fxAdmin?->hasPermission('security'))
                <a href="{{ route('admin.security.audit-logs') }}" class="fx-nav-link {{ request()->routeIs('admin.security.audit-logs') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4 6v6c0 4.5 3.4 8.2 8 9 4.6-.8 8-4.5 8-9V6l-8-3Z"/></svg>
                    <span class="fx-label">Audit Logs</span>
                    <span class="fx-tooltip">Audit Logs</span>
                </a>
                @endif
                @if($fxAdmin?->hasPermission('security'))
                <a href="{{ route('admin.security.blocked-ips') }}" class="fx-nav-link {{ request()->routeIs('admin.security.blocked-ips') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="m5.6 5.6 12.8 12.8"/></svg>
                    <span class="fx-label">Blocked IPs</span>
                    <span class="fx-tooltip">Blocked IPs</span>
                </a>
                @endif
                </div>
                @endif

                @if($fxAdmin?->hasPermission('settings') || $fxAdmin?->isSuperAdmin())
                <button @click="toggleGroup('settings')" class="fx-nav-group-title fx-group-toggle">
                    <span>Settings</span>
                    <svg class="fx-nav-chevron" :class="isGroupOpen('settings', {{ (request()->routeIs('admin.settings.edit') || request()->routeIs('admin.license.*') || request()->routeIs('admin.admin-users.*')) ? 'true' : 'false' }}) ? 'rotated' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6"/></svg>
                </button>
                <div x-show="collapsed || isGroupOpen('settings', {{ (request()->routeIs('admin.settings.edit') || request()->routeIs('admin.license.*') || request()->routeIs('admin.admin-users.*')) ? 'true' : 'false' }})">
                @if($fxAdmin?->hasPermission('settings'))
                <a href="{{ route('admin.settings.edit') }}" class="fx-nav-link {{ request()->routeIs('admin.settings.edit') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09A1.65 1.65 0 0 0 15 4.6a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9c.14.34.51 1 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/></svg>
                    <span class="fx-label">General Settings</span>
                    <span class="fx-tooltip">General Settings</span>
                </a>
                @endif
                @if($fxAdmin?->isSuperAdmin())
                <a href="{{ route('admin.license.index') }}" class="fx-nav-link {{ request()->routeIs('admin.license.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M7 9h10M7 13h6"/></svg>
                    <span class="fx-label">License / Subscription</span>
                    <span class="fx-tooltip">License / Subscription</span>
                </a>
                @endif
                @if(auth('admin')->user() && auth('admin')->user()->isSuperAdmin())
                    <a href="{{ route('admin.admin-users.index') }}" class="fx-nav-link {{ request()->routeIs('admin.admin-users.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2 4 5v6c0 5 3.4 8.7 8 11 4.6-2.3 8-6 8-11V5l-8-3Z"/></svg>
                        <span class="fx-label">Admin Users & Roles</span>
                        <span class="fx-tooltip">Admin Users & Roles</span>
                    </a>
                @endif
                </div>
                @endif
            </nav>

            <div class="px-3 py-3 hidden lg:block sticky bottom-0" style="background-color: var(--fx-surface); border-top: 1px solid var(--fx-border);">
                <button @click="toggleCollapse()" class="fx-nav-link w-full">
                    <svg :class="collapsed ? 'rotate-180' : ''" style="transition: transform .2s ease;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5 5-5m7 10-5-5 5-5"/></svg>
                    <span class="fx-label fx-collapse-label">Collapse</span>
                    <span class="fx-tooltip">Expand</span>
                </button>
            </div>
        </aside>

        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>

        <div class="fx-content-wrap flex-1 flex flex-col h-screen overflow-hidden">
            <header class="shrink-0 sticky top-0 z-20 flex items-center justify-between gap-4 px-5 py-4" style="background-color: var(--fx-surface); border-bottom: 1px solid var(--fx-border);">
                <div class="flex items-center gap-3 min-w-0">
                    <button @click="sidebarOpen = true" class="lg:hidden fx-muted text-xl">&#9776;</button>
                    <div class="min-w-0">
                        <h2 class="fx-heading text-xl truncate" style="color: var(--fx-text);">{{ $title ?? 'Dashboard' }}</h2>
                        @if(!empty($breadcrumbs))
                            <nav class="fx-muted text-xs flex items-center gap-1 mt-0.5">
                                @foreach($breadcrumbs as $i => $crumb)
                                    @if($i > 0) <span>/</span> @endif
                                    @if(!empty($crumb['url']) && $i < count($breadcrumbs) - 1)
                                        <a href="{{ $crumb['url'] }}" class="hover:opacity-80">{{ $crumb['label'] }}</a>
                                    @else
                                        <span style="color: var(--fx-text);">{{ $crumb['label'] }}</span>
                                    @endif
                                @endforeach
                            </nav>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                    <button @click="theme = theme === 'dark' ? 'light' : 'dark'; localStorage.setItem('fx_admin_theme', theme); document.documentElement.classList.toggle('fx-light', theme === 'light');"
                            class="fx-btn-outline text-xs px-2.5 py-2" title="Toggle theme">
                        <span x-text="theme === 'dark' ? '☀' : '🌙'"></span>
                    </button>

                    <div class="relative">
                        <button @click="notifMenu = !notifMenu; userMenu = false" class="fx-btn-outline relative px-2.5 py-2">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-4-5.7V5a2 2 0 1 0-4 0v.3A6 6 0 0 0 6 11v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9"/></svg>
                            <span class="fx-dot"></span>
                        </button>
                        <div x-show="notifMenu" x-cloak @click.outside="notifMenu = false" x-transition
                             class="fx-dropdown absolute right-0 mt-2 w-72 py-2 text-sm">
                            <p class="px-4 pb-2 fx-muted text-xs uppercase tracking-wide">Notifications</p>
                            @if($fxAdmin?->hasPermission('content'))
                            <a href="{{ route('admin.notifications.index') }}" class="fx-dropdown-item">Recent activity has arrived</a>
                            <a href="{{ route('admin.notifications.index') }}" class="fx-dropdown-item">View all notifications</a>
                            @else
                            <span class="fx-dropdown-item fx-muted">No notifications</span>
                            @endif
                        </div>
                    </div>

                    <div class="relative">
                        <button @click="userMenu = !userMenu; notifMenu = false" class="flex items-center gap-2 fx-btn-outline px-2.5 py-1.5">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-semibold" style="background-color: var(--fx-amber); color: #14152B;">
                                {{ strtoupper(substr(Auth::guard('admin')->user()->name ?? 'A', 0, 1)) }}
                            </span>
                            <span class="hidden sm:inline text-sm fx-muted">{{ Auth::guard('admin')->user()->name }}</span>
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-show="userMenu" x-cloak @click.outside="userMenu = false" x-transition
                             class="fx-dropdown absolute right-0 mt-2 w-56 py-2 text-sm">
                            <div class="px-4 pb-2">
                                <p style="color: var(--fx-text);" class="font-medium truncate">{{ Auth::guard('admin')->user()->name }}</p>
                                <span class="fx-badge mt-1" style="background-color: var(--fx-card-hover); color: var(--fx-amber);">{{ ucfirst(str_replace('_',' ', Auth::guard('admin')->user()->role ?? 'admin')) }}</span>
                            </div>
                            <div style="border-top: 1px solid var(--fx-border);"></div>
                            @if($fxAdmin?->hasPermission('settings'))
                            <a href="{{ route('admin.settings.edit') }}" class="fx-dropdown-item">Profile Settings</a>
                            @endif
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="fx-dropdown-item w-full text-left" style="color: var(--fx-coral);">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto overflow-x-hidden px-5 py-6">
                @if (session('status'))
                    <div class="fx-card mb-4 px-4 py-3 text-sm" style="border-left: 3px solid var(--fx-green); color: var(--fx-text);">
                        {{ session('status') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="fx-card mb-4 px-4 py-3 text-sm" style="border-left: 3px solid var(--fx-red); color: var(--fx-text);">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}

                <p class="fx-muted text-xs text-center mt-10">&copy; {{ date('Y') }} {{ $fxSetting->site_name ?? 'Admin Panel' }}. All rights reserved.</p>
            </main>
        </div>
    </div>
</body>
</html>