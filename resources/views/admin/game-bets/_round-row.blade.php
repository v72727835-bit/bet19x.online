{{-- one round: $r (from BetRounds::paginate), $games, optional $pu user --}}
<tr>
    <td class="fx-muted whitespace-nowrap">{{ $r->started_at->format('d M Y H:i:s') }}@if($r->ended_at->ne($r->started_at))<div class="text-xs">settled {{ $r->ended_at->format('H:i:s') }}</div>@endif</td>
    @isset($showPlayer)
        <td>
            @if($pu)
                <a href="{{ route('admin.users.show', $pu) }}" style="color: var(--fx-amber);">{{ $pu->name }}</a>
                <div class="fx-muted text-xs">#{{ $pu->id }} · {{ $pu->email }}</div>
            @else
                <span class="fx-muted">#{{ $r->user_id }} (deleted)</span>
            @endif
        </td>
    @endisset
    <td>{{ $games[$r->game_id]['title'] ?? $r->game_id }}<div class="fx-muted text-xs">{{ \Illuminate\Support\Str::limit($r->game_id, 40) }}</div></td>
    <td class="fx-muted text-xs">{{ $r->round_key }}@if($r->entries > 1)<div>{{ $r->entries }} entries</div>@endif</td>
    <td>₹{{ number_format($r->bet, 2) }}</td>
    <td>₹{{ number_format($r->win, 2) }}</td>
    <td>
        @if($r->refunded)
            <span class="fx-muted">—</span>
        @elseif($r->result > 0)
            <span style="color: var(--fx-green); font-weight:600;">Profit +₹{{ number_format($r->result, 2) }}</span>
        @elseif($r->result < 0)
            <span style="color: var(--fx-red); font-weight:600;">Loss −₹{{ number_format(abs($r->result), 2) }}</span>
        @else
            <span class="fx-muted">Even ₹0.00</span>
        @endif
    </td>
    <td>₹{{ number_format($r->balance_after, 2) }}</td>
    <td>
        <span class="fx-badge" style="background-color: var(--fx-card-hover); color: {{ $r->refunded ? 'var(--fx-amber)' : 'var(--fx-green)' }};">{{ $r->refunded ? 'Refunded' : 'Settled' }}</span>
    </td>
</tr>
