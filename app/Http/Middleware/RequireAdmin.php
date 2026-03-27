<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Requests\Request;
use App\Infrastructure\Security\SessionAuthenticator;
use App\Support\Container;
use App\Support\View\ViewRenderer;

final class RequireAdmin
{
    private readonly SessionAuthenticator $auth;
    private readonly ViewRenderer $renderer;

    public function __construct(Container $container)
    {
        $auth = $container->get(SessionAuthenticator::class);
        $renderer = $container->get(ViewRenderer::class);

        if (!$auth instanceof SessionAuthenticator) {
            throw new \RuntimeException('SessionAuthenticator binding is invalid.');
        }
        if (!$renderer instanceof ViewRenderer) {
            throw new \RuntimeException('ViewRenderer binding is invalid.');
        }

        $this->auth = $auth;
        $this->renderer = $renderer;
    }

    public function handle(Request $request, callable $next): void
    {
        if (!$this->auth->check()) {
            http_response_code(302);
            header('Location: /login?intended=' . urlencode($request->getPath()));
            exit;
        }

        if (!$this->auth->isAdmin()) {
            http_response_code(403);
            echo $this->renderer->render('errors/forbidden', ['title' => 'Toegang geweigerd'], 'layouts/app');
            exit;
        }

        $next($request);
    }
}
