<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#14070f">
    <title>{{ $agent->roleLabel() }} panel - {{ $siteSetting->site_name ?? 'BET19X' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .ag-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .ag-table th { text-align: left; color: var(--bx-muted); font-size: 12px; font-weight: 700; padding: 10px 12px; border-bottom: 1px solid var(--bx-line); white-space: nowrap; }
        .ag-table td { padding: 11px 12px; border-bottom: 1px solid rgba(255,255,255,.06); white-space: nowrap; }
        .ag-scroll { overflow-x: auto; }
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
        <div class="bx-pagehead">
            <h1 class="bx-h1">Hello, {{ $agent->name }}</h1>
            <p class="bx-sub">{{ $agent->email }}@if ($agent->parent) — under {{ $agent->parent->name }} ({{ $agent->parent->roleLabel() }})@endif</p>
        </div>

        <div class="bx-stats" style="margin-top:14px;">
            <div class="bx-stat"><b>{{ number_format($playerCount) }}</b><span>Players in your network</span></div>
            <div class="bx-stat"><b>{{ $subAgents->count() }}</b><span>Agents under you</span></div>
            <div class="bx-stat"><b>{{ rtrim(rtrim(number_format($agent->commission_rate, 2), '0'), '.') }}%</b><span>Your commission</span></div>
            <div class="bx-stat"><b style="color:var(--bx-win);">&#8377;{{ number_format($commission, 2) }}</b><span>Commission earned (estimate)</span></div>
            <div class="bx-stat"><b>&#8377;{{ number_format($totals->bet, 0) }}</b><span>Staked by your players</span></div>
            <div class="bx-stat"><b style="color:{{ $ggr >= 0 ? 'var(--bx-win)' : '#ff8a9a' }};">{{ $ggr < 0 ? '−' : '' }}&#8377;{{ number_format(abs($ggr), 0) }}</b><span>House result from your players</span></div>
            <div class="bx-stat"><b>&#8377;{{ number_format($agent->creditAvailable(), 0) }}</b><span>Credit available of &#8377;{{ number_format($agent->credit_limit, 0) }}</span></div>
            <div class="bx-stat"><b>&#8377;{{ number_format($agent->wallet_balance, 2) }}</b><span>Commission balance</span></div>
        </div>

        @if ($subAgents->isNotEmpty())
            <section class="bx-section">
                <div class="bx-section-head"><h2 class="bx-h2">Your agents</h2></div>
                <div class="bx-panel ag-scroll" style="padding:4px 4px;">
                    <table class="ag-table">
                        <thead><tr><th>Name</th><th>Type</th><th>Under</th><th>Players</th><th>Commission</th><th>Credit limit</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach ($subAgents as $s)
                                <tr>
                                    <td><b>{{ $s->name }}</b><div style="color:var(--bx-muted); font-size:12px;">{{ $s->email }}</div></td>
                                    <td>{{ $s->roleLabel() }}</td>
                                    <td>{{ $s->parent->name ?? '—' }}</td>
                                    <td>{{ $s->users_count }}</td>
                                    <td>{{ rtrim(rtrim(number_format($s->commission_rate, 2), '0'), '.') }}%</td>
                                    <td>&#8377;{{ number_format($s->credit_limit, 0) }}</td>
                                    <td><span class="bx-badge {{ $s->is_active ? 'bx-badge-ok' : 'bx-badge-no' }}">{{ $s->is_active ? 'Active' : 'Inactive' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <section class="bx-section">
            <div class="bx-section-head"><h2 class="bx-h2">Your players</h2></div>
            <form method="GET" style="margin-bottom:10px;">
                <label class="bx-search">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M20 20l-3.5-3.5"/></svg>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by name, email or phone" aria-label="Search players">
                </label>
            </form>
            <div class="bx-panel ag-scroll" style="padding:4px 4px;">
                @if ($users->isEmpty())
                    <p class="bx-empty">No players are assigned to you yet. The admin assigns players to agents.</p>
                @else
                    <table class="ag-table">
                        <thead><tr><th>Player</th><th>Balance</th><th>Staked</th><th>Won</th><th>Player result</th><th>Joined</th></tr></thead>
                        <tbody>
                            @foreach ($users as $u)
                                @php
                                    $pu = $perUser[(string) $u->id] ?? null;
                                    $res = $pu ? (float) $pu->win - (float) $pu->bet : 0;
                                @endphp
                                <tr>
                                    <td><b>{{ $u->name }}</b><div style="color:var(--bx-muted); font-size:12px;">#{{ $u->id }} — {{ $u->email }}</div></td>
                                    <td>&#8377;{{ number_format((float) ($u->wallet->deposit_balance ?? 0) + (float) ($u->wallet->winnings_balance ?? 0), 2) }}</td>
                                    <td>&#8377;{{ number_format($pu->bet ?? 0, 2) }}</td>
                                    <td>&#8377;{{ number_format($pu->win ?? 0, 2) }}</td>
                                    <td style="color:{{ $res >= 0 ? 'var(--bx-win)' : '#ff8a9a' }}; font-weight:800;">{{ $res >= 0 ? '+' : '−' }}&#8377;{{ number_format(abs($res), 2) }}</td>
                                    <td style="color:var(--bx-muted);">{{ $u->created_at?->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            @if ($users->hasPages())
                <div style="margin-top:12px; display:flex; justify-content:space-between;">
                    @if ($users->previousPageUrl())<a class="bx-btn bx-btn-ghost bx-btn-sm" href="{{ $users->previousPageUrl() }}">Previous</a>@else<span></span>@endif
                    @if ($users->nextPageUrl())<a class="bx-btn bx-btn-ghost bx-btn-sm" href="{{ $users->nextPageUrl() }}">Next</a>@endif
                </div>
            @endif
        </section>
    </main>
</body>
</html>
