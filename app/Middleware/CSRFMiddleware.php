<?php
declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response;
use App\Core\Session;

class CSRFMiddleware
{
    public function __invoke(Request $request, Handler $handler): Response
    {
        $session = new Session();

        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $token = $request->getParsedBody()['csrf_token'] ?? 
                     $request->getHeaderLine('X-CSRF-Token');

            if (!$token || !hash_equals($session->get('csrf_token', ''), $token)) {
                $response = new Response();
                $response->getBody()->write('CSRF token mismatch');
                return $response->withStatus(403);
            }
        }

        if (!$session->has('csrf_token')) {
            $session->set('csrf_token', bin2hex(random_bytes(32)));
        }

        return $handler->handle($request);
    }
}
