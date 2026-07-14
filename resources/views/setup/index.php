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
        .toggle-switch { transition: background-color 0.3s; }
        .toggle-switch.active { background-color: #3b82f6; }
        .toggle-switch .toggle-dot { transition: transform 0.3s; }
        .toggle-switch.active .toggle-dot { transform: translateX(1.25rem); }
    </style>
</head>
<body class="h-full bg-gray-50">
    <div class="min-h-full gradient-bg flex items-center justify-center py-12 px-4">
        <div class="max-w-2xl w-full">
            <!-- Logo -->
            <div class="text-center mb-8">
                <i class="fas fa-server text-white text-4xl mb-3"></i>
                <h1 class="text-3xl font-bold text-white">PteroBilling Setup</h1>
                <p class="text-blue-200 mt-2">Configure your billing panel in a few steps</p>
            </div>

            <!-- Progress Steps -->
            <div class="flex items-center justify-center mb-8">
                <?php
                $steps = [
                    1 => ['icon' => 'globe', 'label' => 'Domain'],
                    2 => ['icon' => 'server', 'label' => 'Pterodactyl'],
                    3 => ['icon' => 'credit-card', 'label' => 'Stripe'],
                    4 => ['icon' => 'paypal', 'label' => 'PayPal'],
                    5 => ['icon' => 'check', 'label' => 'Complete'],
                ];
                ?>
                <?php foreach ($steps as $num => $s): ?>
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold <?= $num < $step ? 'step-complete' : ($num === $step ? 'step-active' : 'step-pending') ?>">
                        <?php if ($num < $step): ?>
                            <i class="fas fa-check"></i>
                        <?php else: ?>
                            <i class="fas fa-<?= $s['icon'] ?>"></i>
                        <?php endif; ?>
                    </div>
                    <span class="ml-1 text-xs <?= $num <= $step ? 'text-white' : 'text-blue-200' ?> hidden sm:block"><?= $s['label'] ?></span>
                    <?php if ($num < 5): ?>
                    <div class="w-8 h-0.5 mx-2 <?= $num < $step ? 'bg-green-400' : 'bg-blue-300/30' ?>"></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Step Content -->
            <div class="bg-white rounded-2xl shadow-xl p-8 fade-in">
                <?php if (!empty($error)): ?>
                <div class="mb-6 rounded-lg bg-red-50 p-4 text-red-800 border border-red-200">
                    <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <?php if ($step === 1): ?>
                <!-- Step 1: Domain & General -->
                <h2 class="text-2xl font-bold mb-6">Domain & General Settings</h2>
                <form method="POST" action="/setup/1" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
                        <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? 'PteroBilling') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Panel URL</label>
                        <input type="url" name="panel_url" required value="<?= htmlspecialchars($settings['panel_url'] ?? 'https://billing.example.com') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="https://billing.example.com">
                        <p class="mt-1 text-sm text-gray-500">The full URL where your billing panel will be accessible.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Custom Domain</label>
                        <input type="text" name="panel_domain" value="<?= htmlspecialchars($settings['panel_domain'] ?? 'billing.example.com') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="billing.example.com">
                        <p class="mt-1 text-sm text-gray-500">Your custom domain (e.g., billing.example.com). Point this domain to your server's IP.</p>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                            <div class="text-sm text-blue-800">
                                <p class="font-semibold mb-1">DNS Setup Required</p>
                                <p>Add an A record for <strong><?= htmlspecialchars($settings['panel_domain'] ?? 'billing.example.com') ?></strong> pointing to your server's IP address.</p>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                        Continue <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </form>

                <?php elseif ($step === 2): ?>
                <!-- Step 2: Pterodactyl -->
                <h2 class="text-2xl font-bold mb-6">Pterodactyl Connection</h2>
                <form method="POST" action="/setup/2" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pterodactyl Panel URL</label>
                        <input type="url" name="ptero_url" required value="<?= htmlspecialchars($settings['ptero_url'] ?? 'https://panel.example.com') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="https://panel.example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Application API Key</label>
                        <input type="password" name="ptero_api_key" required value="<?= htmlspecialchars($settings['ptero_api_key'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="ptla_xxxxxxxxxxxxxxxxxxxxxxxx">
                        <p class="mt-1 text-sm text-gray-500">Create this in Admin > Application API in your Pterodactyl panel. Give it full permissions.</p>
                    </div>
                    <div id="api-status" class="hidden">
                        <div class="flex items-center p-4 rounded-lg" id="api-status-box">
                            <i class="mr-3" id="api-status-icon"></i>
                            <span id="api-status-text"></span>
                        </div>
                    </div>
                    <button type="button" onclick="testApiConnection()" class="w-full bg-gray-200 text-gray-700 py-2 rounded-lg font-medium hover:bg-gray-300 transition">
                        <i class="fas fa-plug mr-2"></i>Test Connection
                    </button>
                    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                        Continue <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </form>

                <?php elseif ($step === 3): ?>
                <!-- Step 3: Stripe -->
                <h2 class="text-2xl font-bold mb-6">Stripe Configuration</h2>
                <form method="POST" action="/setup/3" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <p class="text-sm text-gray-600">Stripe allows you to accept credit/debit card payments. Get your keys from <a href="https://dashboard.stripe.com/apikeys" target="_blank" class="text-blue-600 underline">Stripe Dashboard</a>.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stripe Secret Key</label>
                        <input type="password" name="stripe_secret" value="<?= htmlspecialchars($settings['stripe_secret'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="sk_live_xxxxx">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stripe Publishable Key</label>
                        <input type="text" name="stripe_public" value="<?= htmlspecialchars($settings['stripe_public'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="pk_live_xxxxx">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Webhook Signing Secret</label>
                        <input type="password" name="stripe_webhook" value="<?= htmlspecialchars($settings['stripe_webhook'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="whsec_xxxxx">
                        <p class="mt-1 text-sm text-gray-500">Create a webhook in Stripe Dashboard pointing to: <code class="bg-gray-100 px-1 rounded"><?= htmlspecialchars($settings['panel_url'] ?? 'https://billing.example.com') ?>/api/v1/payment/stripe</code></p>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5 mr-3"></i>
                            <div class="text-sm text-yellow-800">
                                <p class="font-semibold mb-1">Webhook Events</p>
                                <p>Select these events: <code>checkout.session.completed</code>, <code>invoice.payment_succeeded</code>, <code>payment_intent.succeeded</code></p>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                        Continue <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </form>

                <?php elseif ($step === 4): ?>
                <!-- Step 4: PayPal -->
                <h2 class="text-2xl font-bold mb-6">PayPal Configuration</h2>
                <form method="POST" action="/setup/4" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <p class="text-sm text-gray-600">PayPal allows customers to pay with their PayPal account. Get your credentials from <a href="https://developer.paypal.com/dashboard/applications" target="_blank" class="text-blue-600 underline">PayPal Developer Dashboard</a>.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">PayPal Client ID</label>
                        <input type="text" name="paypal_client_id" value="<?= htmlspecialchars($settings['paypal_client_id'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="AxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxB">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">PayPal Client Secret</label>
                        <input type="password" name="paypal_secret" value="<?= htmlspecialchars($settings['paypal_secret'] ?? '') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="ExxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxF">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mode</label>
                        <select name="paypal_mode" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="sandbox" <?= ($settings['paypal_mode'] ?? '') === 'sandbox' ? 'selected' : '' ?>>Sandbox (Testing)</option>
                            <option value="live" <?= ($settings['paypal_mode'] ?? 'live') === 'live' ? 'selected' : '' ?>>Live (Production)</option>
                        </select>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5 mr-3"></i>
                            <div class="text-sm text-yellow-800">
                                <p class="font-semibold mb-1">PayPal Webhook</p>
                                <p>Create a webhook in PayPal Dashboard with URL: <code class="bg-gray-100 px-1 rounded"><?= htmlspecialchars($settings['panel_url'] ?? 'https://billing.example.com') ?>/api/v1/payment/paypal</code><br>Select event: <code>PAYMENT.CAPTURE.COMPLETED</code></p>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                        Continue <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </form>

                <?php elseif ($step === 5): ?>
                <!-- Step 5: Complete -->
                <div class="text-center py-8">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-check text-green-600 text-3xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold mb-4">Setup Complete!</h2>
                    <p class="text-gray-600 mb-8 max-w-md mx-auto">Your PteroBilling panel is configured and ready. You can now create plans and start accepting payments.</p>

                    <div class="bg-gray-50 rounded-lg p-6 mb-8 text-left max-w-md mx-auto">
                        <h3 class="font-semibold mb-3">What's Next?</h3>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li><i class="fas fa-check text-green-500 mr-2"></i> Create your admin account</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i> Set up server plans</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i> Configure Pterodactyl nest/egg IDs</li>
                            <li><i class="fas fa-check text-green-500 mr-2"></i> Test with a small payment</li>
                        </ul>
                    </div>

                    <a href="/auth/register" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                        <i class="fas fa-user-plus mr-2"></i>Create Admin Account
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($step > 1 && $step < 5): ?>
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
