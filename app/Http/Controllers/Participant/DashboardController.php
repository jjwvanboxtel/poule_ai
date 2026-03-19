<?php declare(strict_types=1);

namespace App\Http\Controllers\Participant;

use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Infrastructure\Security\SessionAuthenticator;
use App\Support\Container;
use App\Support\View\ViewRenderer;

final class DashboardController
{
    private readonly ViewRenderer $renderer;
    private readonly SessionAuthenticator $authenticator;
    private readonly PdoCompetitionRepository $competitions;

    public function __construct(Container $container)
    {
        $renderer = $container->get(ViewRenderer::class);
        $authenticator = $container->get(SessionAuthenticator::class);
        $competitions = $container->get(PdoCompetitionRepository::class);

        if (!$renderer instanceof ViewRenderer || !$authenticator instanceof SessionAuthenticator || !$competitions instanceof PdoCompetitionRepository) {
            throw new \RuntimeException('DashboardController dependencies are invalid.');
        }

        $this->renderer = $renderer;
        $this->authenticator = $authenticator;
        $this->competitions = $competitions;
    }

    public function index(Request $request): void
    {
        $user = $this->authenticator->user();
        if ($user === null) {
            http_response_code(302);
            header('Location: /login');
            exit;
        }

        echo $this->renderer->render('participants/dashboard', [
            'title' => 'Dashboard',
            'user' => $user,
            'joinedCompetitions' => $this->competitions->findCompetitionsForUser($user->id),
            'openCompetitions' => $this->competitions->findOpenCompetitionsForUser($user->id),
        ]);
    }
}
