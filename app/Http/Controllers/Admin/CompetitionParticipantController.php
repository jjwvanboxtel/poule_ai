<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Competitions\UpdateParticipantPaymentStatusService;
use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionParticipantRepository;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\View\ViewRenderer;

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

        if (!$renderer instanceof ViewRenderer) throw new \RuntimeException('ViewRenderer invalid');
        if (!$session instanceof SessionManager) throw new \RuntimeException('Session invalid');
        if (!$competitions instanceof PdoCompetitionRepository) throw new \RuntimeException('Competitions invalid');
        if (!$participants instanceof PdoCompetitionParticipantRepository) throw new \RuntimeException('Participants invalid');
        if (!$paymentService instanceof UpdateParticipantPaymentStatusService) throw new \RuntimeException('PaymentService invalid');

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
            http_response_code(302);
            header('Location: /admin/competitions');
            exit;
        }

        $rows = $this->participants->findByCompetition($id);

        echo $this->renderer->render('admin/competitions/participants', [
            'title' => 'Deelnemers',
            'competition' => $competition,
            'participants' => $rows,
        ]);
    }

    public function updatePayment(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $userId = (int) $request->routeParam('userId');
        $post = $request->allPost();
        $status = is_string($post['payment_status'] ?? null) ? $post['payment_status'] : 'unpaid';

        try {
            $this->paymentService->update($id, $userId, $status);
            $this->session->flash('success', 'Betaalstatus bijgewerkt.');
        } catch (\DomainException $e) {
            $this->session->flash('error', $e->getMessage());
        }

        http_response_code(302);
        header('Location: /admin/competitions/' . $id . '/participants');
        exit;
    }
}
