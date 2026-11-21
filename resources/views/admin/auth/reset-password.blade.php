<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Naya Password - {{ ($siteSetting ?? \App\Models\SiteSetting::first())->site_name ?? 'Admin Panel' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background-color:#14152B;" class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-full mb-3" style="background-color:#FFB627;">
                <span style="color:#14152B; font-family:'Rajdhani',sans-serif; font-weight:700; font-size:18px;">FX</span>
            </span>
            <h1 style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-2xl">Naya password set karein</h1>
        </div>

        <div style="background-color:#1D1E3D;" class="rounded-xl p-6 shadow-xl">
            @if ($errors->any())
                <div class="mb-4 p-3 rounded text-sm" style="background-color:#3A1622; color:#FF9B9B;">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.password.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div>
                    <label style="color:#8A8CB3;" class="block text-xs mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $email) }}" required autofocus
                        style="background-color:#14152B; color:#F4F3FA; border-color:#24254A;"
                        class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:border-[#FFB627]">
                </div>
                <div>
                    <label style="color:#8A8CB3;" class="block text-xs mb-1">Naya password</label>
                    <input type="password" name="password" required
                        style="background-color:#14152B; color:#F4F3FA; border-color:#24254A;"
                        class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:border-[#FFB627]">
                </div>
                <div>
                    <label style="color:#8A8CB3;" class="block text-xs mb-1">Password confirm karein</label>
                    <input type="password" name="password_confirmation" required
                        style="background-color:#14152B; color:#F4F3FA; border-color:#24254A;"
                        class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:border-[#FFB627]">
                </div>
                <button type="submit"
                    style="background-color:#FFB627; color:#14152B; font-family:'Rajdhani',sans-serif; font-weight:700;"
                    class="w-full rounded-lg py-2.5 text-sm hover:opacity-90 transition">
                    Password reset karein
                </button>
            </form>
        </div>
    </div>
</body>
</html>
