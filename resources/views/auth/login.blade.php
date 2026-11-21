<x-guest-layout :title="'Log in - '.($siteSetting->site_name ?? 'BET19X')">
    <h1 class="bx-h1" style="text-align:center;">Welcome back</h1>
    <p class="bx-sub" style="text-align:center; margin-bottom:18px;">Log in to play and manage your wallet.</p>
    @if (session('status'))
        <div class="bx-alert bx-alert-ok">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="bx-alert bx-alert-err">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="bx-panel">
        @csrf
        <label class="bx-field">
            <span>Email</span>
            <input class="bx-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        </label>
        <label class="bx-field" x-data="{ show: false }">
                <span>Password</span>
                <span style="position:relative; display:block;">
                    <input class="bx-input" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password" style="padding-right:64px;">
                    <button type="button" class="bx-link" @click="show = !show" style="position:absolute; right:12px; top:50%; transform:translateY(-50%);" x-text="show ? 'Hide' : 'Show'">Show</button>
                </span>
            </label>

        <div style="display:flex; align-items:center; justify-content:space-between; margin:-2px 0 16px; font-size:14px;">
            <label style="display:flex; align-items:center; gap:8px; color:var(--bx-muted);"><input type="checkbox" name="remember"> Keep me logged in</label>
            @if (Route::has('password.request'))
                <a class="bx-link" href="{{ route('password.request') }}">Forgot password?</a>
            @endif
        </div>
        <button type="submit" class="bx-btn bx-btn-gold bx-btn-block">Log in</button>
    </form>
    @if (Route::has('register'))
        <p class="bx-sub" style="text-align:center; margin-top:18px;">New here? <a class="bx-link" href="{{ route('register') }}">Create an account</a></p>
    @endif
</x-guest-layout>
