<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Requests\Request;
use App\Support\Container;
use App\Support\Sessions\SessionManager;

final class VerifyCsrfToken
{
    private readonly SessionManager $session;
    private readonly string $tokenName;

    public function __construct(Container $container)
    {
        $session = $container->get(SessionManager::class);
        if (!$session instanceof SessionManager) {
            throw new \RuntimeException('SessionManager binding is invalid.');
        }

        $this->session = $session;

        /** @var array{security: array{csrf_token_name: string, csrf_token_length: int}} $config */
        $config = $container->get('config');
        $this->tokenName = $config['security']['csrf_token_name'];
    }

    /**
     * Ensure a CSRF token exists in the session and, for mutating requests,
     * verify that the submitted token matches.
     */
    public function handle(Request $request, callable $next): void
    {
        $this->ensureTokenExists();

        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $submitted = self::stringValue($request->post($this->tokenName, ''));
            if (!$this->verify($submitted)) {
                http_response_code(403);
                echo '<h1>403 CSRF token mismatch</h1>';
                exit;
            }
        }

        $next($request);
    }

    public function token(): string
    {
        $this->ensureTokenExists();

        return self::stringValue($this->session->get('csrf_token', ''));
    }

    public function field(): string
    {
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            htmlspecialchars($this->tokenName, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($this->token(), ENT_QUOTES, 'UTF-8'),
        );
    }

    private function ensureTokenExists(): void
    {
        if (!$this->session->has('csrf_token')) {
            $this->session->set('csrf_token', bin2hex(random_bytes(20)));
            $this->session->set('csrf_token_name', $this->tokenName);
        }
    }

    private function verify(string $submitted): bool
    {
        $stored = self::stringValue($this->session->get('csrf_token', ''));

        return $stored !== '' && hash_equals($stored, $submitted);
    }

    private static function stringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
