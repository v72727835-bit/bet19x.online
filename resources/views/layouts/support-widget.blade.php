{{-- Floating live-support chat. Polls while open; checks for replies every 30s while closed. --}}
@php
    try {
        $swUnread = auth()->check() ? (int) (\App\Models\SupportConversation::where('user_id', auth()->id())->value('user_unread') ?? 0) : 0;
    } catch (\Throwable $e) {
        $swUnread = 0; // support tables not migrated yet — never break the page over a badge
    }
@endphp
<div x-data="supportChat({{ $swUnread }})" @open-support.window="toggle(true)">
    <button type="button" class="bx-chatfab" @click="toggle()" :aria-expanded="open" aria-label="Live support">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v11H9l-5 4V5z"/></svg>
        <span class="bx-chatfab-dot" x-show="unread > 0" x-text="unread" style="display:none"></span>
    </button>

    <section class="bx-chat" x-show="open" style="display:none" role="dialog" aria-label="Live support chat">
        <header class="bx-chat-head">
            <div>
                <b>Live support</b>
                <small>We usually reply within a few minutes</small>
            </div>
            <button type="button" class="bx-icon-btn" @click="toggle(false)" aria-label="Close chat" style="width:36px;height:36px;">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </header>

        @auth
            <div class="bx-chat-body" x-ref="body">
                <p class="bx-chat-note" x-show="!messages.length">Tell us what you need help with — deposits, withdrawals, a game, or your account.</p>
                <template x-for="m in messages" :key="m.id">
                    <div class="bx-msg" :class="m.from === 'user' ? 'is-me' : ''">
                        <p x-text="m.body"></p>
                        <small x-text="(m.from === 'admin' ? 'Support · ' : '') + m.time"></small>
                    </div>
                </template>
            </div>
            <form class="bx-chat-form" @submit.prevent="send()">
                <textarea x-model="draft" rows="1" maxlength="2000" placeholder="Type your message" aria-label="Message"
                          @keydown.enter.prevent="if (!$event.shiftKey) send(); else draft += '\n'"></textarea>
                <button type="submit" class="bx-btn bx-btn-gold" :disabled="sending || !draft.trim()">Send</button>
            </form>
            <p class="bx-chat-err" x-show="error" x-text="error" style="display:none"></p>
        @else
            <div class="bx-chat-body">
                <p class="bx-chat-note">Log in to chat with our support team about deposits, withdrawals or your account.</p>
                <a href="{{ route('login') }}" class="bx-btn bx-btn-gold bx-btn-block">Log in to chat</a>
            </div>
        @endauth
    </section>
</div>

<script>
function supportChat(initialUnread) {
    return {
        open: false, unread: initialUnread, messages: [], draft: '', sending: false, error: '', lastId: 0, timer: null,
        auth: @json(auth()->check()),
        init() { this.schedule(); },
        toggle(force) {
            this.open = typeof force === 'boolean' ? force : !this.open;
            if (this.open && this.auth) { this.unread = 0; this.poll(true); }
            this.schedule();
        },
        schedule() {
            clearInterval(this.timer);
            if (!this.auth) return;
            this.timer = setInterval(() => this.poll(this.open), this.open ? 4000 : 30000);
        },
        poll(seen) {
            fetch(@json(route('support.messages')) + '?after=' + this.lastId + (seen ? '&seen=1' : ''), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(d => {
                    (d.messages || []).forEach(m => this.push(m));
                    if (!this.open) this.unread = d.unread || 0;
                })
                .catch(() => {});
        },
        push(m) {
            if (this.messages.some(x => x.id === m.id)) return;
            this.messages.push(m);
            this.lastId = Math.max(this.lastId, m.id);
            this.$nextTick(() => { if (this.$refs.body) this.$refs.body.scrollTop = this.$refs.body.scrollHeight; });
        },
        send() {
            const body = this.draft.trim();
            if (!body || this.sending) return;
            this.sending = true; this.error = '';
            fetch(@json(route('support.send')), {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ body })
            })
                .then(r => r.ok ? r.json() : Promise.reject(r.status))
                .then(d => { this.draft = ''; this.push(d.message); })
                .catch(s => { this.error = s === 429 ? 'You are sending messages too fast. Wait a moment and try again.' : 'Message not sent. Check your connection and try again.'; })
                .finally(() => { this.sending = false; });
        }
    };
}
</script>
