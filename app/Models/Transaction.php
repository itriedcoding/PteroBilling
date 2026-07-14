<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Transaction
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $data): int
    {
        $this->db->getConnection()->insert('transactions', [
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'USD',
            'status' => $data['status'] ?? 'pending',
            'provider' => $data['provider'],
            'provider_transaction_id' => $data['provider_transaction_id'] ?? null,
            'description' => $data['description'] ?? '',
            'metadata' => json_encode($data['metadata'] ?? []),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int)$this->db->getConnection()->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        return $this->db->getConnection()->update('transactions', $data, ['id' => $id]) > 0;
    }

    public function getByUser(int $userId, int $page = 1, int $perPage = 25): array
    {
        $offset = ($page - 1) * $perPage;
        $transactions = $this->db->getConnection()->fetchAllAssociative(
            'SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?',
            [$userId, $perPage, $offset]
        );
        $total = $this->db->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM transactions WHERE user_id = ?',
            [$userId]
        );
        return ['data' => $transactions, 'total' => (int)$total];
    }

    public function findByProviderId(string $providerId): ?array
    {
        return $this->db->getConnection()->fetchAssociative(
            'SELECT * FROM transactions WHERE provider_transaction_id = ?',
            [$providerId]
        );
    }
}
