<x-admin-layout title="User Management">

    <form method="GET" class="fx-card p-4 mb-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="fx-muted text-xs block mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, phone" class="fx-input text-sm">
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">Status</label>
            <select name="status" class="fx-input text-sm">
                <option value="">All</option>
                <option value="active" @selected(request('status')==='active')>Active</option>
                <option value="blocked" @selected(request('status')==='blocked')>Blocked</option>
            </select>
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">KYC Status</label>
            <select name="kyc_status" class="fx-input text-sm">
                <option value="">All</option>
                <option value="not_submitted" @selected(request('kyc_status')==='not_submitted')>Not Submitted</option>
                <option value="pending" @selected(request('kyc_status')==='pending')>Pending</option>
                <option value="approved" @selected(request('kyc_status')==='approved')>Approved</option>
                <option value="rejected" @selected(request('kyc_status')==='rejected')>Rejected</option>
            </select>
        </div>
        <button class="fx-btn text-sm">Filter</button>
        <a href="{{ route('admin.users.index') }}" class="fx-btn-outline text-sm">Reset</a>
    </form>

    <div class="fx-card overflow-x-auto">
        <table class="w-full fx-table">
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Email (login ID)</th><th>Wallet</th><th>Entries</th><th>KYC</th><th>Status</th><th>Joined</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td class="fx-muted">#{{ $user->id }}</td>
                        <td><a href="{{ route('admin.users.show', $user) }}" style="color: var(--fx-amber);">{{ $user->name }}</a></td>
                        <td class="fx-muted">{{ $user->email }}</td>
                        <td>₹{{ number_format(($user->wallet->deposit_balance ?? 0) + ($user->wallet->winnings_balance ?? 0) + ($user->wallet->bonus_balance ?? 0), 2) }}</td>
                        <td>{{ $user->contest_entries_count }}</td>
                        <td>
                            <span class="fx-badge" style="background-color: var(--fx-card-hover); color: {{ $user->kyc_status === 'approved' ? 'var(--fx-green)' : ($user->kyc_status === 'rejected' ? 'var(--fx-red)' : 'var(--fx-amber)') }};">
                                {{ ucfirst(str_replace('_',' ',$user->kyc_status)) }}
                            </span>
                        </td>
                        <td>
                            <span class="fx-badge" style="background-color: var(--fx-card-hover); color: {{ $user->is_blocked ? 'var(--fx-red)' : 'var(--fx-green)' }};">
                                {{ $user->is_blocked ? 'Blocked' : 'Active' }}
                            </span>
                        </td>
                        <td class="fx-muted">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="whitespace-nowrap">
                            <div class="flex flex-wrap gap-1">
                                <a href="{{ route('admin.users.show', $user) }}" class="fx-btn-outline text-xs" style="padding:.3rem .6rem;">View</a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="fx-btn-outline text-xs" style="padding:.3rem .6rem;">Edit</a>
                                <form method="POST" action="{{ route('admin.users.login-as', $user) }}" target="_blank" class="inline">
                                    @csrf
                                    <button class="fx-btn text-xs" style="padding:.3rem .6rem;">Login as</button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Delete {{ addslashes($user->email) }}? Their wallet, transactions and support chat will be removed. This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="fx-btn-danger text-xs" style="padding:.3rem .6rem;">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center fx-muted py-6">Koi user nahi mila.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</x-admin-layout>
