<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Competitions\UpdateKnockoutRoundsService;
use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Infrastructure\Persistence\Pdo\PdoKnockoutRoundRepository;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\View\ViewRenderer;
use DomainException;

final class KnockoutRoundController
{
    private readonly ViewRenderer $renderer;
    private readonly SessionManager $session;
    private readonly PdoCompetitionRepository $competitions;
    private readonly PdoKnockoutRoundRepository $knockoutRounds;
    private readonly UpdateKnockoutRoundsService $updateService;

    public function __construct(Container $container)
    {
        $renderer = $container->get(ViewRenderer::class);
        $session = $container->get(SessionManager::class);
        $competitions = $container->get(PdoCompetitionRepository::class);
        $knockoutRounds = $container->get(PdoKnockoutRoundRepository::class);
        $updateService = $container->get(UpdateKnockoutRoundsService::class);

        if (
            !$renderer instanceof ViewRenderer
            || !$session instanceof SessionManager
            || !$competitions instanceof PdoCompetitionRepository
            || !$knockoutRounds instanceof PdoKnockoutRoundRepository
            || !$updateService instanceof UpdateKnockoutRoundsService
        ) {
            throw new \RuntimeException('KnockoutRoundController dependencies are invalid.');
        }

        $this->renderer = $renderer;
        $this->session = $session;
        $this->competitions = $competitions;
        $this->knockoutRounds = $knockoutRounds;
        $this->updateService = $updateService;
    }

    public function edit(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $competition = $this->competitions->findById($id);

        if ($competition === null) {
            http_response_code(404);
            echo '<h1>404 Niet gevonden</h1>';
            return;
        }

        $rounds = $this->knockoutRounds->findActiveRounds($id);
        $entities = $this->competitions->findActiveEntitiesForCompetition($id);

        echo $this->renderer->render('admin/competitions/knockout-rounds', [
            'title' => "Knock-out rondes: {$competition->name}",
            'competition' => $competition,
            'rounds' => $rounds,
            'entities' => $entities,
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int) $request->routeParam('id');

        /** @var array<int, array<string, mixed>> $rawRounds */
        $rawRounds = $request->post('rounds', []);
        if (!is_array($rawRounds)) {
            $rawRounds = [];
        }

        $rounds = [];
        foreach ($rawRounds as $r) {
            if (!is_array($r)) {
                continue;
            }
            $rounds[] = [
                'label' => is_scalar($r['label'] ?? null) ? (string) $r['label'] : '',
                'round_order' => is_numeric($r['round_order'] ?? null) ? (int) $r['round_order'] : 0,
                'team_slot_count' => is_numeric($r['team_slot_count'] ?? null) ? (int) $r['team_slot_count'] : 2,
                'is_active' => isset($r['is_active']) && (bool) $r['is_active'],
                'teams' => [],
            ];
        }

        try {
            $this->updateService->update($id, $rounds);
            $this->session->flash('success', 'Knock-out rondes opgeslagen.');
        } catch (DomainException $e) {
            $this->session->flash('error', $e->getMessage());
        }

        $this->redirect("/admin/competitions/{$id}/knockout-rounds");
    }

    private function redirect(string $location): never
    {
        http_response_code(302);
        header('Location: ' . $location);
        exit;
    }
}
