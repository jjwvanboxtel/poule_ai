<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Competitions\EnrollParticipantService;
use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Infrastructure\Persistence\Pdo\PdoUserRepository;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\View\ViewRenderer;

final class CompetitionEnrollmentController
{
    private readonly ViewRenderer $renderer;
    private readonly SessionManager $session;
    private readonly PdoCompetitionRepository $competitions;
    private readonly PdoUserRepository $users;
    private readonly EnrollParticipantService $enrollService;

    public function __construct(Container $container)
    {
        $renderer = $container->get(ViewRenderer::class);
        $session = $container->get(SessionManager::class);
        $competitions = $container->get(PdoCompetitionRepository::class);
        $users = $container->get(PdoUserRepository::class);
        $enrollService = $container->get(EnrollParticipantService::class);

        if (!$renderer instanceof ViewRenderer) throw new \RuntimeException('ViewRenderer invalid');
        if (!$session instanceof SessionManager) throw new \RuntimeException('Session invalid');
        if (!$competitions instanceof PdoCompetitionRepository) throw new \RuntimeException('Competitions invalid');
        if (!$users instanceof PdoUserRepository) throw new \RuntimeException('Users invalid');
        if (!$enrollService instanceof EnrollParticipantService) throw new \RuntimeException('EnrollService invalid');

        $this->renderer = $renderer;
        $this->session = $session;
        $this->competitions = $competitions;
        $this->users = $users;
        $this->enrollService = $enrollService;
    }

    public function create(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $competition = $this->competitions->findById($id);
        if ($competition === null) {
            http_response_code(302);
            header('Location: /admin/competitions');
            exit;
        }

        echo $this->renderer->render('admin/competitions/participants', [
            'title' => 'Deelnemer inschrijven',
            'competition' => $competition,
            'users' => $this->users->findAll(),
        ]);
    }

    public function store(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $post = $request->allPost();
        $userId = is_numeric($post['user_id'] ?? null) ? (int) $post['user_id'] : 0;

        try {
            $this->enrollService->enroll($id, $userId);
            $this->session->flash('success', 'Deelnemer ingeschreven.');
        } catch (\DomainException $e) {
            $this->session->flash('error', $e->getMessage());
        }

        http_response_code(302);
        header('Location: /admin/competitions/' . $id . '/participants');
        exit;
    }
}
