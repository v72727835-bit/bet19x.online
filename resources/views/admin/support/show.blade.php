<x-admin-layout title="Chat: {{ $conversation->user->name ?? 'Deleted user' }}">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
        <a href="{{ route('admin.support.index') }}" class="fx-muted text-sm">&larr; All chats</a>
        <div class="flex gap-2">
            @if($conversation->user && auth('admin')->user()->hasPermission('users'))
                <a href="{{ route('admin.users.show', $conversation->user) }}" class="fx-btn-outline text-xs">View player</a>
            @endif
            <form method="POST" action="{{ route('admin.support.status', $conversation) }}">
                @csrf
                <input type="hidden" name="status" value="{{ $conversation->status === 'open' ? 'closed' : 'open' }}">
                <button class="fx-btn-outline text-xs">{{ $conversation->status === 'open' ? 'Mark as closed' : 'Reopen' }}</button>
            </form>
        </div>
    </div>

    <div class="fx-card flex flex-col" style="height: calc(100vh - 230px); min-height: 380px;"
         x-data="adminChat({{ $messages->max('id') ?? 0 }})">
        <div class="flex-1 overflow-y-auto p-4 space-y-2" x-ref="body" id="chat-body">
            @forelse($messages as $m)
                <div class="flex {{ $m->sender === 'admin' ? 'justify-end' : 'justify-start' }}">
                    <div class="px-3 py-2 rounded-xl text-sm" style="max-width:75%; background: {{ $m->sender === 'admin' ? 'var(--fx-amber)' : 'var(--fx-card-hover)' }}; color: {{ $m->sender === 'admin' ? '#14152B' : 'var(--fx-text)' }};">
                        <div style="white-space: pre-wrap; word-wrap: break-word;">{{ $m->body }}</div>
                        <div class="text-xs" style="opacity:.65; margin-top:2px;">{{ $m->sender === 'admin' ? 'Support' : ($conversation->user->name ?? 'Player') }} · {{ $m->created_at->format('d M, h:i A') }}</div>
                    </div>
                </div>
            @empty
                <p class="fx-muted text-sm">No messages yet.</p>
            @endforelse
            <template x-for="m in fresh" :key="m.id">
                <div class="flex" :class="m.from === 'admin' ? 'justify-end' : 'justify-start'">
                    <div class="px-3 py-2 rounded-xl text-sm" style="max-width:75%;" :style="m.from === 'admin' ? 'background: var(--fx-amber); color:#14152B' : 'background: var(--fx-card-hover); color: var(--fx-text)'">
                        <div style="white-space: pre-wrap; word-wrap: break-word;" x-text="m.body"></div>
                        <div class="text-xs" style="opacity:.65; margin-top:2px;" x-text="(m.from === 'admin' ? 'Support' : @js($conversation->user->name ?? 'Player')) + ' · ' + m.time"></div>
                    </div>
                </div>
            </template>
        </div>
        <form class="flex gap-2 p-3" style="border-top:1px solid var(--fx-border);" @submit.prevent="send()">
            <textarea x-model="draft" rows="2" maxlength="2000" class="fx-input flex-1 text-sm" placeholder="Type a reply (Enter to send, Shift+Enter for a new line)"
                      @keydown.enter.prevent="if (!$event.shiftKey) send(); else draft += '\n'"></textarea>
            <button class="fx-btn text-sm" :disabled="sending || !draft.trim()">Send</button>
        </form>
        <p class="text-xs px-3 pb-2" style="color: var(--fx-red);" x-show="error" x-text="error"></p>
    </div>

    <script>
    function adminChat(lastId) {
        return {
            lastId: lastId, fresh: [], draft: '', sending: false, error: '',
            init() {
                this.scroll();
                setInterval(() => this.poll(), 4000);
            },
            scroll() { this.$nextTick(() => { this.$refs.body.scrollTop = this.$refs.body.scrollHeight; }); },
            add(m) {
                if (m.id <= this.lastId && this.fresh.some(x => x.id === m.id)) return;
                if (this.fresh.some(x => x.id === m.id) || m.id <= this.lastId) return;
                this.fresh.push(m); this.lastId = m.id; this.scroll();
            },
            poll() {
                fetch(@json(route('admin.support.messages', $conversation)) + '?after=' + this.lastId, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(r => r.json()).then(d => (d.messages || []).forEach(m => this.add(m))).catch(() => {});
            },
            send() {
                const body = this.draft.trim();
                if (!body || this.sending) return;
                this.sending = true; this.error = '';
                fetch(@json(route('admin.support.reply', $conversation)), {
                    method: 'POST', credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                    body: JSON.stringify({ body })
                }).then(r => r.ok ? r.json() : Promise.reject())
                  .then(d => { this.draft = ''; this.add(d.message); })
                  .catch(() => { this.error = 'Reply not sent. Check your connection and try again.'; })
                  .finally(() => { this.sending = false; });
            }
        };
    }
    </script>
</x-admin-layout>
