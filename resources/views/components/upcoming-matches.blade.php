@php
    use App\Models\MatchEvent;

    $matches = MatchEvent::with(['sport', 'contests'])
        ->where('status', 'upcoming')
        ->orderBy('start_time')
        ->take(6)
        ->get();
@endphp

<div class="mb-10">
    <div class="flex items-center justify-between mb-4">
        <h3 style="color:#14152B; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-lg">Upcoming matches</h3>
        <a href="#" style="color:#FF6B4A; font-family:'Rajdhani',sans-serif; font-weight:600;" class="text-sm">See all &rarr;</a>
    </div>

    @if ($matches->isEmpty())
        <div style="background-color:#F4F3FA; border:1px dashed #C7C6DE;" class="rounded-xl p-8 text-center">
            <p style="color:#5C5D80;" class="text-sm">No upcoming matches yet. Check back soon.</p>
        </div>
    @else
        <div class="flex gap-4 overflow-x-auto pb-2 -mx-1 px-1 sm:grid sm:grid-cols-3 sm:overflow-visible">
            @foreach ($matches as $match)
                @php
                    $pool = $match->contests->sum('total_prize_pool');
                    $locksIn = now()->diff($match->lineup_lock_time);
                @endphp
                <div style="background-color:#14152B; min-width:260px;" class="relative rounded-xl overflow-hidden shrink-0 sm:min-w-0">
                    <div style="background-color:#FFB627;" class="px-4 py-2 flex items-center justify-between">
                        <span style="color:#4A190B; font-family:'Rajdhani',sans-serif; font-weight:700; font-size:12px; letter-spacing:0.04em;">PRIZE POOL</span>
                        <span style="color:#4A190B; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-base">₹{{ number_format($pool) }}</span>
                    </div>

                    <div class="p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-center flex-1">
                                <span style="background-color:#24254A; color:#F4F3FA;" class="inline-flex h-10 w-10 items-center justify-center rounded-full text-xs font-semibold mb-1">{{ substr($match->team_a_name, 0, 3) }}</span>
                                <p style="color:#F4F3FA;" class="text-xs truncate">{{ $match->team_a_name }}</p>
                            </div>
                            <span style="color:#FF6B4A; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-xs px-2">VS</span>
                            <div class="text-center flex-1">
                                <span style="background-color:#24254A; color:#F4F3FA;" class="inline-flex h-10 w-10 items-center justify-center rounded-full text-xs font-semibold mb-1">{{ substr($match->team_b_name, 0, 3) }}</span>
                                <p style="color:#F4F3FA;" class="text-xs truncate">{{ $match->team_b_name }}</p>
                            </div>
                        </div>

                        <p style="color:#8A8CB3;" class="text-xs text-center mb-3">
                            @if ($match->lineup_lock_time->isFuture())
                                Lineup locks in {{ $locksIn->d > 0 ? $locksIn->d . 'd ' : '' }}{{ $locksIn->h }}h {{ $locksIn->i }}m
                            @else
                                Lineup locked
                            @endif
                        </p>

                        <a href="#" style="background-color:#FF6B4A; color:#4A190B; font-family:'Rajdhani',sans-serif; font-weight:700;" class="block text-center rounded-full py-2 text-sm hover:opacity-90 transition">Build your XI</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
