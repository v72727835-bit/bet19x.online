<x-admin-layout title="Blocked IPs">
    <div class="fx-card p-5 mb-6 max-w-xl">
        <p class="fx-heading text-sm mb-3">Block New IP</p>
        <form method="POST" action="{{ route('admin.security.blocked-ips.store') }}" class="flex gap-2">
            @csrf
            <input type="text" name="ip_address" placeholder="IP Address" class="fx-input text-sm flex-1" required>
            <input type="text" name="reason" placeholder="Reason (optional)" class="fx-input text-sm flex-1">
            <button class="fx-btn text-sm">Block</button>
        </form>
    </div>

    <div class="fx-card overflow-x-auto">
        <table class="w-full fx-table">
            <thead><tr><th>IP Address</th><th>Reason</th><th>Blocked By</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($ips as $ip)
                    <tr>
                        <td class="font-mono">{{ $ip->ip_address }}</td>
                        <td class="fx-muted">{{ $ip->reason ?? '-' }}</td>
                        <td class="fx-muted">{{ $ip->blockedBy->name ?? '-' }}</td>
                        <td class="fx-muted">{{ $ip->created_at->format('d M Y') }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.security.blocked-ips.destroy', $ip) }}" onsubmit="return confirm('Unblock this IP?');">
                                @csrf @method('DELETE')
                                <button class="fx-btn-danger text-xs">Unblock</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center fx-muted py-6">Koi IP block nahi hai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $ips->links() }}</div>
</x-admin-layout>
