<x-admin-layout title="Menu Items">

    @if (session('status'))
        <div class="fx-card p-4 mb-6 text-sm" style="border-color: var(--fx-green); color: var(--fx-green);">{{ session('status') }}</div>
    @endif

    <div class="fx-card p-6 mb-6">
        <form method="POST" action="{{ route('admin.menu.store') }}" class="flex gap-3">
            @csrf
            <input type="text" name="label" placeholder="Label (e.g. Contests)" class="fx-input flex-1">
            <input type="text" name="url" placeholder="URL (e.g. /contests)" class="fx-input flex-1">
            <button type="submit" class="fx-btn text-sm">Add</button>
        </form>
    </div>

    <div class="fx-card divide-y" style="border-color: var(--fx-border);">
        @forelse ($menuItems as $item)
            <div class="flex items-center justify-between p-4" style="border-color: var(--fx-border);">
                <div>
                    <p class="text-sm font-medium" style="color: var(--fx-text);">{{ $item->label }}</p>
                    <p class="text-xs fx-muted">{{ $item->url }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <form method="POST" action="{{ route('admin.menu.toggle', $item) }}">
                        @csrf
                        <button type="submit" class="text-xs" style="color: {{ $item->is_active ? 'var(--fx-green)' : 'var(--fx-muted)' }};">
                            {{ $item->is_active ? 'Active' : 'Hidden' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.menu.destroy', $item) }}" onsubmit="return confirm('Delete this item?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs" style="color: var(--fx-red);">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="p-4 text-sm fx-muted">No menu items yet.</p>
        @endforelse
    </div>

    <a href="{{ route('admin.settings.edit') }}" class="inline-block mt-4 text-sm" style="color: var(--fx-amber);">&larr; Back to settings</a>

</x-admin-layout>
