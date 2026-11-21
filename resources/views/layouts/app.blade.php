<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php($fxSetting = $siteSetting ?? \App\Models\SiteSetting::first())
        <title>{{ $fxSetting->site_name ?? config('app.name', 'Laravel') }}</title>
        @if(!empty($fxSetting?->favicon_path))
            <link rel="icon" href="{{ asset('storage/'.$fxSetting->favicon_path) }}">
        @endif
        <style>
            :root {
                --fx-amber: {{ $fxSetting->primary_color ?? '#E60012' }};
                --fx-coral: {{ $fxSetting->secondary_color ?? '#8B0000' }};
                --fx-green: {{ $fxSetting->accent_color ?? '#34D399' }};
            }
            html { scroll-behavior: smooth; }
            .fx-fade-in { animation: fxFadeIn .5s ease both; }
            @keyframes fxFadeIn { from { opacity:0; transform: translateY(12px); } to { opacity:1; transform: translateY(0); } }
            .fx-hover-scale { transition: transform .25s ease, box-shadow .25s ease; }
            .fx-hover-scale:hover { transform: scale(1.03); box-shadow: 0 12px 32px -8px rgba(230,0,18,.35); }
            .fx-btn-glow { transition: box-shadow .25s ease, transform .15s ease; }
            .fx-btn-glow:hover { box-shadow: 0 0 18px 2px rgba(230,0,18,.55); transform: translateY(-1px); }
            .fx-btn-glow:active { transform: scale(.97); }
            .fx-sticky-header { position: sticky; top: 0; z-index: 50; transition: box-shadow .2s ease; }
        </style>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased" style="background-color: #0D0D0D;">
        <div class="min-h-screen" style="background-color: #0D0D0D;">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header style="background-color:#14152B; border-bottom:1px solid #24254A;">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="pb-24 sm:pb-0">
                {{ $slot }}
            </main>

            @auth
                @include('layouts.bottom-nav')
            @endauth
        </div>
    </body>
</html>
