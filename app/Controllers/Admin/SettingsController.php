<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Session;
use App\Models\ApiKey;

class SettingsController
{
    private Session $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function index($request, $response)
    {
        $html = $this->render('admin/settings/index', [
            'user' => $request->getAttribute('user'),
            'settings' => $this->getSettings(),
            'csrf_token' => $this->session->get('csrf_token'),
            'success' => $this->session->getFlash('success'),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function update($request, $response)
    {
        $data = $request->getParsedBody();
        $settingsFile = __DIR__ . '/../../config/settings.php';

        $settings = [];
        if (file_exists($settingsFile)) {
            $settings = require $settingsFile;
        }

        foreach ($data as $key => $value) {
            if ($key === 'csrf_token') continue;
            $settings[$key] = $value;
        }

        file_put_contents($settingsFile, '<?php return ' . var_export($settings, true) . ';');

        $this->session->flash('success', 'Settings updated successfully.');
        return $response->withHeader('Location', '/admin/settings')->withStatus(302);
    }

    public function apiKeys($request, $response)
    {
        $apiKeyModel = new ApiKey();
        $user = $request->getAttribute('user');
        $keys = $apiKeyModel->getByUser($user['id']);

        $html = $this->render('admin/settings/api-keys', [
            'user' => $user,
            'api_keys' => $keys,
            'csrf_token' => $this->session->get('csrf_token'),
            'new_key' => $this->session->getFlash('new_key'),
            'success' => $this->session->getFlash('success'),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function createApiKey($request, $response)
    {
        $data = $request->getParsedBody();
        $user = $request->getAttribute('user');
        $apiKeyModel = new ApiKey();

        $key = ApiKey::generateKey();
        $apiKeyModel->create([
            'user_id' => $user['id'],
            'name' => $data['name'] ?? 'API Key',
            'key' => $key,
            'permissions' => $data['permissions'] ?? ['read'],
        ]);

        $this->session->flash('new_key', $key);
        $this->session->flash('success', 'API key created. Save it now - it won\'t be shown again.');
        return $response->withHeader('Location', '/admin/api-keys')->withStatus(302);
    }

    public function deleteApiKey($request, $response, $args)
    {
        $user = $request->getAttribute('user');
        $apiKeyModel = new ApiKey();
        $apiKeyModel->delete((int)$args['id']);

        $this->session->flash('success', 'API key deleted.');
        return $response->withHeader('Location', '/admin/api-keys')->withStatus(302);
    }

    private function getSettings(): array
    {
        $settingsFile = __DIR__ . '/../../config/settings.php';
        if (file_exists($settingsFile)) {
            return require $settingsFile;
        }
        return [];
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
