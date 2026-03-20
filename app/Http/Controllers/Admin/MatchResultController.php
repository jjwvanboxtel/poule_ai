<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\View\ViewRenderer;
use PDO;

final class MatchResultController
{
    private readonly ViewRenderer $renderer;
    private readonly SessionManager $session;
    private readonly PdoCompetitionRepository $competitions;
    private readonly PDO $pdo;

    public function __construct(Container $container)
    {
        $renderer = $container->get(ViewRenderer::class);
        $session = $container->get(SessionManager::class);
        $competitions = $container->get(PdoCompetitionRepository::class);
        $pdo = $container->get(\PDO::class);

        if (
            !$renderer instanceof ViewRenderer
            || !$session instanceof SessionManager
            || !$competitions instanceof PdoCompetitionRepository
            || !$pdo instanceof \PDO
        ) {
            throw new \RuntimeException('MatchResultController dependencies are invalid.');
        }

        $this->renderer = $renderer;
        $this->session = $session;
        $this->competitions = $competitions;
        $this->pdo = $pdo;
    }

    public function edit(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $matchId = (int) $request->routeParam('matchId');

        $competition = $this->competitions->findById($id);
        if ($competition === null) {
            http_response_code(404);
            echo '<h1>404 Niet gevonden</h1>';
            return;
        }

        $stmt = $this->pdo->prepare(
            'SELECT m.id, m.stage, m.kickoff_at, m.status,
                    h.display_name AS home_label, a.display_name AS away_label
             FROM matches m
             INNER JOIN catalog_entities h ON h.id = m.home_entity_id
             INNER JOIN catalog_entities a ON a.id = m.away_entity_id
             WHERE m.id = ? AND m.competition_id = ?
             LIMIT 1',
        );
        $stmt->execute([$matchId, $id]);
        $match = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!is_array($match)) {
            http_response_code(404);
            echo '<h1>404 Niet gevonden</h1>';
            return;
        }

        $resultStmt = $this->pdo->prepare(
            'SELECT * FROM match_results WHERE match_id = ? LIMIT 1',
        );
        $resultStmt->execute([$matchId]);
        $existingResult = $resultStmt->fetch(\PDO::FETCH_ASSOC);
        $result = is_array($existingResult) ? $existingResult : null;

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

        $homeScore = (int) $this->stringValue($request->post('home_score', '0'));
        $awayScore = (int) $this->stringValue($request->post('away_score', '0'));
        $yellowHome = (int) $this->stringValue($request->post('yellow_cards_home', '0'));
        $yellowAway = (int) $this->stringValue($request->post('yellow_cards_away', '0'));
        $redHome = (int) $this->stringValue($request->post('red_cards_home', '0'));
        $redAway = (int) $this->stringValue($request->post('red_cards_away', '0'));

        $outcome = match(true) {
            $homeScore > $awayScore => 'home_win',
            $homeScore < $awayScore => 'away_win',
            default => 'draw',
        };

        $stmt = $this->pdo->prepare(
            'INSERT INTO match_results
                 (match_id, home_score, away_score, outcome,
                  yellow_cards_home, yellow_cards_away, red_cards_home, red_cards_away, recorded_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE
                 home_score         = VALUES(home_score),
                 away_score         = VALUES(away_score),
                 outcome            = VALUES(outcome),
                 yellow_cards_home  = VALUES(yellow_cards_home),
                 yellow_cards_away  = VALUES(yellow_cards_away),
                 red_cards_home     = VALUES(red_cards_home),
                 red_cards_away     = VALUES(red_cards_away),
                 recorded_at        = NOW()',
        );
        $stmt->execute([$matchId, $homeScore, $awayScore, $outcome, $yellowHome, $yellowAway, $redHome, $redAway]);

        // Mark match as completed
        $this->pdo->prepare('UPDATE matches SET status = ? WHERE id = ?')->execute(['completed', $matchId]);

        $this->session->flash('success', 'Uitslag opgeslagen.');
        $this->redirect("/admin/competitions/{$id}/results/{$matchId}/edit");
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
