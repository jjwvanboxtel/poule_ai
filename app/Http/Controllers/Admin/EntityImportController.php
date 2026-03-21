<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Imports\EntityCsvImportService;
use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Infrastructure\Security\SessionAuthenticator;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\Validation\ValidationException;
use App\Support\View\ViewRenderer;

final class EntityImportController
{
    private readonly ViewRenderer $renderer;
    private readonly SessionManager $session;
    private readonly SessionAuthenticator $auth;
    private readonly PdoCompetitionRepository $competitions;
    private readonly EntityCsvImportService $importService;

    public function __construct(Container $container)
    {
        $renderer = $container->get(ViewRenderer::class);
        $session = $container->get(SessionManager::class);
        $auth = $container->get(SessionAuthenticator::class);
        $competitions = $container->get(PdoCompetitionRepository::class);
        $importService = $container->get(EntityCsvImportService::class);

        if (
            !$renderer instanceof ViewRenderer
            || !$session instanceof SessionManager
            || !$auth instanceof SessionAuthenticator
            || !$competitions instanceof PdoCompetitionRepository
            || !$importService instanceof EntityCsvImportService
        ) {
            throw new \RuntimeException('EntityImportController dependencies are invalid.');
        }

        $this->renderer = $renderer;
        $this->session = $session;
        $this->auth = $auth;
        $this->competitions = $competitions;
        $this->importService = $importService;
    }

    public function create(Request $request): void
    {
        $allCompetitions = $this->competitions->findAll();

        echo $this->renderer->render('admin/imports/entities', [
            'title' => "Entiteiten importeren (CSV)",
            'competitions' => $allCompetitions,
            'errors' => $this->flashedArray('errors'),
        ]);
    }

    public function store(Request $request): void
    {
        $user = $this->auth->user();
        if ($user === null) {
            $this->redirect('/login');
        }
        /** @var \App\Domain\User\User $user */
        $competitionId = (int) $this->stringValue($request->post('competition_id', '0'));
        $file = $request->file('csv_file');

        if ($file === null || !isset($file['tmp_name']) || !is_string($file['tmp_name']) || $file['tmp_name'] === '') {
            $this->session->flash('error', 'Geen bestand geüpload.');
            $this->redirect('/admin/imports/entities');
        }

        $csvContent = file_get_contents($file['tmp_name']);
        if ($csvContent === false) {
            $this->session->flash('error', 'Kon het bestand niet lezen.');
            $this->redirect('/admin/imports/entities');
        }

        try {
            $count = $this->importService->import($competitionId, $csvContent, $user->id);
            $this->session->flash('success', "{$count} entiteiten succesvol geïmporteerd.");
        } catch (ValidationException $e) {
            $this->session->flash('error', implode('; ', array_map(
                static fn (array $msgs): string => implode(', ', $msgs),
                $e->errors(),
            )));
            $this->session->flash('errors', $e->errors());
        }

        $this->redirect('/admin/imports/entities');
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
