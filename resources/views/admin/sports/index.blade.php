<x-admin-layout title="Sports">
    <div class="fx-card p-5 mb-4 max-w-xl">
        <p class="fx-heading text-sm mb-3">Add Sport</p>
        <form method="POST" action="{{ route('admin.sports.store') }}" class="flex gap-2">
            @csrf
            <input type="text" name="name" placeholder="Name (e.g. Cricket)" class="fx-input text-sm flex-1" required>
            <input type="text" name="slug" placeholder="slug (e.g. cricket)" class="fx-input text-sm flex-1" required>
            <input type="text" name="icon" placeholder="Icon (optional)" class="fx-input text-sm w-28">
            <button class="fx-btn text-sm">Add</button>
        </form>
    </div>

    <div class="fx-card overflow-x-auto">
        <table class="w-full fx-table">
            <thead><tr><th>Name</th><th>Slug</th><th>Matches</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($sports as $sport)
                    <tr>
                        <td>{{ $sport->name }}</td>
                        <td class="fx-muted">{{ $sport->slug }}</td>
                        <td>{{ $sport->match_events_count }}</td>
                        <td>
                            <span class="fx-badge" style="background-color: var(--fx-card-hover); color: {{ $sport->is_active ? 'var(--fx-green)' : 'var(--fx-red)' }};">
                                {{ $sport->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="space-x-1">
                            <form method="POST" action="{{ route('admin.sports.toggle', $sport) }}" class="inline">
                                @csrf
                                <button class="fx-btn-outline text-xs">{{ $sport->is_active ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.sports.destroy', $sport) }}" class="inline" onsubmit="return confirm('Delete?');">
                                @csrf @method('DELETE')
                                <button class="fx-btn-danger text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center fx-muted py-6">Koi sport nahi mila.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
