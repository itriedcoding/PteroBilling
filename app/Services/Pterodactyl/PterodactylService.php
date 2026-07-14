<?php
declare(strict_types=1);

namespace App\Services\Pterodactyl;

class PterodactylService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim($_ENV['PTERODACTYL_URL'] ?? '', '/');
        $this->apiKey = $_ENV['PTERODACTYL_API_KEY'] ?? '';
    }

    private function request(string $method, string $endpoint, array $data = []): ?array
    {
        $url = $this->baseUrl . '/api/application' . $endpoint;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ]);

        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return json_decode($response, true);
        }

        return null;
    }

    public function getNodes(): ?array
    {
        return $this->request('GET', '/nodes');
    }

    public function getLocations(): ?array
    {
        return $this->request('GET', '/locations');
    }

    public function getNests(): ?array
    {
        return $this->request('GET', '/nests');
    }

    public function getEggs(int $nestId): ?array
    {
        return $this->request('GET', "/nests/$nestId/eggs");
    }

    public function getUsers(): ?array
    {
        return $this->request('GET', '/users');
    }

    public function findUserByEmail(string $email): ?array
    {
        $result = $this->request('GET', '/users?filter[email]=' . urlencode($email));
        $users = $result['data'] ?? [];
        return !empty($users) ? $users[0] : null;
    }

    public function createUser(array $data): ?array
    {
        return $this->request('POST', '/users', $data);
    }

    public function createServer(array $data): ?array
    {
        return $this->request('POST', '/servers', $data);
    }

    public function getServer(int $serverId): ?array
    {
        return $this->request('GET', "/servers/$serverId");
    }

    public function deleteServer(int $serverId): bool
    {
        $result = $this->request('DELETE', "/servers/$serverId");
        return $result !== null;
    }

    public function suspendServer(int $serverId): bool
    {
        $result = $this->request('POST', "/servers/$serverId/suspend");
        return $result !== null;
    }

    public function unsuspendServer(int $serverId): bool
    {
        $result = $this->request('POST', "/servers/$serverId/unsuspend");
        return $result !== null;
    }

    public function createServerForUser(array $params): ?array
    {
        $user = $this->findUserByEmail($params['email']);
        if (!$user) {
            $userResult = $this->createUser([
                'username' => $params['username'],
                'email' => $params['email'],
                'first_name' => $params['first_name'] ?? $params['username'],
                'last_name' => $params['last_name'] ?? '',
                'password' => bin2hex(random_bytes(16)),
            ]);
            if (!$userResult) return null;
            $user = $userResult['attributes'] ?? $userResult;
        }

        $userId = $user['id'] ?? $user['uuid'] ?? null;
        if (!$userId) return null;

        return $this->createServer([
            'name' => $params['server_name'],
            'user' => (int)$userId,
            'egg' => (int)$params['egg_id'],
            'docker_image' => $params['docker_image'] ?? 'ghcr.io/pterodactyl/yolks:nodejs_18',
            'startup' => $params['startup'] ?? '',
            'limits' => [
                'memory' => (int)$params['memory'],
                'swap' => 0,
                'disk' => (int)$params['disk'],
                'io' => (int)$params['io'] ?? 500,
                'cpu' => (int)$params['cpu'],
            ],
            'feature_limits' => [
                'databases' => (int)$params['databases'] ?? 0,
                'allocations' => (int)$params['allocations'] ?? 1,
                'backups' => (int)$params['backups'] ?? 0,
            ],
            'deploy' => [
                'locations' => [(int)($params['location_id'] ?? 1)],
                'dedicated_ip' => false,
                'port_range' => [],
            ],
        ]);
    }
}
