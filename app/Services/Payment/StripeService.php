<?php
declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use App\Models\User;

class StripeService
{
    private string $secretKey;
    private string $webhookSecret;
    private string $publicKey;

    public function __construct()
    {
        $this->secretKey = $_ENV['STRIPE_KEY'] ?? '';
        $this->webhookSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';
        $this->publicKey = $_ENV['STRIPE_PUBLIC_KEY'] ?? '';
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function createCheckoutSession(array $params): ?array
    {
        $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => $this->secretKey . ':',
            CURLOPT_POSTFIELDS => http_build_query([
                'payment_method_types[]' => 'card',
                'line_items[0][price_data][currency]' => 'usd',
                'line_items[0][price_data][product_data][name]' => $params['description'] ?? 'PteroBilling Credit',
                'line_items[0][price_data][unit_amount]' => (int)($params['amount'] * 100),
                'line_items[0][quantity]' => 1,
                'mode' => 'payment',
                'success_url' => $params['success_url'],
                'cancel_url' => $params['cancel_url'],
                'metadata[user_id]' => $params['user_id'],
                'metadata[type]' => $params['type'] ?? 'credit_purchase',
            ]),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return json_decode($response, true);
        }

        return null;
    }

    public function createPaymentIntent(float $amount, array $metadata = []): ?array
    {
        $ch = curl_init('https://api.stripe.com/v1/payment_intents');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => $this->secretKey . ':',
            CURLOPT_POSTFIELDS => http_build_query([
                'amount' => (int)($amount * 100),
                'currency' => 'usd',
                'metadata' => $metadata,
                'automatic_payment_methods[enabled]' => 'true',
            ]),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return json_decode($response, true);
        }

        return null;
    }

    public function createSetupIntent(string $customerId): ?array
    {
        $ch = curl_init('https://api.stripe.com/v1/setup_intents');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => $this->secretKey . ':',
            CURLOPT_POSTFIELDS => http_build_query([
                'customer' => $customerId,
                'automatic_payment_methods[enabled]' => 'true',
            ]),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return json_decode($response, true);
        }

        return null;
    }

    public function handleWebhook(array $payload, string $sigHeader): ?array
    {
        $event = $this->verifyWebhookSignature($payload, $sigHeader);
        if (!$event) return null;

        match ($event['type']) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event['data']['object']),
            'invoice.payment_succeeded' => $this->handleInvoicePaid($event['data']['object']),
            'payment_intent.succeeded' => $this->handlePaymentSucceeded($event['data']['object']),
            default => null,
        };

        return $event;
    }

    private function verifyWebhookSignature(array $payload, string $sigHeader): ?array
    {
        $elements = explode(',', $sigHeader);
        $timestamp = null;
        $signature = null;

        foreach ($elements as $element) {
            $parts = explode('=', $element);
            if (count($parts) === 2) {
                $parts[0] = trim($parts[0]);
                $parts[1] = trim($parts[1]);
                if ($parts[0] === 't') {
                    $timestamp = $parts[1];
                } elseif ($parts[0] === 'v1') {
                    $signature = $parts[1];
                }
            }
        }

        if (!$timestamp || !$signature) return null;

        $signedPayload = $timestamp . '.' . json_encode($payload);
        $expectedSig = hash_hmac('sha256', $signedPayload, $this->webhookSecret);

        if (!hash_equals($expectedSig, $signature)) return null;

        return $payload;
    }

    private function handleCheckoutCompleted(array $session): void
    {
        $userId = $session['metadata']['user_id'] ?? null;
        $type = $session['metadata']['type'] ?? null;

        if (!$userId) return;

        $transaction = new Transaction();
        $user = new User();

        $transaction->create([
            'user_id' => (int)$userId,
            'type' => $type,
            'amount' => $session['amount_total'] / 100,
            'currency' => strtoupper($session['currency']),
            'status' => 'completed',
            'provider' => 'stripe',
            'provider_transaction_id' => $session['payment_intent'],
            'description' => 'Stripe payment - ' . $session['id'],
        ]);

        if ($type === 'credit_purchase') {
            $user->addCredits((int)$userId, $session['amount_total'] / 100);

            $invoice = new Invoice();
            $invoice->create([
                'user_id' => (int)$userId,
                'invoice_number' => $invoice->generateNumber(),
                'amount' => $session['amount_total'] / 100,
                'status' => 'paid',
                'payment_method' => 'stripe',
                'transaction_id' => $session['payment_intent'],
                'description' => 'Credit purchase via Stripe',
            ]);
        }
    }

    private function handleInvoicePaid(array $invoice): void
    {
        // Handle subscription invoices if needed
    }

    private function handlePaymentSucceeded(array $paymentIntent): void
    {
        $transaction = new Transaction();
        $existing = $transaction->findByProviderId($paymentIntent['id']);

        if ($existing) {
            $transaction->update($existing['id'], ['status' => 'completed']);
        }
    }
}
