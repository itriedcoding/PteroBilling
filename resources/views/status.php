<?php $pageTitle = 'Status'; ?>
<?php $content = ob_get_clean(); ob_start(); ?>
<div class="max-w-4xl mx-auto py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">System Status</h1>
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex items-center justify-between mb-4">
            <span class="text-lg font-medium">All Systems</span>
            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">Operational</span>
        </div>
        <div class="space-y-4">
            <div class="flex items-center justify-between py-3 border-b">
                <span>Billing Panel</span>
                <span class="text-green-600"><i class="fas fa-check-circle"></i> Operational</span>
            </div>
            <div class="flex items-center justify-between py-3 border-b">
                <span>Payment Processing</span>
                <span class="text-green-600"><i class="fas fa-check-circle"></i> Operational</span>
            </div>
            <div class="flex items-center justify-between py-3 border-b">
                <span>Pterodactyl API</span>
                <span class="text-green-600"><i class="fas fa-check-circle"></i> Operational</span>
            </div>
            <div class="flex items-center justify-between py-3">
                <span>Active Servers</span>
                <span class="text-blue-600 font-semibold"><?= $total_servers ?? 0 ?></span>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/app.php';
?>
