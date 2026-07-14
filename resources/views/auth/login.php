<?php $pageTitle = 'Login'; ?>
<div class="min-h-screen gradient-bg flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <i class="fas fa-server text-white text-4xl mb-4"></i>
            <h2 class="text-3xl font-bold text-white">Welcome Back</h2>
            <p class="text-blue-200 mt-2">Sign in to your PteroBilling account</p>
        </div>
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <?php if (!empty($error)): ?>
            <div class="mb-4 rounded-lg bg-red-50 p-4 text-red-800 text-sm">
                <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
            <form method="POST" action="/auth/login" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="you@example.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter your password">
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-gray-300 text-blue-600">
                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                    </label>
                    <a href="/auth/forgot-password" class="text-sm text-blue-600 hover:text-blue-800">Forgot password?</a>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                    Sign In
                </button>
            </form>
            <p class="mt-6 text-center text-sm text-gray-600">
                Don't have an account? <a href="/auth/register" class="text-blue-600 hover:text-blue-800 font-semibold">Register</a>
            </p>
        </div>
    </div>
</div>
