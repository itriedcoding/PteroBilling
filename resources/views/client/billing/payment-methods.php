<?php $pageTitle = 'Payment Methods'; ob_start(); ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h2 class="text-2xl font-bold mb-6">Payment Methods</h2>
        
        <?php if (empty($payment_methods)): ?>
        <div class="text-center py-8">
            <i class="fas fa-credit-card text-4xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">No payment methods saved yet.</p>
            <p class="text-sm text-gray-400 mt-1">Payment methods are saved when you make a purchase.</p>
        </div>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($payment_methods as $pm): ?>
            <div class="flex items-center justify-between p-4 border rounded-lg <?= $pm['is_default'] ? 'border-blue-500 bg-blue-50' : '' ?>">
                <div class="flex items-center">
                    <i class="fab fa-<?= $pm['brand'] === 'visa' ? 'cc-visa' : ($pm['brand'] === 'mastercard' ? 'cc-mastercard' : 'cc-unknown') ?> text-2xl mr-3"></i>
                    <div>
                        <p class="font-semibold"><?= ucfirst($pm['brand'] ?? 'Card') ?> ending in <?= $pm['last_four'] ?? '****' ?></p>
                        <p class="text-sm text-gray-500">Expires <?= $pm['exp_month'] ?>/<?= $pm['exp_year'] ?></p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <?php if ($pm['is_default']): ?>
                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">Default</span>
                    <?php endif; ?>
                    <form method="POST" action="/billing/payment-methods/<?= $pm['id'] ?>" onsubmit="return confirm('Remove this payment method?')">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>
