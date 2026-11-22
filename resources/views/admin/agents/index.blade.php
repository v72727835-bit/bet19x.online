<x-admin-layout title="Agent Management">

    <div class="fx-card p-4 mb-4 flex flex-wrap items-center gap-3" x-data="{ copied: false }">
        <div class="flex-1 min-w-0">
            <p class="fx-heading text-sm">Agent login link</p>
            <p class="fx-muted text-xs">Master agents, agents and sub-agents log in here with the email and password you set for them.</p>
        </div>
        <code class="fx-input text-sm" style="user-select:all;">{{ route('agent.login') }}</code>
        <button type="button" class="fx-btn-outline text-xs" @click="navigator.clipboard.writeText(@js(route('agent.login'))); copied = true; setTimeout(() => copied = false, 1500)" x-text="copied ? 'Copied' : 'Copy link'">Copy link</button>
    </div>

    <div class="fx-card p-5 mb-6">
        <p class="fx-heading text-sm mb-3">Naya Agent Banao</p>
        <form method="POST" action="{{ route('admin.agents.store') }}" class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @csrf
            <input type="text" name="name" placeholder="Name" class="fx-input" required>
            <input type="email" name="email" placeholder="Email" class="fx-input" required>
            <input type="text" name="phone" placeholder="Phone" class="fx-input">
            <input type="password" name="password" placeholder="Password" class="fx-input" required>
            <select name="role" class="fx-input" required>
                <option value="master_agent">Master Agent</option>
                <option value="agent">Agent</option>
                <option value="sub_agent">Sub-Agent</option>
            </select>
            <select name="parent_id" class="fx-input">
                <option value="">-- No Parent (top-level) --</option>
                @foreach($allAgents as $a)
                    <option value="{{ $a->id }}">{{ $a->name }} ({{ $a->roleLabel() }})</option>
                @endforeach
            </select>
            <input type="number" step="0.01" name="commission_rate" placeholder="Commission %" class="fx-input" required>
            <input type="number" step="0.01" name="credit_limit" placeholder="Credit Limit ₹" class="fx-input" required>
            <button class="fx-btn text-sm">+ Create Agent</button>
        </form>
    </div>

    <div class="fx-card p-5 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name/email..." class="fx-input">
            <select name="role" class="fx-input">
                <option value="">Sab Roles</option>
                <option value="master_agent" {{ request('role')==='master_agent'?'selected':'' }}>Master Agent</option>
                <option value="agent" {{ request('role')==='agent'?'selected':'' }}>Agent</option>
                <option value="sub_agent" {{ request('role')==='sub_agent'?'selected':'' }}>Sub-Agent</option>
            </select>
            <button class="fx-btn-outline text-sm">Filter</button>
        </form>
    </div>

    <div class="fx-card p-5 overflow-x-auto">
        <table class="w-full fx-table">
            <thead>
                <tr><th>Name</th><th>Role</th><th>Parent</th><th>Users</th><th>Commission %</th><th>Credit Limit</th><th>Credit Used</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($agents as $a)
                    <tr>
                        <td><a href="{{ route('admin.agents.show', $a) }}" style="color: var(--fx-amber);">{{ $a->name }}</a><br><span class="fx-muted text-xs">{{ $a->email }}</span></td>
                        <td><span class="fx-badge" style="background-color: var(--fx-card-hover); color: var(--fx-amber);">{{ $a->roleLabel() }}</span></td>
                        <td class="fx-muted text-xs">{{ $a->parent->name ?? '—' }}</td>
                        <td>{{ $a->users_count }}</td>
                        <td>{{ $a->commission_rate }}%</td>
                        <td>₹{{ number_format($a->credit_limit, 2) }}</td>
                        <td>₹{{ number_format($a->credit_used, 2) }}</td>
                        <td>
                            <span class="fx-badge" style="background-color: {{ $a->is_active ? 'rgba(52,211,153,.15)' : 'rgba(248,113,113,.15)' }}; color: {{ $a->is_active ? 'var(--fx-green)' : 'var(--fx-red)' }};">{{ $a->is_active ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                <a href="{{ route('admin.agents.show', $a) }}" class="fx-btn-outline text-xs" style="padding:.3rem .6rem;">View</a>
                                <a href="{{ route('admin.agents.edit', $a) }}" class="fx-btn-outline text-xs" style="padding:.3rem .6rem;">Edit</a>
                                <form method="POST" action="{{ route('admin.agents.login-as', $a) }}" target="_blank">
                                    @csrf
                                    <button class="fx-btn text-xs" style="padding:.3rem .6rem;">Login as</button>
                                </form>
                                <form method="POST" action="{{ route('admin.agents.toggle', $a) }}">
                                    @csrf
                                    <button class="fx-btn-outline text-xs" style="padding:.3rem .6rem;">{{ $a->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.agents.destroy', $a) }}" onsubmit="return confirm('Delete agent {{ addslashes($a->name) }}? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="fx-btn-danger text-xs" style="padding:.3rem .6rem;">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center fx-muted py-6">Koi agent nahi mila. Upar se create karo.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $agents->links() }}</div>
    </div>

</x-admin-layout>
