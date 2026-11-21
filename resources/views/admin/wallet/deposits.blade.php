<x-admin-layout title="Deposit Requests">
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
            <thead><tr><th>User</th><th>Amount</th><th>Method</th><th>Status</th><th>Requested</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($deposits as $d)
                    <tr>
                        <td>{{ $d->user->name ?? '-' }}</td>
                        <td>₹{{ number_format($d->amount, 2) }}</td>
                        <td class="fx-muted">{{ $d->method }}</td>
                        <td><span class="fx-badge" style="background-color: var(--fx-card-hover); color: {{ $d->status === 'approved' ? 'var(--fx-green)' : ($d->status === 'rejected' ? 'var(--fx-red)' : 'var(--fx-amber)') }};">{{ ucfirst($d->status) }}</span></td>
                        <td class="fx-muted">{{ $d->created_at->diffForHumans() }}</td>
                        <td class="space-x-1 whitespace-nowrap">
                            @if($d->status === 'pending')
                                <form method="POST" action="{{ route('admin.wallet.deposits.approve', $d) }}" class="inline">
                                    @csrf
                                    <button class="fx-btn-success text-xs">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.wallet.deposits.reject', $d) }}" class="inline" onsubmit="return confirm('Reject this deposit?');">
                                    @csrf
                                    <input type="hidden" name="admin_remarks" value="Rejected by admin">
                                    <button class="fx-btn-danger text-xs">Reject</button>
                                </form>
                            @else
                                <span class="fx-muted text-xs">{{ $d->admin_remarks }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center fx-muted py-6">Koi deposit request nahi mili.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $deposits->links() }}</div>
</x-admin-layout>
