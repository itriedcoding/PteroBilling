<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class Invoice
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        return $this->db->getConnection()->fetchAssociative(
            'SELECT i.*, u.username, u.email FROM invoices i JOIN users u ON i.user_id = u.id WHERE i.id = ?',
            [$id]
        );
    }

    public function create(array $data): int
    {
        $this->db->getConnection()->insert('invoices', [
            'user_id' => $data['user_id'],
            'invoice_number' => $data['invoice_number'],
            'amount' => $data['amount'],
            'status' => $data['status'] ?? 'pending',
            'payment_method' => $data['payment_method'] ?? null,
            'transaction_id' => $data['transaction_id'] ?? null,
            'description' => $data['description'] ?? '',
            'metadata' => json_encode($data['metadata'] ?? []),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return (int)$this->db->getConnection()->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->getConnection()->update('invoices', $data, ['id' => $id]) > 0;
    }

    public function getByUser(int $userId, int $page = 1, int $perPage = 25): array
    {
        $offset = ($page - 1) * $perPage;
        $invoices = $this->db->getConnection()->fetchAllAssociative(
            'SELECT * FROM invoices WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?',
            [$userId, $perPage, $offset]
        );
        $total = $this->db->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM invoices WHERE user_id = ?',
            [$userId]
        );
        return ['data' => $invoices, 'total' => (int)$total];
    }

    public function getAll(int $page = 1, int $perPage = 25): array
    {
        $offset = ($page - 1) * $perPage;
        $invoices = $this->db->getConnection()->fetchAllAssociative(
            'SELECT i.*, u.username, u.email FROM invoices i JOIN users u ON i.user_id = u.id ORDER BY i.created_at DESC LIMIT ? OFFSET ?',
            [$perPage, $offset]
        );
        $total = $this->db->getConnection()->fetchOne('SELECT COUNT(*) FROM invoices');
        return ['data' => $invoices, 'total' => (int)$total];
    }

    public function generateNumber(): string
    {
        $year = date('Y');
        $count = $this->db->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM invoices WHERE YEAR(created_at) = ?',
            [$year]
        );
        return sprintf('INV-%s-%05d', $year, (int)$count + 1);
    }

    public function getTotalRevenue(): float
    {
        return (float)($this->db->getConnection()->fetchOne(
            'SELECT COALESCE(SUM(amount), 0) FROM invoices WHERE status = ?',
            ['paid']
        ) ?? 0);
    }

    public function getMonthlyRevenue(): float
    {
        return (float)($this->db->getConnection()->fetchOne(
            'SELECT COALESCE(SUM(amount), 0) FROM invoices WHERE status = ? AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())',
            ['paid']
        ) ?? 0);
    }

    public function count(): int
    {
        return (int)$this->db->getConnection()->fetchOne('SELECT COUNT(*) FROM invoices');
    }

    public function countPending(): int
    {
        return (int)$this->db->getConnection()->fetchOne(
            "SELECT COUNT(*) FROM invoices WHERE status = 'pending'"
        );
    }
}
