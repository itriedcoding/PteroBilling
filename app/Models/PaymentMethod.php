<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class PaymentMethod
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        return $this->db->getConnection()->fetchAssociative(
            'SELECT * FROM payment_methods WHERE id = ?',
            [$id]
        );
    }

    public function getByUser(int $userId): array
    {
        return $this->db->getConnection()->fetchAllAssociative(
            'SELECT * FROM payment_methods WHERE user_id = ? ORDER BY is_default DESC, created_at DESC',
            [$userId]
        );
    }

    public function create(array $data): int
    {
        $this->db->getConnection()->insert('payment_methods', [
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'provider' => $data['provider'],
            'provider_id' => $data['provider_id'] ?? null,
            'last_four' => $data['last_four'] ?? null,
            'brand' => $data['brand'] ?? null,
            'exp_month' => $data['exp_month'] ?? null,
            'exp_year' => $data['exp_year'] ?? null,
            'is_default' => $data['is_default'] ?? false,
            'metadata' => json_encode($data['metadata'] ?? []),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int)$this->db->getConnection()->lastInsertId();
    }

    public function delete(int $id): bool
    {
        return $this->db->getConnection()->delete('payment_methods', ['id' => $id]) > 0;
    }

    public function setDefault(int $id, int $userId): bool
    {
        $this->db->getConnection()->executeStatement(
            'UPDATE payment_methods SET is_default = 0 WHERE user_id = ?',
            [$userId]
        );
        return $this->db->getConnection()->update('payment_methods', [
            'is_default' => true
        ], ['id' => $id, 'user_id' => $userId]) > 0;
    }
}
