<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Infrastructure\Persistence\Pdo\PdoUserRepository;
use App\Support\Container;
use App\Support\View\ViewRenderer;

final class AdminDashboardController
{
    private readonly ViewRenderer $renderer;
    private readonly PdoCompetitionRepository $competitions;
    private readonly PdoUserRepository $users;

    public function __construct(Container $container)
    {
        $renderer = $container->get(ViewRenderer::class);
        $competitions = $container->get(PdoCompetitionRepository::class);
        $users = $container->get(PdoUserRepository::class);

        if (!$renderer instanceof ViewRenderer) {
            throw new \RuntimeException('ViewRenderer invalid.');
        }
        if (!$competitions instanceof PdoCompetitionRepository) {
            throw new \RuntimeException('PdoCompetitionRepository invalid.');
        }
        if (!$users instanceof PdoUserRepository) {
            throw new \RuntimeException('PdoUserRepository invalid.');
        }

        $this->renderer = $renderer;
        $this->competitions = $competitions;
        $this->users = $users;
    }

    public function index(Request $request): void
    {
        $allCompetitions = $this->competitions->findAll();
        $allUsers = $this->users->findAll();

        echo $this->renderer->render('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'totalCompetitions' => count($allCompetitions),
            'totalUsers' => count($allUsers),
            'competitions' => $allCompetitions,
        ]);
    }
}
