<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Requests\Request;
use App\Infrastructure\Security\SessionAuthenticator;
use App\Support\Container;

final class RequireAuth
{
    private readonly SessionAuthenticator $auth;

    public function __construct(Container $container)
    {
        $auth = $container->get(SessionAuthenticator::class);
        if (!$auth instanceof SessionAuthenticator) {
            throw new \RuntimeException('SessionAuthenticator binding is invalid.');
        }

        $this->auth = $auth;
    }

    public function handle(Request $request, callable $next): void
    {
        if (!$this->auth->check()) {
            http_response_code(302);
            header('Location: /login?intended=' . urlencode($request->getPath()));
            exit;
        }

        $next($request);
    }
}
