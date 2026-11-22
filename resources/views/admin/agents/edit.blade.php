<x-admin-layout title="Edit agent: {{ $agent->name }}">
    <a href="{{ route('admin.agents.show', $agent) }}" class="fx-muted text-sm">&larr; Back to agent</a>

    <form method="POST" action="{{ route('admin.agents.update', $agent) }}" class="fx-card p-5 mt-4 max-w-2xl grid grid-cols-1 sm:grid-cols-2 gap-4">
        @csrf
        @method('PUT')
        <div>
            <label class="fx-muted text-xs block mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name', $agent->name) }}" required class="fx-input w-full">
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">Email (login ID)</label>
            <input type="email" name="email" value="{{ old('email', $agent->email) }}" required class="fx-input w-full">
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $agent->phone) }}" class="fx-input w-full">
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">Type</label>
            <select name="role" class="fx-input w-full">
                @foreach (\App\Models\Agent::ROLE_LABELS as $val => $label)
                    <option value="{{ $val }}" @selected(old('role', $agent->role) === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">Works under</label>
            <select name="parent_id" class="fx-input w-full">
                <option value="">No parent (top level)</option>
                @foreach ($parents as $p)
                    <option value="{{ $p->id }}" @selected((string) old('parent_id', $agent->parent_id) === (string) $p->id)>{{ $p->name }} ({{ $p->roleLabel() }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">Commission %</label>
            <input type="number" step="0.01" min="0" max="100" name="commission_rate" value="{{ old('commission_rate', $agent->commission_rate) }}" required class="fx-input w-full">
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">Credit limit (₹)</label>
            <input type="number" step="0.01" min="0" name="credit_limit" value="{{ old('credit_limit', $agent->credit_limit) }}" required class="fx-input w-full">
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">New password</label>
            <input type="text" name="new_password" minlength="6" autocomplete="off" placeholder="Leave empty to keep the current one" class="fx-input w-full">
        </div>
        <p class="fx-muted text-xs sm:col-span-2">Passwords are stored encrypted, so the current one can't be shown. Set a new one here and share it with the agent along with the login link: {{ route('agent.login') }}</p>
        <div class="sm:col-span-2 flex gap-2">
            <button class="fx-btn text-sm">Save changes</button>
            <a href="{{ route('admin.agents.show', $agent) }}" class="fx-btn-outline text-sm">Cancel</a>
        </div>
    </form>
</x-admin-layout>
