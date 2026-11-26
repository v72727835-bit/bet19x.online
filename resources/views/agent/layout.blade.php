<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#14070f">
    <title>@yield('title', 'Overview') - {{ $agent->roleLabel() }} panel - {{ $siteSetting->site_name ?? 'BET19X' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    @include('partials.pwa-head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .ag-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .ag-table th { text-align: left; color: var(--bx-muted); font-size: 12px; font-weight: 700; padding: 10px 12px; border-bottom: 1px solid var(--bx-line); white-space: nowrap; }
        .ag-table td { padding: 11px 12px; border-bottom: 1px solid rgba(255,255,255,.06); white-space: nowrap; }
        .ag-table tr:last-child td { border-bottom: 0; }
        .ag-scroll { overflow-x: auto; }
        .ag-tabs { display: flex; gap: 6px; overflow-x: auto; padding: 12px 0 2px; scrollbar-width: none; }
        .ag-tabs::-webkit-scrollbar { display: none; }
        .ag-tabs a { flex: none; padding: 9px 15px; border-radius: 999px; background: var(--bx-velvet); box-shadow: inset 0 0 0 1px var(--bx-line); color: var(--bx-text); font-weight: 700; font-size: 14px; text-decoration: none; }
        .ag-tabs a.is-on { background: var(--bx-foil); color: #2a1206; box-shadow: none; }
        .ag-bar { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin: 14px 0 4px; }
        .ag-bar a, .ag-bar select { padding: 7px 12px; border-radius: 10px; background: var(--bx-velvet); color: var(--bx-muted); box-shadow: inset 0 0 0 1px var(--bx-line); font: 700 13px var(--bx-body); text-decoration: none; border: 0; }
        .ag-bar a.is-on { color: var(--bx-gold); box-shadow: inset 0 0 0 2px var(--bx-gold); }
        .ag-pos { color: var(--bx-win); font-weight: 800; }
        .ag-neg { color: #ff8a9a; font-weight: 800; }
        .ag-mut { color: var(--bx-muted); font-size: 12px; }
    </style>
</head>
<body class="bx-body">
    @if (session('agent_impersonator_admin_id'))
        <div style="display:flex; align-items:center; justify-content:center; gap:12px; flex-wrap:wrap; padding:8px 14px; background:#f2c14e; color:#2a1206; font-weight:800; font-size:14px;">
            <span>Admin view: you are logged in as {{ $agent->name }} ({{ $agent->roleLabel() }})</span>
            <form method="POST" action="{{ route('agent.logout') }}" style="margin:0;">
                @csrf
                <button type="submit" style="border:0; border-radius:8px; padding:6px 12px; background:#2a1206; color:#f2c14e; font-weight:800; cursor:pointer;">Back to admin panel</button>
            </form>
        </div>
    @endif

    <header class="bx-header">
        <div class="bx-wrap bx-header-in">
            <span class="bx-logo-txt">{{ $siteSetting->site_name ?? 'BET19X' }}</span>
            <span class="bx-badge bx-badge-wait" style="margin-left:6px;">{{ $agent->roleLabel() }} panel</span>
            <div class="bx-header-right">
                @unless (session('agent_impersonator_admin_id'))
                    <form method="POST" action="{{ route('agent.logout') }}" style="margin:0;">
                        @csrf
                        <button class="bx-btn bx-btn-ghost bx-btn-sm">Log out</button>
                    </form>
                @endunless
            </div>
        </div>
    </header>

    <main class="bx-wrap" style="padding-bottom:40px;">
        <nav class="ag-tabs" aria-label="Agent panel">
            <a href="{{ route('agent.dashboard') }}" class="{{ $tab === 'overview' ? 'is-on' : '' }}">Overview</a>
            <a href="{{ route('agent.players') }}" class="{{ $tab === 'players' ? 'is-on' : '' }}">Players</a>
            <a href="{{ route('agent.profit-loss') }}" class="{{ $tab === 'pl' ? 'is-on' : '' }}">Profit / Loss</a>
            <a href="{{ route('agent.bets') }}" class="{{ $tab === 'bets' ? 'is-on' : '' }}">Bet history</a>
            <a href="{{ route('agent.deposits') }}" class="{{ $tab === 'deposits' ? 'is-on' : '' }}">Deposits</a>
            <a href="{{ route('agent.withdrawals') }}" class="{{ $tab === 'withdrawals' ? 'is-on' : '' }}">Withdrawals</a>
            <a href="{{ route('agent.transfer') }}" class="{{ $tab === 'transfer' ? 'is-on' : '' }}">Transfer</a>
            @if ($agent->canCreateAgents() || $agent->children()->exists())
                <a href="{{ route('agent.agents') }}" class="{{ $tab === 'agents' ? 'is-on' : '' }}">My agents</a>
            @endif
        </nav>

        <div class="ag-bar" aria-label="Date range">
            @foreach ($ranges as $key => $label)
                <a href="{{ request()->fullUrlWithQuery(['range' => $key, 'page' => null]) }}" class="{{ $rangeKey === $key ? 'is-on' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        @if (session('status'))
            <div class="bx-alert bx-alert-ok">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="bx-alert bx-alert-err">{{ $errors->first() }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>
