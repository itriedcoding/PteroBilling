<?php $pageTitle = 'Edit Plan'; ob_start(); ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border p-8">
        <h2 class="text-2xl font-bold mb-6">Edit Plan: <?= htmlspecialchars($plan['name'] ?? '') ?></h2>
        <form method="POST" action="/admin/plans/<?= $plan['id'] ?? '' ?>" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            <input type="hidden" name="_method" value="PUT">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plan Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($plan['name'] ?? '') ?>" required class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price ($/month)</label>
                    <input type="number" name="price" step="0.01" value="<?= $plan['price'] ?? '' ?>" required class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="2" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($plan['description'] ?? '') ?></textarea>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CPU (%)</label>
                    <input type="number" name="cpu" value="<?= $plan['cpu'] ?? 100 ?>" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Memory (MB)</label>
                    <input type="number" name="memory" value="<?= $plan['memory'] ?? 1024 ?>" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Disk (MB)</label>
                    <input type="number" name="disk" value="<?= $plan['disk'] ?? 10240 ?>" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">IO Limit</label>
                    <input type="number" name="io" value="<?= $plan['io'] ?? 500 ?>" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Databases</label>
                    <input type="number" name="databases" value="<?= $plan['databases'] ?? 1 ?>" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Backups</label>
                    <input type="number" name="backups" value="<?= $plan['backups'] ?? 1 ?>" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nest ID</label>
                    <input type="number" name="nest_id" value="<?= $plan['nest_id'] ?? 1 ?>" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Egg ID</label>
                    <input type="number" name="egg_id" value="<?= $plan['egg_id'] ?? 1 ?>" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="is_active" <?= ($plan['is_active'] ?? false) ? 'checked' : '' ?> class="rounded border-gray-300 text-blue-600 mr-2">
                <label class="text-sm text-gray-700">Active</label>
            </div>
            <div class="flex space-x-4">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700">Update Plan</button>
                <a href="/admin/plans" class="flex-1 text-center bg-gray-200 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-300">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>