<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\Payment\StripeService;
use App\Services\Payment\PayPalService;
use App\Services\Payment\CreditService;

class BillingController
{
    private Session $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function index($request, $response)
    {
        $user = $request->getAttribute('user');
        $invoiceModel = new Invoice();
        $transactionModel = new Transaction();
        $creditService = new CreditService();

        $recentInvoices = $invoiceModel->getByUser($user['id'], 1, 5);
        $recentTransactions = $transactionModel->getByUser($user['id'], 1, 5);
        $balance = $creditService->getBalance($user['id']);

        $html = $this->render('client/billing/index', [
            'user' => $user,
            'balance' => $balance,
            'recent_invoices' => $recentInvoices['data'],
            'recent_transactions' => $recentTransactions['data'],
            'csrf_token' => $this->session->get('csrf_token'),
            'success' => $this->session->getFlash('success'),
            'error' => $this->session->getFlash('error'),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function addFunds($request, $response)
    {
        $user = $request->getAttribute('user');
        $stripeService = new StripeService();
        $paypalService = new PayPalService();

        $html = $this->render('client/billing/add-funds', [
            'user' => $user,
            'stripe_key' => $stripeService->getPublicKey(),
            'paypal_client_id' => $_ENV['PAYPAL_CLIENT_ID'] ?? '',
            'csrf_token' => $this->session->get('csrf_token'),
            'error' => $this->session->getFlash('error'),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function processAddFunds($request, $response)
    {
        $user = $request->getAttribute('user');
        $data = $request->getParsedBody();
        $method = $data['payment_method'] ?? 'stripe';
        $amount = (float)($data['amount'] ?? 0);

        if ($amount < 1 || $amount > 1000) {
            $this->session->flash('error', 'Invalid amount. Must be between $1 and $1000.');
            return $response->withHeader('Location', '/billing/add-funds')->withStatus(302);
        }

        if ($method === 'stripe') {
            $stripeService = new StripeService();
            $session = $stripeService->createCheckoutSession([
                'amount' => $amount,
                'user_id' => $user['id'],
                'type' => 'credit_purchase',
                'description' => 'Add $' . number_format($amount, 2) . ' credit',
                'success_url' => $_ENV['APP_URL'] . '/billing?payment=success',
                'cancel_url' => $_ENV['APP_URL'] . '/billing/add-funds?payment=cancelled',
            ]);

            if ($session && $session['url']) {
                return $response->withHeader('Location', $session['url'])->withStatus(302);
            }
        } elseif ($method === 'paypal') {
            $paypalService = new PayPalService();
            $order = $paypalService->createOrder($amount, [
                'user_id' => $user['id'],
                'description' => 'Add $' . number_format($amount, 2) . ' credit',
                'success_url' => $_ENV['APP_URL'] . '/billing?payment=success',
                'cancel_url' => $_ENV['APP_URL'] . '/billing/add-funds?payment=cancelled',
            ]);

            if ($order) {
                $approveUrl = null;
                foreach ($order['links'] ?? [] as $link) {
                    if ($link['rel'] === 'approve') {
                        $approveUrl = $link['href'];
                        break;
                    }
                }
                if ($approveUrl) {
                    return $response->withHeader('Location', $approveUrl)->withStatus(302);
                }
            }
        }

        $this->session->flash('error', 'Payment processing failed. Please try again.');
        return $response->withHeader('Location', '/billing/add-funds')->withStatus(302);
    }

    public function invoices($request, $response)
    {
        $user = $request->getAttribute('user');
        $invoiceModel = new Invoice();
        $invoices = $invoiceModel->getByUser($user['id']);

        $html = $this->render('client/billing/invoices', [
            'user' => $user,
            'invoices' => $invoices['data'],
            'total' => $invoices['total'],
            'csrf_token' => $this->session->get('csrf_token'),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function invoice($request, $response, $args)
    {
        $user = $request->getAttribute('user');
        $invoiceModel = new Invoice();
        $invoice = $invoiceModel->findById((int)$args['id']);

        if (!$invoice || $invoice['user_id'] !== $user['id']) {
            return $response->withHeader('Location', '/billing/invoices')->withStatus(302);
        }

        $html = $this->render('client/billing/invoice', [
            'user' => $user,
            'invoice' => $invoice,
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function paymentMethods($request, $response)
    {
        $user = $request->getAttribute('user');
        $pmModel = new PaymentMethod();
        $methods = $pmModel->getByUser($user['id']);

        $html = $this->render('client/billing/payment-methods', [
            'user' => $user,
            'payment_methods' => $methods,
            'csrf_token' => $this->session->get('csrf_token'),
            'stripe_key' => (new StripeService())->getPublicKey(),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function addPaymentMethod($request, $response)
    {
        $user = $request->getAttribute('user');
        $data = $request->getParsedBody();

        $pmModel = new PaymentMethod();
        $pmModel->create([
            'user_id' => $user['id'],
            'type' => $data['type'] ?? 'card',
            'provider' => 'stripe',
            'provider_id' => $data['payment_method_id'] ?? null,
            'last_four' => $data['last_four'] ?? null,
            'brand' => $data['brand'] ?? null,
            'exp_month' => $data['exp_month'] ?? null,
            'exp_year' => $data['exp_year'] ?? null,
            'is_default' => true,
        ]);

        $this->session->flash('success', 'Payment method added successfully.');
        return $response->withHeader('Location', '/billing/payment-methods')->withStatus(302);
    }

    public function deletePaymentMethod($request, $response, $args)
    {
        $user = $request->getAttribute('user');
        $pmModel = new PaymentMethod();
        $pm = $pmModel->findById((int)$args['id']);

        if ($pm && $pm['user_id'] === $user['id']) {
            $pmModel->delete((int)$args['id']);
            $this->session->flash('success', 'Payment method removed.');
        }

        return $response->withHeader('Location', '/billing/payment-methods')->withStatus(302);
    }

    public function transactions($request, $response)
    {
        $user = $request->getAttribute('user');
        $transactionModel = new Transaction();
        $transactions = $transactionModel->getByUser($user['id']);

        $html = $this->render('client/billing/transactions', [
            'user' => $user,
            'transactions' => $transactions['data'],
            'total' => $transactions['total'],
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    private function render(string $template, array $data = []): string
    {
        extract($data);
        ob_start();
        $templatePath = __DIR__ . '/../../resources/views/' . $template . '.php';
        if (file_exists($templatePath)) {
            require $templatePath;
        } else {
            echo "Template not found: $template";
        }
        return ob_get_clean();
    }
}
