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

        $this->updateSettingsFile($data);
        $this->updateEnvFile($data);

        $this->session->flash('success', 'Settings saved successfully.');
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

    private function updateSettingsFile(array $data): void
    {
        $settingsFile = __DIR__ . '/../../config/settings.php';
        $settings = [];
        if (file_exists($settingsFile)) {
            $settings = require $settingsFile;
        }

        $keys = [
            'site_name', 'site_url', 'custom_domain', 'currency', 'min_deposit', 'max_deposit',
            'allow_registration', 'require_email_verification', 'maintenance_mode',
            'stripe_enabled', 'stripe_secret', 'stripe_public', 'stripe_webhook_secret',
            'paypal_enabled', 'paypal_client_id', 'paypal_client_secret', 'paypal_mode',
            'credits_enabled', 'ptero_url', 'ptero_api_key',
            'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from', 'mail_from_name',
        ];

        foreach ($keys as $key) {
            if (isset($data[$key])) {
                $settings[$key] = $data[$key];
            }
        }

        if (isset($data['allow_registration'])) {
            $settings['allow_registration'] = true;
        } else {
            $settings['allow_registration'] = false;
        }

        if (isset($data['maintenance_mode'])) {
            $settings['maintenance_mode'] = true;
        } else {
            $settings['maintenance_mode'] = false;
        }

        foreach (['stripe_enabled', 'paypal_enabled', 'credits_enabled'] as $toggle) {
            $settings[$toggle] = isset($data[$toggle]);
        }

        file_put_contents($settingsFile, '<?php return ' . var_export($settings, true) . ";\n");
    }

    private function updateEnvFile(array $data): void
    {
        $envFile = __DIR__ . '/../../.env';
        if (!file_exists($envFile)) return;

        $envMap = [
            'site_url' => 'APP_URL',
            'custom_domain' => 'APP_DOMAIN',
            'ptero_url' => 'PTERODACTYL_URL',
            'ptero_api_key' => 'PTERODACTYL_API_KEY',
            'stripe_secret' => 'STRIPE_KEY',
            'stripe_public' => 'STRIPE_PUBLIC_KEY',
            'stripe_webhook_secret' => 'STRIPE_WEBHOOK_SECRET',
            'paypal_client_id' => 'PAYPAL_CLIENT_ID',
            'paypal_client_secret' => 'PAYPAL_CLIENT_SECRET',
            'paypal_mode' => 'PAYPAL_MODE',
            'mail_host' => 'MAIL_HOST',
            'mail_port' => 'MAIL_PORT',
            'mail_username' => 'MAIL_USERNAME',
            'mail_password' => 'MAIL_PASSWORD',
            'mail_from' => 'MAIL_FROM_ADDRESS',
            'mail_from_name' => 'MAIL_FROM_NAME',
        ];

        $lines = file($envFile, FILE_IGNORE_NEW_LINES);
        $newLines = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (strpos($trimmed, '=') === false || strpos($trimmed, '#') === 0) {
                $newLines[] = $line;
                continue;
            }

            list($key) = explode('=', $trimmed, 2);
            $envKey = trim($key);
            $found = false;

            foreach ($envMap as $formKey => $envName) {
                if ($envKey === $envName && isset($data[$formKey])) {
                    $newLines[] = $envName . '=' . $data[$formKey];
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $newLines[] = $line;
            }
        }

        file_put_contents($envFile, implode("\n", $newLines) . "\n");
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
