@extends('agent.layout')
@section('title', $kind === 'deposits' ? 'Deposits' : 'Withdrawals')
@section('content')
    @php
        $isDep = $kind === 'deposits';
        $route = $isDep ? 'agent.deposits' : 'agent.withdrawals';
    @endphp
    <div class="bx-pagehead">
        <h1 class="bx-h1">{{ $isDep ? 'Deposits' : 'Withdrawals' }}</h1>
        <p class="bx-sub">{{ $isDep ? 'Money your players added.' : 'Money your players took out.' }} Approvals are handled by the admin.</p>
    </div>

    <div class="bx-stats" style="margin-top:12px;">
        <div class="bx-stat"><b>₹{{ number_format($sum['approved'], 0) }}</b><span>{{ $isDep ? 'Approved' : 'Paid out' }}</span></div>
        <div class="bx-stat"><b>₹{{ number_format($sum['pending'], 0) }}</b><span>Waiting for approval</span></div>
        <div class="bx-stat"><b>{{ number_format($sum['count']) }}</b><span>Requests</span></div>
    </div>

    <div class="ag-bar">
        @foreach (['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $val => $label)
            <a href="{{ route($route, ['range' => $rangeKey, 'status' => $val ?: null]) }}" class="{{ (string) request('status') === $val ? 'is-on' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="bx-panel ag-scroll" style="padding:4px; margin-top:10px;">
        @if ($rows->isEmpty())
            <p class="bx-empty">Nothing here for this period.</p>
        @else
            <table class="ag-table">
                <thead><tr><th>Time</th><th>Player</th><th>Amount</th><th>{{ $isDep ? 'Method' : 'Account' }}</th><th>Status</th><th>Processed</th></tr></thead>
                <tbody>
                    @foreach ($rows as $r)
                        <tr>
                            <td class="ag-mut">{{ $r->created_at?->format('d M, h:i A') }}</td>
                            <td><b>{{ $r->user->name ?? '#' . $r->user_id }}</b><div class="ag-mut">{{ $r->user->email ?? '' }}</div></td>
                            <td><b>₹{{ number_format($r->amount, 2) }}</b></td>
                            <td class="ag-mut">{{ $isDep ? strtoupper((string) $r->method) : \Illuminate\Support\Str::limit((string) $r->account_details, 28) }}</td>
                            <td>
                                @if ($r->status === 'approved')
                                    <span class="bx-badge bx-badge-ok">{{ $isDep ? 'Approved' : 'Paid' }}</span>
                                @elseif ($r->status === 'rejected')
                                    <span class="bx-badge bx-badge-no">Rejected</span>
                                @else
                                    <span class="bx-badge bx-badge-wait">Pending</span>
                                @endif
                            </td>
                            <td class="ag-mut">{{ $r->processed_at?->format('d M, h:i A') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    @if ($rows->hasPages())
        <div style="margin-top:12px; display:flex; justify-content:space-between;">
            @if ($rows->previousPageUrl())<a class="bx-btn bx-btn-ghost bx-btn-sm" href="{{ $rows->previousPageUrl() }}">Newer</a>@else<span></span>@endif
            @if ($rows->nextPageUrl())<a class="bx-btn bx-btn-ghost bx-btn-sm" href="{{ $rows->nextPageUrl() }}">Older</a>@endif
        </div>
    @endif
@endsection
