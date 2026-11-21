<x-guest-layout :title="'Forgot Password - '.($siteSetting->site_name ?? 'Admin Panel')">
    <div class="text-center mb-8">
        <h1 style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-2xl sm:text-3xl">Reset Your Password</h1>
        <p style="color:#8A8CB3;" class="text-sm mt-1 px-2">Enter your email and we'll send you a reset link</p>
    </div>

    <div style="background-color:#1D1E3D; border:1px solid #24254A;" class="rounded-2xl p-6 sm:p-8 shadow-2xl">
        @if (session('status'))
            <div class="mb-4 p-3 rounded-lg text-sm" style="background-color:#16332A; color:#8FE3B0;">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg text-sm" style="background-color:#3A1622; color:#FF9B9B;">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf
            <div>
                <label style="color:#8A8CB3;" class="block text-xs mb-1.5">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    style="background-color:#14152B; color:#F4F3FA; border-color:#24254A;"
                    class="w-full rounded-lg border px-4 py-3 text-sm focus:outline-none focus:border-[#FFB627] transition">
            </div>
            <button type="submit"
                style="background-color:#FFB627; color:#14152B; font-family:'Rajdhani',sans-serif; font-weight:700;"
                class="w-full rounded-lg py-3 text-base hover:opacity-90 transition">
                Send Reset Link
            </button>
        </form>
    </div>

    <p class="text-center text-sm mt-6" style="color:#8A8CB3;">
        <a href="{{ route('login') }}" style="color:#FF6B4A; font-weight:600;" class="hover:opacity-80">&larr; Back to Login</a>
    </p>
</x-guest-layout>
