<?php
declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;

class PayPalService
{
    private string $clientId;
    private string $clientSecret;
    private string $mode;
    private string $baseUrl;

    public function __construct()
    {
        $this->clientId = $_ENV['PAYPAL_CLIENT_ID'] ?? '';
        $this->clientSecret = $_ENV['PAYPAL_CLIENT_SECRET'] ?? '';
        $this->mode = $_ENV['PAYPAL_MODE'] ?? 'sandbox';
        $this->baseUrl = $this->mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    private function getAccessToken(): ?string
    {
        $ch = curl_init($this->baseUrl . '/v1/oauth2/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => $this->clientId . ':' . $this->clientSecret,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'Accept-Language: en_US'],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $data['access_token'] ?? null;
        }

        return null;
    }

    public function createOrder(float $amount, array $params = []): ?array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) return null;

        $ch = curl_init($this->baseUrl . '/v2/checkout/orders');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "Authorization: Bearer $accessToken",
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => number_format($amount, 2, '.', ''),
                    ],
                    'description' => $params['description'] ?? 'PteroBilling Credit Purchase',
                    'custom_id' => $params['user_id'] ?? '',
                ]],
                'application_context' => [
                    'return_url' => $params['success_url'] ?? $_ENV['APP_URL'] . '/billing?payment=success',
                    'cancel_url' => $params['cancel_url'] ?? $_ENV['APP_URL'] . '/billing?payment=cancelled',
                    'brand_name' => $_ENV['APP_NAME'] ?? 'PteroBilling',
                    'landing_page' => 'BILLING',
                    'user_action' => 'PAY_NOW',
                ],
            ]),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201) {
            return json_decode($response, true);
        }

        return null;
    }

    public function captureOrder(string $orderId): ?array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) return null;

        $ch = curl_init($this->baseUrl . "/v2/checkout/orders/$orderId/capture");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "Authorization: Bearer $accessToken",
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 201) {
            return json_decode($response, true);
        }

        return null;
    }

    public function handleWebhook(array $payload): void
    {
        $eventType = $payload['event_type'] ?? '';

        match ($eventType) {
            'PAYMENT.CAPTURE.COMPLETED' => $this->handlePaymentCompleted($payload['resource']),
            default => null,
        };
    }

    private function handlePaymentCompleted(array $resource): void
    {
        $customId = $resource['custom_id'] ?? null;
        $captureId = $resource['id'] ?? null;

        if (!$customId || !$captureId) return;

        $userId = (int)$customId;
        $amount = (float)($resource['amount']['value'] ?? 0);

        $transaction = new Transaction();
        $transaction->create([
            'user_id' => $userId,
            'type' => 'credit_purchase',
            'amount' => $amount,
            'currency' => $resource['amount']['currency_code'] ?? 'USD',
            'status' => 'completed',
            'provider' => 'paypal',
            'provider_transaction_id' => $captureId,
            'description' => 'PayPal payment',
        ]);

        $user = new User();
        $user->addCredits($userId, $amount);

        $invoice = new Invoice();
        $invoice->create([
            'user_id' => $userId,
            'invoice_number' => $invoice->generateNumber(),
            'amount' => $amount,
            'status' => 'paid',
            'payment_method' => 'paypal',
            'transaction_id' => $captureId,
            'description' => 'Credit purchase via PayPal',
        ]);
    }
}
