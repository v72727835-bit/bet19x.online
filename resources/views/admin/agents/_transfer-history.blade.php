<div class="fx-card overflow-x-auto mt-4">
    <p class="fx-heading text-base px-4 pt-4">Agent transfer history</p>
    <table class="w-full fx-table">
        <thead><tr><th>Time</th><th>From</th><th>To</th><th>What</th><th>Amount</th><th>By</th><th>Note</th><th>Ref</th></tr></thead>
        <tbody>
            @forelse($history as $h)
                <tr>
                    <td class="fx-muted whitespace-nowrap">{{ $h->at?->format('d M Y H:i') }}</td>
                    <td>{{ $h->from_name ?? '#' . $h->from }}</td>
                    <td>{{ $h->to_name ?? '#' . $h->to }}</td>
                    <td>{{ \App\Services\AgentTransfer::KINDS[$h->kind ?? ''] ?? ($h->kind ?? '') }}</td>
                    <td>₹{{ number_format((float) ($h->amount ?? 0), 2) }}</td>
                    <td class="fx-muted">{{ $h->by ?? '' }}</td>
                    <td class="text-sm">{{ $h->note ?? '' }}</td>
                    <td class="fx-muted text-xs">{{ $h->ref ?? '' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center fx-muted py-6">Abhi tak koi agent transfer nahi hua.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
