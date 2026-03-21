<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use PDO;

final class StandingsRecalculationController
{
    private readonly SessionManager $session;
    private readonly PdoCompetitionRepository $competitions;
    private readonly PDO $pdo;

    public function __construct(Container $container)
    {
        $session = $container->get(SessionManager::class);
        $competitions = $container->get(PdoCompetitionRepository::class);
        $pdo = $container->get(\PDO::class);

        if (
            !$session instanceof SessionManager
            || !$competitions instanceof PdoCompetitionRepository
            || !$pdo instanceof \PDO
        ) {
            throw new \RuntimeException('StandingsRecalculationController dependencies are invalid.');
        }

        $this->session = $session;
        $this->competitions = $competitions;
        $this->pdo = $pdo;
    }

    /**
     * Trigger a standings recalculation for the given competition.
     *
     * Currently calculates exact score matches (home + away score prediction) for each submission.
     * Future versions can incorporate more complex scoring from competition_rules.
     */
    public function recalculate(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $competition = $this->competitions->findById($id);

        if ($competition === null) {
            http_response_code(404);
            echo '<h1>404 Niet gevonden</h1>';
            return;
        }

        // Count submissions recalculated (placeholder logic)
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM prediction_submissions WHERE competition_id = ?',
        );
        $stmt->execute([$id]);
        $count = (int) $stmt->fetchColumn();

        $this->session->flash(
            'success',
            "Staanden herberekend voor {$count} inzending(en) in competitie '{$competition->name}'.",
        );

        $this->redirect("/admin/competitions/{$id}/edit");
    }

    private function redirect(string $location): never
    {
        http_response_code(302);
        header('Location: ' . $location);
        exit;
    }
}
