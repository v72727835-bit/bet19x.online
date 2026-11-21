<x-guest-layout :title="'Reset Password - '.($siteSetting->site_name ?? 'Admin Panel')">
    <div class="text-center mb-8">
        <h1 style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-2xl sm:text-3xl">Set New Password</h1>
    </div>

    <div style="background-color:#1D1E3D; border:1px solid #24254A;" class="rounded-2xl p-6 sm:p-8 shadow-2xl">
        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg text-sm" style="background-color:#3A1622; color:#FF9B9B;">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <div>
                <label style="color:#8A8CB3;" class="block text-xs mb-1.5">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus
                    style="background-color:#14152B; color:#F4F3FA; border-color:#24254A;"
                    class="w-full rounded-lg border px-4 py-3 text-sm focus:outline-none focus:border-[#FFB627] transition">
            </div>
            <div>
                <label style="color:#8A8CB3;" class="block text-xs mb-1.5">New Password</label>
                <input type="password" name="password" required
                    style="background-color:#14152B; color:#F4F3FA; border-color:#24254A;"
                    class="w-full rounded-lg border px-4 py-3 text-sm focus:outline-none focus:border-[#FFB627] transition">
            </div>
            <div>
                <label style="color:#8A8CB3;" class="block text-xs mb-1.5">Confirm Password</label>
                <input type="password" name="password_confirmation" required
                    style="background-color:#14152B; color:#F4F3FA; border-color:#24254A;"
                    class="w-full rounded-lg border px-4 py-3 text-sm focus:outline-none focus:border-[#FFB627] transition">
            </div>
            <button type="submit"
                style="background-color:#FFB627; color:#14152B; font-family:'Rajdhani',sans-serif; font-weight:700;"
                class="w-full rounded-lg py-3 text-base hover:opacity-90 transition">
                Reset Password
            </button>
        </form>
    </div>
</x-guest-layout>
