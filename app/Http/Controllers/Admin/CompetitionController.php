<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Competitions\CreateCompetitionService;
use App\Application\Competitions\UpdateCompetitionService;
use App\Domain\Competition\CompetitionStatus;
use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Infrastructure\Security\SessionAuthenticator;
use App\Infrastructure\Storage\LogoStorage;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\View\ViewRenderer;
use DomainException;

final class CompetitionController
{
    private readonly ViewRenderer $renderer;
    private readonly SessionManager $session;
    private readonly SessionAuthenticator $auth;
    private readonly PdoCompetitionRepository $competitions;
    private readonly CreateCompetitionService $createService;
    private readonly UpdateCompetitionService $updateService;
    private readonly LogoStorage $logoStorage;

    public function __construct(Container $container)
    {
        $renderer = $container->get(ViewRenderer::class);
        $session = $container->get(SessionManager::class);
        $auth = $container->get(SessionAuthenticator::class);
        $competitions = $container->get(PdoCompetitionRepository::class);
        $createService = $container->get(CreateCompetitionService::class);
        $updateService = $container->get(UpdateCompetitionService::class);
        $logoStorage = $container->get(LogoStorage::class);

        if (
            !$renderer instanceof ViewRenderer
            || !$session instanceof SessionManager
            || !$auth instanceof SessionAuthenticator
            || !$competitions instanceof PdoCompetitionRepository
            || !$createService instanceof CreateCompetitionService
            || !$updateService instanceof UpdateCompetitionService
            || !$logoStorage instanceof LogoStorage
        ) {
            throw new \RuntimeException('CompetitionController dependencies are invalid.');
        }

        $this->renderer = $renderer;
        $this->session = $session;
        $this->auth = $auth;
        $this->competitions = $competitions;
        $this->createService = $createService;
        $this->updateService = $updateService;
        $this->logoStorage = $logoStorage;
    }

    public function index(Request $request): void
    {
        $all = $this->competitions->findAll();

        echo $this->renderer->render('admin/competitions/index', [
            'title' => 'Competities beheren',
            'competitions' => $all,
        ]);
    }

    public function create(Request $request): void
    {
        echo $this->renderer->render('admin/competitions/create', [
            'title' => 'Nieuwe competitie',
            'errors' => $this->flashedArray('errors'),
            'old' => $this->flashedArray('old'),
            'statuses' => CompetitionStatus::cases(),
        ]);
    }

    public function store(Request $request): void
    {
        $user = $this->auth->user();
        if ($user === null) {
            $this->redirect('/login');
        }

        /** @var \App\Domain\User\User $user */
        $name = $this->stringValue($request->post('name', ''));
        $description = $this->stringValue($request->post('description', ''));
        $startDate = $this->stringValue($request->post('start_date', ''));
        $endDate = $this->stringValue($request->post('end_date', ''));
        $deadline = $this->stringValue($request->post('submission_deadline', ''));
        $feeAmount = (float) $this->stringValue($request->post('entry_fee_amount', '0'));
        $prizeFirst = (int) $this->stringValue($request->post('prize_first_percent', '60'));
        $prizeSecond = (int) $this->stringValue($request->post('prize_second_percent', '30'));
        $prizeThird = (int) $this->stringValue($request->post('prize_third_percent', '10'));
        $isPublic = $this->stringValue($request->post('is_public', '1')) === '1';

        $old = compact('name', 'description', 'startDate', 'endDate', 'deadline');

        try {
            $competition = $this->createService->create([
                'name' => $name,
                'description' => $description,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'submission_deadline' => $deadline,
                'entry_fee_amount' => $feeAmount,
                'prize_first_percent' => $prizeFirst,
                'prize_second_percent' => $prizeSecond,
                'prize_third_percent' => $prizeThird,
                'is_public' => $isPublic,
                'created_by_user_id' => $user->id,
            ]);

            $this->session->flash('success', "Competitie '{$competition->name}' is aangemaakt.");
            $this->redirect('/admin/competitions');
        } catch (DomainException $e) {
            $this->session->flash('error', $e->getMessage());
            $this->session->flash('errors', ['general' => $e->getMessage()]);
            $this->session->flash('old', $old);
            $this->redirect('/admin/competitions/create');
        }
    }

    public function edit(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $competition = $this->competitions->findById($id);

        if ($competition === null) {
            http_response_code(404);
            echo '<h1>404 Niet gevonden</h1>';
            return;
        }

        echo $this->renderer->render('admin/competitions/edit', [
            'title' => "Competitie bewerken: {$competition->name}",
            'competition' => $competition,
            'errors' => $this->flashedArray('errors'),
            'statuses' => CompetitionStatus::cases(),
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $competition = $this->competitions->findById($id);

        if ($competition === null) {
            http_response_code(404);
            echo '<h1>404 Niet gevonden</h1>';
            return;
        }

        $name = $this->stringValue($request->post('name', ''));
        $description = $this->stringValue($request->post('description', ''));
        $startDate = $this->stringValue($request->post('start_date', ''));
        $endDate = $this->stringValue($request->post('end_date', ''));
        $deadline = $this->stringValue($request->post('submission_deadline', ''));
        $feeAmount = (float) $this->stringValue($request->post('entry_fee_amount', '0'));
        $prizeFirst = (int) $this->stringValue($request->post('prize_first_percent', '60'));
        $prizeSecond = (int) $this->stringValue($request->post('prize_second_percent', '30'));
        $prizeThird = (int) $this->stringValue($request->post('prize_third_percent', '10'));
        $status = $this->stringValue($request->post('status', 'draft'));
        $isPublic = $this->stringValue($request->post('is_public', '1')) === '1';

        // Handle logo upload
        $logoPath = $competition->logoPath;
        $logoFile = $request->file('logo');
        if ($logoFile !== null && isset($logoFile['error']) && is_numeric($logoFile['error']) && (int) $logoFile['error'] === UPLOAD_ERR_OK) {
            try {
                /** @var array{tmp_name: string, name: string, error: int, size: int} $logoFile */
                $logoPath = $this->logoStorage->store($logoFile);
            } catch (\RuntimeException $e) {
                $this->session->flash('error', $e->getMessage());
                $this->redirect("/admin/competitions/{$id}/edit");
            }
        }

        try {
            $this->updateService->update($id, [
                'name' => $name,
                'description' => $description,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'submission_deadline' => $deadline,
                'entry_fee_amount' => $feeAmount,
                'prize_first_percent' => $prizeFirst,
                'prize_second_percent' => $prizeSecond,
                'prize_third_percent' => $prizeThird,
                'status' => $status,
                'is_public' => $isPublic,
                'logo_path' => $logoPath,
            ]);

            $this->session->flash('success', 'Competitie bijgewerkt.');
            $this->redirect('/admin/competitions');
        } catch (DomainException $e) {
            $this->session->flash('error', $e->getMessage());
            $this->redirect("/admin/competitions/{$id}/edit");
        }
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
