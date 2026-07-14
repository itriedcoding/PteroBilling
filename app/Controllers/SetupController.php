<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;

class SetupController
{
    private Session $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function index($request, $response)
    {
        if ($this->isSetupComplete()) {
            return $response->withHeader('Location', '/')->withStatus(302);
        }

        $step = (int)($this->session->get('setup_step') ?? 1);
        $html = $this->render('setup/index', [
            'step' => $step,
            'settings' => $this->getSetupData(),
            'csrf_token' => $this->session->get('csrf_token'),
            'error' => $this->session->getFlash('error'),
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function step($request, $response, $args)
    {
        $step = (int)($args['step'] ?? 1);
        $data = $request->getParsedBody();

        $this->session->set('setup_step', $step);

        $stepData = $this->session->get('setup_data', []);
        foreach ($data as $key => $value) {
            if ($key === 'csrf_token' || $key === '_method') continue;
            $stepData[$key] = $value;
        }
        $this->session->set('setup_data', $stepData);

        if ($step === 6) {
            return $this->completeSetup($request, $response);
        }

        return $response->withHeader('Location', '/setup/' . ($step + 1))->withStatus(302);
    }

    public function complete($request, $response)
    {
        return $this->completeSetup($request, $response);
    }

    private function completeSetup($request, $response)
    {
        $data = $this->session->get('setup_data', []);

        $this->writeEnvFile($data);
        $this->writeSettingsFile($data);

        $this->session->set('setup_complete', true);
        $this->session->remove('setup_step');
        $this->session->remove('setup_data');

        return $response->withHeader('Location', '/setup/6')->withStatus(302);
    }

    private function writeEnvFile(array $data): void
    {
        $envFile = __DIR__ . '/../../.env';
        $existing = [];
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
                list($key) = explode('=', $line, 2);
                $existing[trim($key)] = true;
            }
        }

        $envLines = file_exists($envFile) ? file($envFile, FILE_IGNORE_NEW_LINES) : [];

        $newVars = [
            'APP_URL' => $data['panel_url'] ?? ($_ENV['APP_URL'] ?? ''),
            'APP_DOMAIN' => $data['panel_domain'] ?? ($_ENV['APP_DOMAIN'] ?? ''),
            'PTERODACTYL_URL' => $data['ptero_url'] ?? ($_ENV['PTERODACTYL_URL'] ?? ''),
            'PTERODACTYL_API_KEY' => $data['ptero_api_key'] ?? ($_ENV['PTERODACTYL_API_KEY'] ?? ''),
            'STRIPE_KEY' => $data['stripe_secret'] ?? ($_ENV['STRIPE_KEY'] ?? ''),
            'STRIPE_PUBLIC_KEY' => $data['stripe_public'] ?? ($_ENV['STRIPE_PUBLIC_KEY'] ?? ''),
            'STRIPE_WEBHOOK_SECRET' => $data['stripe_webhook'] ?? ($_ENV['STRIPE_WEBHOOK_SECRET'] ?? ''),
            'PAYPAL_CLIENT_ID' => $data['paypal_client_id'] ?? ($_ENV['PAYPAL_CLIENT_ID'] ?? ''),
            'PAYPAL_CLIENT_SECRET' => $data['paypal_secret'] ?? ($_ENV['PAYPAL_CLIENT_SECRET'] ?? ''),
            'PAYPAL_MODE' => $data['paypal_mode'] ?? ($_ENV['PAYPAL_MODE'] ?? 'live'),
        ];

        $output = [];
        foreach ($envLines as $line) {
            $trimmed = trim($line);
            if (strpos($trimmed, '=') === false) { $output[] = $line; continue; }
            list($key) = explode('=', $trimmed, 2);
            $envKey = trim($key);
            if (isset($newVars[$envKey]) && !empty($newVars[$envKey])) {
                $output[] = $envKey . '=' . $newVars[$envKey];
                unset($newVars[$envKey]);
            } else {
                $output[] = $line;
            }
        }

        foreach ($newVars as $key => $value) {
            if (!empty($value) && !isset($existing[$key])) {
                $output[] = $key . '=' . $value;
            }
        }

        file_put_contents($envFile, implode("\n", $output) . "\n");
    }

    private function writeSettingsFile(array $data): void
    {
        $settingsFile = __DIR__ . '/../../config/settings.php';
        $settings = [];
        if (file_exists($settingsFile)) {
            $settings = require $settingsFile;
        }

        $settings['site_name'] = $data['site_name'] ?? $settings['site_name'] ?? 'PteroBilling';
        $settings['site_url'] = $data['panel_url'] ?? $settings['site_url'] ?? '';
        $settings['custom_domain'] = $data['panel_domain'] ?? $settings['custom_domain'] ?? '';
        $settings['stripe_enabled'] = !empty($data['stripe_secret']);
        $settings['stripe_secret'] = $data['stripe_secret'] ?? $settings['stripe_secret'] ?? '';
        $settings['stripe_public'] = $data['stripe_public'] ?? $settings['stripe_public'] ?? '';
        $settings['stripe_webhook_secret'] = $data['stripe_webhook'] ?? $settings['stripe_webhook_secret'] ?? '';
        $settings['paypal_enabled'] = !empty($data['paypal_client_id']);
        $settings['paypal_client_id'] = $data['paypal_client_id'] ?? $settings['paypal_client_id'] ?? '';
        $settings['paypal_client_secret'] = $data['paypal_secret'] ?? $settings['paypal_client_secret'] ?? '';
        $settings['paypal_mode'] = $data['paypal_mode'] ?? $settings['paypal_mode'] ?? 'live';
        $settings['credits_enabled'] = true;
        $settings['min_deposit'] = (float)($data['min_deposit'] ?? $settings['min_deposit'] ?? 1.00);
        $settings['max_deposit'] = (float)($data['max_deposit'] ?? $settings['max_deposit'] ?? 1000.00);
        $settings['currency'] = $data['currency'] ?? $settings['currency'] ?? 'USD';
        $settings['ptero_url'] = $data['ptero_url'] ?? $settings['ptero_url'] ?? '';
        $settings['ptero_api_key'] = $data['ptero_api_key'] ?? $settings['ptero_api_key'] ?? '';

        file_put_contents($settingsFile, '<?php return ' . var_export($settings, true) . ";\n");
    }

    public function checkApi($request, $response)
    {
        $data = $request->getParsedBody();
        $url = $data['url'] ?? '';
        $key = $data['key'] ?? '';

        if (empty($url) || empty($key)) {
            return $response->withJson(['success' => false, 'message' => 'URL and API key are required']);
        }

        $ch = curl_init(rtrim($url, '/') . '/api/application/users');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $key, 'Accept: application/json'],
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return $response->withJson(['success' => false, 'message' => 'Connection error: ' . $error]);
        }

        if ($httpCode === 200) {
            $data = json_decode($result, true);
            $userCount = count($data['data'] ?? []);
            return $response->withJson(['success' => true, 'message' => "Connected! Found {$userCount} users in Pterodactyl."]);
        }

        return $response->withJson(['success' => false, 'message' => 'API returned status ' . $httpCode . '. Check your key.']);
    }

    private function isSetupComplete(): bool
    {
        $envFile = __DIR__ . '/../../.env';
        if (!file_exists($envFile)) return false;
        $env = file_get_contents($envFile);
        if (preg_match('/PTERODACTYL_API_KEY=(.+)/', $env, $matches)) {
            $key = trim($matches[1]);
            return !empty($key) && $key !== 'change-this-to-your-ptero-api-key';
        }
        return false;
    }

    private function getSetupData(): array
    {
        return $this->session->get('setup_data', []);
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
