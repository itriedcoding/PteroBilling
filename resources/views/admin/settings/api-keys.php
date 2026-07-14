<?php $pageTitle = 'API Keys'; ob_start(); ?>
<div class="max-w-2xl mx-auto">
    <?php if (!empty($new_key)): ?>
    <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-green-800 mb-2">API Key Created</h3>
        <p class="text-sm text-green-700 mb-3">Copy this key now. It will not be shown again.</p>
        <code class="block bg-green-100 p-3 rounded text-sm break-all font-mono"><?= htmlspecialchars($new_key) ?></code>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Create API Key</h2>
        <form method="POST" action="/admin/api-keys" class="flex space-x-4">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            <input type="text" name="name" required placeholder="Key name" class="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Create</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border">
        <div class="p-6 border-b"><h2 class="text-xl font-bold">API Keys</h2></div>
        <div class="divide-y">
            <?php if (empty($api_keys)): ?>
            <p class="p-6 text-gray-500 text-center">No API keys.</p>
            <?php else: ?>
            <?php foreach ($api_keys as $key): ?>
            <div class="p-6 flex items-center justify-between">
                <div>
                    <p class="font-semibold"><?= htmlspecialchars($key['name']) ?></p>
                    <p class="text-sm text-gray-500 font-mono"><?= substr($key['key'], 0, 12) ?>...</p>
                    <p class="text-xs text-gray-400">Created: <?= date('M j, Y', strtotime($key['created_at'])) ?></p>
                </div>
                <form method="POST" action="/admin/api-keys/<?= $key['id'] ?>" onsubmit="return confirm('Delete this key?')">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button>
                </form>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>