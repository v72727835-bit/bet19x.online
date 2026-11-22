@extends('agent.layout')
@section('title', 'Players')
@section('content')
    @php
        $rate = (float) $agent->commission_rate / 100;
        $money = fn ($v, $dec = 2) => ($v < 0 ? '−' : '') . '₹' . number_format(abs($v), $dec);
    @endphp
    <div class="bx-pagehead"><h1 class="bx-h1">Players</h1><p class="bx-sub">Everyone who joined through you or the agents under you.</p></div>

    <form method="GET" style="margin:12px 0;">
        <input type="hidden" name="range" value="{{ $rangeKey }}">
        <label class="bx-search">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M20 20l-3.5-3.5"/></svg>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by name, email or phone" aria-label="Search players">
        </label>
    </form>

    <div class="bx-panel ag-scroll" style="padding:4px;">
        @if ($users->isEmpty())
            <p class="bx-empty">No players yet. Share your referral link from the Overview tab.</p>
        @else
            <table class="ag-table">
                <thead><tr><th>Player</th><th>Via</th><th>Balance</th><th>Deposited</th><th>Bets</th><th>Staked</th><th>Won</th><th>Your share</th><th>Last bet</th><th>Joined</th><th></th></tr></thead>
                <tbody>
                    @foreach ($users as $u)
                        @php
                            $pu = $perUser[(string) $u->id] ?? null;
                            $hr = $pu ? (float) $pu->bet - (float) $pu->win : 0;
                        @endphp
                        <tr>
                            <td><b>{{ $u->name }}</b><div class="ag-mut">#{{ $u->id }} — {{ $u->email }}</div></td>
                            <td class="ag-mut">{{ $u->agent_id === $agent->id ? 'You' : ($u->agent->name ?? '—') }}</td>
                            <td>₹{{ number_format((float) ($u->wallet->deposit_balance ?? 0) + (float) ($u->wallet->winnings_balance ?? 0), 2) }}</td>
                            <td>₹{{ number_format((float) ($deposits[$u->id] ?? 0), 0) }}</td>
                            <td>{{ number_format($pu->rounds ?? 0) }}</td>
                            <td>₹{{ number_format($pu->bet ?? 0, 0) }}</td>
                            <td>₹{{ number_format($pu->win ?? 0, 0) }}</td>
                            <td class="{{ $hr >= 0 ? 'ag-pos' : 'ag-neg' }}">{{ $money($hr * $rate) }}</td>
                            <td class="ag-mut">{{ $pu && $pu->last_bet ? \Carbon\Carbon::parse($pu->last_bet)->diffForHumans() : '—' }}</td>
                            <td class="ag-mut">{{ $u->created_at?->format('d M Y') }}</td>
                            <td><a class="bx-link" href="{{ route('agent.bets', ['player' => $u->id]) }}">Bets</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    @if ($users->hasPages())
        <div style="margin-top:12px; display:flex; justify-content:space-between;">
            @if ($users->previousPageUrl())<a class="bx-btn bx-btn-ghost bx-btn-sm" href="{{ $users->previousPageUrl() }}">Previous</a>@else<span></span>@endif
            @if ($users->nextPageUrl())<a class="bx-btn bx-btn-ghost bx-btn-sm" href="{{ $users->nextPageUrl() }}">Next</a>@endif
        </div>
    @endif
@endsection
