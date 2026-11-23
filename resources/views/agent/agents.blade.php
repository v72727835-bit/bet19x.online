@extends('agent.layout')
@section('title', 'My agents')
@section('content')
    @php
        $money = fn ($v, $dec = 0) => ($v < 0 ? '−' : '') . '₹' . number_format(abs($v), $dec);
    @endphp
    <div class="bx-pagehead"><h1 class="bx-h1">My agents</h1><p class="bx-sub">Distributors and retailers under you, with their players' result for the selected period.</p></div>

    @if ($agent->canCreateAgents())
        @php
            $myPct = (float) $agent->commission_rate;
        @endphp
        <section class="bx-section" style="margin-top:12px;" x-data="{ open: {{ $errors->any() ? 'true' : 'false' }} }">
            <div class="bx-panel">
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    <div class="bx-grow">
                        <b style="font-size:16px;">Create an agent under you</b>
                        <small style="color:var(--bx-muted);">You can give up to your own {{ rtrim(rtrim(number_format($myPct, 2), '0'), '.') }}% commission, and credit from your available ₹{{ number_format(max(0, $agent->creditAvailable()), 0) }}.</small>
                    </div>
                    <button type="button" class="bx-btn bx-btn-gold bx-btn-sm" @click="open = !open" x-text="open ? 'Cancel' : 'New agent'">New agent</button>
                </div>
                <form method="POST" action="{{ route('agent.agents.store') }}" x-show="open" style="display:none; margin-top:14px;">
                    @csrf
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:10px;">
                        <label class="bx-field"><span>Name</span><input class="bx-input" name="name" value="{{ old('name') }}" required></label>
                        <label class="bx-field"><span>Email (login ID)</span><input class="bx-input" type="email" name="email" value="{{ old('email') }}" required></label>
                        <label class="bx-field"><span>Phone</span><input class="bx-input" name="phone" value="{{ old('phone') }}"></label>
                        <label class="bx-field"><span>Password</span><input class="bx-input" type="text" name="password" minlength="6" autocomplete="off" required></label>
                        <label class="bx-field"><span>Type</span>
                            <select class="bx-input" name="role">
                                @foreach ($agent->allowedChildRoles() as $r)
                                    <option value="{{ $r }}" @selected(old('role') === $r)>{{ \App\Models\Agent::ROLE_LABELS[$r] }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="bx-field"><span>Commission % (max {{ rtrim(rtrim(number_format($myPct, 2), '0'), '.') }})</span><input class="bx-input" type="number" step="0.01" min="0" max="{{ $myPct }}" name="commission_rate" value="{{ old('commission_rate') }}" required></label>
                        <label class="bx-field"><span>Credit limit (₹, optional)</span><input class="bx-input" type="number" step="0.01" min="0" max="{{ max(0, $agent->creditAvailable()) }}" name="credit_limit" value="{{ old('credit_limit') }}"></label>
                    </div>
                    <button type="submit" class="bx-btn bx-btn-gold">Create agent</button>
                    <p class="bx-sub" style="margin-top:8px;">They log in at {{ route('agent.login') }} with this email and password.</p>
                </form>
            </div>
        </section>
    @endif

    <div class="bx-panel ag-scroll" style="padding:4px; margin-top:12px;">
        @if ($subAgents->isEmpty())
            <p class="bx-empty">No agents under you yet.@if ($agent->canCreateAgents()) Create one with the button above.@endif</p>
        @else
            <table class="ag-table">
                <thead><tr><th>Name</th><th>Type</th><th>Under</th><th>Players</th><th>Their %</th><th>Players' loss</th><th>Credit limit</th><th>Status</th><th></th></tr></thead>
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
                            <td>
                                @if ((int) $s->parent_id === (int) $agent->id)
                                    <div x-data="{ edit: false }">
                                        <div style="display:flex; gap:6px;">
                                            <button type="button" class="bx-btn bx-btn-ghost bx-btn-sm" @click="edit = !edit" x-text="edit ? 'Cancel' : 'Edit'">Edit</button>
                                            <form method="POST" action="{{ route('agent.agents.toggle', $s) }}" style="margin:0;">
                                                @csrf
                                                <button class="bx-btn bx-btn-ghost bx-btn-sm">{{ $s->is_active ? 'Deactivate' : 'Activate' }}</button>
                                            </form>
                                        </div>
                                        <form method="POST" action="{{ route('agent.agents.update', $s) }}" x-show="edit" style="display:none; margin-top:8px; white-space:normal; min-width:240px;">
                                            @csrf
                                            <label class="bx-field" style="margin-bottom:8px;"><span>Commission % (max {{ (float) $agent->commission_rate }})</span><input class="bx-input" type="number" step="0.01" min="0" max="{{ (float) $agent->commission_rate }}" name="commission_rate" value="{{ (float) $s->commission_rate }}" required></label>
                                            <label class="bx-field" style="margin-bottom:8px;"><span>Credit limit (₹)</span><input class="bx-input" type="number" step="0.01" min="0" name="credit_limit" value="{{ (float) $s->credit_limit }}"></label>
                                            <label class="bx-field" style="margin-bottom:8px;"><span>New password (optional)</span><input class="bx-input" type="text" name="new_password" minlength="6" autocomplete="off"></label>
                                            <button class="bx-btn bx-btn-gold bx-btn-sm">Save</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="ag-mut">Managed by {{ $s->parent->name ?? '—' }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
