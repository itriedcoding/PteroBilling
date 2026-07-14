<?php $pageTitle = 'Settings'; ob_start(); ?>
<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border mb-6">
        <div class="border-b" x-data="{ activeTab: 'general' }">
            <nav class="flex -mb-px px-6">
                <button @click="activeTab = 'general'" :class="activeTab === 'general' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-4 py-4 border-b-2 font-medium text-sm transition">
                    <i class="fas fa-globe mr-1"></i> General & Domain
                </button>
                <button @click="activeTab = 'payments'" :class="activeTab === 'payments' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-4 py-4 border-b-2 font-medium text-sm transition">
                    <i class="fas fa-credit-card mr-1"></i> Payments
                </button>
                <button @click="activeTab = 'pterodactyl'" :class="activeTab === 'pterodactyl' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-4 py-4 border-b-2 font-medium text-sm transition">
                    <i class="fas fa-server mr-1"></i> Pterodactyl
                </button>
                <button @click="activeTab = 'mail'" :class="activeTab === 'mail' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-4 py-4 border-b-2 font-medium text-sm transition">
                    <i class="fas fa-envelope mr-1"></i> Mail
                </button>
            </nav>
        </div>
    </div>

    <form method="POST" action="/admin/settings" class="space-y-6" x-data="{ activeTab: 'general' }">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
        <input type="hidden" name="_method" value="PUT">

        <!-- General & Domain -->
        <div x-show="activeTab === 'general'" class="bg-white rounded-xl shadow-sm border p-8">
            <h3 class="text-xl font-bold mb-6">General & Domain</h3>
            <div class="space-y-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-globe text-blue-500 mt-0.5 mr-3"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-1">Custom Domain Setup</p>
                            <p>To use a custom domain (e.g., billing.example.com):</p>
                            <ol class="list-decimal list-inside mt-2 space-y-1">
                                <li>Add an A record for your domain pointing to this server's IP</li>
                                <li>Enter the domain below</li>
                                <li>Run: <code class="bg-blue-100 px-1 rounded">sudo certbot --nginx -d your-domain.com</code></li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
                    <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? 'PteroBilling') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Panel URL</label>
                    <input type="url" name="site_url" value="<?= htmlspecialchars($settings['site_url'] ?? $_ENV['APP_URL'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="https://billing.example.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Custom Domain</label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 border border-r-0 border-gray-300 bg-gray-50 text-gray-500 rounded-l-lg text-sm">https://</span>
                        <input type="text" name="custom_domain" value="<?= htmlspecialchars($settings['custom_domain'] ?? $_ENV['APP_DOMAIN'] ?? '') ?>" class="flex-1 px-4 py-3 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-blue-500" placeholder="billing.example.com">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                    <select name="currency" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="USD" <?= ($settings['currency'] ?? 'USD') === 'USD' ? 'selected' : '' ?>>USD</option>
                        <option value="EUR" <?= ($settings['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR</option>
                        <option value="GBP" <?= ($settings['currency'] ?? '') === 'GBP' ? 'selected' : '' ?>>GBP</option>
                        <option value="CAD" <?= ($settings['currency'] ?? '') === 'CAD' ? 'selected' : '' ?>>CAD</option>
                        <option value="AUD" <?= ($settings['currency'] ?? '') === 'AUD' ? 'selected' : '' ?>>AUD</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Min Deposit ($)</label>
                        <input type="number" name="min_deposit" step="0.01" value="<?= $settings['min_deposit'] ?? 1.00 ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Deposit ($)</label>
                        <input type="number" name="max_deposit" step="0.01" value="<?= $settings['max_deposit'] ?? 1000.00 ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex items-center space-x-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="allow_registration" value="1" <?= ($settings['allow_registration'] ?? true) ? 'checked' : '' ?> class="rounded border-gray-300 text-blue-600">
                        <span class="ml-2 text-sm text-gray-700">Allow Registration</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="maintenance_mode" value="1" <?= ($settings['maintenance_mode'] ?? false) ? 'checked' : '' ?> class="rounded border-gray-300 text-red-600">
                        <span class="ml-2 text-sm text-gray-700">Maintenance Mode</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Payments -->
        <div x-show="activeTab === 'payments'" x-cloak class="bg-white rounded-xl shadow-sm border p-8">
            <h3 class="text-xl font-bold mb-6">Payment Settings</h3>
            <div class="space-y-8">
                <!-- Stripe -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center"><i class="fab fa-stripe text-2xl text-indigo-600 mr-3"></i><h4 class="text-lg font-semibold">Stripe</h4></div>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="stripe_enabled" value="1" <?= ($settings['stripe_enabled'] ?? false) ? 'checked' : '' ?> class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2 text-sm text-gray-700">Enabled</span>
                        </label>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Secret Key</label><input type="password" name="stripe_secret" value="<?= htmlspecialchars($settings['stripe_secret'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="sk_live_xxxxx"></div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Publishable Key</label><input type="text" name="stripe_public" value="<?= htmlspecialchars($settings['stripe_public'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="pk_live_xxxxx"></div>
                        </div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Webhook Signing Secret</label><input type="password" name="stripe_webhook_secret" value="<?= htmlspecialchars($settings['stripe_webhook_secret'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="whsec_xxxxx"></div>
                        <div class="bg-blue-50 border border-blue-200 rounded p-3 text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-1"></i> Webhook: <code class="font-mono bg-blue-100 px-1 rounded"><?= htmlspecialchars($_ENV['APP_URL'] ?? '') ?>/api/v1/payment/stripe</code><br>
                            Events: <code>checkout.session.completed</code>, <code>invoice.payment_succeeded</code>, <code>payment_intent.succeeded</code>
                        </div>
                    </div>
                </div>

                <!-- PayPal -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center"><i class="fab fa-paypal text-2xl text-blue-600 mr-3"></i><h4 class="text-lg font-semibold">PayPal</h4></div>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="paypal_enabled" value="1" <?= ($settings['paypal_enabled'] ?? false) ? 'checked' : '' ?> class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2 text-sm text-gray-700">Enabled</span>
                        </label>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Client ID</label><input type="text" name="paypal_client_id" value="<?= htmlspecialchars($settings['paypal_client_id'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Client Secret</label><input type="password" name="paypal_client_secret" value="<?= htmlspecialchars($settings['paypal_client_secret'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mode</label>
                            <select name="paypal_mode" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="sandbox" <?= ($settings['paypal_mode'] ?? '') === 'sandbox' ? 'selected' : '' ?>>Sandbox (Testing)</option>
                                <option value="live" <?= ($settings['paypal_mode'] ?? 'live') === 'live' ? 'selected' : '' ?>>Live (Production)</option>
                            </select>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded p-3 text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-1"></i> Webhook: <code class="font-mono bg-blue-100 px-1 rounded"><?= htmlspecialchars($_ENV['APP_URL'] ?? '') ?>/api/v1/payment/paypal</code><br>
                            Event: <code>PAYMENT.CAPTURE.COMPLETED</code>
                        </div>
                    </div>
                </div>

                <!-- Credits -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center"><i class="fas fa-coins text-2xl text-yellow-500 mr-3"></i><h4 class="text-lg font-semibold">Credit System</h4></div>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="credits_enabled" value="1" <?= ($settings['credits_enabled'] ?? true) ? 'checked' : '' ?> class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2 text-sm text-gray-700">Enabled</span>
                        </label>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600">Users add funds to their account balance and use credits to create/renew servers.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pterodactyl -->
        <div x-show="activeTab === 'pterodactyl'" x-cloak class="bg-white rounded-xl shadow-sm border p-8">
            <h3 class="text-xl font-bold mb-6">Pterodactyl Connection</h3>

            <!-- Sync Status Card -->
            <?php if (($ptero_status['connected'] ?? false)): ?>
            <div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-check text-green-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-green-800">Connected to Pterodactyl</h4>
                            <p class="text-sm text-green-600">Auto-sync is active</p>
                        </div>
                    </div>
                    <a href="/admin/settings/sync-ptero" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition">
                        <i class="fas fa-sync-alt mr-1"></i> Sync Now
                    </a>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center p-3 bg-white rounded-lg border">
                        <p class="text-2xl font-bold text-green-600"><?= $ptero_status['users'] ?? 0 ?></p>
                        <p class="text-xs text-gray-500">Users</p>
                    </div>
                    <div class="text-center p-3 bg-white rounded-lg border">
                        <p class="text-2xl font-bold text-green-600"><?= $ptero_status['nodes'] ?? 0 ?></p>
                        <p class="text-xs text-gray-500">Nodes</p>
                    </div>
                    <div class="text-center p-3 bg-white rounded-lg border">
                        <p class="text-2xl font-bold text-green-600"><?= $ptero_status['nests'] ?? 0 ?></p>
                        <p class="text-xs text-gray-500">Nests</p>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-6">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-yellow-800">Not Connected</h4>
                        <p class="text-sm text-yellow-600"><?= htmlspecialchars($ptero_status['message'] ?? 'Configure your Pterodactyl API key below') ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Panel URL</label>
                    <input type="url" name="ptero_url" value="<?= htmlspecialchars($settings['ptero_url'] ?? $_ENV['PTERODACTYL_URL'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="https://panel.example.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Application API Key</label>
                    <input type="password" name="ptero_api_key" value="<?= htmlspecialchars($settings['ptero_api_key'] ?? $_ENV['PTERODACTYL_API_KEY'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="ptla_xxxxxxxxxxxxxxxxxxxxxxxx">
                    <p class="mt-1 text-sm text-gray-500">Create in Admin > Application API in your Pterodactyl panel with full permissions.</p>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-1">How to get your API Key:</p>
                            <ol class="list-decimal list-inside space-y-1">
                                <li>Login to your Pterodactyl Panel as admin</li>
                                <li>Go to <strong>Admin</strong> > <strong>Application</strong></li>
                                <li>Click <strong>Create New</strong></li>
                                <li>Give it a name, select all permissions</li>
                                <li>Copy the key (starts with <code>ptla_</code>)</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div id="api-test-result" class="hidden"></div>
                <button type="button" onclick="testPteroApi()" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-medium hover:bg-gray-300 transition">
                    <i class="fas fa-plug mr-2"></i>Test Connection
                </button>
            </div>
        </div>

        <!-- Mail -->
        <div x-show="activeTab === 'mail'" x-cloak class="bg-white rounded-xl shadow-sm border p-8">
            <h3 class="text-xl font-bold mb-6">Email Configuration</h3>
            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">SMTP Host</label><input type="text" name="mail_host" value="<?= htmlspecialchars($settings['mail_host'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="smtp.gmail.com"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">SMTP Port</label><input type="number" name="mail_port" value="<?= $settings['mail_port'] ?? 587 ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Username</label><input type="text" name="mail_username" value="<?= htmlspecialchars($settings['mail_username'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Password</label><input type="password" name="mail_password" value="<?= htmlspecialchars($settings['mail_password'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Encryption</label>
                    <select name="mail_encryption" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="tls" <?= ($settings['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
                        <option value="ssl" <?= ($settings['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                        <option value="" <?= ($settings['mail_encryption'] ?? '') === '' ? 'selected' : '' ?>>None</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">From Address</label><input type="email" name="mail_from" value="<?= htmlspecialchars($settings['mail_from'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="noreply@example.com"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">From Name</label><input type="text" name="mail_from_name" value="<?= htmlspecialchars($settings['mail_from_name'] ?? 'PteroBilling') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></div>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                <i class="fas fa-save mr-2"></i>Save All Settings
            </button>
        </div>
    </form>
</div>

<script>
async function testPteroApi() {
    const url = document.querySelector('input[name="ptero_url"]').value;
    const key = document.querySelector('input[name="ptero_api_key"]').value;
    const resultDiv = document.getElementById('api-test-result');

    resultDiv.classList.remove('hidden');
    resultDiv.innerHTML = '<div class="flex items-center p-4 rounded-lg bg-blue-50 text-blue-800"><i class="fas fa-spinner fa-spin mr-3"></i>Testing connection...</div>';

    try {
        const response = await fetch('/setup/check-api', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `url=${encodeURIComponent(url)}&key=${encodeURIComponent(key)}`
        });
        const result = await response.json();
        if (result.success) {
            resultDiv.innerHTML = `<div class="flex items-center p-4 rounded-lg bg-green-50 text-green-800"><i class="fas fa-check-circle mr-3"></i>${result.message}</div>`;
        } else {
            resultDiv.innerHTML = `<div class="flex items-center p-4 rounded-lg bg-red-50 text-red-800"><i class="fas fa-times-circle mr-3"></i>${result.message}</div>`;
        }
    } catch (e) {
        resultDiv.innerHTML = '<div class="flex items-center p-4 rounded-lg bg-red-50 text-red-800"><i class="fas fa-times-circle mr-3"></i>Connection failed.</div>';
    }
}
</script>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>
