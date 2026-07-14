<?php
declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class RateLimitMiddleware
{
    private $cache;
    private int $maxAttempts;
    private int $decayMinutes;

    public function __construct(int $maxAttempts = 60, int $decayMinutes = 1)
    {
        $this->cache = new FilesystemAdapter('rate_limit', 600, __DIR__ . '/../../storage/cache');
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }

    public function __invoke(Request $request, Handler $handler): Response
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
        $key = 'rate_limit_' . md5($ip . $request->getUri()->getPath());

        $item = $this->cache->getItem($key);

        if (!$item->isHit()) {
            $item->set(1);
            $item->expiresAfter($this->decayMinutes * 60);
            $this->cache->save($item);
        } else {
            $attempts = $item->get();
            if ($attempts >= $this->maxAttempts) {
                $response = new Response();
                $response->getBody()->write(json_encode([
                    'error' => 'Too many requests. Please try again later.'
                ]));
                return $response->withStatus(429)
                    ->withHeader('Content-Type', 'application/json')
                    ->withHeader('Retry-After', (string)($this->decayMinutes * 60));
            }
            $item->set($attempts + 1);
            $this->cache->save($item);
        }

        return $handler->handle($request);
    }
}
