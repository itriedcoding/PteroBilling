<?php $pageTitle = 'Invoice ' . ($invoice['invoice_number'] ?? ''); ob_start(); ?>
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border p-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl font-bold">Invoice <?= htmlspecialchars($invoice['invoice_number'] ?? '') ?></h2>
                <p class="text-gray-500"><?= date('F j, Y', strtotime($invoice['created_at'] ?? '')) ?></p>
            </div>
            <span class="px-4 py-2 rounded-full text-sm font-semibold <?= ($invoice['status'] ?? '') === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                <?= ucfirst($invoice['status'] ?? '') ?>
            </span>
        </div>

        <div class="border-t pt-6 mb-6">
            <div class="flex justify-between mb-2">
                <span class="text-gray-600">Description</span>
                <span class="font-medium"><?= htmlspecialchars($invoice['description'] ?? 'N/A') ?></span>
            </div>
            <div class="flex justify-between mb-2">
                <span class="text-gray-600">Payment Method</span>
                <span class="font-medium capitalize"><?= htmlspecialchars($invoice['payment_method'] ?? 'N/A') ?></span>
            </div>
            <div class="flex justify-between mb-2">
                <span class="text-gray-600">Transaction ID</span>
                <span class="font-medium font-mono text-sm"><?= htmlspecialchars($invoice['transaction_id'] ?? 'N/A') ?></span>
            </div>
        </div>

        <div class="border-t pt-6">
            <div class="flex justify-between text-xl">
                <span class="font-bold">Total</span>
                <span class="font-bold">$<?= number_format((float)($invoice['amount'] ?? 0), 2) ?></span>
            </div>
        </div>
    </div>
    <div class="mt-4 text-center">
        <a href="/billing/invoices" class="text-blue-600 hover:text-blue-800"><i class="fas fa-arrow-left mr-1"></i> Back to Invoices</a>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>
