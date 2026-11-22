<x-admin-layout title="Agent Profile">

    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
        <a href="{{ route('admin.agents.index') }}" class="fx-muted text-sm">&larr; All agents</a>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.agents.edit', $agent) }}" class="fx-btn-outline text-xs">Edit / set password</a>
            <form method="POST" action="{{ route('admin.agents.login-as', $agent) }}" target="_blank">
                @csrf
                <button class="fx-btn text-xs" style="padding:.4rem .8rem;">Login as agent</button>
            </form>
            <form method="POST" action="{{ route('admin.agents.destroy', $agent) }}" onsubmit="return confirm('Delete this agent? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button class="fx-btn-danger text-xs">Delete agent</button>
            </form>
        </div>
    </div>

    <div class="fx-card p-4 mb-4 flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-0">
            <p class="fx-heading text-sm">Referral link — code {{ $agent->referralCode() }}</p>
            <p class="fx-muted text-xs">Players who sign up with this link are added under {{ $agent->name }}. Share: {{ rtrim(rtrim(number_format($agent->commission_rate, 2), '0'), '.') }}% of their players' win/loss.</p>
        </div>
        <code class="fx-input text-sm" style="user-select:all;">{{ $agent->referralLink() }}</code>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="fx-card p-5">
            <p class="fx-muted text-xs uppercase">Downline Revenue</p>
            <p class="fx-heading text-2xl mt-1" style="color: var(--fx-amber);">₹{{ number_format($revenue, 2) }}</p>
        </div>
        <div class="fx-card p-5">
            <p class="fx-muted text-xs uppercase">Payouts</p>
            <p class="fx-heading text-2xl mt-1">₹{{ number_format($payouts, 2) }}</p>
        </div>
        <div class="fx-card p-5">
            <p class="fx-muted text-xs uppercase">Net Revenue</p>
            <p class="fx-heading text-2xl mt-1" style="color: {{ $netRevenue >= 0 ? 'var(--fx-green)' : 'var(--fx-red)' }};">₹{{ number_format($netRevenue, 2) }}</p>
        </div>
        <div class="fx-card p-5">
            <p class="fx-muted text-xs uppercase">Commission Earned ({{ $agent->commission_rate }}%)</p>
            <p class="fx-heading text-2xl mt-1" style="color: var(--fx-green);">₹{{ number_format($commissionEarned, 2) }}</p>
        </div>
    </div>

    <div class="fx-card p-5 mb-6">
        <p class="fx-heading text-sm mb-3">Agent Details — {{ $agent->name }} ({{ str_replace('_',' ', $agent->role) }})</p>
        <form method="POST" action="{{ route('admin.agents.update', $agent) }}" class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @csrf @method('PUT')
            <input type="text" name="name" value="{{ $agent->name }}" class="fx-input">
            <input type="text" name="phone" value="{{ $agent->phone }}" class="fx-input" placeholder="Phone">
            <input type="number" step="0.01" name="commission_rate" value="{{ $agent->commission_rate }}" class="fx-input" placeholder="Commission %">
            <input type="number" step="0.01" name="credit_limit" value="{{ $agent->credit_limit }}" class="fx-input" placeholder="Credit Limit">
            <button class="fx-btn text-sm">Save Changes</button>
        </form>
        <p class="fx-muted text-xs mt-3">Parent: {{ $agent->parent->name ?? '— Top level —' }} | Credit Available: ₹{{ number_format($agent->creditAvailable(), 2) }}</p>
    </div>

    @if($agent->children->isNotEmpty())
    <div class="fx-card p-5 mb-6 overflow-x-auto">
        <p class="fx-heading text-sm mb-3">Sub-Agents</p>
        <table class="w-full fx-table">
            <thead><tr><th>Name</th><th>Role</th><th>Commission %</th><th>Credit Limit</th></tr></thead>
            <tbody>
            @foreach($agent->children as $c)
                <tr>
                    <td><a href="{{ route('admin.agents.show', $c) }}" style="color: var(--fx-amber);">{{ $c->name }}</a></td>
                    <td>{{ str_replace('_',' ', $c->role) }}</td>
                    <td>{{ $c->commission_rate }}%</td>
                    <td>₹{{ number_format($c->credit_limit, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="fx-card p-5 overflow-x-auto">
        <div class="flex items-center justify-between mb-3">
            <p class="fx-heading text-sm">Downline Users</p>
            <form method="POST" action="{{ route('admin.agents.assign-user', $agent) }}" class="flex gap-2">
                @csrf
                <input type="number" name="user_id" placeholder="User ID assign karo" class="fx-input text-xs w-40" required>
                <button class="fx-btn-outline text-xs">Assign</button>
            </form>
        </div>
        <table class="w-full fx-table">
            <thead><tr><th>User</th><th>Email</th><th>Wallet</th><th>KYC</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($agent->users as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td class="fx-muted text-xs">{{ $u->email }}</td>
                        <td>₹{{ number_format($u->wallet_balance ?? 0, 2) }}</td>
                        <td>{{ ucfirst($u->kyc_status) }}</td>
                        <td>{{ $u->is_blocked ? 'Blocked' : 'Active' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center fx-muted py-6">Is agent ke under koi user nahi hai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-admin-layout>
