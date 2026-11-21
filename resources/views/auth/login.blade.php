<x-guest-layout :title="'Log In - '.($siteSetting->site_name ?? 'Admin Panel')">
    <div class="text-center mb-8">
        <h1 style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-2xl sm:text-3xl">Welcome Back</h1>
        <p style="color:#8A8CB3;" class="text-sm mt-1">Log in to view and join your contests</p>
    </div>

    <div style="background-color:#1D1E3D; border:1px solid #24254A;" class="rounded-2xl p-6 sm:p-8 shadow-2xl">
        @if (session('status'))
            <div class="mb-4 p-3 rounded-lg text-sm" style="background-color:#16332A; color:#8FE3B0;">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg text-sm" style="background-color:#3A1622; color:#FF9B9B;">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <div>
                <label style="color:#8A8CB3;" class="block text-xs mb-1.5">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    style="background-color:#14152B; color:#F4F3FA; border-color:#24254A;"
                    class="w-full rounded-lg border px-4 py-3 text-sm focus:outline-none focus:border-[#FFB627] transition">
            </div>
            <div x-data="{ showPassword: false }">
                <label style="color:#8A8CB3;" class="block text-xs mb-1.5">Password</label>
                <div class="relative">
                    <input :type="showPassword ? 'text' : 'password'" name="password" required
                        style="background-color:#14152B; color:#F4F3FA; border-color:#24254A;"
                        class="w-full rounded-lg border px-4 py-3 pr-11 text-sm focus:outline-none focus:border-[#FFB627] transition">
                    <button type="button" @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-3" style="color:#8A8CB3;">
                        <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2" style="color:#8A8CB3;">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" style="color:#FFB627;" class="hover:opacity-80">Forgot password?</a>
                @endif
            </div>
            <button type="submit"
                style="background-color:#FFB627; color:#14152B; font-family:'Rajdhani',sans-serif; font-weight:700;"
                class="w-full rounded-lg py-3 text-base hover:opacity-90 transition">
                Log In
            </button>
        </form>
    </div>

    @if (Route::has('register'))
        <p class="text-center text-sm mt-6" style="color:#8A8CB3;">
            New to {{ $siteSetting->site_name ?? 'us' }}?
            <a href="{{ route('register') }}" style="color:#FF6B4A; font-weight:600;" class="hover:opacity-80">Sign up</a>
        </p>
    @endif
</x-guest-layout>
