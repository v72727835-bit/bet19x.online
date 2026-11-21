@guest
<div style="max-width:1400px; margin:14px auto 0; padding:0 14px; font-family:'Rajdhani',sans-serif;">
    <div style="display:flex; align-items:center; gap:12px; padding:12px 14px; border-radius:16px; border:1px solid #5a3a10;
                background:linear-gradient(135deg,#3a0a10 0%,#1a0a0c 60%,#2a1a05 100%); box-shadow:0 6px 20px rgba(0,0,0,.45);">
        <div style="flex:1; min-width:0;">
            <div style="color:#ffd76a; font-size:20px; font-weight:800; line-height:1.1;">Play &amp; Win Big</div>
            <div style="color:#c9a86a; font-size:13px;">Games khelne ke liye login karein</div>
        </div>
        <a href="{{ route('login') }}" style="background:linear-gradient(180deg,#ffd76a,#f0a81a); color:#1a0a0c; font-weight:800; font-size:15px; padding:10px 16px; border-radius:12px; text-decoration:none;">Log in</a>
        <a href="{{ route('register') }}" style="background:linear-gradient(180deg,#ff2d3d,#b30010); color:#fff; font-weight:800; font-size:15px; padding:10px 16px; border-radius:12px; text-decoration:none;">Sign up</a>
    </div>
</div>
@else
@php
    $wbWallet = \App\Models\Wallet::where('user_id', Auth::id())->first();
    $wbMain = (float) ($wbWallet->deposit_balance ?? 0) + (float) ($wbWallet->winnings_balance ?? 0);
    $wbBonus = (float) ($wbWallet->bonus_balance ?? 0);
@endphp
<div style="max-width:1400px; margin:14px auto 0; padding:0 14px; font-family:'Rajdhani',sans-serif;">
    <div style="display:flex; align-items:center; gap:12px; padding:12px 14px; border-radius:16px; border:1px solid #5a3a10;
                background:linear-gradient(135deg,#3a0a10 0%,#1a0a0c 60%,#2a1a05 100%); box-shadow:0 6px 20px rgba(0,0,0,.45);">
        <div style="flex:1; min-width:0;">
            <div style="color:#c9a86a; font-size:12px; font-weight:600; letter-spacing:1px; text-transform:uppercase;">Main Balance</div>
            <div style="color:#ffd76a; font-size:28px; font-weight:800; line-height:1.1;">&#8377;{{ number_format($wbMain, 2) }}</div>
            @if ($wbBonus > 0)
                <div style="color:#e38fb3; font-size:12px; font-weight:600;">Bonus &#8377;{{ number_format($wbBonus, 2) }}</div>
            @endif
        </div>
        <a href="{{ route('wallet.deposit') }}" style="background:linear-gradient(180deg,#34d399,#059669); color:#fff; font-weight:800; font-size:15px; padding:10px 16px; border-radius:12px; text-decoration:none; box-shadow:0 4px 12px rgba(5,150,105,.4);">Deposit</a>
        <a href="{{ route('wallet.withdraw') }}" style="background:linear-gradient(180deg,#ff2d3d,#b30010); color:#fff; font-weight:800; font-size:15px; padding:10px 16px; border-radius:12px; text-decoration:none; box-shadow:0 4px 12px rgba(230,0,18,.4);">Withdraw</a>
    </div>
</div>

@endguest
