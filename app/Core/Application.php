<?php
declare(strict_types=1);

namespace App\Core;

use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteCollectorInterface;
use App\Middleware\AuthMiddleware;
use App\Middleware\AdminMiddleware;
use App\Middleware\CSRFMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\SecurityHeadersMiddleware;
use App\Core\Database;
use App\Core\Session;
use App\Core\Router;

class Application
{
    private App $app;
    private Database $db;
    private Session $session;

    public function __construct()
    {
        $this->app = AppFactory::create();
        $this->db = Database::getInstance();
        $this->session = new Session();

        $this->registerMiddleware();
        $this->registerRoutes();
        $this->registerErrorHandlers();
    }

    private function registerMiddleware(): void
    {
        $app = $this->app;

        $app->add(new SecurityHeadersMiddleware());
        $app->add(new CSRFMiddleware());
        $app->add(new RateLimitMiddleware());
        $app->add(new AuthMiddleware($this->session));
        $app->addBodyParsingMiddleware();
        $app->addRoutingMiddleware();
    }

    private function registerRoutes(): void
    {
        Router::load($this->app);
    }

    private function registerErrorHandlers(): void
    {
        $errorMiddleware = $this->app->addErrorMiddleware(
            (bool)$_ENV['APP_DEBUG'],
            true,
            true
        );

        $errorMiddleware->setDefaultErrorHandler(
            function ($request, $exception) {
                $logger = $this->db->getLogger();
                $logger->error($exception->getMessage(), [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString()
                ]);

                $response = new \Slim\Psr7\Response();
                $response->getBody()->write('Internal Server Error');
                return $response->withStatus(500);
            }
        );
    }

    public function run(): void
    {
        $this->app->run();
    }
}