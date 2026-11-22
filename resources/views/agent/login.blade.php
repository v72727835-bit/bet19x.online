<x-guest-layout :title="'Agent login - '.($siteSetting->site_name ?? 'BET19X')">
    <h1 class="bx-h1" style="text-align:center;">Agent login</h1>
    <p class="bx-sub" style="text-align:center; margin-bottom:18px;">For master agents, agents and sub-agents.</p>

    @if ($errors->any())
        <div class="bx-alert bx-alert-err">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('agent.login.submit') }}" class="bx-panel">
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
        <button type="submit" class="bx-btn bx-btn-gold bx-btn-block">Log in</button>
    </form>
    <p class="bx-sub" style="text-align:center; margin-top:18px;">Forgot your password? Ask the admin to set a new one.</p>
</x-guest-layout>
