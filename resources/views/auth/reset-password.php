<?php $pageTitle = 'Reset Password'; ?>
<div class="min-h-screen gradient-bg flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <i class="fas fa-lock text-white text-4xl mb-4"></i>
            <h2 class="text-3xl font-bold text-white">New Password</h2>
        </div>
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <form method="POST" action="/auth/reset-password" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                <input type="hidden" name="token" value="<?= $token ?? '' ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input type="password" name="password" required minlength="8"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                    Reset Password
                </button>
            </form>
        </div>
    </div>
</div>
