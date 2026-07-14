<?php
declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response;
use App\Core\Session;
use App\Models\User;

class AuthMiddleware
{
    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function __invoke(Request $request, Handler $handler): Response
    {
        $userId = $this->session->get('user_id');

        if (!$userId) {
            $response = new Response();
            $response->withHeader('Location', '/auth/login')->withStatus(302);
            return $response;
        }

        $user = new User();
        $userData = $user->findById((int)$userId);

        if (!$userData) {
            $this->session->destroy();
            $response = new Response();
            $response->withHeader('Location', '/auth/login')->withStatus(302);
            return $response;
        }

        $request = $request->withAttribute('user', $userData);
        return $handler->handle($request);
    }
}
