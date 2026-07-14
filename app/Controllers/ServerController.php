<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Models\Server;
use App\Models\Plan;
use App\Services\Pterodactyl\PterodactylService;
use App\Services\Payment\CreditService;

class ServerController
{
    private Session $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function index($request, $response)
    {
        $user = $request->getAttribute('user');
        $serverModel = new Server();
        $servers = $serverModel->getByUser($user['id']);

        $html = $this->render('client/servers/index', [
            'user' => $user,
            'servers' => $servers,
            'csrf_token' => $this->session->get('csrf_token'),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function create($request, $response)
    {
        $user = $request->getAttribute('user');
        $planModel = new Plan();
        $plans = $planModel->getAll(true);

        $pteroService = new PterodactylService();
        $nests = $pteroService->getNests();
        $locations = $pteroService->getLocations();

        $html = $this->render('client/servers/create', [
            'user' => $user,
            'plans' => $plans,
            'nests' => $nests['data'] ?? [],
            'locations' => $locations['data'] ?? [],
            'csrf_token' => $this->session->get('csrf_token'),
            'error' => $this->session->getFlash('error'),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function store($request, $response)
    {
        $user = $request->getAttribute('user');
        $data = $request->getParsedBody();

        $planModel = new Plan();
        $plan = $planModel->findById((int)($data['plan_id'] ?? 0));

        if (!$plan) {
            $this->session->flash('error', 'Invalid plan selected.');
            return $response->withHeader('Location', '/servers/create')->withStatus(302);
        }

        $creditService = new CreditService();
        if ($creditService->getBalance($user['id']) < $plan['price']) {
            $this->session->flash('error', 'Insufficient credits. Please add funds first.');
            return $response->withHeader('Location', '/servers/create')->withStatus(302);
        }

        $pteroService = new PterodactylService();
        $serverResult = $pteroService->createServerForUser([
            'email' => $user['email'],
            'username' => $user['username'],
            'server_name' => $data['server_name'] ?? 'My Server',
            'egg_id' => $plan['egg_id'],
            'memory' => $plan['memory'],
            'disk' => $plan['disk'],
            'cpu' => $plan['cpu'],
            'io' => $plan['io'],
            'databases' => $plan['databases'],
            'allocations' => $plan['allocations'],
            'backups' => $plan['backups'],
            'location_id' => $data['location_id'] ?? 1,
        ]);

        if (!$serverResult) {
            $this->session->flash('error', 'Failed to create server. Please try again.');
            return $response->withHeader('Location', '/servers/create')->withStatus(302);
        }

        $serverModel = new Server();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 month'));
        $serverModel->create([
            'user_id' => $user['id'],
            'plan_id' => $plan['id'],
            'ptero_server_id' => $serverResult['attributes']['id'] ?? null,
            'name' => $data['server_name'] ?? 'My Server',
            'expires_at' => $expiresAt,
        ]);

        $creditService->deductForServer($user['id'], $serverModel->findById($serverModel->getConnection()->lastInsertId())['id']);

        $this->session->flash('success', 'Server created successfully!');
        return $response->withHeader('Location', '/servers')->withStatus(302);
    }

    public function show($request, $response, $args)
    {
        $user = $request->getAttribute('user');
        $serverModel = new Server();
        $server = $serverModel->findById((int)$args['id']);

        if (!$server || $server['user_id'] !== $user['id']) {
            return $response->withHeader('Location', '/servers')->withStatus(302);
        }

        $html = $this->render('client/servers/show', [
            'user' => $user,
            'server' => $server,
            'csrf_token' => $this->session->get('csrf_token'),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function renew($request, $response, $args)
    {
        $user = $request->getAttribute('user');
        $creditService = new CreditService();

        $success = $creditService->deductForServer($user['id'], (int)$args['id']);

        if ($success) {
            $this->session->flash('success', 'Server renewed for 30 days.');
        } else {
            $this->session->flash('error', 'Failed to renew server. Insufficient credits.');
        }

        return $response->withHeader('Location', '/servers/' . $args['id'])->withStatus(302);
    }

    public function destroy($request, $response, $args)
    {
        $user = $request->getAttribute('user');
        $serverModel = new Server();
        $server = $serverModel->findById((int)$args['id']);

        if (!$server || $server['user_id'] !== $user['id']) {
            return $response->withHeader('Location', '/servers')->withStatus(302);
        }

        if ($server['ptero_server_id']) {
            $pteroService = new PterodactylService();
            $pteroService->deleteServer((int)$server['ptero_server_id']);
        }

        $serverModel->delete((int)$args['id']);

        $this->session->flash('success', 'Server deleted successfully.');
        return $response->withHeader('Location', '/servers')->withStatus(302);
    }

    private function render(string $template, array $data = []): string
    {
        extract($data);
        ob_start();
        $templatePath = __DIR__ . '/../../resources/views/' . $template . '.php';
        if (file_exists($templatePath)) {
            require $templatePath;
        } else {
            echo "Template not found: $template";
        }
        return ob_get_clean();
    }
}
