<x-admin-layout title="CMS Pages">
    <div class="fx-card p-5 mb-6 max-w-3xl">
        <p class="fx-heading text-sm mb-3">Create Page / News / Rule</p>
        <form method="POST" action="{{ route('admin.cms-pages.store') }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <input type="text" name="title" placeholder="Title" class="fx-input text-sm" required>
                <select name="type" class="fx-input text-sm">
                    <option value="page">Page</option>
                    <option value="news">News</option>
                    <option value="rule">Rules & Regulations</option>
                </select>
            </div>
            <textarea name="content" placeholder="Content" rows="5" class="fx-input text-sm w-full"></textarea>
            <button class="fx-btn text-sm">Create</button>
        </form>
    </div>

    <form method="GET" class="mb-4">
        <select name="type" class="fx-input text-sm" onchange="this.form.submit()">
            <option value="">All Types</option>
            <option value="page" @selected(request('type')==='page')>Pages</option>
            <option value="news" @selected(request('type')==='news')>News</option>
            <option value="rule" @selected(request('type')==='rule')>Rules</option>
        </select>
    </form>

    <div class="fx-card overflow-x-auto">
        <table class="w-full fx-table">
            <thead><tr><th>Title</th><th>Type</th><th>Slug</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($pages as $p)
                    <tr>
                        <td>{{ $p->title }}</td>
                        <td class="fx-muted">{{ ucfirst($p->type) }}</td>
                        <td class="fx-muted text-xs">{{ $p->slug }}</td>
                        <td><span class="fx-badge" style="background-color: var(--fx-card-hover); color: {{ $p->is_active ? 'var(--fx-green)' : 'var(--fx-red)' }};">{{ $p->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="space-x-1">
                            <form method="POST" action="{{ route('admin.cms-pages.toggle', $p) }}" class="inline">
                                @csrf
                                <button class="fx-btn-outline text-xs">{{ $p->is_active ? 'Disable' : 'Enable' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.cms-pages.destroy', $p) }}" class="inline" onsubmit="return confirm('Delete?');">
                                @csrf @method('DELETE')
                                <button class="fx-btn-danger text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center fx-muted py-6">Koi page nahi mila.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $pages->links() }}</div>
</x-admin-layout>
