<x-admin-layout title="Agent transfer">
    <a href="{{ route('admin.agents.index') }}" class="fx-muted text-sm">&larr; All agents</a>

    <form method="POST" action="{{ route('admin.agents.transfer.store') }}" class="fx-card p-5 mt-4 max-w-3xl grid grid-cols-1 sm:grid-cols-2 gap-4"
          onsubmit="return confirm('Transfer ₹' + this.amount.value + ' now?');">
        @csrf
        <p class="fx-muted text-sm sm:col-span-2">
            Move <b>credit</b> (only the unused part can move: the sender's credit limit goes down, the receiver's goes up)
            or <b>commission balance</b> from one agent to another. Every transfer is saved in the history below and in the audit log.
        </p>
        <div>
            <label class="fx-muted text-xs block mb-1">From</label>
            <select name="from_id" class="fx-input w-full" required>
                <option value="">Choose agent</option>
                @foreach($agents as $a)
                    <option value="{{ $a->id }}" @selected((string) old('from_id', request('from')) === (string) $a->id)>
                        {{ $a->name }} ({{ $a->roleLabel() }}, {{ $a->referralCode() }}) — credit free ₹{{ number_format(max(0, $a->creditAvailable()), 0) }}, balance ₹{{ number_format($a->wallet_balance, 0) }}{{ $a->is_active ? '' : ' — inactive' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">To</label>
            <select name="to_id" class="fx-input w-full" required>
                <option value="">Choose agent</option>
                @foreach($agents as $a)
                    <option value="{{ $a->id }}" @selected((string) old('to_id') === (string) $a->id) @disabled(! $a->is_active)>
                        {{ $a->name }} ({{ $a->roleLabel() }}, {{ $a->referralCode() }}){{ $a->is_active ? '' : ' — inactive' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">What to move</label>
            <select name="kind" class="fx-input w-full">
                @foreach(\App\Services\AgentTransfer::KINDS as $k => $label)
                    <option value="{{ $k }}" @selected(old('kind', 'credit') === $k)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">Amount (₹)</label>
            <input type="number" step="0.01" min="{{ \App\Services\AgentTransfer::MIN }}" name="amount" value="{{ old('amount') }}" required class="fx-input w-full">
        </div>
        <div class="sm:col-span-2">
            <label class="fx-muted text-xs block mb-1">Reason</label>
            <input type="text" name="reason" maxlength="120" value="{{ old('reason') }}" required class="fx-input w-full" placeholder="e.g. credit moved on request">
        </div>
        <div class="sm:col-span-2"><button class="fx-btn text-sm">Transfer</button></div>
    </form>

    @include('admin.agents._transfer-history', ['history' => $history])

    {{-- Agent <-> player: any agent with any player, either direction --}}
    <p class="fx-heading text-lg mt-8" id="agent-user">Agent ↔ player transfer</p>
    <form method="POST" action="{{ route('admin.agents.transfer.user') }}" class="fx-card p-5 mt-3 max-w-3xl grid grid-cols-1 sm:grid-cols-2 gap-4"
          onsubmit="return confirm('Transfer ₹' + this.amount.value + ' now?');">
        @csrf
        <p class="fx-muted text-sm sm:col-span-2">
            <b>Agent → player</b>: paid from the agent's unused credit (credit limit goes down) or commission balance; the player gets deposit balance.
            <b>Player → agent</b>: taken from the player's deposit balance first, then winnings; the agent gets it as credit (limit goes up) or commission balance.
        </p>
        <div>
            <label class="fx-muted text-xs block mb-1">Direction</label>
            <select name="direction" class="fx-input w-full">
                <option value="agent_to_user" @selected(old('direction', 'agent_to_user') === 'agent_to_user')>Agent → player</option>
                <option value="user_to_agent" @selected(old('direction') === 'user_to_agent')>Player → agent</option>
            </select>
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">Agent</label>
            <select name="agent_id" class="fx-input w-full" required>
                <option value="">Choose agent</option>
                @foreach($agents as $a)
                    <option value="{{ $a->id }}" @selected((string) old('agent_id') === (string) $a->id)>
                        {{ $a->name }} ({{ $a->roleLabel() }}, {{ $a->referralCode() }}) — credit free ₹{{ number_format(max(0, $a->creditAvailable()), 0) }}, balance ₹{{ number_format($a->wallet_balance, 0) }}{{ $a->is_active ? '' : ' — inactive' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">Player (user ID, email or phone)</label>
            <input type="text" name="user" maxlength="255" value="{{ old('user') }}" required class="fx-input w-full" placeholder="e.g. 27">
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">Agent side: credit or commission balance</label>
            <select name="kind" class="fx-input w-full">
                @foreach(\App\Services\AgentTransfer::KINDS as $k => $label)
                    <option value="{{ $k }}" @selected(old('kind', 'credit') === $k)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">Amount (₹)</label>
            <input type="number" step="0.01" min="{{ \App\Services\AgentUserTransfer::MIN }}" max="{{ \App\Services\AgentUserTransfer::MAX }}" name="amount" value="{{ old('amount') }}" required class="fx-input w-full">
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">Reason</label>
            <input type="text" name="reason" maxlength="120" value="{{ old('reason') }}" required class="fx-input w-full" placeholder="e.g. top-up on request">
        </div>
        <div class="sm:col-span-2"><button class="fx-btn text-sm">Transfer</button></div>
    </form>

    <div class="fx-card overflow-x-auto mt-4">
        <p class="fx-heading text-base px-4 pt-4">Agent ↔ player transfer history</p>
        <table class="w-full fx-table">
            <thead><tr><th>Time</th><th>From</th><th>To</th><th>Agent side</th><th>Amount</th><th>By</th><th>Note</th><th>Ref</th></tr></thead>
            <tbody>
                @forelse($userHistory as $h)
                    @php($agentLabel = ($h->agent_name ?? '#' . $h->agent) . ' (' . ($h->agent_code ?? '') . ')')
                    @php($userLabel = ($h->user_name ?? '') . ' (#' . $h->user . ')')
                    <tr>
                        <td class="fx-muted whitespace-nowrap">{{ $h->at?->format('d M Y H:i') }}</td>
                        <td>{{ ($h->dir ?? '') === 'agent_to_user' ? $agentLabel : $userLabel }}</td>
                        <td>{{ ($h->dir ?? '') === 'agent_to_user' ? $userLabel : $agentLabel }}</td>
                        <td>{{ \App\Services\AgentTransfer::KINDS[$h->kind ?? ''] ?? ($h->kind ?? '') }}</td>
                        <td>₹{{ number_format((float) ($h->amount ?? 0), 2) }}</td>
                        <td class="fx-muted">{{ $h->by ?? '' }}</td>
                        <td class="text-sm">{{ $h->note ?? '' }}</td>
                        <td class="fx-muted text-xs">{{ $h->ref ?? '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center fx-muted py-6">Abhi tak koi agent ↔ player transfer nahi hua.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
