<x-admin-layout title="Audit Logs">
    <form method="GET" class="mb-4">
        <select name="module" class="fx-input text-sm" onchange="this.form.submit()">
            <option value="">All Modules</option>
            @foreach($modules as $m)
                <option value="{{ $m }}" @selected(request('module') === $m)>{{ $m }}</option>
            @endforeach
        </select>
    </form>

    <div class="fx-card overflow-x-auto">
        <table class="w-full fx-table">
            <thead><tr><th>Admin</th><th>Module</th><th>Action</th><th>Description</th><th>IP</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->admin->name ?? 'System' }}</td>
                        <td class="fx-muted">{{ $log->module }}</td>
                        <td>{{ $log->action }}</td>
                        <td class="fx-muted text-xs">{{ $log->description }}</td>
                        <td class="fx-muted text-xs">{{ $log->ip_address }}</td>
                        <td class="fx-muted">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center fx-muted py-6">Koi audit log nahi mila.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
</x-admin-layout>
