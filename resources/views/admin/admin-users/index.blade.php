<x-admin-layout title="Admin Users & Roles">
    <div class="fx-card p-5 mb-6 max-w-2xl">
        <p class="fx-heading text-sm mb-3">Create Admin User</p>
        <form method="POST" action="{{ route('admin.admin-users.store') }}" class="grid grid-cols-2 gap-3">
            @csrf
            <input type="text" name="name" placeholder="Name" class="fx-input text-sm" required>
            <input type="email" name="email" placeholder="Email" class="fx-input text-sm" required>
            <input type="password" name="password" placeholder="Password" class="fx-input text-sm" required>
            <select name="role" class="fx-input text-sm" required>
                <option value="support">Support</option>
                <option value="manager">Manager</option>
                <option value="super_admin">Super Admin</option>
            </select>
            <button class="fx-btn text-sm col-span-2">Create Admin</button>
        </form>
    </div>

    <div class="fx-card overflow-x-auto">
        <table class="w-full fx-table">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($admins as $a)
                    <tr>
                        <td>{{ $a->name }}</td>
                        <td class="fx-muted">{{ $a->email }}</td>
                        <td><span class="fx-badge" style="background-color: var(--fx-card-hover); color: var(--fx-amber);">{{ ucfirst(str_replace('_',' ', $a->role)) }}</span></td>
                        <td><span class="fx-badge" style="background-color: var(--fx-card-hover); color: {{ $a->is_active ? 'var(--fx-green)' : 'var(--fx-red)' }};">{{ $a->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="space-x-1">
                            <form method="POST" action="{{ route('admin.admin-users.toggle', $a) }}" class="inline">
                                @csrf
                                <button class="fx-btn-outline text-xs">{{ $a->is_active ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.admin-users.destroy', $a) }}" class="inline" onsubmit="return confirm('Delete this admin?');">
                                @csrf @method('DELETE')
                                <button class="fx-btn-danger text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center fx-muted py-6">Koi admin nahi mila.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $admins->links() }}</div>
</x-admin-layout>
