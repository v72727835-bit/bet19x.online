<x-admin-layout>
    <div style="background-color:#0F1023;" class="min-h-screen py-8">
        <div class="max-w-5xl mx-auto px-4 space-y-6">
            <h2 style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-2xl">Sport Categories</h2>

            @if (session('status'))
                <div style="background:#1B2A1E; border:1px solid #2E5C33; color:#8CD98F;" class="rounded-lg px-4 py-3 text-sm">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div style="background:#2A1B1B; border:1px solid #5C2E2E; color:#D98C8C;" class="rounded-lg px-4 py-3 text-sm">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.sport-categories.store') }}" style="border:1px solid #24254A;" class="rounded-2xl p-5 grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                @csrf
                <div>
                    <label style="color:#8A8CB3;" class="block text-xs mb-1">Name</label>
                    <input type="text" name="name" required style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label style="color:#8A8CB3;" class="block text-xs mb-1">Icon (emoji)</label>
                    <input type="text" name="icon" placeholder="🏏" style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label style="color:#8A8CB3;" class="block text-xs mb-1">Link URL (optional)</label>
                    <input type="text" name="link_url" style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm">
                </div>
                <button type="submit" style="background-color:#FFB627; color:#14152B; font-weight:700;" class="rounded-lg px-4 py-2 text-sm hover:opacity-90">Add Category</button>
            </form>

            <div style="border:1px solid #24254A;" class="rounded-2xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead style="background:#14152B; color:#8A8CB3;">
                        <tr>
                            <th class="text-left px-4 py-3">Icon</th>
                            <th class="text-left px-4 py-3">Name</th>
                            <th class="text-left px-4 py-3">Status</th>
                            <th class="text-right px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $cat)
                            <tr style="border-top:1px solid #24254A; color:#F4F3FA;">
                                <td class="px-4 py-3 text-xl">{{ $cat->icon }}</td>
                                <td class="px-4 py-3">{{ $cat->name }}</td>
                                <td class="px-4 py-3">
                                    <span style="color: {{ $cat->is_active ? '#8CD98F' : '#D98C8C' }};">{{ $cat->is_active ? 'Active' : 'Hidden' }}</span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <form method="POST" action="{{ route('admin.sport-categories.toggle', $cat) }}" class="inline">
                                        @csrf
                                        <button style="color:#FFB627;" class="hover:underline">{{ $cat->is_active ? 'Hide' : 'Show' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.sport-categories.destroy', $cat) }}" class="inline" onsubmit="return confirm('Delete karein?')">
                                        @csrf
                                        @method('DELETE')
                                        <button style="color:#FF6B4A;" class="hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
