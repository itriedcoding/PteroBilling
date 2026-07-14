<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\Payment\StripeService;
use App\Services\Payment\PayPalService;

class PaymentController
{
    public function stripeWebhook($request, $response)
    {
        $payload = (string)$request->getBody();
        $sigHeader = $request->getHeaderLine('Stripe-Signature');

        $stripeService = new StripeService();
        $event = $stripeService->handleWebhook(json_decode($payload, true), $sigHeader);

        if ($event) {
            return $response->withStatus(200);
        }

        return $response->withStatus(400);
    }

    public function paypalWebhook($request, $response)
    {
        $payload = (array)json_decode((string)$request->getBody(), true);

        $paypalService = new PayPalService();
        $paypalService->handleWebhook($payload);

        return $response->withStatus(200);
    }

    public function paymentStatus($request, $response, $args)
    {
        $paymentId = $args['id'];
        return $response->withJson([
            'status' => 'processing',
            'payment_id' => $paymentId,
        ]);
    }
}
