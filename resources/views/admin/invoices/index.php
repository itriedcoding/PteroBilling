<?php $pageTitle = 'All Invoices'; ob_start(); ?>
<div class="bg-white rounded-xl shadow-sm border">
    <div class="p-6 border-b">
        <h2 class="text-2xl font-bold">All Invoices (<?= $total ?? 0 ?>)</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (empty($invoices)): ?>
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">No invoices found.</td></tr>
                <?php else: ?>
                <?php foreach ($invoices as $inv): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium font-mono text-sm"><?= htmlspecialchars($inv['invoice_number']) ?></td>
                    <td class="px-6 py-4">
                        <p class="font-medium"><?= htmlspecialchars($inv['username'] ?? '') ?></p>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($inv['email'] ?? '') ?></p>
                    </td>
                    <td class="px-6 py-4 font-semibold">$<?= number_format((float)$inv['amount'], 2) ?></td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full <?= $inv['status'] === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                            <?= ucfirst($inv['status']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 capitalize"><?= htmlspecialchars($inv['payment_method'] ?? 'N/A') ?></td>
                    <td class="px-6 py-4 text-gray-500"><?= date('M j, Y', strtotime($inv['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>