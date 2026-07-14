<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Server
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        return $this->db->getConnection()->fetchAssociative(
            'SELECT s.*, p.name as plan_name, u.username FROM servers s JOIN plans p ON s.plan_id = p.id JOIN users u ON s.user_id = u.id WHERE s.id = ?',
            [$id]
        );
    }

    public function create(array $data): int
    {
        $this->db->getConnection()->insert('servers', [
            'user_id' => $data['user_id'],
            'plan_id' => $data['plan_id'],
            'ptero_server_id' => $data['ptero_server_id'] ?? null,
            'name' => $data['name'],
            'status' => $data['status'] ?? 'active',
            'expires_at' => $data['expires_at'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return (int)$this->db->getConnection()->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->getConnection()->update('servers', $data, ['id' => $id]) > 0;
    }

    public function getByUser(int $userId): array
    {
        return $this->db->getConnection()->fetchAllAssociative(
            'SELECT s.*, p.name as plan_name FROM servers s JOIN plans p ON s.plan_id = p.id WHERE s.user_id = ? ORDER BY s.created_at DESC',
            [$userId]
        );
    }

    public function getExpiringSoon(int $days = 7): array
    {
        return $this->db->getConnection()->fetchAllAssociative(
            'SELECT s.*, u.email, u.username, p.name as plan_name FROM servers s JOIN users u ON s.user_id = u.id JOIN plans p ON s.plan_id = p.id WHERE s.status = ? AND s.expires_at <= DATE_ADD(NOW(), INTERVAL ? DAY) AND s.expires_at > NOW()',
            ['active', $days]
        );
    }

    public function count(): int
    {
        return (int)$this->db->getConnection()->fetchOne('SELECT COUNT(*) FROM servers');
    }

    public function countActive(): int
    {
        return (int)$this->db->getConnection()->fetchOne(
            "SELECT COUNT(*) FROM servers WHERE status = 'active'"
        );
    }

    public function delete(int $id): bool
    {
        return $this->db->getConnection()->delete('servers', ['id' => $id]) > 0;
    }
}
