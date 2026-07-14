<?php $pageTitle = 'Invoices'; ob_start(); ?>
<div class="bg-white rounded-xl shadow-sm border">
    <div class="p-6 border-b">
        <h2 class="text-2xl font-bold">Invoices</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (empty($invoices)): ?>
                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No invoices found.</td></tr>
                <?php else: ?>
                <?php foreach ($invoices as $inv): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium"><?= htmlspecialchars($inv['invoice_number']) ?></td>
                    <td class="px-6 py-4 text-gray-500"><?= date('M j, Y', strtotime($inv['created_at'])) ?></td>
                    <td class="px-6 py-4 font-semibold">$<?= number_format((float)$inv['amount'], 2) ?></td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full <?= $inv['status'] === 'paid' ? 'bg-green-100 text-green-800' : ($inv['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                            <?= ucfirst($inv['status']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-500 capitalize"><?= htmlspecialchars($inv['payment_method'] ?? 'N/A') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>
