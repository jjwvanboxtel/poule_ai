<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Request;
use App\Support\Container;
use App\Support\View\ViewRenderer;

final class ErrorController
{
    private readonly ViewRenderer $renderer;

    public function __construct(Container $container)
    {
        $renderer = $container->get(ViewRenderer::class);
        if (!$renderer instanceof ViewRenderer) {
            throw new \RuntimeException('ViewRenderer binding is invalid.');
        }

        $this->renderer = $renderer;
    }

    public function notFound(Request $request): void
    {
        http_response_code(404);

        echo $this->renderer->render('errors/404', [
            'title' => 'Pagina niet gevonden',
            'path' => $request->getPath(),
        ]);
    }

    public function serverError(Request $request, \Throwable $e): void
    {
        http_response_code(500);

        /** @var array{app: array{debug: bool}} $config */
        $config = $this->renderer->getConfig();
        $debug = $config['app']['debug'];

        echo $this->renderer->render('errors/500', [
            'title' => 'Serverfout',
            'message' => $debug ? $e->getMessage() : 'Er is een interne fout opgetreden.',
            'trace' => $debug ? $e->getTraceAsString() : '',
        ]);
    }

    public function forbidden(Request $request): void
    {
        http_response_code(403);

        echo $this->renderer->render('errors/403', [
            'title' => 'Toegang geweigerd',
        ]);
    }
}
