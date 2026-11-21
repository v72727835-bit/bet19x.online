<x-admin-layout>
    <x-slot name="header">
        <h2 style="color:#F4F3FA; font-family:'Rajdhani',sans-serif; font-weight:700;" class="text-2xl">Hero Banners</h2>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 p-3 rounded-lg text-sm" style="background-color:#16332A; color:#8FE3B0;">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 p-3 rounded-lg text-sm" style="background-color:#3A1622; color:#FF9B9B;">{{ $errors->first() }}</div>
    @endif

    <div style="background-color:#1D1E3D; border:1px solid #24254A;" class="rounded-xl p-6 mb-6">
        <h3 style="color:#F4F3FA;" class="text-sm font-semibold mb-4">Naya banner add karein</h3>
        <form method="POST" action="{{ route('admin.hero-banners.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label style="color:#8A8CB3;" class="block text-xs mb-1.5">Title (optional)</label>
                    <input type="text" name="title" style="background-color:#14152B; color:#F4F3FA; border-color:#24254A;" class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:border-[#FFB627]">
                </div>
                <div>
                    <label style="color:#8A8CB3;" class="block text-xs mb-1.5">Link URL (optional)</label>
                    <input type="text" name="link_url" style="background-color:#14152B; color:#F4F3FA; border-color:#24254A;" class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:border-[#FFB627]">
                </div>
            </div>
            <div>
                <label style="color:#8A8CB3;" class="block text-xs mb-1.5">Image upload (ya neeche URL daalein)</label>
                <input type="file" name="image" style="color:#8A8CB3;" class="w-full text-sm">
            </div>
            <div>
                <label style="color:#8A8CB3;" class="block text-xs mb-1.5">— YA — External Image URL</label>
                <input type="text" name="image_url" placeholder="https://..." style="background-color:#14152B; color:#F4F3FA; border-color:#24254A;" class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:border-[#FFB627]">
            </div>
            <button type="submit" style="background-color:#FFB627; color:#14152B; font-family:'Rajdhani',sans-serif; font-weight:700;" class="rounded-lg px-5 py-2.5 text-sm hover:opacity-90 transition">Add Banner</button>
        </form>
    </div>

    <div class="space-y-3">
        @forelse ($banners as $banner)
            <div style="background-color:#1D1E3D; border:1px solid #24254A;" class="rounded-xl p-4 flex items-center gap-4">
                <img src="{{ str_starts_with($banner->image_path, 'http') ? $banner->image_path : asset('storage/' . $banner->image_path) }}" class="h-16 w-28 object-cover rounded-lg shrink-0">
                <div class="flex-1 min-w-0">
                    <p style="color:#F4F3FA;" class="text-sm font-medium truncate">{{ $banner->title ?? 'Untitled' }}</p>
                    <p style="color:#8A8CB3;" class="text-xs truncate">{{ $banner->link_url ?? 'No link' }}</p>
                </div>
                <form method="POST" action="{{ route('admin.hero-banners.toggle', $banner) }}">
                    @csrf
                    <button type="submit" class="text-xs" style="color:{{ $banner->is_active ? '#8FE3B0' : '#8A8CB3' }};">
                        {{ $banner->is_active ? 'Active' : 'Hidden' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.hero-banners.destroy', $banner) }}" onsubmit="return confirm('Delete karein?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="color:#FF6B4A;" class="text-xs">Delete</button>
                </form>
            </div>
        @empty
            <p style="color:#8A8CB3;" class="text-sm">Koi banner nahi hai.</p>
        @endforelse
    </div>
</x-admin-layout>
