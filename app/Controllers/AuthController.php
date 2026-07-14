<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Models\User;

class AuthController
{
    private Session $session;
    private User $userModel;

    public function __construct()
    {
        $this->session = new Session();
        $this->userModel = new User();
    }

    public function showLogin($request, $response)
    {
        $html = $this->render('auth/login', [
            'csrf_token' => $this->session->get('csrf_token'),
            'error' => $this->session->getFlash('error'),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function login($request, $response)
    {
        $data = $request->getParsedBody();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->session->flash('error', 'Invalid email or password.');
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $this->session->set('user_id', $user['id']);
        $this->session->regenerate();

        if ($user['role'] === 'admin') {
            return $response->withHeader('Location', '/admin')->withStatus(302);
        }

        return $response->withHeader('Location', '/billing')->withStatus(302);
    }

    public function showRegister($request, $response)
    {
        $html = $this->render('auth/register', [
            'csrf_token' => $this->session->get('csrf_token'),
            'error' => $this->session->getFlash('error'),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function register($request, $response)
    {
        $data = $request->getParsedBody();
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $passwordConfirm = $data['password_confirmation'] ?? '';

        if (empty($username) || empty($email) || empty($password)) {
            $this->session->flash('error', 'All fields are required.');
            return $response->withHeader('Location', '/auth/register')->withStatus(302);
        }

        if ($password !== $passwordConfirm) {
            $this->session->flash('error', 'Passwords do not match.');
            return $response->withHeader('Location', '/auth/register')->withStatus(302);
        }

        if (strlen($password) < 8) {
            $this->session->flash('error', 'Password must be at least 8 characters.');
            return $response->withHeader('Location', '/auth/register')->withStatus(302);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->session->flash('error', 'Invalid email address.');
            return $response->withHeader('Location', '/auth/register')->withStatus(302);
        }

        if ($this->userModel->findByEmail($email)) {
            $this->session->flash('error', 'Email already registered.');
            return $response->withHeader('Location', '/auth/register')->withStatus(302);
        }

        $userId = $this->userModel->create([
            'username' => $username,
            'email' => $email,
            'password' => $password,
        ]);

        $this->session->set('user_id', $userId);
        $this->session->regenerate();

        return $response->withHeader('Location', '/billing')->withStatus(302);
    }

    public function logout($request, $response)
    {
        $this->session->destroy();
        return $response->withHeader('Location', '/')->withStatus(302);
    }

    public function showForgotPassword($request, $response)
    {
        $html = $this->render('auth/forgot-password', [
            'csrf_token' => $this->session->get('csrf_token'),
            'error' => $this->session->getFlash('error'),
            'success' => $this->session->getFlash('success'),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function forgotPassword($request, $response)
    {
        $data = $request->getParsedBody();
        $email = $data['email'] ?? '';

        $this->session->flash('success', 'If an account with that email exists, a password reset link has been sent.');
        return $response->withHeader('Location', '/auth/forgot-password')->withStatus(302);
    }

    public function showResetPassword($request, $response, $args)
    {
        $html = $this->render('auth/reset-password', [
            'csrf_token' => $this->session->get('csrf_token'),
            'token' => $args['token'] ?? '',
            'error' => $this->session->getFlash('error'),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function resetPassword($request, $response)
    {
        return $response->withHeader('Location', '/auth/login')->withStatus(302);
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
