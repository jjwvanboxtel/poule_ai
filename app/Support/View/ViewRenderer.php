<?php declare(strict_types=1);

namespace App\Support\View;

use App\Support\Sessions\SessionManager;
use RuntimeException;

final class ViewRenderer
{
    public function __construct(
        private readonly string $viewsPath,
        private readonly SessionManager $session,
        private readonly bool $debug = false,
    ) {
    }

    /**
     * Render a view (with optional layout) and return HTML.
     *
     * @param array<string, mixed> $data
     */
    public function render(string $view, array $data = [], ?string $layout = 'layouts/app'): string
    {
        $viewFile = $this->resolvePath($view);

        // Render the inner content
        $content = $this->renderFile($viewFile, array_merge($data, [
            'renderer' => $this,
            'session' => $this->session,
        ]));

        if ($layout === null) {
            return $content;
        }

        $layoutFile = $this->resolvePath($layout);

        return $this->renderFile($layoutFile, array_merge($data, [
            'content' => $content,
            'renderer' => $this,
            'session' => $this->session,
        ]));
    }

    /**
     * Render a partial view (no layout).
     *
     * @param array<string, mixed> $data
     */
    public function partial(string $view, array $data = []): string
    {
        $viewFile = $this->resolvePath($view);

        return $this->renderFile($viewFile, array_merge($data, [
            'renderer' => $this,
            'session' => $this->session,
        ]));
    }

    /**
     * HTML-escape a value for safe output.
     */
    public function e(mixed $value): string
    {
        return Escaper::html($value);
    }

    /** @return array{app: array{debug: bool}} */
    public function getConfig(): array
    {
        return ['app' => ['debug' => $this->debug]];
    }

    private function resolvePath(string $view): string
    {
        $path = $this->viewsPath . '/' . ltrim($view, '/') . '.php';
        if (!file_exists($path)) {
            throw new RuntimeException("View [{$view}] not found at [{$path}].");
        }

        return $path;
    }

    /** @param array<string, mixed> $data */
    private function renderFile(string $__path, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();

        try {
            include $__path;

            return (string) ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }
}
