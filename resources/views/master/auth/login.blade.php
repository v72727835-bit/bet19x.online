<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $masterSetting->company_name ?? 'Master Panel' }} Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background-color:#0A0A18;" class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            @if(!empty($masterSetting?->logo_path))
                <img src="{{ asset('storage/'.$masterSetting->logo_path) }}" alt="{{ $masterSetting->company_name }}" class="h-16 w-16 rounded-full object-cover mx-auto mb-3">
            @else
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-full mb-3" style="background-color:{{ $masterSetting->primary_color ?? '#8B5CF6' }};">
                    <span style="color:#0A0A18; font-family:'Rajdhani',sans-serif; font-weight:700; font-size:18px;">{{ strtoupper(substr($masterSetting->company_name ?? 'MP', 0, 2)) }}</span>
                </span>
            @endif
            <h1 style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-2xl">{{ $masterSetting->company_name ?? 'Master White-Label Panel' }}</h1>
            <p style="color:#8A8CB3;" class="text-sm mt-1">Yahan se naye client domains add karo</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg text-sm" style="background-color:rgba(248,113,113,.1); color:#F87171; border:1px solid rgba(248,113,113,.3);">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('master.login') }}" class="space-y-4" style="background-color:#14152B; padding:28px; border-radius:14px; border:1px solid #2B2C55;">
            @csrf
            <div>
                <label style="color:#8A8CB3;" class="text-xs block mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    style="background-color:#1D1E3D; border:1px solid #2B2C55; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm outline-none">
            </div>
            <div>
                <label style="color:#8A8CB3;" class="text-xs block mb-1">Password</label>
                <input type="password" name="password" required
                    style="background-color:#1D1E3D; border:1px solid #2B2C55; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm outline-none">
            </div>
            <label class="flex items-center gap-2 text-xs" style="color:#8A8CB3;">
                <input type="checkbox" name="remember"> Remember me
            </label>
            <button type="submit" style="background-color:#8B5CF6; color:#0A0A18;" class="w-full rounded-lg py-2.5 text-sm font-semibold">
                Login
            </button>
        </form>
    </div>
</body>
</html>
