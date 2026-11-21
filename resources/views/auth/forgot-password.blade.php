<x-guest-layout :title="'Forgot Password - '.($siteSetting->site_name ?? 'Admin Panel')">
    <div class="text-center mb-8">
        <h1 style="color:#f8efe7; font-family:'Big Shoulders Display',sans-serif; font-weight:700;" class="text-2xl sm:text-3xl">Reset Your Password</h1>
        <p style="color:#b99ca4;" class="text-sm mt-1 px-2">Enter your email and we'll send you a reset link</p>
    </div>

    <div style="background-color:#22101a; border:1px solid #4a2a1c;" class="rounded-2xl p-6 sm:p-8 shadow-2xl">
        @if (session('status'))
            <div class="mb-4 p-3 rounded-lg text-sm" style="background-color:#16332A; color:#8FE3B0;">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg text-sm" style="background-color:#3A1622; color:#FF9B9B;">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf
            <div>
                <label style="color:#b99ca4;" class="block text-xs mb-1.5">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    style="background-color:#14070f; color:#f8efe7; border-color:#4a2a1c;"
                    class="w-full rounded-lg border px-4 py-3 text-sm focus:outline-none focus:border-[#f2c14e] transition">
            </div>
            <button type="submit"
                style="background-color:#f2c14e; color:#14070f; font-family:'Big Shoulders Display',sans-serif; font-weight:700;"
                class="w-full rounded-lg py-3 text-base hover:opacity-90 transition">
                Send Reset Link
            </button>
        </form>
    </div>

    <p class="text-center text-sm mt-6" style="color:#b99ca4;">
        <a href="{{ route('login') }}" style="color:#f2c14e; font-weight:600;" class="hover:opacity-80">&larr; Back to Login</a>
    </p>
</x-guest-layout>
