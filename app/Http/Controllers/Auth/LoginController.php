<?php declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Request;
use App\Infrastructure\Security\SessionAuthenticator;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\View\ViewRenderer;

final class LoginController
{
    private readonly ViewRenderer $renderer;
    private readonly SessionManager $session;
    private readonly SessionAuthenticator $authenticator;

    public function __construct(Container $container)
    {
        $renderer = $container->get(ViewRenderer::class);
        $session = $container->get(SessionManager::class);
        $authenticator = $container->get(SessionAuthenticator::class);

        if (!$renderer instanceof ViewRenderer || !$session instanceof SessionManager || !$authenticator instanceof SessionAuthenticator) {
            throw new \RuntimeException('LoginController dependencies are invalid.');
        }

        $this->renderer = $renderer;
        $this->session = $session;
        $this->authenticator = $authenticator;
    }

    public function create(Request $request): void
    {
        echo $this->renderer->render('auth/login', [
            'title' => 'Inloggen',
            'errors' => $this->flashedArray('errors'),
            'old' => $this->flashedArray('old'),
            'intended' => $this->stringValue($request->query('intended', '')),
        ]);
    }

    public function store(Request $request): void
    {
        $email = trim($this->stringValue($request->post('email', '')));
        $password = $this->stringValue($request->post('password', ''));
        $intended = trim($this->stringValue($request->post('intended', '')));

        if (!$this->authenticator->attempt($email, $password)) {
            $this->session->flash('error', 'De combinatie van e-mailadres en wachtwoord klopt niet.');
            $this->session->flash('errors', ['email' => 'Controleer je inloggegevens.']);
            $this->session->flash('old', ['email' => $email]);
            $this->redirect('/login' . ($intended !== '' ? '?intended=' . urlencode($intended) : ''));
        }

        $this->session->flash('success', 'Welkom terug!');
        $this->redirect($this->normalizeIntendedPath($intended));
    }

    public function destroy(Request $request): void
    {
        $this->authenticator->logout();

        http_response_code(302);
        header('Location: /');
        exit;
    }

    /**
     * @return array<string, string>
     */
    private function flashedArray(string $key): array
    {
        $value = $this->session->getFlash($key, []);

        if (!is_array($value)) {
            return [];
        }

        $result = [];

        foreach ($value as $itemKey => $item) {
            if (is_string($itemKey) && is_scalar($item)) {
                $result[$itemKey] = (string) $item;
            }
        }

        return $result;
    }

    private function normalizeIntendedPath(string $path): string
    {
        if ($path === '' || !str_starts_with($path, '/')) {
            return '/dashboard';
        }

        return $path;
    }

    private function stringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    private function redirect(string $location): void
    {
        http_response_code(302);
        header('Location: ' . $location);
        exit;
    }
}
