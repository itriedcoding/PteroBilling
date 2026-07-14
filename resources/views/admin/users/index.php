<?php $pageTitle = 'Manage Users'; ob_start(); ?>
<div class="bg-white rounded-xl shadow-sm border">
    <div class="p-6 border-b">
        <h2 class="text-2xl font-bold">Users (<?= $total ?? 0 ?>)</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Credits</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($users as $u): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">#<?= $u['id'] ?></td>
                    <td class="px-6 py-4 font-semibold"><?= htmlspecialchars($u['username']) ?></td>
                    <td class="px-6 py-4 text-gray-500"><?= htmlspecialchars($u['email']) ?></td>
                    <td class="px-6 py-4">$<?= number_format((float)$u['credits'], 2) ?></td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full <?= $u['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' ?>">
                            <?= ucfirst($u['role']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-500"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                    <td class="px-6 py-4">
                        <a href="/admin/users/<?= $u['id'] ?>" class="text-blue-600 hover:text-blue-800"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>