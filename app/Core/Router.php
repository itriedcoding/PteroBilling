<?php
declare(strict_types=1);

namespace App\Core;

use Slim\App;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\BillingController;
use App\Controllers\ServerController;
use App\Controllers\SetupController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\PlanController;
use App\Controllers\Admin\UserController;
use App\Controllers\Admin\SettingsController;
use App\Controllers\Admin\InvoiceController;
use App\Controllers\Api\PaymentController;
use App\Middleware\AuthMiddleware;
use App\Middleware\AdminMiddleware;

class Router
{
    public static function load(App $app): void
    {
        $app->get('/', [HomeController::class, 'index'])->setName('home');
        $app->get('/status', [HomeController::class, 'status'])->setName('status');

        $app->group('/setup', function ($group) {
            $group->get('', [SetupController::class, 'index'])->setName('setup.index');
            $group->post('/{step}', [SetupController::class, 'step']);
            $group->post('/complete', [SetupController::class, 'complete']);
            $group->post('/check-api', [SetupController::class, 'checkApi']);
        });

        $app->group('/auth', function ($group) {
            $group->get('/login', [AuthController::class, 'showLogin'])->setName('auth.login');
            $group->post('/login', [AuthController::class, 'login']);
            $group->get('/register', [AuthController::class, 'showRegister'])->setName('auth.register');
            $group->post('/register', [AuthController::class, 'register']);
            $group->post('/logout', [AuthController::class, 'logout'])->setName('auth.logout');
            $group->get('/forgot-password', [AuthController::class, 'showForgotPassword'])->setName('auth.forgot');
            $group->post('/forgot-password', [AuthController::class, 'forgotPassword']);
            $group->get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->setName('auth.reset');
            $group->post('/reset-password', [AuthController::class, 'resetPassword']);
        });

        $app->group('/billing', function ($group) {
            $group->get('', [BillingController::class, 'index'])->setName('billing.index');
            $group->get('/add-funds', [BillingController::class, 'addFunds'])->setName('billing.addFunds');
            $group->post('/add-funds', [BillingController::class, 'processAddFunds']);
            $group->get('/invoices', [BillingController::class, 'invoices'])->setName('billing.invoices');
            $group->get('/invoice/{id}', [BillingController::class, 'invoice'])->setName('billing.invoice');
            $group->get('/payment-methods', [BillingController::class, 'paymentMethods'])->setName('billing.paymentMethods');
            $group->post('/payment-methods', [BillingController::class, 'addPaymentMethod']);
            $group->delete('/payment-methods/{id}', [BillingController::class, 'deletePaymentMethod']);
            $group->get('/transactions', [BillingController::class, 'transactions'])->setName('billing.transactions');
        })->add(new AuthMiddleware());

        $app->group('/servers', function ($group) {
            $group->get('', [ServerController::class, 'index'])->setName('servers.index');
            $group->get('/create', [ServerController::class, 'create'])->setName('servers.create');
            $group->post('/create', [ServerController::class, 'store']);
            $group->get('/{id}', [ServerController::class, 'show'])->setName('servers.show');
            $group->post('/{id}/renew', [ServerController::class, 'renew']);
            $group->delete('/{id}', [ServerController::class, 'destroy']);
        })->add(new AuthMiddleware());

        $app->group('/admin', function ($group) {
            $group->get('', [DashboardController::class, 'index'])->setName('admin.dashboard');
            $group->get('/plans', [PlanController::class, 'index'])->setName('admin.plans');
            $group->get('/plans/create', [PlanController::class, 'create'])->setName('admin.plans.create');
            $group->post('/plans', [PlanController::class, 'store']);
            $group->get('/plans/{id}/edit', [PlanController::class, 'edit'])->setName('admin.plans.edit');
            $group->put('/plans/{id}', [PlanController::class, 'update']);
            $group->delete('/plans/{id}', [PlanController::class, 'destroy']);
            $group->get('/users', [UserController::class, 'index'])->setName('admin.users');
            $group->get('/users/{id}', [UserController::class, 'show'])->setName('admin.users.show');
            $group->put('/users/{id}', [UserController::class, 'update']);
            $group->get('/invoices', [InvoiceController::class, 'index'])->setName('admin.invoices');
            $group->get('/settings', [SettingsController::class, 'index'])->setName('admin.settings');
            $group->put('/settings', [SettingsController::class, 'update']);
            $group->get('/api-keys', [SettingsController::class, 'apiKeys'])->setName('admin.apiKeys');
            $group->post('/api-keys', [SettingsController::class, 'createApiKey']);
            $group->delete('/api-keys/{id}', [SettingsController::class, 'deleteApiKey']);
        })->add(new AdminMiddleware())->add(new AuthMiddleware());

        $app->group('/api/v1', function ($group) {
            $group->post('/payment/stripe', [PaymentController::class, 'stripeWebhook']);
            $group->post('/payment/paypal', [PaymentController::class, 'paypalWebhook']);
            $group->get('/payment/status/{id}', [PaymentController::class, 'paymentStatus']);
        });
    }
}
