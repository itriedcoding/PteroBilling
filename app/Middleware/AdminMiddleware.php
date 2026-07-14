<?php
declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response;

class AdminMiddleware
{
    public function __invoke(Request $request, Handler $handler): Response
    {
        $user = $request->getAttribute('user');

        if (!$user || $user['role'] !== 'admin') {
            $response = new Response();
            $response->withHeader('Location', '/')->withStatus(302);
            return $response;
        }

        return $handler->handle($request);
    }
}
