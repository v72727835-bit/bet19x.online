<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-900">
                        Add Funds to Wallet
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Deposit money into your wallet to join contests and win prizes.
                    </p>
                </div>
                <div class="p-6" style="background:#0F1023; border-radius:12px; color:#FFF0F5;">
                    <div class="bg-gray-50 p-6 rounded-lg" style="background:rgba(255,255,255,0.03); border:1px solid rgba(212,175,55,0.2);">
                        <h2 class="text-lg font-medium text-gray-100 mb-4">
                            Add Funds
                        </h2>
                        <form action="{{ route('wallet.deposit.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-300 mb-2">
                                    Amount (₹)
                                </label>
                                <input type="number" name="amount" id="amount" required
                                       min="100" step="10"
                                       class="w-full px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#FF4F9A]"
                                       placeholder="Enter amount (minimum ₹100)">
                            </div>

                            <div>
                                <label for="payment_method" class="block text-sm font-medium text-gray-300 mb-2">
                                    Payment Method
                                </label>
                                <select name="payment_method" id="payment_method" required
                                        class="w-full px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#FF4F9A]">
                                    <option value="">Select Payment Method</option>
                                    <option value="upi">UPI (PhonePe, Google Pay, Paytm)</option>
                                    <option value="netbanking">Net Banking</option>
                                    <option value="card">Debit/Credit Card</option>
                                    <option value="wallet">Paytm/PhonePe Wallet</option>
                                </select>
                            </div>

                            <div class="flex items-center space-x-3">
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-[#FF4F9A] to-[#FF1F4D] hover:from-[#FF1F4D] hover:to-[#FF4F9A] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#FF4F9A]">
                                    Add Funds
                                </button>
                                <a href="{{ route('wallet.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-gray-300 hover:text-white bg-gray-800 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-600">
                                    Cancel
                                </a>
                            </div>
                        </form>

                        <div class="mt-6 p-4 bg-gray-800 rounded-lg border border-gray-700">
                            <p class="text-sm text-gray-400">
                                <strong>Note:</strong> This is a demo implementation. In a production environment, this would integrate with a payment gateway like Razorpay, Paytm, or Stripe to process real transactions.
                            </p>
                            <p class="mt-2 text-sm text-gray-400">
                                Minimum deposit amount: ₹100<br>
                                Funds will be added to your Deposit Balance instantly upon successful transaction.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>