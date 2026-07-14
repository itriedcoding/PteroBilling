<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class ApiKey
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $data): int
    {
        $this->db->getConnection()->insert('api_keys', [
            'user_id' => $data['user_id'],
            'name' => $data['name'],
            'key' => $data['key'],
            'permissions' => json_encode($data['permissions'] ?? []),
            'last_used_at' => null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int)$this->db->getConnection()->lastInsertId();
    }

    public function findByKey(string $key): ?array
    {
        return $this->db->getConnection()->fetchAssociative(
            'SELECT * FROM api_keys WHERE key = ?',
            [$key]
        );
    }

    public function getByUser(int $userId): array
    {
        return $this->db->getConnection()->fetchAllAssociative(
            'SELECT * FROM api_keys WHERE user_id = ? ORDER BY created_at DESC',
            [$userId]
        );
    }

    public function delete(int $id): bool
    {
        return $this->db->getConnection()->delete('api_keys', ['id' => $id]) > 0;
    }

    public function updateLastUsed(int $id): void
    {
        $this->db->getConnection()->update('api_keys', [
            'last_used_at' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);
    }

    public static function generateKey(): string
    {
        return 'pbk_' . bin2hex(random_bytes(32));
    }
}
