<x-admin-layout title="Notifications">
    <div class="fx-card p-5 mb-6 max-w-2xl">
        <p class="fx-heading text-sm mb-3">Send Notification</p>
        <form method="POST" action="{{ route('admin.notifications.store') }}" class="space-y-3">
            @csrf
            <input type="text" name="title" placeholder="Title" class="fx-input text-sm w-full" required>
            <textarea name="message" placeholder="Message" rows="3" class="fx-input text-sm w-full" required></textarea>
            <div class="grid grid-cols-2 gap-3">
                <select name="channel" class="fx-input text-sm">
                    <option value="push">Push Notification</option>
                    <option value="sms">SMS</option>
                    <option value="email">Email</option>
                </select>
                <select name="audience" class="fx-input text-sm">
                    <option value="all">All Users</option>
                    <option value="active">Active Users</option>
                    <option value="blocked">Blocked Users</option>
                    <option value="kyc_pending">KYC Pending Users</option>
                </select>
            </div>
            <button class="fx-btn text-sm">Send Notification</button>
        </form>
    </div>

    <div class="fx-card overflow-x-auto">
        <table class="w-full fx-table">
            <thead><tr><th>Title</th><th>Channel</th><th>Audience</th><th>Recipients</th><th>Sent By</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($notifications as $n)
                    <tr>
                        <td>{{ $n->title }}</td>
                        <td class="fx-muted">{{ strtoupper($n->channel) }}</td>
                        <td class="fx-muted">{{ ucfirst(str_replace('_',' ',$n->audience)) }}</td>
                        <td>{{ $n->recipient_count }}</td>
                        <td class="fx-muted">{{ $n->sentBy->name ?? '-' }}</td>
                        <td class="fx-muted">{{ $n->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center fx-muted py-6">Koi notification nahi bheji gayi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $notifications->links() }}</div>
</x-admin-layout>
