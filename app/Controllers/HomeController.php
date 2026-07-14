<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Models\Plan;
use App\Models\Server;

class HomeController
{
    private Session $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function index($request, $response)
    {
        if ($this->needsSetup()) {
            return $response->withHeader('Location', '/setup')->withStatus(302);
        }

        $planModel = new Plan();
        $plans = $planModel->getAll(true);

        $serverModel = new Server();
        $totalServers = $serverModel->countActive();

        $viewData = [
            'plans' => $plans,
            'total_servers' => $totalServers,
            'user' => $request->getAttribute('user'),
            'csrf_token' => $this->session->get('csrf_token'),
        ];

        $html = $this->render('home', $viewData);
        $response->getBody()->write($html);
        return $response;
    }

    public function status($request, $response)
    {
        $serverModel = new Server();
        $viewData = [
            'total_servers' => $serverModel->countActive(),
            'user' => $request->getAttribute('user'),
        ];

        $html = $this->render('status', $viewData);
        $response->getBody()->write($html);
        return $response;
    }

    private function needsSetup(): bool
    {
        $envFile = __DIR__ . '/../../.env';
        if (!file_exists($envFile)) return true;

        $env = file_get_contents($envFile);

        if (preg_match('/PTERODACTYL_API_KEY=(.+)/', $env, $matches)) {
            $key = trim($matches[1]);
            if (empty($key) || $key === 'change-this-to-your-ptero-api-key') {
                return true;
            }
        } else {
            return true;
        }

        return false;
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
