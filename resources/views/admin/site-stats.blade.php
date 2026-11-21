<x-admin-layout>
    <div style="background-color:#0F1023;" class="min-h-screen py-8">
        <div class="max-w-5xl mx-auto px-4 space-y-6">
            <h2 style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-2xl">Site Stats</h2>

            @if (session('status'))
                <div style="background:#1B2A1E; border:1px solid #2E5C33; color:#8CD98F;" class="rounded-lg px-4 py-3 text-sm">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div style="background:#2A1B1B; border:1px solid #5C2E2E; color:#D98C8C;" class="rounded-lg px-4 py-3 text-sm">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.site-stats.store') }}" style="border:1px solid #24254A;" class="rounded-2xl p-5 grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                @csrf
                <div>
                    <label style="color:#8A8CB3;" class="block text-xs mb-1">Icon (emoji)</label>
                    <input type="text" name="icon" placeholder="🏆" style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label style="color:#8A8CB3;" class="block text-xs mb-1">Value</label>
                    <input type="text" name="value" required placeholder="10L+" style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label style="color:#8A8CB3;" class="block text-xs mb-1">Label</label>
                    <input type="text" name="label" required placeholder="Active Players" style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-3">
                    <button type="submit" style="background-color:#FFB627; color:#14152B; font-weight:700;" class="rounded-lg px-4 py-2 text-sm hover:opacity-90">Add Stat</button>
                </div>
            </form>

            <div style="border:1px solid #24254A;" class="rounded-2xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead style="background:#14152B; color:#8A8CB3;">
                        <tr>
                            <th class="text-left px-4 py-3">Icon</th>
                            <th class="text-left px-4 py-3">Value</th>
                            <th class="text-left px-4 py-3">Label</th>
                            <th class="text-left px-4 py-3">Status</th>
                            <th class="text-right px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stats as $s)
                            <tr style="border-top:1px solid #24254A; color:#F4F3FA;">
                                <td class="px-4 py-3 text-xl">{{ $s->icon }}</td>
                                <td class="px-4 py-3">{{ $s->value }}</td>
                                <td class="px-4 py-3">{{ $s->label }}</td>
                                <td class="px-4 py-3">
                                    <span style="color: {{ $s->is_active ? '#8CD98F' : '#D98C8C' }};">{{ $s->is_active ? 'Active' : 'Hidden' }}</span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <form method="POST" action="{{ route('admin.site-stats.toggle', $s) }}" class="inline">
                                        @csrf
                                        <button style="color:#FFB627;" class="hover:underline">{{ $s->is_active ? 'Hide' : 'Show' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.site-stats.destroy', $s) }}" class="inline" onsubmit="return confirm('Delete karein?')">
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
