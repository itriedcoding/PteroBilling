<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Session;
use App\Models\Plan;

class PlanController
{
    private Session $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function index($request, $response)
    {
        $planModel = new Plan();
        $plans = $planModel->getAll();

        $html = $this->render('admin/plans/index', [
            'user' => $request->getAttribute('user'),
            'plans' => $plans,
            'csrf_token' => $this->session->get('csrf_token'),
            'success' => $this->session->getFlash('success'),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function create($request, $response)
    {
        $html = $this->render('admin/plans/create', [
            'user' => $request->getAttribute('user'),
            'csrf_token' => $this->session->get('csrf_token'),
            'error' => $this->session->getFlash('error'),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function store($request, $response)
    {
        $data = $request->getParsedBody();
        $planModel = new Plan();

        $planModel->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'price' => (float)$data['price'],
            'billing_cycle' => $data['billing_cycle'] ?? 'monthly',
            'cpu' => (int)$data['cpu'] ?? 100,
            'memory' => (int)$data['memory'] ?? 1024,
            'disk' => (int)$data['disk'] ?? 10240,
            'io' => (int)$data['io'] ?? 500,
            'cpu_limit' => (int)$data['cpu_limit'] ?? 100,
            'databases' => (int)$data['databases'] ?? 1,
            'allocations' => (int)$data['allocations'] ?? 1,
            'backups' => (int)$data['backups'] ?? 1,
            'nest_id' => (int)$data['nest_id'] ?? 0,
            'egg_id' => (int)$data['egg_id'] ?? 0,
            'is_active' => isset($data['is_active']),
            'sort_order' => (int)$data['sort_order'] ?? 0,
        ]);

        $this->session->flash('success', 'Plan created successfully.');
        return $response->withHeader('Location', '/admin/plans')->withStatus(302);
    }

    public function edit($request, $response, $args)
    {
        $planModel = new Plan();
        $plan = $planModel->findById((int)$args['id']);

        if (!$plan) {
            return $response->withHeader('Location', '/admin/plans')->withStatus(302);
        }

        $html = $this->render('admin/plans/edit', [
            'user' => $request->getAttribute('user'),
            'plan' => $plan,
            'csrf_token' => $this->session->get('csrf_token'),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function update($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $planModel = new Plan();

        $planModel->update((int)$args['id'], [
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'price' => (float)$data['price'],
            'billing_cycle' => $data['billing_cycle'] ?? 'monthly',
            'cpu' => (int)$data['cpu'] ?? 100,
            'memory' => (int)$data['memory'] ?? 1024,
            'disk' => (int)$data['disk'] ?? 10240,
            'io' => (int)$data['io'] ?? 500,
            'cpu_limit' => (int)$data['cpu_limit'] ?? 100,
            'databases' => (int)$data['databases'] ?? 1,
            'allocations' => (int)$data['allocations'] ?? 1,
            'backups' => (int)$data['backups'] ?? 1,
            'nest_id' => (int)$data['nest_id'] ?? 0,
            'egg_id' => (int)$data['egg_id'] ?? 0,
            'is_active' => isset($data['is_active']),
            'sort_order' => (int)$data['sort_order'] ?? 0,
        ]);

        $this->session->flash('success', 'Plan updated successfully.');
        return $response->withHeader('Location', '/admin/plans')->withStatus(302);
    }

    public function destroy($request, $response, $args)
    {
        $planModel = new Plan();
        $planModel->delete((int)$args['id']);
        $this->session->flash('success', 'Plan deleted.');
        return $response->withHeader('Location', '/admin/plans')->withStatus(302);
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
