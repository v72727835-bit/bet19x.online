<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php($fxSetting = $siteSetting ?? \App\Models\SiteSetting::first())
    <title>{{ $title ?? ($fxSetting->site_name ?? 'Admin Panel') }}</title>
    @if(!empty($fxSetting?->favicon_path))
        <link rel="icon" href="{{ asset('storage/'.$fxSetting->favicon_path) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background-color:{{ $fxSetting->bg_dark_color ?? '#0F1023' }}; background-image: radial-gradient(circle at 15% 10%, {{ $fxSetting->primary_color ?? '#FFB627' }}1A, transparent 45%), radial-gradient(circle at 85% 90%, {{ $fxSetting->secondary_color ?? '#FF6B4A' }}1A, transparent 45%);" class="min-h-screen flex flex-col items-center justify-center px-4 py-10 sm:py-16">
    <a href="{{ route('login') }}" class="mb-8">
        @if ($fxSetting && $fxSetting->logo_path)
            <img src="{{ asset('storage/' . $fxSetting->logo_path) }}" alt="{{ $fxSetting->site_name ?? 'Logo' }}" style="height:90px; width:auto; display:block;">
        @else
            <span class="inline-flex h-28 w-28 items-center justify-center rounded-full shadow-lg" style="background-color:{{ $fxSetting->primary_color ?? '#FFB627' }};">
                <span style="color:#14152B; font-family:'Rajdhani',sans-serif; font-weight:700; font-size:38px;">{{ strtoupper(substr($fxSetting->site_name ?? 'FX', 0, 2)) }}</span>
            </span>
        @endif
    </a>
    <div class="w-full max-w-sm sm:max-w-md">
        {{ $slot }}
    </div>
</body>
</html>

