<?php $pageTitle = 'Register'; ?>
<div class="min-h-screen gradient-bg flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <i class="fas fa-server text-white text-4xl mb-4"></i>
            <h2 class="text-3xl font-bold text-white">Create Account</h2>
            <p class="text-blue-200 mt-2">Join PteroBilling today</p>
        </div>
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <?php if (!empty($error)): ?>
            <div class="mb-4 rounded-lg bg-red-50 p-4 text-red-800 text-sm">
                <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
            <form method="POST" action="/auth/register" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" name="username" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Choose a username">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="you@example.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required minlength="8"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Min. 8 characters">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Repeat password">
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                    Create Account
                </button>
            </form>
            <p class="mt-6 text-center text-sm text-gray-600">
                Already have an account? <a href="/auth/login" class="text-blue-600 hover:text-blue-800 font-semibold">Sign in</a>
            </p>
        </div>
    </div>
</div>
