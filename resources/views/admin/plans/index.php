<?php $pageTitle = 'Manage Plans'; ob_start(); ?>
<div class="bg-white rounded-xl shadow-sm border">
    <div class="p-6 border-b flex items-center justify-between">
        <h2 class="text-2xl font-bold">Plans</h2>
        <a href="/admin/plans/create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-1"></i> Create Plan
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">CPU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Memory</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Disk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($plans as $p): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-semibold"><?= htmlspecialchars($p['name']) ?></td>
                    <td class="px-6 py-4">$<?= number_format((float)$p['price'], 2) ?>/mo</td>
                    <td class="px-6 py-4"><?= $p['cpu'] ?>%</td>
                    <td class="px-6 py-4"><?= $p['memory'] ?>MB</td>
                    <td class="px-6 py-4"><?= $p['disk'] ?>MB</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full <?= $p['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                            <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="/admin/plans/<?= $p['id'] ?>/edit" class="text-blue-600 hover:text-blue-800 mr-3"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="/admin/plans/<?= $p['id'] ?>" class="inline" onsubmit="return confirm('Delete this plan?')">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>