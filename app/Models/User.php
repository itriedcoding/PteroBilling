<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

class User
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        return $this->db->getConnection()->fetchAssociative(
            'SELECT * FROM users WHERE id = ?',
            [$id]
        );
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->getConnection()->fetchAssociative(
            'SELECT * FROM users WHERE email = ?',
            [$email]
        );
    }

    public function create(array $data): int
    {
        $this->db->getConnection()->insert('users', [
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_ARGON2ID),
            'credits' => 0,
            'role' => 'user',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return (int)$this->db->getConnection()->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->getConnection()->update('users', $data, ['id' => $id]) > 0;
    }

    public function addCredits(int $userId, float $amount): bool
    {
        $user = $this->findById($userId);
        if (!$user) return false;

        return $this->update($userId, [
            'credits' => $user['credits'] + $amount,
        ]);
    }

    public function deductCredits(int $userId, float $amount): bool
    {
        $user = $this->findById($userId);
        if (!$user || $user['credits'] < $amount) return false;

        return $this->update($userId, [
            'credits' => $user['credits'] - $amount,
        ]);
    }

    public function getAll(int $page = 1, int $perPage = 25): array
    {
        $offset = ($page - 1) * $perPage;
        $users = $this->db->getConnection()->fetchAllAssociative(
            'SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?',
            [$perPage, $offset]
        );
        $total = $this->db->getConnection()->fetchOne('SELECT COUNT(*) FROM users');
        return ['data' => $users, 'total' => (int)$total];
    }

    public function count(): int
    {
        return (int)$this->db->getConnection()->fetchOne('SELECT COUNT(*) FROM users');
    }

    public function delete(int $id): bool
    {
        return $this->db->getConnection()->delete('users', ['id' => $id]) > 0;
    }
}
