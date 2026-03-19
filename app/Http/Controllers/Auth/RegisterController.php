<?php declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Infrastructure\Persistence\Pdo\PdoUserRepository;
use App\Infrastructure\Security\SessionAuthenticator;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\View\ViewRenderer;

final class RegisterController
{
    private readonly ViewRenderer $renderer;
    private readonly SessionManager $session;
    private readonly PdoUserRepository $users;
    private readonly PdoCompetitionRepository $competitions;
    private readonly SessionAuthenticator $authenticator;

    public function __construct(Container $container)
    {
        $renderer = $container->get(ViewRenderer::class);
        $session = $container->get(SessionManager::class);
        $users = $container->get(PdoUserRepository::class);
        $competitions = $container->get(PdoCompetitionRepository::class);
        $authenticator = $container->get(SessionAuthenticator::class);

        if (
            !$renderer instanceof ViewRenderer
            || !$session instanceof SessionManager
            || !$users instanceof PdoUserRepository
            || !$competitions instanceof PdoCompetitionRepository
            || !$authenticator instanceof SessionAuthenticator
        ) {
            throw new \RuntimeException('RegisterController dependencies are invalid.');
        }

        $this->renderer = $renderer;
        $this->session = $session;
        $this->users = $users;
        $this->competitions = $competitions;
        $this->authenticator = $authenticator;
    }

    public function create(Request $request): void
    {
        echo $this->renderer->render('auth/register', [
            'title' => 'Registreren',
            'errors' => $this->flashedArray('errors'),
            'old' => $this->flashedArray('old'),
        ]);
    }

    public function store(Request $request): void
    {
        $old = [
            'first_name' => trim($this->stringValue($request->post('first_name', ''))),
            'last_name' => trim($this->stringValue($request->post('last_name', ''))),
            'email' => trim($this->stringValue($request->post('email', ''))),
            'phone_number' => trim($this->stringValue($request->post('phone_number', ''))),
        ];
        $password = $this->stringValue($request->post('password', ''));
        $errors = [];

        if ($old['first_name'] === '') {
            $errors['first_name'] = 'Voornaam is verplicht.';
        }

        if ($old['last_name'] === '') {
            $errors['last_name'] = 'Achternaam is verplicht.';
        }

        if ($old['email'] === '' || filter_var($old['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Vul een geldig e-mailadres in.';
        } elseif ($this->users->findByEmail($old['email']) !== null) {
            $errors['email'] = 'Er bestaat al een account met dit e-mailadres.';
        }

        if ($old['phone_number'] === '') {
            $errors['phone_number'] = 'Telefoonnummer is verplicht.';
        }

        if (strlen($password) < 8) {
            $errors['password'] = 'Het wachtwoord moet minimaal 8 tekens bevatten.';
        }

        if ($errors !== []) {
            $this->session->flash('error', 'Controleer het formulier en probeer opnieuw.');
            $this->session->flash('errors', $errors);
            $this->session->flash('old', $old);
            $this->redirect('/register');
        }

        $userId = $this->users->insert(
            $old['first_name'],
            $old['last_name'],
            $old['email'],
            $old['phone_number'],
            password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
        );

        $this->competitions->enrollUserInOpenCompetitions($userId);
        $this->authenticator->attempt($old['email'], $password);
        $this->session->flash('success', 'Je account is aangemaakt. Je kunt direct meedoen.');

        $this->redirect('/dashboard');
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

    private function redirect(string $location): void
    {
        http_response_code(302);
        header('Location: ' . $location);
        exit;
    }

    private function stringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
