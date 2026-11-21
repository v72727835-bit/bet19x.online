<style>
    .fx-mobile-nav {
        display: none;
    }
    @media (max-width: 640px) {
        .fx-mobile-nav {
            display: flex;
            position: fixed;
            left: 0; right: 0; bottom: 0;
            z-index: 50;
            background: linear-gradient(180deg, #3A0F22 0%, #1A0510 100%);
            border-top: 2px solid #D4AF37;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.5);
            padding: 8px 4px 10px 4px;
            justify-content: space-around;
            align-items: flex-end;
        }
        .fx-mobile-nav-spacer { display: block; height: 80px; }
    }
    .fx-mobile-nav-spacer { display: none; }

    .fx-nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        text-decoration: none;
        flex: 1;
    }
    .fx-nav-icon {
        width: 34px; height: 34px;
        border-radius: 999px;
        background: rgba(212,175,55,0.10);
        border: 1px solid rgba(212,175,55,0.35);
        display: flex; align-items: center; justify-content: center;
        font-size: 15px;
    }
    .fx-nav-label {
        font-family: 'Rajdhani', sans-serif;
        font-weight: 600;
        font-size: 10px;
        letter-spacing: 0.3px;
    }

    @keyframes fxHomeGlow {
        0%, 100% { box-shadow: 0 0 0 0 rgba(212,175,55,0.55), 0 4px 14px rgba(0,0,0,0.45); }
        50%      { box-shadow: 0 0 0 9px rgba(212,175,55,0), 0 4px 14px rgba(0,0,0,0.45); }
    }
    .fx-nav-item-home {
        flex: 1;
        position: relative;
    }
    .fx-nav-crown {
        position: absolute;
        top: -12px; left: 50%;
        transform: translateX(-50%);
        font-size: 12px;
        filter: drop-shadow(0 1px 2px rgba(0,0,0,0.5));
    }
    .fx-nav-icon-home {
        width: 34px; height: 34px;
        border-radius: 999px;
        background: radial-gradient(circle at 35% 30%, #F9E9B0, #D4AF37 55%, #8A6A17 100%);
        border: 2px solid #F9E9B0;
        box-shadow: inset 0 0 0 3px #1A0510, inset 0 0 10px rgba(0,0,0,0.25);
        display: flex; align-items: center; justify-content: center;
        font-size: 26px;
        animation: fxHomeGlow 2.4s ease-in-out infinite;
    }
    .fx-nav-label-home {
        font-family: 'Rajdhani', sans-serif;
        font-weight: 700;
        font-size: 11px;
        letter-spacing: 0.5px;
        color: #D4AF37;
        margin-top: 4px;
    }
</style>

<nav class="fx-mobile-nav">
    <a href="{{ route('dashboard') }}" class="fx-nav-item">
        <span class="fx-nav-icon" style="color:#E38FB3;">🏆</span>
        <span class="fx-nav-label" style="color:#E38FB3;">Contests</span>
    </a>
    <a href="{{ route('in-play.index') }}" class="fx-nav-item">
        <span class="fx-nav-icon" style="color:#E38FB3;">🎮</span>
        <span class="fx-nav-label" style="color:#E38FB3;">In Play</span>
    </a>
    <a href="{{ route('dashboard') }}" class="fx-nav-item fx-nav-item-home">
        <span class="fx-nav-crown">👑</span>
        <span class="fx-nav-icon-home">🏰</span>
        <span class="fx-nav-label-home">Home</span>
    </a>
    <a href="{{ route('wallet.index') }}" class="fx-nav-item">
        <span class="fx-nav-icon" style="color:#E38FB3;">💰</span>
        <span class="fx-nav-label" style="color:#E38FB3;">Wallet</span>
    </a>
    <a href="{{ route('profile.edit') }}" class="fx-nav-item">
        <span class="fx-nav-icon" style="color:#E38FB3;">👤</span>
        <span class="fx-nav-label" style="color:#E38FB3;">Profile</span>
    </a>
</nav>
<div class="fx-mobile-nav-spacer"></div>
