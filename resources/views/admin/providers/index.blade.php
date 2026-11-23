<x-admin-layout title="Provider order">
    @unless($ready)
        <div class="fx-card p-4 mb-4" style="border-color: var(--fx-amber);">
            <p class="text-sm">Ye setting save karne ke liye server par ek baar <code>php artisan migrate --force</code> chalana hoga.</p>
        </div>
    @endunless

    <form method="POST" action="{{ route('admin.providers.update') }}">
        @csrf
        <div class="fx-card p-4 mb-4 flex flex-wrap items-center gap-3">
            <p class="fx-muted text-sm flex-1">Jis provider ko home page par sabse aage dikhana hai, uske aage <b>1</b> likhiye, agle ke aage <b>2</b>, aur aise hi. Khaali chhode providers baad mein, games ki ginti ke hisaab se dikhenge.</p>
            <button class="fx-btn text-sm">Save order</button>
            <button class="fx-btn-outline text-sm" name="clear" value="1" onclick="return confirm('Remove all pinned positions?');">Clear all</button>
        </div>

        <div class="fx-card overflow-x-auto">
            <table class="w-full fx-table">
                <thead><tr><th style="width:110px;">Position</th><th>Provider</th><th>Games</th><th>Now shown at</th></tr></thead>
                <tbody>
                    @forelse($providers as $key => $p)
                        <tr>
                            <td><input type="number" min="1" max="999" name="position[{{ $key }}]" value="{{ $order[$key] ?? '' }}" class="fx-input w-20 text-sm" placeholder="—"></td>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if($p['logo'])
                                        <img src="{{ $p['logo'] }}" alt="" style="height:28px; width:56px; object-fit:contain; background:#1a0911; border-radius:6px;">
                                    @endif
                                    <span>{{ $p['name'] }} <span class="fx-muted text-xs">{{ $key }}</span></span>
                                    @if(isset($order[$key]))<span class="fx-badge" style="background: var(--fx-card-hover); color: var(--fx-amber);">Pinned</span>@endif
                                </div>
                            </td>
                            <td>{{ number_format($p['count']) }}</td>
                            <td class="fx-muted">#{{ $loop->iteration }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center fx-muted py-6">Providers load nahi hue (games API check karein).</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4"><button class="fx-btn text-sm">Save order</button></div>
    </form>
</x-admin-layout>
