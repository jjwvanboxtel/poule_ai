<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Imports\EntityCsvImportService;
use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\View\ViewRenderer;

final class EntityImportController
{
    private readonly ViewRenderer $renderer;
    private readonly SessionManager $session;
    private readonly PdoCompetitionRepository $competitions;
    private readonly EntityCsvImportService $importService;

    public function __construct(Container $container)
    {
        $renderer = $container->get(ViewRenderer::class);
        $session = $container->get(SessionManager::class);
        $competitions = $container->get(PdoCompetitionRepository::class);
        $importService = $container->get(EntityCsvImportService::class);

        if (!$renderer instanceof ViewRenderer) throw new \RuntimeException('ViewRenderer invalid');
        if (!$session instanceof SessionManager) throw new \RuntimeException('Session invalid');
        if (!$competitions instanceof PdoCompetitionRepository) throw new \RuntimeException('Competitions invalid');
        if (!$importService instanceof EntityCsvImportService) throw new \RuntimeException('ImportService invalid');

        $this->renderer = $renderer;
        $this->session = $session;
        $this->competitions = $competitions;
        $this->importService = $importService;
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

        echo $this->renderer->render('admin/imports/entities', [
            'title' => 'Entiteiten importeren',
            'competition' => $competition,
        ]);
    }

    public function store(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $fileInfo = $request->file('csv_file');

        if ($fileInfo === null || !is_numeric($fileInfo['error'] ?? null) || (int) $fileInfo['error'] !== UPLOAD_ERR_OK) {
            $this->session->flash('error', 'Geen geldig bestand geüpload.');
            http_response_code(302);
            header('Location: /admin/competitions/' . $id . '/import/entities');
            exit;
        }

        $tmpPath = is_string($fileInfo['tmp_name'] ?? null) ? $fileInfo['tmp_name'] : '';

        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            $this->session->flash('error', 'Ongeldig bestand.');
            http_response_code(302);
            header('Location: /admin/competitions/' . $id . '/import/entities');
            exit;
        }

        try {
            $count = $this->importService->import($tmpPath, $id);
            $this->session->flash('success', "{$count} entiteiten geïmporteerd.");
        } catch (\Throwable $e) {
            $this->session->flash('error', $e->getMessage());
        }

        http_response_code(302);
        header('Location: /admin/competitions/' . $id . '/import/entities');
        exit;
    }
}
