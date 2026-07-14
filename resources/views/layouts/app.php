<?php $pageTitle = $pageTitle ?? 'PteroBilling'; ?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PteroBilling - Game Server Billing Panel">
    <title><?= htmlspecialchars($pageTitle) ?> - PteroBilling</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: { 50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' },
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .sidebar-link { transition: all 0.2s ease; }
        .sidebar-link:hover, .sidebar-link.active { background: rgba(255,255,255,0.1); }
        .stat-card { backdrop-filter: blur(10px); }
    </style>
</head>
<body class="h-full bg-gray-50">
    <?php if (isset($user)): ?>
    <div class="min-h-full" x-data="{ sidebarOpen: false }">
        <!-- Mobile sidebar -->
        <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 lg:hidden">
            <div class="fixed inset-0 bg-gray-600 bg-opacity-75" @click="sidebarOpen = false"></div>
            <div class="fixed inset-y-0 left-0 flex w-64 flex-col bg-gray-900">
                <div class="flex h-16 items-center justify-center border-b border-gray-700">
                    <i class="fas fa-server text-blue-400 text-2xl mr-2"></i>
                    <span class="text-white font-bold text-xl">PteroBilling</span>
                </div>
                <nav class="flex-1 space-y-1 px-3 py-4">
                    <a href="/billing" class="sidebar-link flex items-center px-3 py-2 text-sm text-gray-300 rounded-lg">
                        <i class="fas fa-wallet w-6"></i> Billing
                    </a>
                    <a href="/servers" class="sidebar-link flex items-center px-3 py-2 text-sm text-gray-300 rounded-lg">
                        <i class="fas fa-server w-6"></i> Servers
                    </a>
                    <a href="/billing/invoices" class="sidebar-link flex items-center px-3 py-2 text-sm text-gray-300 rounded-lg">
                        <i class="fas fa-file-invoice w-6"></i> Invoices
                    </a>
                    <a href="/billing/transactions" class="sidebar-link flex items-center px-3 py-2 text-sm text-gray-300 rounded-lg">
                        <i class="fas fa-exchange-alt w-6"></i> Transactions
                    </a>
                </nav>
            </div>
        </div>

        <!-- Desktop sidebar -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col">
            <div class="flex min-h-0 flex-1 flex-col bg-gray-900">
                <div class="flex h-16 items-center justify-center border-b border-gray-700">
                    <i class="fas fa-server text-blue-400 text-2xl mr-2"></i>
                    <span class="text-white font-bold text-xl">PteroBilling</span>
                </div>
                <div class="flex flex-1 flex-col overflow-y-auto pt-5 pb-4">
                    <nav class="flex-1 space-y-1 px-3">
                        <?php if (($user['role'] ?? '') === 'admin'): ?>
                        <a href="/admin" class="sidebar-link flex items-center px-3 py-2 text-sm text-gray-300 rounded-lg">
                            <i class="fas fa-tachometer-alt w-6"></i> Admin Dashboard
                        </a>
                        <a href="/admin/plans" class="sidebar-link flex items-center px-3 py-2 text-sm text-gray-300 rounded-lg">
                            <i class="fas fa-box w-6"></i> Plans
                        </a>
                        <a href="/admin/users" class="sidebar-link flex items-center px-3 py-2 text-sm text-gray-300 rounded-lg">
                            <i class="fas fa-users w-6"></i> Users
                        </a>
                        <a href="/admin/invoices" class="sidebar-link flex items-center px-3 py-2 text-sm text-gray-300 rounded-lg">
                            <i class="fas fa-file-invoice w-6"></i> Invoices
                        </a>
                        <a href="/admin/settings" class="sidebar-link flex items-center px-3 py-2 text-sm text-gray-300 rounded-lg">
                            <i class="fas fa-cog w-6"></i> Settings
                        </a>
                        <div class="border-t border-gray-700 my-3"></div>
                        <?php endif; ?>
                        <a href="/billing" class="sidebar-link flex items-center px-3 py-2 text-sm text-gray-300 rounded-lg">
                            <i class="fas fa-wallet w-6"></i> Billing
                        </a>
                        <a href="/billing/add-funds" class="sidebar-link flex items-center px-3 py-2 text-sm text-gray-300 rounded-lg">
                            <i class="fas fa-plus-circle w-6"></i> Add Funds
                        </a>
                        <a href="/servers" class="sidebar-link flex items-center px-3 py-2 text-sm text-gray-300 rounded-lg">
                            <i class="fas fa-server w-6"></i> Servers
                        </a>
                        <a href="/billing/invoices" class="sidebar-link flex items-center px-3 py-2 text-sm text-gray-300 rounded-lg">
                            <i class="fas fa-file-invoice w-6"></i> Invoices
                        </a>
                        <a href="/billing/payment-methods" class="sidebar-link flex items-center px-3 py-2 text-sm text-gray-300 rounded-lg">
                            <i class="fas fa-credit-card w-6"></i> Payment Methods
                        </a>
                    </nav>
                </div>
                <div class="border-t border-gray-700 p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center">
                                <span class="text-white font-semibold"><?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?></span>
                            </div>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-white"><?= htmlspecialchars($user['username'] ?? 'User') ?></p>
                            <p class="text-xs text-gray-400">$<?= number_format((float)($user['credits'] ?? 0), 2) ?> credits</p>
                        </div>
                        <form method="POST" action="/auth/logout">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                            <button type="submit" class="text-gray-400 hover:text-white"><i class="fas fa-sign-out-alt"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="lg:pl-64 flex flex-col flex-1">
            <div class="sticky top-0 z-10 flex h-16 flex-shrink-0 bg-white shadow">
                <button @click="sidebarOpen = true" class="px-4 text-gray-500 lg:hidden">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div class="flex flex-1 justify-between px-4">
                    <div class="flex items-center">
                        <h1 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
                    </div>
                </div>
            </div>
            <main class="flex-1">
                <div class="py-6 px-4 sm:px-6 lg:px-8">
                    <?php if (!empty($success)): ?>
                    <div class="mb-4 rounded-lg bg-green-50 p-4 text-green-800 border border-green-200">
                        <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success) ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($error)): ?>
                    <div class="mb-4 rounded-lg bg-red-50 p-4 text-red-800 border border-red-200">
                        <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?>
                    </div>
                    <?php endif; ?>
                    <?= $content ?? '' ?>
                </div>
            </main>
        </div>
    </div>
    <?php else: ?>
    <?= $content ?? '' ?>
    <?php endif; ?>
</body>
</html>
