<x-admin-layout title="Login History">
    <div class="fx-card overflow-x-auto">
        <table class="w-full fx-table">
            <thead><tr><th>Type</th><th>Name</th><th>IP Address</th><th>Device</th><th>Time</th></tr></thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td><span class="fx-badge" style="background-color: var(--fx-card-hover);">{{ $log->admin_id ? 'Admin' : 'User' }}</span></td>
                        <td>{{ $log->admin->name ?? $log->user->name ?? '-' }}</td>
                        <td>{{ $log->ip_address }}</td>
                        <td class="fx-muted text-xs">{{ \Illuminate\Support\Str::limit($log->device, 50) }}</td>
                        <td class="fx-muted">{{ $log->login_at?->format('d M Y, h:i A') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center fx-muted py-6">Koi login history nahi mili.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
</x-admin-layout>
