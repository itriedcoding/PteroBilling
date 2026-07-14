<?php $pageTitle = 'Setup Wizard'; ?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - PteroBilling</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); }
        .step-active { background: #3b82f6; color: white; }
        .step-complete { background: #10b981; color: white; }
        .step-pending { background: #e5e7eb; color: #9ca3af; }
        .fade-in { animation: fadeIn 0.3s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="h-full bg-gray-50">
    <div class="min-h-full gradient-bg flex items-center justify-center py-12 px-4">
        <div class="max-w-2xl w-full">
            <div class="text-center mb-8">
                <i class="fas fa-server text-white text-4xl mb-3"></i>
                <h1 class="text-3xl font-bold text-white">PteroBilling Setup</h1>
                <p class="text-blue-200 mt-2">Configure your billing panel in a few steps</p>
            </div>

            <div class="flex items-center justify-center mb-8 overflow-x-auto">
                <?php
                $steps = [
                    1 => ['icon' => 'globe', 'label' => 'Domain'],
                    2 => ['icon' => 'server', 'label' => 'Pterodactyl'],
                    3 => ['icon' => 'credit-card', 'label' => 'Stripe'],
                    4 => ['icon' => 'coins', 'label' => 'Credits'],
                    5 => ['icon' => 'paypal', 'label' => 'PayPal'],
                    6 => ['icon' => 'check', 'label' => 'Done'],
                ];
                ?>
                <?php foreach ($steps as $num => $s): ?>
                <div class="flex items-center">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-semibold <?= $num < $step ? 'step-complete' : ($num === $step ? 'step-active' : 'step-pending') ?>">
                        <?php if ($num < $step): ?>
                            <i class="fas fa-check"></i>
                        <?php else: ?>
                            <i class="fas fa-<?= $s['icon'] ?>"></i>
                        <?php endif; ?>
                    </div>
                    <span class="ml-1 text-xs <?= $num <= $step ? 'text-white' : 'text-blue-200' ?> hidden sm:block"><?= $s['label'] ?></span>
                    <?php if ($num < 6): ?>
                    <div class="w-6 h-0.5 mx-1 <?= $num < $step ? 'bg-green-400' : 'bg-blue-300/30' ?>"></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-8 fade-in">
                <?php if (!empty($error)): ?>
                <div class="mb-6 rounded-lg bg-red-50 p-4 text-red-800 border border-red-200">
                    <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <?php if ($step === 1): ?>
                <h2 class="text-2xl font-bold mb-2">Domain & General</h2>
                <p class="text-gray-500 text-sm mb-6">Set up your billing panel URL and custom domain.</p>
                <form method="POST" action="/setup/1" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
                        <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? 'PteroBilling') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Panel URL</label>
                        <input type="url" name="panel_url" required value="<?= htmlspecialchars($settings['panel_url'] ?? 'https://billing.example.com') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="https://billing.example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Custom Domain</label>
                        <input type="text" name="panel_domain" value="<?= htmlspecialchars($settings['panel_domain'] ?? 'billing.example.com') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="billing.example.com">
                        <p class="mt-1 text-sm text-gray-500">Add an A record for this domain pointing to your server IP.</p>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                        Continue <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </form>

                <?php elseif ($step === 2): ?>
                <h2 class="text-2xl font-bold mb-2">Pterodactyl Connection</h2>
                <p class="text-gray-500 text-sm mb-6">Connect to your Pterodactyl panel for server management.</p>
                <form method="POST" action="/setup/2" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pterodactyl Panel URL</label>
                        <input type="url" name="ptero_url" required value="<?= htmlspecialchars($settings['ptero_url'] ?? 'https://panel.example.com') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="https://panel.example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Application API Key</label>
                        <input type="password" name="ptero_api_key" required value="<?= htmlspecialchars($settings['ptero_api_key'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="ptla_xxxxxxxxxxxxxxxxxxxxxxxx">
                        <p class="mt-1 text-sm text-gray-500">Find this in Admin > Application API in your Pterodactyl panel.</p>
                    </div>
                    <div id="api-status" class="hidden">
                        <div class="flex items-center p-4 rounded-lg" id="api-status-box">
                            <i class="mr-3" id="api-status-icon"></i>
                            <span id="api-status-text"></span>
                        </div>
                    </div>
                    <button type="button" onclick="testApiConnection()" class="w-full bg-gray-100 text-gray-700 py-2 rounded-lg font-medium hover:bg-gray-200 transition">
                        <i class="fas fa-plug mr-2"></i>Test Connection
                    </button>
                    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                        Continue <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </form>

                <?php elseif ($step === 3): ?>
                <h2 class="text-2xl font-bold mb-2">Stripe (Credit/Debit Cards)</h2>
                <p class="text-gray-500 text-sm mb-6">Accept card payments via Stripe. You can skip this and enable later.</p>
                <form method="POST" action="/setup/3" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">Get your keys from <a href="https://dashboard.stripe.com/apikeys" target="_blank" class="text-blue-600 underline">Stripe Dashboard</a>.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Secret Key</label>
                        <input type="password" name="stripe_secret" value="<?= htmlspecialchars($settings['stripe_secret'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="sk_live_xxxxx">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Publishable Key</label>
                        <input type="text" name="stripe_public" value="<?= htmlspecialchars($settings['stripe_public'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="pk_live_xxxxx">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Webhook Signing Secret</label>
                        <input type="password" name="stripe_webhook" value="<?= htmlspecialchars($settings['stripe_webhook'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="whsec_xxxxx">
                        <p class="mt-1 text-sm text-gray-500">Webhook URL: <code class="bg-gray-100 px-1 rounded text-xs"><?= htmlspecialchars($settings['panel_url'] ?? 'https://billing.example.com') ?>/api/v1/payment/stripe</code></p>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-sm text-yellow-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        Events: <code>checkout.session.completed</code>, <code>invoice.payment_succeeded</code>, <code>payment_intent.succeeded</code>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                        Save & Continue <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                    <a href="/setup/4" class="block text-center text-gray-500 hover:text-gray-700 text-sm py-2">Skip for now <i class="fas fa-arrow-right ml-1"></i></a>
                </form>

                <?php elseif ($step === 4): ?>
                <h2 class="text-2xl font-bold mb-2">Credit System</h2>
                <p class="text-gray-500 text-sm mb-6">Built-in credit system. Users add funds and use credits to buy servers.</p>
                <form method="POST" action="/setup/4" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-coins text-green-500 mt-0.5 mr-3"></i>
                            <div class="text-sm text-green-800">
                                <p class="font-semibold mb-1">Credit System Enabled by Default</p>
                                <p>Users can add funds and use credits to create/renew servers. This is the core billing mechanism.</p>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Deposit ($)</label>
                            <input type="number" name="min_deposit" step="0.01" value="<?= $settings['min_deposit'] ?? 1.00 ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Maximum Deposit ($)</label>
                            <input type="number" name="max_deposit" step="0.01" value="<?= $settings['max_deposit'] ?? 1000.00 ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                        <select name="currency" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="USD" <?= ($settings['currency'] ?? 'USD') === 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                            <option value="EUR" <?= ($settings['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                            <option value="GBP" <?= ($settings['currency'] ?? '') === 'GBP' ? 'selected' : '' ?>>GBP - British Pound</option>
                            <option value="CAD" <?= ($settings['currency'] ?? '') === 'CAD' ? 'selected' : '' ?>>CAD - Canadian Dollar</option>
                            <option value="AUD" <?= ($settings['currency'] ?? '') === 'AUD' ? 'selected' : '' ?>>AUD - Australian Dollar</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                        Continue <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </form>

                <?php elseif ($step === 5): ?>
                <h2 class="text-2xl font-bold mb-2">PayPal</h2>
                <p class="text-gray-500 text-sm mb-6">Accept PayPal payments. You can skip this and enable later.</p>
                <form method="POST" action="/setup/5" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">Get credentials from <a href="https://developer.paypal.com/dashboard/applications" target="_blank" class="text-blue-600 underline">PayPal Developer Dashboard</a>.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Client ID</label>
                        <input type="text" name="paypal_client_id" value="<?= htmlspecialchars($settings['paypal_client_id'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Client Secret</label>
                        <input type="password" name="paypal_secret" value="<?= htmlspecialchars($settings['paypal_secret'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mode</label>
                        <select name="paypal_mode" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="sandbox" <?= ($settings['paypal_mode'] ?? '') === 'sandbox' ? 'selected' : '' ?>>Sandbox (Testing)</option>
                            <option value="live" <?= ($settings['paypal_mode'] ?? 'live') === 'live' ? 'selected' : '' ?>>Live (Production)</option>
                        </select>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-sm text-yellow-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        Webhook URL: <code class="bg-gray-100 px-1 rounded text-xs"><?= htmlspecialchars($settings['panel_url'] ?? 'https://billing.example.com') ?>/api/v1/payment/paypal</code><br>
                        Event: <code>PAYMENT.CAPTURE.COMPLETED</code>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                        Save & Continue <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                    <a href="/setup/6" class="block text-center text-gray-500 hover:text-gray-700 text-sm py-2">Skip for now <i class="fas fa-arrow-right ml-1"></i></a>
                </form>

                <?php elseif ($step === 6): ?>
                <div class="text-center py-8">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-check text-green-600 text-3xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold mb-4">Setup Complete!</h2>
                    <p class="text-gray-600 mb-6 max-w-md mx-auto">Your PteroBilling panel is ready. You can manage all settings from the admin panel at any time.</p>

                    <div class="bg-gray-50 rounded-lg p-6 mb-8 text-left max-w-md mx-auto">
                        <h3 class="font-semibold mb-3">What's Next?</h3>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li><i class="fas fa-check text-green-500 mr-2"></i> Create your admin account</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i> Set up server plans</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i> Configure Pterodactyl nest/egg IDs</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i> Test with a small payment</li>
                        </ul>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="/auth/register" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                            <i class="fas fa-user-plus mr-2"></i>Create Admin Account
                        </a>
                        <a href="/admin/settings" class="bg-gray-200 text-gray-700 px-8 py-3 rounded-lg font-semibold hover:bg-gray-300 transition">
                            <i class="fas fa-cog mr-2"></i>Settings
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($step > 1 && $step < 6): ?>
            <div class="text-center mt-4">
                <a href="/setup/<?= $step - 1 ?>" class="text-blue-200 hover:text-white text-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    async function testApiConnection() {
        const url = document.querySelector('input[name="ptero_url"]').value;
        const key = document.querySelector('input[name="ptero_api_key"]').value;
        const statusDiv = document.getElementById('api-status');
        const statusBox = document.getElementById('api-status-box');
        const statusIcon = document.getElementById('api-status-icon');
        const statusText = document.getElementById('api-status-text');

        statusDiv.classList.remove('hidden');
        statusBox.className = 'flex items-center p-4 rounded-lg bg-blue-50 text-blue-800';
        statusIcon.className = 'fas fa-spinner fa-spin mr-3';
        statusText.textContent = 'Testing connection...';

        try {
            const response = await fetch('/setup/check-api', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `url=${encodeURIComponent(url)}&key=${encodeURIComponent(key)}`
            });
            const result = await response.json();

            if (result.success) {
                statusBox.className = 'flex items-center p-4 rounded-lg bg-green-50 text-green-800';
                statusIcon.className = 'fas fa-check-circle mr-3';
            } else {
                statusBox.className = 'flex items-center p-4 rounded-lg bg-red-50 text-red-800';
                statusIcon.className = 'fas fa-times-circle mr-3';
            }
            statusText.textContent = result.message;
        } catch (e) {
            statusBox.className = 'flex items-center p-4 rounded-lg bg-red-50 text-red-800';
            statusIcon.className = 'fas fa-times-circle mr-3';
            statusText.textContent = 'Connection failed. Please check your URL and try again.';
        }
    }
    </script>
</body>
</html>
