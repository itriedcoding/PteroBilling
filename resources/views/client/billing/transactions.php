<?php $pageTitle = 'Transactions'; ob_start(); ?>
<div class="bg-white rounded-xl shadow-sm border">
    <div class="p-6 border-b">
        <h2 class="text-2xl font-bold">Transaction History</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Provider</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (empty($transactions)): ?>
                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No transactions found.</td></tr>
                <?php else: ?>
                <?php foreach ($transactions as $tx): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-gray-500"><?= date('M j, Y g:i A', strtotime($tx['created_at'])) ?></td>
                    <td class="px-6 py-4"><?= htmlspecialchars($tx['description'] ?: $tx['type']) ?></td>
                    <td class="px-6 py-4 capitalize"><?= htmlspecialchars($tx['provider']) ?></td>
                    <td class="px-6 py-4 font-semibold <?= $tx['type'] === 'credit_purchase' ? 'text-green-600' : 'text-red-600' ?>">
                        <?= $tx['type'] === 'credit_purchase' ? '+' : '-' ?>$<?= number_format((float)$tx['amount'], 2) ?>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full <?= $tx['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                            <?= ucfirst($tx['status']) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>
