<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Plan
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        return $this->db->getConnection()->fetchAssociative(
            'SELECT * FROM plans WHERE id = ?',
            [$id]
        );
    }

    public function getAll(bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM plans';
        if ($activeOnly) {
            $sql .= ' WHERE is_active = 1';
        }
        $sql .= ' ORDER BY sort_order ASC, price ASC';
        return $this->db->getConnection()->fetchAllAssociative($sql);
    }

    public function create(array $data): int
    {
        $this->db->getConnection()->insert('plans', [
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'price' => $data['price'],
            'billing_cycle' => $data['billing_cycle'] ?? 'monthly',
            'cpu' => $data['cpu'] ?? 0,
            'memory' => $data['memory'] ?? 0,
            'disk' => $data['disk'] ?? 0,
            'io' => $data['io'] ?? 0,
            'cpu_limit' => $data['cpu_limit'] ?? 100,
            'databases' => $data['databases'] ?? 0,
            'allocations' => $data['allocations'] ?? 0,
            'backups' => $data['backups'] ?? 0,
            'nest_id' => $data['nest_id'] ?? 0,
            'egg_id' => $data['egg_id'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return (int)$this->db->getConnection()->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->getConnection()->update('plans', $data, ['id' => $id]) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->db->getConnection()->delete('plans', ['id' => $id]) > 0;
    }

    public function count(): int
    {
        return (int)$this->db->getConnection()->fetchOne('SELECT COUNT(*) FROM plans');
    }
}
