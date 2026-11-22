<x-admin-layout title="Live Support">
    <form method="GET" class="mb-4 flex gap-2">
        <select name="status" class="fx-input text-sm" onchange="this.form.submit()">
            <option value="">All chats</option>
            <option value="open" @selected(request('status')==='open')>Open</option>
            <option value="closed" @selected(request('status')==='closed')>Closed</option>
        </select>
    </form>

    <div class="fx-card overflow-x-auto">
        <table class="w-full fx-table">
            <thead><tr><th>Player</th><th>Last message</th><th>Updated</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($conversations as $c)
                    @php($last = $previews[$c->id] ?? null)
                    <tr>
                        <td>
                            {{ $c->user->name ?? 'Deleted user' }}
                            <div class="fx-muted text-xs">#{{ $c->user_id }} {{ $c->user->email ?? '' }}</div>
                        </td>
                        <td class="text-sm" style="max-width:380px;">
                            @if($last)
                                <span class="fx-muted">{{ $last->sender === 'admin' ? 'You:' : '' }}</span> {{ \Illuminate\Support\Str::limit($last->body, 90) }}
                            @endif
                        </td>
                        <td class="fx-muted whitespace-nowrap">{{ $c->last_message_at?->diffForHumans() }}</td>
                        <td class="whitespace-nowrap">
                            @if($c->admin_unread > 0)
                                <span class="fx-badge" style="background: var(--fx-coral); color:#fff;">{{ $c->admin_unread }} new</span>
                            @endif
                            <span class="fx-badge" style="background-color: var(--fx-card-hover); color: {{ $c->status === 'open' ? 'var(--fx-green)' : 'var(--fx-muted)' }};">{{ ucfirst($c->status) }}</span>
                        </td>
                        <td><a href="{{ route('admin.support.show', $c) }}" class="fx-btn text-xs" style="padding:.35rem .8rem;">Open chat</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center fx-muted py-6">Abhi tak kisi user ne support chat shuru nahi ki.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $conversations->links() }}</div>
    <script>setTimeout(function () { location.reload(); }, 30000);</script>
</x-admin-layout>
