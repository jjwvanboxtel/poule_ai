<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\View\ViewRenderer;
use PDO;

final class MatchManagementController
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
            throw new \RuntimeException('MatchManagementController dependencies are invalid.');
        }

        $this->renderer = $renderer;
        $this->session = $session;
        $this->competitions = $competitions;
        $this->pdo = $pdo;
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

        $matches = $this->competitions->findMatchesForCompetition($id);
        $entities = $this->competitions->findActiveEntitiesForCompetition($id);

        echo $this->renderer->render('admin/matches/index', [
            'title' => "Wedstrijden: {$competition->name}",
            'competition' => $competition,
            'matches' => $matches,
            'entities' => $entities,
        ]);
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

        $entities = $this->competitions->findActiveEntitiesForCompetition($id);

        echo $this->renderer->render('admin/matches/create', [
            'title' => "Nieuwe wedstrijd: {$competition->name}",
            'competition' => $competition,
            'entities' => $entities,
            'errors' => $this->flashedArray('errors'),
        ]);
    }

    public function store(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $competition = $this->competitions->findById($id);

        if ($competition === null) {
            http_response_code(404);
            echo '<h1>404 Niet gevonden</h1>';
            return;
        }

        $homeEntityId = (int) $this->stringValue($request->post('home_entity_id', '0'));
        $awayEntityId = (int) $this->stringValue($request->post('away_entity_id', '0'));
        $stage = $this->stringValue($request->post('stage', 'group'));
        $kickoffAt = $this->stringValue($request->post('kickoff_at', ''));

        if ($homeEntityId === 0 || $awayEntityId === 0 || $kickoffAt === '') {
            $this->session->flash('error', 'Vul alle verplichte velden in.');
            $this->redirect("/admin/competitions/{$id}/matches/create");
        }

        if ($homeEntityId === $awayEntityId) {
            $this->session->flash('error', 'Thuis- en uitploeg mogen niet hetzelfde zijn.');
            $this->redirect("/admin/competitions/{$id}/matches/create");
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO matches (competition_id, home_entity_id, away_entity_id, stage, kickoff_at, status)
             VALUES (?, ?, ?, ?, ?, ?)',
        );
        $stmt->execute([$id, $homeEntityId, $awayEntityId, $stage, $kickoffAt, 'scheduled']);

        $this->session->flash('success', 'Wedstrijd aangemaakt.');
        $this->redirect("/admin/competitions/{$id}/matches");
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

        $stmt = $this->pdo->prepare('SELECT * FROM matches WHERE id = ? AND competition_id = ? LIMIT 1');
        $stmt->execute([$matchId, $id]);
        $match = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!is_array($match)) {
            http_response_code(404);
            echo '<h1>404 Niet gevonden</h1>';
            return;
        }

        $entities = $this->competitions->findActiveEntitiesForCompetition($id);

        echo $this->renderer->render('admin/matches/edit', [
            'title' => 'Wedstrijd bewerken',
            'competition' => $competition,
            'match' => $match,
            'entities' => $entities,
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $matchId = (int) $request->routeParam('matchId');

        $homeEntityId = (int) $this->stringValue($request->post('home_entity_id', '0'));
        $awayEntityId = (int) $this->stringValue($request->post('away_entity_id', '0'));
        $stage = $this->stringValue($request->post('stage', 'group'));
        $kickoffAt = $this->stringValue($request->post('kickoff_at', ''));
        $status = $this->stringValue($request->post('status', 'scheduled'));

        $stmt = $this->pdo->prepare(
            'UPDATE matches SET home_entity_id = ?, away_entity_id = ?, stage = ?, kickoff_at = ?, status = ?
             WHERE id = ? AND competition_id = ?',
        );
        $stmt->execute([$homeEntityId, $awayEntityId, $stage, $kickoffAt, $status, $matchId, $id]);

        $this->session->flash('success', 'Wedstrijd bijgewerkt.');
        $this->redirect("/admin/competitions/{$id}/matches");
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
        foreach ($value as $k => $v) {
            if (is_string($k) && is_scalar($v)) {
                $result[$k] = (string) $v;
            }
        }
        return $result;
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
