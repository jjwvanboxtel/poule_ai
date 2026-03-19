<?php declare(strict_types=1);

namespace App\Support\Routing;

use App\Http\Controllers\ErrorController;
use App\Http\Requests\Request;
use App\Support\Container;

final class Router
{
    /** @var list<array{method: string, pattern: string, handler: array{0: class-string, 1: string}|callable}> */
    private array $routes = [];

    public function __construct(private readonly Container $container)
    {
    }

    /** @param array{0: class-string, 1: string}|callable $handler */
    public function get(string $path, array|callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /** @param array{0: class-string, 1: string}|callable $handler */
    public function post(string $path, array|callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /** @param array{0: class-string, 1: string}|callable $handler */
    private function addRoute(string $method, string $path, array|callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $path,
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchPattern($route['pattern'], $path);
            if ($params === null) {
                continue;
            }

            $request->setRouteParams($params);
            $this->callHandler($route['handler'], $request);

            return;
        }

        // No route matched — show 404
        $errorController = new ErrorController($this->container);
        $errorController->notFound($request);
    }

    /**
     * Match a route pattern against a path, returning captured params or null.
     *
     * @return array<string, string>|null
     */
    private function matchPattern(string $pattern, string $path): ?array
    {
        // Convert {param} placeholders to named captures
        $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (!preg_match($regex, $path, $matches)) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    /** @param array{0: class-string, 1: string}|callable $handler */
    private function callHandler(array|callable $handler, Request $request): void
    {
        if (is_callable($handler)) {
            $handler($request);

            return;
        }

        [$controllerClass, $method] = $handler;
        $controller = $this->container->has($controllerClass)
            ? $this->container->get($controllerClass)
            : new $controllerClass($this->container);

        $controller->$method($request);
    }
}
