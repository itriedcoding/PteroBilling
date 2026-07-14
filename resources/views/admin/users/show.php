<?php $pageTitle = 'User: ' . ($target_user['username'] ?? ''); ob_start(); ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border p-8">
        <h2 class="text-2xl font-bold mb-6">Edit User: <?= htmlspecialchars($target_user['username'] ?? '') ?></h2>
        <form method="POST" action="/admin/users/<?= $target_user['id'] ?? '' ?>" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            <input type="hidden" name="_method" value="PUT">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($target_user['username'] ?? '') ?>" required class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($target_user['email'] ?? '') ?>" required class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select name="role" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="user" <?= ($target_user['role'] ?? '') === 'user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= ($target_user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">New Password (leave blank to keep current)</label>
                <input type="password" name="password" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500">Credits: <strong>$<?= number_format((float)($target_user['credits'] ?? 0), 2) ?></strong></p>
                <p class="text-sm text-gray-500">Joined: <strong><?= date('M j, Y', strtotime($target_user['created_at'] ?? '')) ?></strong></p>
            </div>
            <div class="flex space-x-4">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700">Update User</button>
                <a href="/admin/users" class="flex-1 text-center bg-gray-200 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-300">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>