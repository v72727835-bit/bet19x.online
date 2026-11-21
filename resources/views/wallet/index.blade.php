<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-900">
                        My Wallet
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Your wallet balance and transaction history.
                    </p>
                </div>
                <div class="p-6 space-y-6" style="background:#0F1023; border-radius:12px; color:#FFF0F5;">
                    @if (session('status'))
                        <div class="rounded-lg p-3 text-sm" style="background:rgba(52,211,153,.12); border:1px solid rgba(52,211,153,.4); color:#6EE7B7;">{{ session('status') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="rounded-lg p-3 text-sm" style="background:rgba(230,0,18,.12); border:1px solid rgba(230,0,18,.4); color:#FCA5A5;">{{ session('error') }}</div>
                    @endif
                    <!-- Wallet Balances -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="border rounded-lg p-4" style="background:rgba(255,255,255,0.05); border-color:rgba(212,175,55,0.2);">
                            <div class="text-sm font-medium text-gray-300">Deposit Balance</div>
                            <div class="mt-1 text-2xl font-semibold text-[#D4AF37]">
                                ₹{{ number_format($wallet->deposit_balance, 2) }}
                            </div>
                        </div>
                        <div class="border rounded-lg p-4" style="background:rgba(255,255,255,0.05); border-color:rgba(212,175,55,0.2);">
                            <div class="text-sm font-medium text-gray-300">Winnings Balance</div>
                            <div class="mt-1 text-2xl font-semibold text-[#FF4F9A]">
                                ₹{{ number_format($wallet->winnings_balance, 2) }}
                            </div>
                        </div>
                        <div class="border rounded-lg p-4" style="background:rgba(255,255,255,0.05); border-color:rgba(212,175,55,0.2);">
                            <div class="text-sm font-medium text-gray-300">Bonus Balance</div>
                            <div class="mt-1 text-2xl font-semibold text-[#E38FB3]">
                                ₹{{ number_format($wallet->bonus_balance, 2) }}
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-3">
                        <a href="{{ route('wallet.deposit') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-[#FF4F9A] to-[#FF1F4D] hover:from-[#FF1F4D] hover:to-[#FF4F9A] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#FF4F9A]">
                            Add Funds
                        </a>
                        <a href="{{ route('wallet.withdraw') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-[#FF4F9A] to-[#FF1F4D] hover:from-[#FF1F4D] hover:to-[#FF4F9A] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#FF4F9A]">
                            Withdraw
                        </a>
                    </div>

                    <!-- Deposit Requests -->
                    @if ($depositRequests->isNotEmpty())
                        <div class="border-t border-gray-600 pt-5">
                            <h2 class="text-lg font-medium text-gray-100">Deposit Requests</h2>
                            <div class="mt-4 space-y-3">
                                @foreach ($depositRequests as $dr)
                                    @php
                                        $c = $dr->status === 'approved' ? '#34D399' : ($dr->status === 'rejected' ? '#F87171' : '#FBBF24');
                                    @endphp
                                    <div class="border rounded-lg p-3 flex items-center justify-between" style="background:rgba(255,255,255,0.03); border-color:rgba(212,175,55,0.15);">
                                        <div>
                                            <p class="text-sm font-medium text-gray-100">₹{{ number_format($dr->amount, 2) }} <span class="text-xs text-gray-400">via {{ strtoupper($dr->method) }}</span></p>
                                            <p class="text-xs text-gray-500">{{ $dr->created_at->format('M d, Y H:i') }}@if($dr->status==='rejected' && $dr->admin_remarks) · {{ $dr->admin_remarks }}@endif</p>
                                        </div>
                                        <span class="text-xs font-semibold px-2 py-1 rounded-full" style="color:{{ $c }}; border:1px solid {{ $c }};">{{ $dr->status === 'pending' ? 'Pending approval' : ucfirst($dr->status) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Transaction History -->
                    <div class="border-t border-gray-600 pt-5">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-medium text-gray-100">
                                Transaction History
                            </h2>
                            @if ($transactions->count())
                                <a href="#" class="text-sm text-[#FF4F9A] hover:text-[#FF1F4D]">
                                    View All
                                </a>
                            @endif
                        </div>

                        @if ($transactions->isEmpty())
                            <div class="mt-6 text-center text-gray-400">
                                No transactions yet.
                            </div>
                        @else
                            <div class="mt-6 space-y-4">
                                @foreach ($transactions as $transaction)
                                    <div class="border rounded-lg p-4 flex items-center space-x-4" style="background:rgba(255,255,255,0.03); border-color:rgba(212,175,55,0.15);">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-lg bg-gradient-to-r from-[#FF4F9A] to-[#FF1F4D] text-white flex items-center justify-center">
                                            @if ($transaction->type == 'deposit')
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"></path></svg>
                                            @else
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16V8l-3-3"></path></svg>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-100">
                                                @if ($transaction->type == 'deposit')
                                                    Deposit
                                                @elseif ($transaction->type == 'withdrawal')
                                                    Withdrawal
                                                @else
                                                    {{ ucfirst($transaction->type) }}
                                                @endif
                                            </p>
                                            <p class="mt-1 text-sm text-gray-400 truncate">{{ $transaction->description }}</p>
                                            @if (!empty($transaction->reference_id))
                                                <p class="mt-1 text-xs text-gray-500">Ref: {{ $transaction->reference_id }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right space-y-1">
                                            <p class="text-sm font-medium text-gray-100">
                                                @if (in_array($transaction->type, ['deposit','credit','refund']))
                                                    +₹{{ number_format($transaction->amount, 2) }} <span class="text-[#D4AF37]">(+)</span>
                                                @else
                                                    -₹{{ number_format($transaction->amount, 2) }} <span class="text-[#FF4F9A]">(-)</span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ $transaction->created_at->format('M d, Y H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 flex items-center justify-between p-2 border-t border-gray-600">
                                <span class="text-sm text-gray-600">
                                    Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }} entries
                                </span>
                                {{ $transactions->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>