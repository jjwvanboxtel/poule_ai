<?php declare(strict_types=1);

namespace App\Http\Requests;

final class Request
{
    /** @var array<string, string> */
    private array $routeParams = [];

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $post
     * @param array<string, mixed> $server
     * @param array<string, mixed> $files
     * @param array<string, mixed> $cookies
     */
    private function __construct(
        private readonly array $query,
        private readonly array $post,
        private readonly array $server,
        private readonly array $files,
        private readonly array $cookies,
    ) {
    }

    public static function fromGlobals(): self
    {
        return new self($_GET, $_POST, $_SERVER, $_FILES, $_COOKIE);
    }

    public function getMethod(): string
    {
        return strtoupper($this->serverString('REQUEST_METHOD', 'GET'));
    }

    public function getPath(): string
    {
        $uri = $this->serverString('REQUEST_URI', '/');
        $path = parse_url($uri, PHP_URL_PATH);
        $normalizedPath = is_string($path) && $path !== '' ? $path : '/';

        return '/' . ltrim($normalizedPath, '/');
    }

    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    /** @param array<string, string> $params */
    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function routeParam(string $key, string $default = ''): string
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    /** @return array<string, mixed> */
    public function allPost(): array
    {
        return $this->post;
    }

    /** @return array<string, mixed>|null */
    public function file(string $key): ?array
    {
        $file = $this->files[$key] ?? null;

        return is_array($file) ? $file : null;
    }

    public function getClientIp(): string
    {
        return $this->serverString('REMOTE_ADDR', '0.0.0.0');
    }

    public function header(string $name): string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

        return $this->serverString($key);
    }

    private function serverString(string $key, string $default = ''): string
    {
        $value = $this->server[$key] ?? $default;

        return is_scalar($value) ? (string) $value : $default;
    }
}
