<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-900">
                        Withdraw Funds from Wallet
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Withdraw your winnings to your bank account.
                    </p>
                </div>
                <div class="p-6" style="background:#0F1023; border-radius:12px; color:#FFF0F5;">
                    <div class="bg-gray-50 p-6 rounded-lg" style="background:rgba(255,255,255,0.03); border:1px solid rgba(212,175,55,0.2);">
                        <h2 class="text-lg font-medium text-gray-100 mb-4">
                            Withdraw Funds
                        </h2>
                        <form action="{{ route('wallet.withdraw.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-300 mb-2">
                                    Amount to Withdraw (₹)
                                </label>
                                <input type="number" name="amount" id="amount" required
                                       min="100" step="10"
                                       class="w-full px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#FF4F9A]"
                                       placeholder="Enter amount (minimum ₹100)">
                            </div>

                            <div>
                                <label for="bank_details" class="block text-sm font-medium text-gray-300 mb-2">
                                    Bank Account Details
                                </label>
                                <textarea name="bank_details" id="bank_details" rows="4" required
                                          class="w-full px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#FF4F9A]"
                                              placeholder="Enter your bank account name, account number, IFSC code, and bank name"></textarea>
                            </div>

                            <div class="flex items-center space-x-3">
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-[#FF4F9A] to-[#FF1F4D] hover:from-[#FF1F4D] hover:to-[#FF4F9A] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#FF4F9A]">
                                    Request Withdrawal
                                </button>
                                <a href="{{ route('wallet.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-gray-300 hover:text-white bg-gray-800 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-600">
                                    Cancel
                                </a>
                            </div>
                        </form>

                        <div class="mt-6 p-4 bg-gray-800 rounded-lg border border-gray-700">
                            <p class="text-sm text-gray-400">
                                <strong>Note:</strong> This is a demo implementation. In a production environment, this would integrate with a payment gateway or banking API to process real withdrawals after verification.
                            </p>
                            <p class="mt-2 text-sm text-gray-400">
                                Minimum withdrawal amount: ₹100<br>
                                Withdrawals are processed within 2-4 business days after verification.<br>
                                Please ensure your bank account details are correct to avoid delays.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>