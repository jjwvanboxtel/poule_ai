<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Infrastructure\Persistence\Pdo\PdoMatchManagementRepository;
use App\Infrastructure\Persistence\Pdo\PdoMatchResultRepository;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\View\ViewRenderer;

final class MatchResultController
{
    private readonly ViewRenderer $renderer;
    private readonly SessionManager $session;
    private readonly PdoCompetitionRepository $competitions;
    private readonly PdoMatchManagementRepository $matchRepo;
    private readonly PdoMatchResultRepository $resultRepo;

    public function __construct(Container $container)
    {
        $renderer = $container->get(ViewRenderer::class);
        $session = $container->get(SessionManager::class);
        $competitions = $container->get(PdoCompetitionRepository::class);
        $matchRepo = $container->get(PdoMatchManagementRepository::class);
        $resultRepo = $container->get(PdoMatchResultRepository::class);

        if (!$renderer instanceof ViewRenderer) throw new \RuntimeException('ViewRenderer invalid');
        if (!$session instanceof SessionManager) throw new \RuntimeException('Session invalid');
        if (!$competitions instanceof PdoCompetitionRepository) throw new \RuntimeException('Competitions invalid');
        if (!$matchRepo instanceof PdoMatchManagementRepository) throw new \RuntimeException('MatchRepo invalid');
        if (!$resultRepo instanceof PdoMatchResultRepository) throw new \RuntimeException('ResultRepo invalid');

        $this->renderer = $renderer;
        $this->session = $session;
        $this->competitions = $competitions;
        $this->matchRepo = $matchRepo;
        $this->resultRepo = $resultRepo;
    }

    public function edit(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $matchId = (int) $request->routeParam('matchId');
        $competition = $this->competitions->findById($id);
        $match = $this->matchRepo->findMatchById($matchId);

        if ($competition === null || $match === null) {
            http_response_code(302);
            header('Location: /admin/competitions/' . $id . '/matches');
            exit;
        }

        $result = $this->resultRepo->findByMatchId($matchId);

        echo $this->renderer->render('admin/results/edit', [
            'title' => 'Uitslag invoeren',
            'competition' => $competition,
            'match' => $match,
            'result' => $result,
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $matchId = (int) $request->routeParam('matchId');
        $post = $request->allPost();

        $homeScore = is_numeric($post['home_score'] ?? null) ? (int) $post['home_score'] : null;
        $awayScore = is_numeric($post['away_score'] ?? null) ? (int) $post['away_score'] : null;
        $outcome = is_string($post['result_outcome'] ?? null) && $post['result_outcome'] !== '' ? $post['result_outcome'] : null;
        $yellowHome = is_numeric($post['yellow_cards_home'] ?? null) ? (int) $post['yellow_cards_home'] : 0;
        $yellowAway = is_numeric($post['yellow_cards_away'] ?? null) ? (int) $post['yellow_cards_away'] : 0;
        $redHome = is_numeric($post['red_cards_home'] ?? null) ? (int) $post['red_cards_home'] : 0;
        $redAway = is_numeric($post['red_cards_away'] ?? null) ? (int) $post['red_cards_away'] : 0;

        try {
            $this->resultRepo->save($matchId, $homeScore, $awayScore, $outcome, $yellowHome, $yellowAway, $redHome, $redAway);
            $this->session->flash('success', 'Uitslag opgeslagen.');
        } catch (\Throwable $e) {
            $this->session->flash('error', $e->getMessage());
        }

        http_response_code(302);
        header('Location: /admin/competitions/' . $id . '/results/' . $matchId . '/edit');
        exit;
    }
}
