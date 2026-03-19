<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Infrastructure\Persistence\Pdo\PdoMatchManagementRepository;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\View\ViewRenderer;

final class MatchManagementController
{
    private readonly ViewRenderer $renderer;
    private readonly SessionManager $session;
    private readonly PdoCompetitionRepository $competitions;
    private readonly PdoMatchManagementRepository $matchRepo;

    public function __construct(Container $container)
    {
        $renderer = $container->get(ViewRenderer::class);
        $session = $container->get(SessionManager::class);
        $competitions = $container->get(PdoCompetitionRepository::class);
        $matchRepo = $container->get(PdoMatchManagementRepository::class);

        if (!$renderer instanceof ViewRenderer) throw new \RuntimeException('ViewRenderer invalid');
        if (!$session instanceof SessionManager) throw new \RuntimeException('Session invalid');
        if (!$competitions instanceof PdoCompetitionRepository) throw new \RuntimeException('Competitions invalid');
        if (!$matchRepo instanceof PdoMatchManagementRepository) throw new \RuntimeException('MatchRepo invalid');

        $this->renderer = $renderer;
        $this->session = $session;
        $this->competitions = $competitions;
        $this->matchRepo = $matchRepo;
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

        $matches = $this->matchRepo->findAllForCompetition($id);
        $groups = $this->matchRepo->findGroups($id);
        $venues = $this->matchRepo->findVenues($id);
        $entities = $this->competitions->findActiveEntitiesForCompetition($id);

        echo $this->renderer->render('admin/matches/index', [
            'title' => 'Wedstrijden beheren',
            'competition' => $competition,
            'matches' => $matches,
            'groups' => $groups,
            'venues' => $venues,
            'entities' => $entities,
        ]);
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

        $post = $request->allPost();
        $homeEntityId = is_numeric($post['home_entity_id'] ?? null) ? (int) $post['home_entity_id'] : 0;
        $awayEntityId = is_numeric($post['away_entity_id'] ?? null) ? (int) $post['away_entity_id'] : 0;
        $stage = is_string($post['stage'] ?? null) ? $post['stage'] : 'group';
        $kickoffAt = is_string($post['kickoff_at'] ?? null) ? $post['kickoff_at'] : '';
        $groupId = is_numeric($post['group_id'] ?? null) && (int) $post['group_id'] > 0 ? (int) $post['group_id'] : null;
        $venueId = is_numeric($post['venue_id'] ?? null) && (int) $post['venue_id'] > 0 ? (int) $post['venue_id'] : null;

        try {
            $this->matchRepo->createMatch($id, $homeEntityId, $awayEntityId, $stage, $kickoffAt, $groupId, $venueId);
            $this->session->flash('success', 'Wedstrijd aangemaakt.');
        } catch (\Throwable $e) {
            $this->session->flash('error', $e->getMessage());
        }

        http_response_code(302);
        header('Location: /admin/competitions/' . $id . '/matches');
        exit;
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

        $groups = $this->matchRepo->findGroups($id);
        $venues = $this->matchRepo->findVenues($id);
        $entities = $this->competitions->findActiveEntitiesForCompetition($id);

        echo $this->renderer->render('admin/matches/edit', [
            'title' => 'Wedstrijd bewerken',
            'competition' => $competition,
            'match' => $match,
            'groups' => $groups,
            'venues' => $venues,
            'entities' => $entities,
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $matchId = (int) $request->routeParam('matchId');
        $post = $request->allPost();

        $homeEntityId = is_numeric($post['home_entity_id'] ?? null) ? (int) $post['home_entity_id'] : 0;
        $awayEntityId = is_numeric($post['away_entity_id'] ?? null) ? (int) $post['away_entity_id'] : 0;
        $stage = is_string($post['stage'] ?? null) ? $post['stage'] : 'group';
        $kickoffAt = is_string($post['kickoff_at'] ?? null) ? $post['kickoff_at'] : '';
        $groupId = is_numeric($post['group_id'] ?? null) && (int) $post['group_id'] > 0 ? (int) $post['group_id'] : null;
        $venueId = is_numeric($post['venue_id'] ?? null) && (int) $post['venue_id'] > 0 ? (int) $post['venue_id'] : null;

        try {
            $this->matchRepo->updateMatch($matchId, $homeEntityId, $awayEntityId, $stage, $kickoffAt, $groupId, $venueId);
            $this->session->flash('success', 'Wedstrijd bijgewerkt.');
        } catch (\Throwable $e) {
            $this->session->flash('error', $e->getMessage());
        }

        http_response_code(302);
        header('Location: /admin/competitions/' . $id . '/matches');
        exit;
    }

    public function destroy(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $matchId = (int) $request->routeParam('matchId');

        try {
            $this->matchRepo->deleteMatch($matchId);
            $this->session->flash('success', 'Wedstrijd verwijderd.');
        } catch (\Throwable $e) {
            $this->session->flash('error', $e->getMessage());
        }

        http_response_code(302);
        header('Location: /admin/competitions/' . $id . '/matches');
        exit;
    }
}
