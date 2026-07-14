<?php
declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\Server;
use App\Models\Plan;

class CreditService
{
    public function deductForServer(int $userId, int $serverId): bool
    {
        $user = new User();
        $server = new Server();
        $plan = new Plan();

        $serverData = $server->findById($serverId);
        if (!$serverData || $serverData['user_id'] !== $userId) return false;

        $planData = $plan->findById($serverData['plan_id']);
        if (!$planData) return false;

        $userData = $user->findById($userId);
        if (!$userData || $userData['credits'] < $planData['price']) return false;

        $success = $user->deductCredits($userId, $planData['price']);
        if (!$success) return false;

        $transaction = new Transaction();
        $transaction->create([
            'user_id' => $userId,
            'type' => 'server_renewal',
            'amount' => $planData['price'],
            'currency' => 'USD',
            'status' => 'completed',
            'provider' => 'credits',
            'description' => 'Server renewal: ' . $serverData['name'],
        ]);

        $invoice = new Invoice();
        $invoice->create([
            'user_id' => $userId,
            'invoice_number' => $invoice->generateNumber(),
            'amount' => $planData['price'],
            'status' => 'paid',
            'payment_method' => 'credits',
            'description' => 'Server renewal: ' . $serverData['name'],
        ]);

        $newExpiry = strtotime($serverData['expires_at'] . ' +1 month');
        $server->update($serverId, [
            'expires_at' => date('Y-m-d H:i:s', $newExpiry),
        ]);

        return true;
    }

    public function purchaseCredits(int $userId, float $amount): bool
    {
        $user = new User();
        return $user->addCredits($userId, $amount);
    }

    public function getBalance(int $userId): float
    {
        $user = new User();
        $userData = $user->findById($userId);
        return $userData ? (float)$userData['credits'] : 0;
    }
}
