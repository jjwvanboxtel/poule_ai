<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Competitions\UpdateParticipantPaymentStatusService;
use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionParticipantRepository;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\View\ViewRenderer;
use DomainException;

final class CompetitionParticipantController
{
    private readonly ViewRenderer $renderer;
    private readonly SessionManager $session;
    private readonly PdoCompetitionRepository $competitions;
    private readonly PdoCompetitionParticipantRepository $participants;
    private readonly UpdateParticipantPaymentStatusService $paymentService;

    public function __construct(Container $container)
    {
        $renderer = $container->get(ViewRenderer::class);
        $session = $container->get(SessionManager::class);
        $competitions = $container->get(PdoCompetitionRepository::class);
        $participants = $container->get(PdoCompetitionParticipantRepository::class);
        $paymentService = $container->get(UpdateParticipantPaymentStatusService::class);

        if (
            !$renderer instanceof ViewRenderer
            || !$session instanceof SessionManager
            || !$competitions instanceof PdoCompetitionRepository
            || !$participants instanceof PdoCompetitionParticipantRepository
            || !$paymentService instanceof UpdateParticipantPaymentStatusService
        ) {
            throw new \RuntimeException('CompetitionParticipantController dependencies are invalid.');
        }

        $this->renderer = $renderer;
        $this->session = $session;
        $this->competitions = $competitions;
        $this->participants = $participants;
        $this->paymentService = $paymentService;
    }

    public function index(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $competition = $this->competitions->findById($id);

        if ($competition === null) {
            http_response_code(404);
            echo '<h1>404 Niet gevonden</h1>';
            return;
        }

        $participantList = $this->participants->findByCompetitionId($id);

        echo $this->renderer->render('admin/competitions/participants', [
            'title' => "Deelnemers: {$competition->name}",
            'competition' => $competition,
            'participants' => $participantList,
            'allUsers' => [],
        ]);
    }

    public function updatePayment(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $participantId = (int) $request->routeParam('participantId');
        $status = $this->stringValue($request->post('payment_status', 'unpaid'));

        try {
            $this->paymentService->update($participantId, $status);
            $this->session->flash('success', 'Betaalstatus bijgewerkt.');
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
