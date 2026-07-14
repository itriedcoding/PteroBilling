<?php $pageTitle = 'Settings'; ob_start(); ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border p-8">
        <h2 class="text-2xl font-bold mb-6">Panel Settings</h2>
        <form method="POST" action="/admin/settings" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            <input type="hidden" name="_method" value="PUT">
            <h3 class="text-lg font-semibold border-b pb-2">Pterodactyl</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Panel URL</label>
                <input type="url" name="ptero_url" value="<?= htmlspecialchars($settings['ptero_url'] ?? '') ?>" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="https://panel.example.com">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                <input type="password" name="ptero_api_key" value="<?= htmlspecialchars($settings['ptero_api_key'] ?? '') ?>" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <h3 class="text-lg font-semibold border-b pb-2">Stripe</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Secret Key</label>
                    <input type="password" name="stripe_key" value="<?= htmlspecialchars($settings['stripe_key'] ?? '') ?>" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Public Key</label>
                    <input type="text" name="stripe_public_key" value="<?= htmlspecialchars($settings['stripe_public_key'] ?? '') ?>" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Webhook Secret</label>
                <input type="password" name="stripe_webhook_secret" value="<?= htmlspecialchars($settings['stripe_webhook_secret'] ?? '') ?>" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <h3 class="text-lg font-semibold border-b pb-2">PayPal</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client ID</label>
                    <input type="text" name="paypal_client_id" value="<?= htmlspecialchars($settings['paypal_client_id'] ?? '') ?>" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client Secret</label>
                    <input type="password" name="paypal_client_secret" value="<?= htmlspecialchars($settings['paypal_client_secret'] ?? '') ?>" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <h3 class="text-lg font-semibold border-b pb-2">Mail</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SMTP Host</label>
                    <input type="text" name="mail_host" value="<?= htmlspecialchars($settings['mail_host'] ?? '') ?>" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SMTP Port</label>
                    <input type="number" name="mail_port" value="<?= $settings['mail_port'] ?? 587 ?>" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700">Save Settings</button>
        </form>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>