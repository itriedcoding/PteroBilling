<?php $pageTitle = $server['name'] ?? 'Server'; ob_start(); ?>
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border p-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold"><?= htmlspecialchars($server['name'] ?? '') ?></h2>
            <span class="px-3 py-1 text-sm rounded-full <?= ($server['status'] ?? '') === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                <?= ucfirst($server['status'] ?? 'Unknown') ?>
            </span>
        </div>
        <div class="grid grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500">Plan</p>
                <p class="font-semibold"><?= htmlspecialchars($server['plan_name'] ?? '') ?></p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500">Expires</p>
                <p class="font-semibold"><?= date('M j, Y', strtotime($server['expires_at'] ?? '')) ?></p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500">Pterodactyl ID</p>
                <p class="font-semibold font-mono">#<?= $server['ptero_server_id'] ?? 'N/A' ?></p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500">Created</p>
                <p class="font-semibold"><?= date('M j, Y', strtotime($server['created_at'] ?? '')) ?></p>
            </div>
        </div>
        <div class="flex space-x-4">
            <form method="POST" action="/servers/<?= $server['id'] ?>/renew" class="flex-1">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700">
                    <i class="fas fa-sync-alt mr-2"></i>Renew (30 days)
                </button>
            </form>
            <form method="POST" action="/servers/<?= $server['id'] ?>" class="flex-1" onsubmit="return confirm('Are you sure you want to delete this server?')">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="w-full bg-red-600 text-white py-3 rounded-lg font-semibold hover:bg-red-700">
                    <i class="fas fa-trash mr-2"></i>Delete Server
                </button>
            </form>
        </div>
    </div>
    <div class="mt-4 text-center">
        <a href="/servers" class="text-blue-600 hover:text-blue-800"><i class="fas fa-arrow-left mr-1"></i> Back to Servers</a>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>