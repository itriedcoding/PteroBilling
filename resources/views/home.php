<?php $pageTitle = 'Home'; ?>
<div class="gradient-bg min-h-screen">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center">
                <i class="fas fa-server text-white text-2xl mr-2"></i>
                <span class="text-white font-bold text-xl"><?= htmlspecialchars($settings['site_name'] ?? 'PteroBilling') ?></span>
            </div>
            <div class="flex items-center space-x-4">
                <?php if (isset($user)): ?>
                    <a href="/billing" class="text-white hover:text-blue-200">Dashboard</a>
                    <?php if (($user['role'] ?? '') === 'admin'): ?>
                    <a href="/admin" class="text-white hover:text-blue-200">Admin</a>
                    <?php endif; ?>
                    <form method="POST" action="/auth/logout" class="inline">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                        <button type="submit" class="text-white hover:text-blue-200">Logout</button>
                    </form>
                <?php else: ?>
                    <a href="/auth/login" class="text-white hover:text-blue-200">Login</a>
                    <a href="/auth/register" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-semibold hover:bg-blue-50">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center">
            <h1 class="text-5xl font-bold text-white mb-6">Game Server Billing Made Easy</h1>
            <p class="text-xl text-blue-100 mb-10 max-w-2xl mx-auto">Professional billing panel for Pterodactyl. Accept payments via Stripe, PayPal, or credit system. Beautiful, secure, and feature-rich.</p>
            <div class="flex justify-center space-x-4">
                <?php if (!isset($user)): ?>
                <a href="/auth/register" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold text-lg hover:bg-blue-50 transition">Get Started</a>
                <?php else: ?>
                <a href="/billing" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold text-lg hover:bg-blue-50 transition">Go to Dashboard</a>
                <?php endif; ?>
                <a href="#plans" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold text-lg hover:bg-white hover:text-blue-600 transition">View Plans</a>
            </div>
        </div>

        <div class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white/10 backdrop-blur rounded-xl p-8 text-center stat-card">
                <i class="fas fa-shield-alt text-4xl text-blue-200 mb-4"></i>
                <h3 class="text-xl font-semibold text-white mb-2">Secure</h3>
                <p class="text-blue-100">Enterprise-grade security with CSRF protection, rate limiting, and encrypted payments.</p>
            </div>
            <div class="bg-white/10 backdrop-blur rounded-xl p-8 text-center stat-card">
                <i class="fas fa-credit-card text-4xl text-blue-200 mb-4"></i>
                <h3 class="text-xl font-semibold text-white mb-2">Multiple Payment Methods</h3>
                <p class="text-blue-100">Accept Stripe, PayPal, and credit-based payments out of the box.</p>
            </div>
            <div class="bg-white/10 backdrop-blur rounded-xl p-8 text-center stat-card">
                <i class="fas fa-server text-4xl text-blue-200 mb-4"></i>
                <h3 class="text-xl font-semibold text-white mb-2">Pterodactyl Integration</h3>
                <p class="text-blue-100">Seamlessly creates and manages servers on your Pterodactyl panel.</p>
            </div>
        </div>

        <?php if (!empty($plans)): ?>
        <div id="plans" class="mt-20">
            <h2 class="text-3xl font-bold text-white text-center mb-10">Available Plans</h2>
            <div class="grid grid-cols-1 md:grid-cols-<?= min(count($plans), 3) ?> gap-6">
                <?php foreach ($plans as $plan): ?>
                <div class="bg-white rounded-xl shadow-lg p-8 card-hover">
                    <h3 class="text-xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($plan['name']) ?></h3>
                    <div class="mb-4">
                        <span class="text-4xl font-bold text-blue-600">$<?= number_format((float)$plan['price'], 2) ?></span>
                        <span class="text-gray-500">/month</span>
                    </div>
                    <ul class="space-y-2 mb-6 text-sm text-gray-600">
                        <li><i class="fas fa-check text-green-500 mr-2"></i><?= $plan['cpu'] ?>% CPU</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i><?= $plan['memory'] ?>MB RAM</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i><?= $plan['disk'] ?>MB Disk</li>
                        <?php if ($plan['databases'] > 0): ?>
                        <li><i class="fas fa-check text-green-500 mr-2"></i><?= $plan['databases'] ?> Database<?= $plan['databases'] > 1 ? 's' : '' ?></li>
                        <?php endif; ?>
                        <?php if ($plan['backups'] > 0): ?>
                        <li><i class="fas fa-check text-green-500 mr-2"></i><?= $plan['backups'] ?> Backup<?= $plan['backups'] > 1 ? 's' : '' ?></li>
                        <?php endif; ?>
                    </ul>
                    <?php if (isset($user)): ?>
                    <a href="/servers/create" class="block w-full bg-blue-600 text-white text-center py-3 rounded-lg font-semibold hover:bg-blue-700 transition">Create Server</a>
                    <?php else: ?>
                    <a href="/auth/register" class="block w-full bg-blue-600 text-white text-center py-3 rounded-lg font-semibold hover:bg-blue-700 transition">Get Started</a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <footer class="py-8 text-center text-blue-200 text-sm">
        <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($settings['site_name'] ?? 'PteroBilling') ?>. All rights reserved.</p>
    </footer>
</div>
