<x-admin-layout title="Edit user: {{ $user->name }}">
    <a href="{{ route('admin.users.show', $user) }}" class="fx-muted text-sm">&larr; Back to user</a>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="fx-card p-5 mt-4 max-w-xl space-y-4">
        @csrf
        @method('PUT')
        <p class="fx-muted text-sm">User ID #{{ $user->id }} — logs in with their email.</p>
        <div>
            <label class="fx-muted text-xs block mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="fx-input w-full">
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">Email (login ID)</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="fx-input w-full">
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="fx-input w-full">
        </div>
        <div>
            <label class="fx-muted text-xs block mb-1">New password</label>
            <input type="text" name="new_password" minlength="8" autocomplete="off" placeholder="Leave empty to keep the current password" class="fx-input w-full">
            <p class="fx-muted text-xs mt-1">Passwords are stored encrypted, so the current one can't be shown. Type a new one here and share it with the user.</p>
        </div>
        <div class="flex gap-2">
            <button class="fx-btn text-sm">Save changes</button>
            <a href="{{ route('admin.users.show', $user) }}" class="fx-btn-outline text-sm">Cancel</a>
        </div>
    </form>
</x-admin-layout>
