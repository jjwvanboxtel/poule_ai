<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Support\Container;
use App\Support\Sessions\SessionManager;

final class EnforceCompetitionDeadline
{
    private readonly PdoCompetitionRepository $competitions;
    private readonly SessionManager $session;

    public function __construct(Container $container)
    {
        $competitions = $container->get(PdoCompetitionRepository::class);
        $session = $container->get(SessionManager::class);

        if (!$competitions instanceof PdoCompetitionRepository || !$session instanceof SessionManager) {
            throw new \RuntimeException('EnforceCompetitionDeadline dependencies are invalid.');
        }

        $this->competitions = $competitions;
        $this->session = $session;
    }

    public function handle(Request $request, callable $next): void
    {
        $competition = $this->competitions->findBySlug($request->routeParam('slug'));

        if ($competition !== null && !$competition->isOpen()) {
            $this->session->flash('error', 'Deze competitie is gesloten voor nieuwe of gewijzigde inzendingen.');
            http_response_code(302);
            header('Location: /competitions/' . $competition->slug . '/prediction');
            exit;
        }

        $next($request);
    }
}
