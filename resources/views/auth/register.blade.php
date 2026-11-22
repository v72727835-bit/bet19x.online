<x-guest-layout :title="'Sign up - '.($siteSetting->site_name ?? 'BET19X')">
    <h1 class="bx-h1" style="text-align:center;">Create your account</h1>
    <p class="bx-sub" style="text-align:center; margin-bottom:18px;">It takes under a minute. You must be 18 or older.</p>
    @if (session('status'))
        <div class="bx-alert bx-alert-ok">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="bx-alert bx-alert-err">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="bx-panel">
        @csrf
        @php
            $refCode = request('ref') ?: session('ref_code') ?: request()->cookie('bx_ref');
            $refAgent = \App\Models\Agent::findByReferralCode($refCode);
        @endphp
        @if ($refAgent)
            <input type="hidden" name="ref" value="{{ $refAgent->referralCode() }}">
            <p class="bx-alert bx-alert-ok" style="margin-top:0;">Invited by {{ $refAgent->name }} (code {{ $refAgent->referralCode() }})</p>
        @endif
        <label class="bx-field">
            <span>Full name</span>
            <input class="bx-input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
        </label>
        <label class="bx-field">
            <span>Email</span>
            <input class="bx-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
        </label>
            <label class="bx-field" x-data="{ show: false }">
                <span>Password</span>
                <span style="position:relative; display:block;">
                    <input class="bx-input" :type="show ? 'text' : 'password'" name="password" required autocomplete="new-password" style="padding-right:64px;">
                    <button type="button" class="bx-link" @click="show = !show" style="position:absolute; right:12px; top:50%; transform:translateY(-50%);" x-text="show ? 'Hide' : 'Show'">Show</button>
                </span>
            </label>
            <label class="bx-field" x-data="{ show: false }">
                <span>Confirm password</span>
                <span style="position:relative; display:block;">
                    <input class="bx-input" :type="show ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" style="padding-right:64px;">
                    <button type="button" class="bx-link" @click="show = !show" style="position:absolute; right:12px; top:50%; transform:translateY(-50%);" x-text="show ? 'Hide' : 'Show'">Show</button>
                </span>
            </label>

        <button type="submit" class="bx-btn bx-btn-gold bx-btn-block">Create account</button>
    </form>
    <p class="bx-sub" style="text-align:center; margin-top:18px;">Already playing? <a class="bx-link" href="{{ route('login') }}">Log in</a></p>
</x-guest-layout>
