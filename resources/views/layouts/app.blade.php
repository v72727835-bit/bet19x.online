<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#14070f">
        @php($fxSetting = $siteSetting ?? \App\Models\SiteSetting::first())
        <title>{{ $fxSetting->site_name ?? config('app.name', 'BET19X') }}</title>
        @if(!empty($fxSetting?->favicon_path))
            <link rel="icon" href="{{ asset('storage/'.$fxSetting->favicon_path) }}">
        @endif
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bx-body">
        @if (session('impersonator_admin_id') && auth()->check())
            <div style="position:sticky; top:0; z-index:70; display:flex; align-items:center; justify-content:center; gap:12px; flex-wrap:wrap; padding:8px 14px; background:#f2c14e; color:#2a1206; font-weight:800; font-size:14px;">
                <span>Admin view: you are logged in as {{ auth()->user()->name }} (#{{ auth()->id() }})</span>
                <form method="POST" action="{{ route('impersonation.leave') }}" style="margin:0;">
                    @csrf
                    <button type="submit" style="border:0; border-radius:8px; padding:6px 12px; background:#2a1206; color:#f2c14e; font-weight:800; cursor:pointer;">Back to admin panel</button>
                </form>
            </div>
        @endif
        @include('layouts.navigation')
        @include('layouts.games-sidebar')

        <main class="bx-main">
            @isset($header)
                <div class="bx-wrap bx-pagehead">{{ $header }}</div>
            @endisset
            {{ $slot }}
        </main>

        @include('layouts.bottom-nav')
        @include('layouts.support-widget')
    </body>
</html>
