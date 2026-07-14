<?php $pageTitle = 'Billing'; ob_start(); ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Account Balance</p>
                <p class="text-3xl font-bold text-gray-900">$<?= number_format($balance ?? 0, 2) ?></p>
            </div>
            <div class="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-wallet text-green-600 text-xl"></i>
            </div>
        </div>
        <a href="/billing/add-funds" class="mt-4 inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
            <i class="fas fa-plus mr-1"></i> Add Funds
        </a>
    </div>
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Invoices</p>
                <p class="text-3xl font-bold text-gray-900"><?= count($recent_invoices ?? []) ?></p>
            </div>
            <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-file-invoice text-blue-600 text-xl"></i>
            </div>
        </div>
        <a href="/billing/invoices" class="mt-4 inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
            View All <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Active Servers</p>
                <p class="text-3xl font-bold text-gray-900"><?= count(array_filter($recent_invoices ?? [], fn($i) => $i['status'] === 'paid')) ?></p>
            </div>
            <div class="h-12 w-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-server text-purple-600 text-xl"></i>
            </div>
        </div>
        <a href="/servers" class="mt-4 inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
            Manage <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow-sm border">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold">Recent Invoices</h3>
        </div>
        <div class="p-6">
            <?php if (empty($recent_invoices)): ?>
            <p class="text-gray-500 text-center py-4">No invoices yet.</p>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach (array_slice($recent_invoices, 0, 5) as $inv): ?>
                <div class="flex items-center justify-between py-2 border-b last:border-0">
                    <div>
                        <p class="font-medium text-sm"><?= htmlspecialchars($inv['invoice_number']) ?></p>
                        <p class="text-xs text-gray-500"><?= date('M j, Y', strtotime($inv['created_at'])) ?></p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold">$<?= number_format((float)$inv['amount'], 2) ?></p>
                        <span class="text-xs px-2 py-0.5 rounded-full <?= $inv['status'] === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                            <?= ucfirst($inv['status']) ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold">Recent Transactions</h3>
        </div>
        <div class="p-6">
            <?php if (empty($recent_transactions)): ?>
            <p class="text-gray-500 text-center py-4">No transactions yet.</p>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach (array_slice($recent_transactions, 0, 5) as $tx): ?>
                <div class="flex items-center justify-between py-2 border-b last:border-0">
                    <div class="flex items-center">
                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                            <i class="fas fa-<?= $tx['type'] === 'credit_purchase' ? 'plus text-green-600' : 'minus text-red-600' ?> text-xs"></i>
                        </div>
                        <div>
                            <p class="font-medium text-sm"><?= htmlspecialchars($tx['description'] ?: $tx['type']) ?></p>
                            <p class="text-xs text-gray-500"><?= ucfirst($tx['provider']) ?></p>
                        </div>
                    </div>
                    <p class="font-semibold <?= $tx['type'] === 'credit_purchase' ? 'text-green-600' : 'text-red-600' ?>">
                        <?= $tx['type'] === 'credit_purchase' ? '+' : '-' ?>$<?= number_format((float)$tx['amount'], 2) ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>
