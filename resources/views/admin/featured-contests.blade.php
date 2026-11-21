<x-admin-layout>
    <div style="background-color:#0F1023;" class="min-h-screen py-8">
        <div class="max-w-5xl mx-auto px-4 space-y-6">
            <h2 style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-2xl">Featured Contests</h2>

            @if (session('status'))
                <div style="background:#1B2A1E; border:1px solid #2E5C33; color:#8CD98F;" class="rounded-lg px-4 py-3 text-sm">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div style="background:#2A1B1B; border:1px solid #5C2E2E; color:#D98C8C;" class="rounded-lg px-4 py-3 text-sm">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.featured-contests.store') }}" enctype="multipart/form-data" style="border:1px solid #24254A;" class="rounded-2xl p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label style="color:#8A8CB3;" class="block text-xs mb-1">Title</label>
                    <input type="text" name="title" required style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label style="color:#8A8CB3;" class="block text-xs mb-1">Subtitle</label>
                    <input type="text" name="subtitle" style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label style="color:#8A8CB3;" class="block text-xs mb-1">Prize Text</label>
                    <input type="text" name="prize_text" placeholder="Prize Pool ₹1,00,000" style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label style="color:#8A8CB3;" class="block text-xs mb-1">Link URL (optional)</label>
                    <input type="text" name="link_url" style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label style="color:#8A8CB3;" class="block text-xs mb-1">Image Upload</label>
                    <input type="file" name="image" style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label style="color:#8A8CB3;" class="block text-xs mb-1">Ya Image URL</label>
                    <input type="text" name="image_url" style="background:#14152B; border:1px solid #24254A; color:#F4F3FA;" class="w-full rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-2">
                    <button type="submit" style="background-color:#FFB627; color:#14152B; font-weight:700;" class="rounded-lg px-4 py-2 text-sm hover:opacity-90">Add Contest</button>
                </div>
            </form>

            <div style="border:1px solid #24254A;" class="rounded-2xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead style="background:#14152B; color:#8A8CB3;">
                        <tr>
                            <th class="text-left px-4 py-3">Image</th>
                            <th class="text-left px-4 py-3">Title</th>
                            <th class="text-left px-4 py-3">Status</th>
                            <th class="text-right px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($contests as $c)
                            <tr style="border-top:1px solid #24254A; color:#F4F3FA;">
                                <td class="px-4 py-3"><img src="{{ $c->image_url }}" style="width:70px; height:44px; object-fit:cover; border-radius:8px;"></td>
                                <td class="px-4 py-3">{{ $c->title }}<br><span style="color:#8A8CB3;" class="text-xs">{{ $c->subtitle }}</span></td>
                                <td class="px-4 py-3">
                                    <span style="color: {{ $c->is_active ? '#8CD98F' : '#D98C8C' }};">{{ $c->is_active ? 'Active' : 'Hidden' }}</span>
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <form method="POST" action="{{ route('admin.featured-contests.toggle', $c) }}" class="inline">
                                        @csrf
                                        <button style="color:#FFB627;" class="hover:underline">{{ $c->is_active ? 'Hide' : 'Show' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.featured-contests.destroy', $c) }}" class="inline" onsubmit="return confirm('Delete karein?')">
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
