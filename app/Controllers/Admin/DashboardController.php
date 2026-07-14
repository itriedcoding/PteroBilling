<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Session;
use App\Models\User;
use App\Models\Plan;
use App\Models\Server;
use App\Models\Invoice;

class DashboardController
{
    private Session $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function index($request, $response)
    {
        $userModel = new User();
        $planModel = new Plan();
        $serverModel = new Server();
        $invoiceModel = new Invoice();

        $viewData = [
            'user' => $request->getAttribute('user'),
            'total_users' => $userModel->count(),
            'total_plans' => $planModel->count(),
            'total_servers' => $serverModel->count(),
            'active_servers' => $serverModel->countActive(),
            'total_revenue' => $invoiceModel->getTotalRevenue(),
            'monthly_revenue' => $invoiceModel->getMonthlyRevenue(),
            'pending_invoices' => $invoiceModel->countPending(),
            'recent_invoices' => $invoiceModel->getAll(1, 10)['data'],
            'csrf_token' => $this->session->get('csrf_token'),
        ];

        $html = $this->render('admin/dashboard/index', $viewData);
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
