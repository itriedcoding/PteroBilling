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
