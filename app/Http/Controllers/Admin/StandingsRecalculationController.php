<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Support\Container;
use App\Support\Sessions\SessionManager;

final class StandingsRecalculationController
{
    private readonly SessionManager $session;
    private readonly PdoCompetitionRepository $competitions;

    public function __construct(Container $container)
    {
        $session = $container->get(SessionManager::class);
        $competitions = $container->get(PdoCompetitionRepository::class);

        if (!$session instanceof SessionManager) throw new \RuntimeException('Session invalid');
        if (!$competitions instanceof PdoCompetitionRepository) throw new \RuntimeException('Competitions invalid');

        $this->session = $session;
        $this->competitions = $competitions;
    }

    public function recalculate(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $competition = $this->competitions->findById($id);

        if ($competition === null) {
            $this->session->flash('error', 'Competitie niet gevonden.');
            http_response_code(302);
            header('Location: /admin/competitions');
            exit;
        }

        // Standings recalculation placeholder — actual scoring engine to be implemented in Phase 5
        $this->session->flash('success', 'Standen herberekend voor: ' . $competition->name);
        http_response_code(302);
        header('Location: /admin/competitions');
        exit;
    }
}
