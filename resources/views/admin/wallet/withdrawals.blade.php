<x-admin-layout title="Withdrawal Requests">
    <form method="GET" class="mb-4">
        <select name="status" class="fx-input text-sm" onchange="this.form.submit()">
            <option value="">All</option>
            <option value="pending" @selected(request('status')==='pending')>Pending</option>
            <option value="approved" @selected(request('status')==='approved')>Approved</option>
            <option value="rejected" @selected(request('status')==='rejected')>Rejected</option>
        </select>
    </form>

    <div class="fx-card overflow-x-auto">
        <table class="w-full fx-table">
            <thead><tr><th>User</th><th>Amount</th><th>Account Details</th><th>Status</th><th>Requested</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($withdrawals as $w)
                    <tr>
                        <td>{{ $w->user->name ?? '-' }}</td>
                        <td>₹{{ number_format($w->amount, 2) }}</td>
                        <td class="fx-muted text-xs">{{ $w->account_details }}</td>
                        <td><span class="fx-badge" style="background-color: var(--fx-card-hover); color: {{ $w->status === 'approved' ? 'var(--fx-green)' : ($w->status === 'rejected' ? 'var(--fx-red)' : 'var(--fx-amber)') }};">{{ ucfirst($w->status) }}</span></td>
                        <td class="fx-muted">{{ $w->created_at->diffForHumans() }}</td>
                        <td class="space-x-1 whitespace-nowrap">
                            @if($w->status === 'pending')
                                <form method="POST" action="{{ route('admin.wallet.withdrawals.approve', $w) }}" class="inline">
                                    @csrf
                                    <button class="fx-btn-success text-xs">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.wallet.withdrawals.reject', $w) }}" class="inline" onsubmit="return confirm('Reject this withdrawal?');">
                                    @csrf
                                    <input type="hidden" name="admin_remarks" value="Rejected by admin">
                                    <button class="fx-btn-danger text-xs">Reject</button>
                                </form>
                            @else
                                <span class="fx-muted text-xs">{{ $w->admin_remarks }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center fx-muted py-6">Koi withdrawal request nahi mili.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $withdrawals->links() }}</div>
</x-admin-layout>
