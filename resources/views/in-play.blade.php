<x-app-layout>
    <style>
        @keyframes fxLivePulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255,31,77,0.55); }
            50%      { box-shadow: 0 0 0 8px rgba(255,31,77,0); }
        }
        .ip-live-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: linear-gradient(135deg, #FF4F9A, #FF1F4D);
            color: #FFF0F5;
            font-family: 'Rajdhani', sans-serif;
            font-weight: 700;
            font-size: 11px;
            letter-spacing: 0.5px;
            padding: 4px 10px;
            border-radius: 999px;
            animation: fxLivePulse 1.8s ease-in-out infinite;
        }
        .ip-live-dot {
            width: 6px; height: 6px;
            border-radius: 999px;
            background: #FFF0F5;
        }
        .ip-card {
            border-radius: 18px;
            border: 1px solid #5C2038;
            background: linear-gradient(160deg, rgba(58,15,34,0.9), rgba(30,7,17,0.95));
            padding: 18px;
            margin-bottom: 16px;
        }
        .ip-empty {
            text-align: center;
            padding: 60px 20px;
            color: #E38FB3;
            font-family: 'Rajdhani', sans-serif;
        }
    </style>

    <div style="max-width:900px; margin:32px auto 0 auto; padding:0 16px 40px 16px;">
        <h2 style="color:#FFF0F5; font-family:'Rajdhani',sans-serif; font-weight:800; font-size:22px; margin-bottom:20px; text-align:center;">
            🎮 In Play
        </h2>

        @forelse ($entries as $entry)
            @php
                $contest = $entry->contest;
                $match = $contest->matchEvent ?? null;
            @endphp
            <div class="ip-card">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                    <div>
                        <p style="color:#FFF0F5; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-sm">
                            {{ $contest->title ?? 'Contest' }}
                        </p>
                        @if ($match)
                            <p style="color:#E38FB3;" class="text-xs mt-1">
                                {{ $match->team_a ?? '' }} vs {{ $match->team_b ?? '' }}
                            </p>
                        @endif
                    </div>
                    <span class="ip-live-badge">
                        <span class="ip-live-dot"></span> LIVE
                    </span>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #5C2038; padding-top:12px;">
                    <div>
                        <p style="color:#8A6478;" class="text-xs">Your Team</p>
                        <p style="color:#FFF0F5; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-sm">
                            {{ $entry->userTeam->team_name ?? '—' }}
                        </p>
                    </div>
                    <div style="text-align:center;">
                        <p style="color:#8A6478;" class="text-xs">Rank</p>
                        <p style="color:#D4AF37; font-family:'Rajdhani',sans-serif; font-weight:800;" class="text-sm">
                            {{ $entry->rank ?? '—' }}
                        </p>
                    </div>
                    <div style="text-align:right;">
                        <p style="color:#8A6478;" class="text-xs">Points</p>
                        <p style="color:#FF4F9A; font-family:'Rajdhani',sans-serif; font-weight:800;" class="text-sm">
                            {{ $entry->points ?? '0' }}
                        </p>
                    </div>
                </div>

                <a href="#" style="display:block; text-align:center; margin-top:14px; background:linear-gradient(90deg, #FF4F9A, #FF1F4D); color:#2B0A18; font-weight:700; border-radius:8px; padding:8px 0; font-size:12px; text-decoration:none;">
                    View Scorecard
                </a>
            </div>
        @empty
            <div class="ip-empty">
                <p style="font-size:40px; margin-bottom:10px;">🏏</p>
                <p style="font-weight:700; font-size:15px;">Abhi koi contest live nahi hai</p>
                <p style="font-size:12px; margin-top:6px; color:#8A6478;">Jaise hi aapka joined contest live hoga, wo yahan dikhega.</p>
            </div>
        @endforelse
    </div>
    <x-bottom-nav />
</x-app-layout>
