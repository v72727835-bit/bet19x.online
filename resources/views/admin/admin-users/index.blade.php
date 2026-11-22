<x-admin-layout title="Admins & Sub-admins">
    <div class="fx-card p-5 mb-6 max-w-3xl" x-data="{ type: '{{ old('type', 'sub_admin') }}' }">
        <p class="fx-heading text-base mb-1">Create admin</p>
        <p class="fx-muted text-xs mb-4">A sub-admin only sees and opens the sections you tick below.</p>
        <form method="POST" action="{{ route('admin.admin-users.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Name" class="fx-input text-sm" required>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email (login ID)" class="fx-input text-sm" required>
                <input type="text" name="password" placeholder="Password (min 8)" minlength="8" autocomplete="off" class="fx-input text-sm" required>
            </div>
            <div class="flex gap-4 text-sm">
                <label class="flex items-center gap-2"><input type="radio" name="type" value="sub_admin" x-model="type"> Sub-admin</label>
                <label class="flex items-center gap-2"><input type="radio" name="type" value="super_admin" x-model="type"> Super admin (full access)</label>
            </div>
            <div x-show="type === 'sub_admin'" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach (\App\Models\Admin::MODULES as $key => $label)
                    <label class="flex items-center gap-2 text-sm fx-card px-3 py-2" style="border-radius:.6rem;">
                        <input type="checkbox" name="permissions[]" value="{{ $key }}" @checked(in_array($key, old('permissions', [])))>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            <button class="fx-btn text-sm">Create admin</button>
        </form>
    </div>

    <div class="fx-card overflow-x-auto">
        <table class="w-full fx-table">
            <thead><tr><th>Name</th><th>Email</th><th>Type</th><th>Can open</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($admins as $a)
                    <tr x-data="{ edit: false }">
                        <td>{{ $a->name }}</td>
                        <td class="fx-muted">{{ $a->email }}</td>
                        <td><span class="fx-badge" style="background-color: var(--fx-card-hover); color: var(--fx-amber);">{{ $a->roleLabel() }}</span></td>
                        <td class="text-xs fx-muted" style="max-width:320px;">
                            {{ $a->isSuperAdmin() ? 'Everything' : implode(', ', array_map(fn ($m) => ucfirst($m), $a->modules())) }}
                            @unless($a->isSuperAdmin())
                                <form x-show="edit" x-cloak method="POST" action="{{ route('admin.admin-users.update', $a) }}" class="mt-2 space-y-2">
                                    @csrf
                                    @method('PUT')
                                    <div class="grid grid-cols-1 gap-1">
                                        @foreach (\App\Models\Admin::MODULES as $key => $label)
                                            <label class="flex items-center gap-2" style="color: var(--fx-text);">
                                                <input type="checkbox" name="permissions[]" value="{{ $key }}" @checked($a->hasPermission($key))> {{ $label }}
                                            </label>
                                        @endforeach
                                    </div>
                                    <input type="text" name="new_password" minlength="8" placeholder="New password (optional)" autocomplete="off" class="fx-input text-xs w-full">
                                    <button class="fx-btn text-xs">Save permissions</button>
                                </form>
                            @endunless
                        </td>
                        <td><span class="fx-badge" style="background-color: var(--fx-card-hover); color: {{ $a->is_active ? 'var(--fx-green)' : 'var(--fx-red)' }};">{{ $a->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="whitespace-nowrap space-x-1">
                            @unless($a->isSuperAdmin())
                                <button type="button" class="fx-btn-outline text-xs" @click="edit = !edit" x-text="edit ? 'Cancel' : 'Edit permissions'">Edit permissions</button>
                            @endunless
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
                    <tr><td colspan="6" class="text-center fx-muted py-6">Koi admin nahi mila.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $admins->links() }}</div>
</x-admin-layout>
