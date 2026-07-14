<?php $pageTitle = 'My Servers'; ob_start(); ?>
<div class="bg-white rounded-xl shadow-sm border">
    <div class="p-6 border-b flex items-center justify-between">
        <h2 class="text-2xl font-bold">My Servers</h2>
        <a href="/servers/create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
            <i class="fas fa-plus mr-1"></i> Create Server
        </a>
    </div>
    <div class="p-6">
        <?php if (empty($servers)): ?>
        <div class="text-center py-12">
            <i class="fas fa-server text-5xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg">No servers yet.</p>
            <a href="/servers/create" class="mt-4 inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Create Your First Server</a>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($servers as $s): ?>
            <div class="border rounded-xl p-5 card-hover">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-lg"><?= htmlspecialchars($s['name']) ?></h3>
                    <span class="px-2 py-1 text-xs rounded-full <?= $s['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                        <?= ucfirst($s['status']) ?>
                    </span>
                </div>
                <p class="text-sm text-gray-500 mb-1">Plan: <?= htmlspecialchars($s['plan_name']) ?></p>
                <p class="text-sm text-gray-500 mb-3">Expires: <?= date('M j, Y', strtotime($s['expires_at'])) ?></p>
                <div class="flex space-x-2">
                    <a href="/servers/<?= $s['id'] ?>" class="flex-1 text-center bg-gray-100 text-gray-700 py-2 rounded-lg text-sm hover:bg-gray-200">Details</a>
                    <form method="POST" action="/servers/<?= $s['id'] ?>/renew" class="flex-1">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                        <button type="submit" class="w-full bg-blue-100 text-blue-700 py-2 rounded-lg text-sm hover:bg-blue-200">Renew</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>