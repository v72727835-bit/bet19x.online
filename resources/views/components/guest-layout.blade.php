<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#14070f">
    @php($fxSetting = $siteSetting ?? \App\Models\SiteSetting::first())
    <title>{{ $title ?? ($fxSetting->site_name ?? 'BET19X') }}</title>
    @if(!empty($fxSetting?->favicon_path))
        <link rel="icon" href="{{ asset('storage/'.$fxSetting->favicon_path) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bx-body">
    <div class="bx-auth">
        <div class="bx-auth-card">
            <a href="{{ route('dashboard') }}" aria-label="Back to games">
                @if ($fxSetting && $fxSetting->logo_path)
                    <img class="bx-auth-logo" src="{{ asset('storage/' . $fxSetting->logo_path) }}" alt="{{ $fxSetting->site_name ?? 'BET19X' }}">
                @else
                    <span class="bx-logo-txt" style="display:block; text-align:center; font-size:44px; margin-bottom:18px;">{{ $fxSetting->site_name ?? 'BET19X' }}</span>
                @endif
            </a>
            {{ $slot }}
        </div>
    </div>
</body>
</html>
