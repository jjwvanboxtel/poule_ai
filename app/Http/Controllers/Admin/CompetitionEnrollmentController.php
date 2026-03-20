<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Competitions\EnrollParticipantService;
use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Infrastructure\Persistence\Pdo\PdoUserRepository;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\View\ViewRenderer;
use DomainException;

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

        if (
            !$renderer instanceof ViewRenderer
            || !$session instanceof SessionManager
            || !$competitions instanceof PdoCompetitionRepository
            || !$users instanceof PdoUserRepository
            || !$enrollService instanceof EnrollParticipantService
        ) {
            throw new \RuntimeException('CompetitionEnrollmentController dependencies are invalid.');
        }

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
            http_response_code(404);
            echo '<h1>404 Niet gevonden</h1>';
            return;
        }

        $allUsers = $this->users->findAll();

        echo $this->renderer->render('admin/competitions/participants', [
            'title' => "Deelnemers: {$competition->name}",
            'competition' => $competition,
            'allUsers' => $allUsers,
            'participants' => [],
        ]);
    }

    public function store(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $userId = (int) $this->stringValue($request->post('user_id', '0'));

        try {
            $this->enrollService->enroll($id, $userId);
            $this->session->flash('success', 'Deelnemer ingeschreven.');
        } catch (DomainException $e) {
            $this->session->flash('error', $e->getMessage());
        }

        $this->redirect("/admin/competitions/{$id}/participants");
    }

    public function destroy(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $participantId = (int) $request->routeParam('participantId');

        try {
            $this->enrollService->unenroll($participantId);
            $this->session->flash('success', 'Inschrijving verwijderd.');
        } catch (DomainException $e) {
            $this->session->flash('error', $e->getMessage());
        }

        $this->redirect("/admin/competitions/{$id}/participants");
    }

    private function stringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    private function redirect(string $location): never
    {
        http_response_code(302);
        header('Location: ' . $location);
        exit;
    }
}
