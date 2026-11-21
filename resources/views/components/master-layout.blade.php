<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php($mSetting = $masterSetting ?? \App\Models\MasterSetting::first())
    <title>{{ $title ?? 'Master Panel' }} - {{ $mSetting->company_name ?? 'Master Panel' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --mp-bg: #0A0A18; --mp-surface: #14152B; --mp-card: #1A1B36; --mp-card-hover: #22234A;
            --mp-border: #2B2C55; --mp-text: #F4F3FA; --mp-muted: #8A8CB3;
            --mp-accent: {{ $mSetting->primary_color ?? '#8B5CF6' }}; --mp-green: #34D399; --mp-red: #F87171;
        }
        * { box-sizing: border-box; }
        html, body { margin:0; padding:0; height:100%; }
        body { background-color: var(--mp-bg); color: var(--mp-text); font-family:'Inter',sans-serif; overflow:hidden; }
        .mp-layout { display:flex; height:100vh; width:100%; }
        .mp-aside { position:fixed; top:0; left:0; bottom:0; width:260px; height:100vh; overflow-y:auto; z-index:40;
                    background-color: var(--mp-surface); border-right:1px solid var(--mp-border); display:flex; flex-direction:column; }
        .mp-aside-header { padding:20px; display:flex; align-items:center; gap:10px; position:sticky; top:0; z-index:5;
                            background-color: var(--mp-surface); border-bottom:1px solid var(--mp-border); }
        .mp-nav { flex:1; padding:8px 0; }
        .mp-content { flex:1; display:flex; flex-direction:column; height:100vh; overflow:hidden; margin-left:260px; }
        .mp-header { flex-shrink:0; display:flex; align-items:center; justify-content:space-between; padding:16px 24px;
                     background-color: var(--mp-surface); border-bottom:1px solid var(--mp-border); }
        .mp-main { flex:1; overflow-y:auto; overflow-x:hidden; padding:24px; }
        .mp-card { background-color: var(--mp-card); border:1px solid var(--mp-border); border-radius:14px; }
        .mp-input { background-color: var(--mp-surface); border:1px solid var(--mp-border); color: var(--mp-text); border-radius:8px; padding:8px 12px; font-size:13px; width:100%; }
        .mp-btn { background-color: var(--mp-accent); color:#0A0A18; border-radius:8px; padding:10px 18px; font-size:14px; font-weight:600; border:none; cursor:pointer; }
        .mp-btn-outline { background-color:transparent; border:1px solid var(--mp-border); color: var(--mp-text); border-radius:8px; padding:6px 12px; font-size:12px; cursor:pointer; }
        .mp-table { width:100%; border-collapse:collapse; }
        .mp-table th { text-align:left; color: var(--mp-muted); font-size:11px; text-transform:uppercase; padding:8px; border-bottom:1px solid var(--mp-border); }
        .mp-table td { padding:10px 8px; border-bottom:1px solid var(--mp-surface); font-size:13px; vertical-align:top; }
        .mp-badge { padding:3px 10px; border-radius:99px; font-size:11px; font-weight:600; }
        .mp-nav-link { display:block; padding:9px 16px; font-size:13px; color: var(--mp-muted); border-radius:8px; margin:0 10px 2px 10px; text-decoration:none; }
        .mp-nav-link:hover { background-color: var(--mp-card-hover); color: var(--mp-text); }
        .mp-nav-link.active { background-color: var(--mp-card-hover); color: var(--mp-accent); font-weight:600; }
        .mp-nav-group-title { font-size:10px; text-transform:uppercase; letter-spacing:.05em; color:#565881; padding:16px 16px 6px 16px; margin:0; }
        .mp-grid { display:grid; gap:16px; }
        .mp-grid-2 { grid-template-columns: repeat(2, 1fr); }
        .mp-grid-5 { grid-template-columns: repeat(5, 1fr); }
        @media (max-width: 900px) {
            .mp-aside { transform: translateX(-100%); transition: transform .2s; }
            .mp-aside.open { transform: translateX(0); }
            .mp-content { margin-left:0; }
            .mp-grid-5 { grid-template-columns: repeat(2, 1fr); }
        }
        .mp-hamburger { display:none; background:none; border:none; color: var(--mp-muted); font-size:22px; cursor:pointer; }
        @media (max-width: 900px) { .mp-hamburger { display:inline-block; } }
    </style>
</head>
<body>
    <div class="mp-layout">
        <aside class="mp-aside" id="mpAside">
            <div class="mp-aside-header">
                @if(!empty($mSetting?->logo_path))
                    <img src="{{ asset('storage/'.$mSetting->logo_path) }}" style="height:32px; width:32px; border-radius:50%; object-fit:cover;">
                @endif
                <span style="font-family:'Rajdhani',sans-serif; font-weight:700; font-size:16px;">{{ $mSetting->company_name ?? 'Master Panel' }}</span>
            </div>

            <nav class="mp-nav">
                <p class="mp-nav-group-title">Overview</p>
                <a href="{{ route('master.dashboard') }}" class="mp-nav-link {{ request()->routeIs('master.dashboard') ? 'active' : '' }}">Dashboard</a>

                <p class="mp-nav-group-title">Clients</p>
                <a href="{{ route('master.tenants.index') }}" class="mp-nav-link {{ request()->routeIs('master.tenants.*') ? 'active' : '' }}">Tenants / White-Label Domains</a>

                <p class="mp-nav-group-title">Panel</p>
                <a href="{{ route('master.settings.edit') }}" class="mp-nav-link {{ request()->routeIs('master.settings.*') ? 'active' : '' }}">Panel Branding</a>
            </nav>

            <form method="POST" action="{{ route('master.logout') }}" style="padding:16px;">
                @csrf
                <button class="mp-btn-outline" style="width:100%;">Logout</button>
            </form>
        </aside>

        <div class="mp-content">
            <header class="mp-header">
                <div style="display:flex; align-items:center; gap:12px;">
                    <button class="mp-hamburger" onclick="document.getElementById('mpAside').classList.toggle('open')">&#9776;</button>
                    <h1 style="font-family:'Rajdhani',sans-serif; font-weight:700; font-size:20px; margin:0;">{{ $title ?? 'Dashboard' }}</h1>
                </div>
            </header>

            <main class="mp-main">
                @if (session('status'))
                    <div class="mp-card" style="padding:16px; margin-bottom:24px; font-size:13px; border-color: var(--mp-green); color: var(--mp-green);">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mp-card" style="padding:16px; margin-bottom:24px; font-size:13px; border-color: var(--mp-red); color: var(--mp-red);">
                        <ul style="margin:0; padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
