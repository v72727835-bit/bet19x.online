@extends('agent.layout')
@section('title', 'My agents')
@section('content')
    @php
        $money = fn ($v, $dec = 0) => ($v < 0 ? '−' : '') . '₹' . number_format(abs($v), $dec);
    @endphp
    <div class="bx-pagehead"><h1 class="bx-h1">My agents</h1><p class="bx-sub">Distributors and retailers under you, with their players' result for the selected period.</p></div>

    <div class="bx-panel ag-scroll" style="padding:4px; margin-top:12px;">
        @if ($subAgents->isEmpty())
            <p class="bx-empty">No agents under you yet. The admin creates agents and places them under you.</p>
        @else
            <table class="ag-table">
                <thead><tr><th>Name</th><th>Type</th><th>Under</th><th>Players</th><th>Their %</th><th>Players' loss</th><th>Credit limit</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach ($subAgents as $s)
                        @php
                            $hr = $results[$s->id] ?? 0;
                        @endphp
                        <tr>
                            <td><b>{{ $s->name }}</b><div class="ag-mut">{{ $s->email }} — code {{ $s->referralCode() }}</div></td>
                            <td>{{ $s->roleLabel() }}</td>
                            <td>{{ $s->parent->name ?? '—' }}</td>
                            <td>{{ $s->users_count }}</td>
                            <td>{{ rtrim(rtrim(number_format($s->commission_rate, 2), '0'), '.') }}%</td>
                            <td class="{{ $hr >= 0 ? 'ag-pos' : 'ag-neg' }}">{{ $money($hr) }}</td>
                            <td>₹{{ number_format($s->credit_limit, 0) }}</td>
                            <td><span class="bx-badge {{ $s->is_active ? 'bx-badge-ok' : 'bx-badge-no' }}">{{ $s->is_active ? 'Active' : 'Inactive' }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
