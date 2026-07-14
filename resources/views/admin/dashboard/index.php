<?php $pageTitle = 'Admin Dashboard'; ob_start(); ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Users</p>
                <p class="text-3xl font-bold"><?= $total_users ?? 0 ?></p>
            </div>
            <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-users text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Active Servers</p>
                <p class="text-3xl font-bold"><?= $active_servers ?? 0 ?> / <?= $total_servers ?? 0 ?></p>
            </div>
            <div class="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-server text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Monthly Revenue</p>
                <p class="text-3xl font-bold">$<?= number_format($monthly_revenue ?? 0, 2) ?></p>
            </div>
            <div class="h-12 w-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-dollar-sign text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Revenue</p>
                <p class="text-3xl font-bold">$<?= number_format($total_revenue ?? 0, 2) ?></p>
            </div>
            <div class="h-12 w-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-line text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow-sm border">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold">Quick Actions</h3>
        </div>
        <div class="p-6 grid grid-cols-2 gap-4">
            <a href="/admin/plans/create" class="bg-blue-600 text-white p-4 rounded-lg text-center hover:bg-blue-700 transition">
                <i class="fas fa-plus text-xl mb-2"></i><p class="text-sm font-medium">Create Plan</p>
            </a>
            <a href="/admin/users" class="bg-gray-600 text-white p-4 rounded-lg text-center hover:bg-gray-700 transition">
                <i class="fas fa-users text-xl mb-2"></i><p class="text-sm font-medium">Manage Users</p>
            </a>
            <a href="/admin/settings" class="bg-green-600 text-white p-4 rounded-lg text-center hover:bg-green-700 transition">
                <i class="fas fa-cog text-xl mb-2"></i><p class="text-sm font-medium">Settings</p>
            </a>
            <a href="/admin/api-keys" class="bg-purple-600 text-white p-4 rounded-lg text-center hover:bg-purple-700 transition">
                <i class="fas fa-key text-xl mb-2"></i><p class="text-sm font-medium">API Keys</p>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold">Recent Invoices</h3>
        </div>
        <div class="p-6">
            <?php if (empty($recent_invoices)): ?>
            <p class="text-gray-500 text-center py-4">No invoices yet.</p>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach (array_slice($recent_invoices, 0, 8) as $inv): ?>
                <div class="flex items-center justify-between py-2 border-b last:border-0">
                    <div>
                        <p class="font-medium text-sm"><?= htmlspecialchars($inv['username'] ?? 'User') ?></p>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($inv['invoice_number']) ?></p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold">$<?= number_format((float)$inv['amount'], 2) ?></p>
                        <span class="text-xs <?= $inv['status'] === 'paid' ? 'text-green-600' : 'text-yellow-600' ?>"><?= ucfirst($inv['status']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>