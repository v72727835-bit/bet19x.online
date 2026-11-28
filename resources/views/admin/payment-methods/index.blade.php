<x-admin-layout title="Payment details">
    @unless($ready)
        <div class="fx-card p-4 mb-4" style="border-color: var(--fx-amber);">
            <p class="text-sm">Ye feature chalu karne ke liye server par ek baar <code>php artisan migrate --force</code> chalana hoga.</p>
        </div>
    @endunless

    <div class="fx-card p-5 mb-4 max-w-3xl">
        <p class="fx-heading text-base mb-1">Add a way to pay</p>
        <p class="fx-muted text-xs mb-4">What you add here appears on the players' Deposit page. After paying, they send you the UTR number, and you approve the deposit to credit their wallet.</p>
        @include('admin.payment-methods._form', ['method' => null, 'action' => route('admin.payment-methods.store'), 'submit' => 'Add payment method'])
    </div>

    <div class="fx-card overflow-x-auto">
        <table class="w-full fx-table">
            <thead><tr><th>Name</th><th>What players see</th><th>Limits</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($methods as $m)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                @if($m->qr_image_path)
                                    <img src="{{ $m->qrUrl() }}" alt="" style="height:44px; width:44px; object-fit:contain; background:#fff; border-radius:6px; padding:2px;">
                                @endif
                                <b>{{ $m->title }}</b>
                            </div>
                        </td>
                        <td class="text-sm">
                            {{ $m->summary() }}
                            @if($m->upi_id)<div class="fx-muted text-xs">{{ $m->upi_id }}</div>@endif
                            @if($m->account_number)<div class="fx-muted text-xs">{{ $m->bank_name }} · {{ $m->account_number }}</div>@endif
                        </td>
                        <td class="fx-muted text-xs">₹{{ number_format($m->min_amount, 0) }} – {{ $m->max_amount ? '₹' . number_format($m->max_amount, 0) : 'no limit' }}</td>
                        <td><span class="fx-badge" style="background-color: var(--fx-card-hover); color: {{ $m->is_active ? 'var(--fx-green)' : 'var(--fx-red)' }};">{{ $m->is_active ? 'Visible to players' : 'Hidden' }}</span></td>
                        <td class="whitespace-nowrap">
                            <div class="flex flex-wrap gap-1">
                                <a href="{{ route('admin.payment-methods.edit', $m) }}" class="fx-btn-outline text-xs" style="padding:.3rem .6rem;">Edit</a>
                                <form method="POST" action="{{ route('admin.payment-methods.toggle', $m) }}">@csrf<button class="fx-btn-outline text-xs" style="padding:.3rem .6rem;">{{ $m->is_active ? 'Hide' : 'Show' }}</button></form>
                                <form method="POST" action="{{ route('admin.payment-methods.destroy', $m) }}" onsubmit="return confirm('Delete {{ addslashes($m->title) }}? Players will no longer see it.');">@csrf @method('DELETE')<button class="fx-btn-danger text-xs" style="padding:.3rem .6rem;">Delete</button></form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center fx-muted py-6">Abhi koi payment detail nahi hai. Players tab tak deposit nahi kar payenge. Upar se ek add kariye.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
