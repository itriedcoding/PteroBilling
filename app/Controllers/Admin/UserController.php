<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Session;
use App\Models\User;

class UserController
{
    private Session $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function index($request, $response)
    {
        $userModel = new User();
        $users = $userModel->getAll();

        $html = $this->render('admin/users/index', [
            'user' => $request->getAttribute('user'),
            'users' => $users['data'],
            'total' => $users['total'],
            'csrf_token' => $this->session->get('csrf_token'),
            'success' => $this->session->getFlash('success'),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function show($request, $response, $args)
    {
        $userModel = new User();
        $userData = $userModel->findById((int)$args['id']);

        if (!$userData) {
            return $response->withHeader('Location', '/admin/users')->withStatus(302);
        }

        $html = $this->render('admin/users/show', [
            'user' => $request->getAttribute('user'),
            'target_user' => $userData,
            'csrf_token' => $this->session->get('csrf_token'),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function update($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $userModel = new User();

        $updateData = [
            'username' => $data['username'],
            'email' => $data['email'],
            'role' => $data['role'] ?? 'user',
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = password_hash($data['password'], PASSWORD_ARGON2ID);
        }

        $userModel->update((int)$args['id'], $updateData);

        $this->session->flash('success', 'User updated successfully.');
        return $response->withHeader('Location', '/admin/users/' . $args['id'])->withStatus(302);
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
