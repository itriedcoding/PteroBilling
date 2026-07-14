<?php $pageTitle = 'Add Funds'; ob_start(); ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border p-8">
        <h2 class="text-2xl font-bold mb-6">Add Funds to Account</h2>
        <form method="POST" action="/billing/add-funds" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Amount (USD)</label>
                <div class="grid grid-cols-4 gap-3 mb-3">
                    <button type="button" onclick="setAmount(5)" class="amount-btn border-2 border-gray-200 rounded-lg py-2 hover:border-blue-500 transition font-semibold">$5</button>
                    <button type="button" onclick="setAmount(10)" class="amount-btn border-2 border-gray-200 rounded-lg py-2 hover:border-blue-500 transition font-semibold">$10</button>
                    <button type="button" onclick="setAmount(25)" class="amount-btn border-2 border-gray-200 rounded-lg py-2 hover:border-blue-500 transition font-semibold">$25</button>
                    <button type="button" onclick="setAmount(50)" class="amount-btn border-2 border-gray-200 rounded-lg py-2 hover:border-blue-500 transition font-semibold">$50</button>
                </div>
                <div class="relative">
                    <span class="absolute left-3 top-3 text-gray-500 text-lg">$</span>
                    <input type="number" name="amount" id="amount" min="1" max="1000" step="0.01" value="10" required
                        class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-lg"
                        placeholder="Enter amount">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Payment Method</label>
                <div class="space-y-3">
                    <label class="flex items-center p-4 border-2 border-blue-500 rounded-lg bg-blue-50 cursor-pointer payment-method">
                        <input type="radio" name="payment_method" value="stripe" checked class="text-blue-600">
                        <div class="ml-3 flex items-center">
                            <i class="fab fa-stripe text-2xl mr-3 text-indigo-600"></i>
                            <div>
                                <p class="font-semibold">Credit / Debit Card</p>
                                <p class="text-sm text-gray-500">Pay securely with Stripe</p>
                            </div>
                        </div>
                    </label>
                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer payment-method hover:border-blue-300">
                        <input type="radio" name="payment_method" value="paypal" class="text-blue-600">
                        <div class="ml-3 flex items-center">
                            <i class="fab fa-paypal text-2xl mr-3 text-blue-600"></i>
                            <div>
                                <p class="font-semibold">PayPal</p>
                                <p class="text-sm text-gray-500">Pay with your PayPal account</p>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                <i class="fas fa-lock mr-2"></i>Proceed to Payment
            </button>

            <p class="text-center text-sm text-gray-500">
                <i class="fas fa-shield-alt mr-1"></i>Payments are processed securely. We never store your card details.
            </p>
        </form>
    </div>
</div>

<script>
function setAmount(amount) {
    document.getElementById('amount').value = amount;
    document.querySelectorAll('.amount-btn').forEach(btn => {
        btn.classList.remove('border-blue-500', 'bg-blue-50');
        btn.classList.add('border-gray-200');
    });
    event.target.classList.remove('border-gray-200');
    event.target.classList.add('border-blue-500', 'bg-blue-50');
}

document.querySelectorAll('.payment-method input').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.payment-method').forEach(el => {
            el.classList.remove('border-blue-500', 'bg-blue-50');
            el.classList.add('border-gray-200');
        });
        this.closest('.payment-method').classList.remove('border-gray-200');
        this.closest('.payment-method').classList.add('border-blue-500', 'bg-blue-50');
    });
});
</script>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>
