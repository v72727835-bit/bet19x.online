<x-app-layout>
    @php
        $total = $heroBanners->count();
    @endphp

    @if ($total > 0)
        <style>
            @keyframes fxSlideFade {
                0% { opacity: 0; }
                3% { opacity: 1; }
                {{ round(100 / $total - 3, 2) }}% { opacity: 1; }
                {{ round(100 / $total, 2) }}% { opacity: 0; }
                100% { opacity: 0; }
            }
            .fx-slide {
                animation-name: fxSlideFade;
                animation-duration: {{ $total * 5 }}s;
                animation-iteration-count: infinite;
                animation-timing-function: ease-in-out;
            }
        </style>
        <div class="dashboard-hero relative w-full overflow-hidden" style="background:#0F1023; aspect-ratio:2.35/1; max-height:460px;">
            @foreach ($heroBanners as $i => $banner)
                @php
                    $img = str_starts_with($banner->image_path, 'http')
                        ? $banner->image_path
                        : asset('storage/' . $banner->image_path);
                @endphp
                <a href="{{ $banner->link_url ?? '#' }}" class="absolute inset-0 flex items-center justify-center fx-slide" style="animation-delay: {{ -($i * 5) }}s;">
                    <img src="{{ $img }}" class="fx-hero-img" alt="Banner {{ $i + 1 }}" style="width:100%; height:100%;">
                </a>
            @endforeach
        </div>
    @endif
@include("partials.astech-games")
<style id="dashboard-visual-17oct">
body:has(#ag-root),body:has(#ag-root) main{background:#100c04!important}
.dashboard-hero{display:block!important;position:relative;isolation:isolate;overflow:hidden;width:100%;min-height:160px;background:#0d0902!important}
.dashboard-hero .fx-slide{position:absolute;inset:0;z-index:1;opacity:0}
.dashboard-hero .fx-slide:first-child{animation:none!important;opacity:1!important;z-index:0}
.dashboard-hero .fx-hero-img{display:block;width:100%;height:100%;object-fit:contain;object-position:center}
@media(max-width:640px){.dashboard-hero{aspect-ratio:2.05/1!important;min-height:160px;max-height:300px!important}}
@media(prefers-reduced-motion:reduce){.dashboard-hero .fx-slide{animation:none!important;opacity:0!important}.dashboard-hero .fx-slide:first-child{opacity:1!important}}
#ag-root{max-width:1600px!important;margin:0 auto 70px!important;padding:72px 32px 32px!important;font-family:Inter,Arial,sans-serif!important;color:#f8f4e8!important}
#ag-root h3{font-family:Inter,Arial,sans-serif;font-size:clamp(30px,4vw,56px);line-height:1.2;color:#efc64a;font-weight:700;margin:0 0 42px;text-align:center}
#ag-root .ag-bar{justify-content:center;gap:12px;margin:0 auto 42px;max-width:1050px}
#ag-root .ag-bar input,#ag-root .ag-bar select{background:#080704;color:#f8f4e8;border:1px solid #493313;border-radius:15px;padding:16px 18px;min-height:54px;font-family:inherit;font-size:15px;box-shadow:none}
#ag-root .ag-bar input:focus,#ag-root .ag-bar select:focus{outline:2px solid #e5b12b;outline-offset:3px}
#ag-root .ag-chips{justify-content:center;gap:12px;margin-bottom:32px;padding:4px 2px 12px}
#ag-root .ag-chip{background:#050402;color:#f8f4e8;border:1px solid #493313;border-radius:15px;padding:14px 20px;font-family:inherit;font-size:15px}
#ag-root .ag-chip.on{background:#e3ad22;color:#120e03;border-color:#e3ad22}
#ag-root .ag-grid{grid-template-columns:repeat(6,minmax(0,1fr));gap:15px}
#ag-root .ag-card{background:#080603;border:1px solid #493313;border-radius:16px;color:#faf6eb;transition:transform .18s,border-color .18s;box-shadow:none}
#ag-root .ag-card:hover{transform:translateY(-3px);border-color:#d7a92b}
#ag-root .ag-card:focus-visible{outline:3px solid #edc54b;outline-offset:4px}
#ag-root .ag-img{aspect-ratio:1.75;background:radial-gradient(ellipse at center,#493117 0%,#251506 48%,#170d04 100%);font-size:36px;font-weight:700;color:#efc64a}
#ag-root .ag-img img{width:100%;height:100%;object-fit:contain;padding:8px}
#ag-root .ag-t{background:#040402;padding:13px 10px;text-align:center;min-height:60px}
#ag-root .ag-t b{font-family:inherit;font-size:13px;line-height:1.35}
#ag-root .ag-t small{display:block;color:#bcaa7a;font-size:11px;margin-top:5px}
#ag-root .ag-more{background:#e3ad22;color:#120e03;border:1px solid #e3ad22;border-radius:14px;padding:14px 30px;font-family:inherit;margin-top:32px}
@media(min-width:1500px){#ag-root .ag-grid{grid-template-columns:repeat(8,minmax(0,1fr))}}
@media(max-width:1000px){#ag-root .ag-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
@media(max-width:640px){#ag-root{padding:42px 16px 24px!important}#ag-root h3{margin-bottom:25px}#ag-root .ag-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}#ag-root .ag-bar{margin-bottom:26px}#ag-root .ag-bar input{flex-basis:100%}#ag-root .ag-bar select{min-width:0;width:45%}#ag-root .ag-chips{justify-content:flex-start}}
@media(prefers-reduced-motion:reduce){#ag-root .ag-card{transition:none}#ag-root .ag-card:hover{transform:none}}
</style>

    
    
    @if (isset($topWinners) && $topWinners->count())
        <style>
            @keyframes fxWinnerScroll {
                0%   { transform: translateY(0); }
                100% { transform: translateY(-50%); }
            }
            .fx-winner-track {
                animation: fxWinnerScroll 22s linear infinite;
            }
            .fx-winner-viewport:hover .fx-winner-track {
                animation-play-state: paused;
            }
        </style>

        <div style="max-width:1152px; margin:40px auto 0 auto; padding:0 16px;">
            <h3 style="color:#FFFFFF; font-family:'Rajdhani',sans-serif; font-weight:800; text-align:center; width:100%; text-shadow:0 2px 8px rgba(0,0,0,.4);">Top Winners</h3>
            <div class="fx-winner-viewport" style="border-radius:22px; overflow:hidden; border:1px solid #5C2038; background:linear-gradient(180deg, #2B0A18 0%, #3A0F22 100%); margin-top:16px; height:440px; position:relative;">
                <div class="fx-winner-track">
                    @foreach ([1, 2] as $loopPass)
                        @foreach ($topWinners as $i => $winner)
                            <div style="display:flex; align-items:center; gap:14px; padding:14px 20px; border-bottom:1px solid #5C2038;">
                                <span style="color:#FF4F9A; font-family:'Rajdhani',sans-serif; font-weight:800; width:26px; text-align:center; font-size:15px;">#{{ $i + 1 }}</span>
                                <img src="{{ $winner->avatar }}" style="width:44px; height:44px; border-radius:999px; border:2px solid #FF4F9A;">
                                <div style="flex:1;">
                                    <p style="color:#FFF0F5; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-sm">{{ $winner->name }}</p>
                                    <p style="color:#E38FB3;" class="text-xs">{{ $winner->contest_name }}</p>
                                </div>
                                <span style="color:#FF4F9A; font-family:'Rajdhani',sans-serif; font-weight:800;" class="text-sm">₹{{ number_format($winner->amount_won) }}</span>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    
    @if (isset($siteStats) && $siteStats->count())
        <style>
            @keyframes fxStatFadeUp {
                0%   { opacity: 0; transform: translateY(24px); }
                100% { opacity: 1; transform: translateY(0); }
            }
            @keyframes fxCrownGlow {
                0%, 100% { box-shadow: 0 0 0 0 rgba(212,175,55,0.6), inset 0 0 12px rgba(212,175,55,0.15); }
                50%      { box-shadow: 0 0 0 12px rgba(212,175,55,0), inset 0 0 18px rgba(212,175,55,0.3); }
            }
            @keyframes fxGoldShine {
                0%   { background-position: 0% 50%; }
                100% { background-position: 200% 50%; }
            }
            .fx-stat-card {
                opacity: 0;
                position: relative;
                animation: fxStatFadeUp 0.6s ease forwards;
                transition: transform .25s ease, box-shadow .25s ease;
            }
            .fx-stat-card:nth-child(1) { animation-delay: 0.05s; }
            .fx-stat-card:nth-child(2) { animation-delay: 0.20s; }
            .fx-stat-card:nth-child(3) { animation-delay: 0.35s; }
            .fx-stat-card:nth-child(4) { animation-delay: 0.50s; }
            .fx-stat-card:hover {
                transform: translateY(-6px);
                box-shadow: 0 14px 34px rgba(212,175,55,0.35);
            }
            .fx-stat-card::before, .fx-stat-card::after {
                content: "◆";
                position: absolute;
                color: #D4AF37;
                font-size: 10px;
                top: 8px;
            }
            .fx-stat-card::before { left: 10px; }
            .fx-stat-card::after { right: 10px; }
            .fx-stat-icon-badge {
                width: 60px; height: 60px; border-radius: 999px;
                background: radial-gradient(circle at 35% 30%, #F5D77A, #D4AF37 60%, #9C7A1E 100%);
                border: 2px solid #F5D77A;
                display: flex; align-items: center; justify-content: center;
                font-size: 26px; margin: 0 auto;
                animation: fxCrownGlow 2.6s ease-in-out infinite;
            }
            .fx-stat-value {
                background: linear-gradient(90deg, #D4AF37, #F9E9B0, #D4AF37);
                background-size: 200% auto;
                -webkit-background-clip: text;
                background-clip: text;
                -webkit-text-fill-color: transparent;
                animation: fxGoldShine 3s linear infinite;
                font-family: Georgia, 'Times New Roman', serif !important;
            }
        </style>

        <div style="max-width:1152px; margin:40px auto 0 auto; padding:0 16px;">
            <p style="text-align:center; color:#D4AF37; font-family:'Rajdhani',sans-serif; font-weight:700; letter-spacing:2px; text-transform:uppercase; font-size:12px; margin-bottom:10px;">👑 By The Numbers</p>
            <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:16px;" class="fx-stat-grid">
                @foreach ($siteStats as $stat)
                    <div class="fx-stat-card" style="border-radius:16px; border:1px solid #D4AF37; background:linear-gradient(160deg, rgba(58,15,34,0.9), rgba(30,7,17,0.95)); padding:26px 12px 20px 12px; text-align:center;">
                        <div class="fx-stat-icon-badge">{{ $stat->icon }}</div>
                        <p class="fx-stat-value" style="font-weight:800;" class="text-3xl mt-3">{{ $stat->value }}</p>
                        <p style="color:#E8C97A; letter-spacing:0.5px;" class="text-xs mt-1">{{ $stat->label }}</p>
                    </div>
                @endforeach
            </div>
        </div>
        <style>
            @media (min-width: 641px) {
                .fx-stat-grid { grid-template-columns: repeat(4, 1fr) !important; }
            }
        </style>
    @endif
    

    <footer class="fx-site-footer" style="background:linear-gradient(180deg, #2B0A18 0%, #1A0510 100%); border-top:1px solid #5C2038; margin-top:48px; padding:40px 16px 24px 16px;">
        <div style="max-width:1152px; margin:0 auto;">

            <div style="display:grid; grid-template-columns:1fr; gap:32px;" class="fx-footer-grid">

                <div>
                    @if ($siteSetting && $siteSetting->logo_path)
                        <img src="{{ asset('storage/' . $siteSetting->logo_path) }}" alt="{{ $siteSetting->site_name ?? 'FantasyXI' }}" style="height:44px; width:auto; margin-bottom:12px;">
                    @else
                        <p style="color:#FFF0F5; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-lg mb-3">{{ $siteSetting->site_name ?? 'FantasyXI' }}</p>
                    @endif
                    <p style="color:#E38FB3;" class="text-xs leading-relaxed">{{ $siteSetting->footer_text ?? 'Play responsibly. 18+ only.' }}</p>
                </div>

                <div>
                    <p style="color:#FFF0F5; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-sm mb-3">Payment Methods</p>
                    <div style="display:flex; flex-wrap:wrap; gap:8px; justify-content:center;">
                        @foreach (['UPI', 'Paytm', 'Net Banking', 'Debit/Credit Card'] as $pm)
                            <span style="background:rgba(255,79,154,0.12); border:1px solid #5C2038; color:#FFF0F5; font-size:11px; padding:6px 10px; border-radius:8px;">{{ $pm }}</span>
                        @endforeach
                    </div>
                </div>

            </div>

            <div style="border-top:1px solid #5C2038; margin-top:32px; padding-top:16px; display:flex; flex-wrap:wrap; justify-content:center; gap:16px; text-align:center;">
                <p style="color:#8A6478;" class="text-xs">&copy; {{ date('Y') }} {{ $siteSetting->site_name ?? 'FantasyXI' }}. All rights reserved.</p>
                <p style="color:#8A6478;" class="text-xs">This is a game of skill. Play responsibly. 18+</p>
            </div>

        </div>
    </footer>
    <style>
        @media (min-width: 768px) {
            .fx-footer-grid { grid-template-columns: 1.4fr 1fr !important; }
        }
    </style>
</x-app-layout>
