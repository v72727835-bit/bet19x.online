<x-admin-layout title="Master to master transfer">
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
                        {{ $a->name }} ({{ $a->roleLabel() }}) — credit free ₹{{ number_format(max(0, $a->creditAvailable()), 0) }}, balance ₹{{ number_format($a->wallet_balance, 0) }}{{ $a->is_active ? '' : ' — inactive' }}
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
                        {{ $a->name }} ({{ $a->roleLabel() }}){{ $a->is_active ? '' : ' — inactive' }}
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
</x-admin-layout>
