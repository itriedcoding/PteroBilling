<?php $pageTitle = 'Create Server'; ob_start(); ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border p-8">
        <h2 class="text-2xl font-bold mb-6">Create New Server</h2>
        <form method="POST" action="/servers/create" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Server Name</label>
                <input type="text" name="server_name" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="My Game Server">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Plan</label>
                <select name="plan_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Select a plan</option>
                    <?php foreach ($plans as $p): ?>
                    <option value="<?= $p['id'] ?>">
                        <?= htmlspecialchars($p['name']) ?> - $<?= number_format((float)$p['price'], 2) ?>/mo
                        (<?= $p['memory'] ?>MB RAM, <?= $p['disk'] ?>MB Disk)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <select name="location_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <?php foreach ($locations as $loc): ?>
                    <option value="<?= $loc['attributes']['id'] ?? $loc['id'] ?>">
                        <?= htmlspecialchars($loc['attributes']['short'] ?? $loc['short'] ?? 'Location') ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                <i class="fas fa-server mr-2"></i>Create Server
            </button>
        </form>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>