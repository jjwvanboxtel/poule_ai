<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Competitions\CreateCompetitionService;
use App\Application\Competitions\UpdateBonusQuestionsService;
use App\Application\Competitions\UpdateCompetitionRulesService;
use App\Application\Competitions\UpdateCompetitionSectionsService;
use App\Application\Competitions\UpdateCompetitionService;
use App\Application\Competitions\UpdateKnockoutRoundsService;
use App\Domain\Competition\SectionType;
use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRuleRepository;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionSectionRepository;
use App\Infrastructure\Persistence\Pdo\PdoKnockoutRoundRepository;
use App\Infrastructure\Security\SessionAuthenticator;
use App\Infrastructure\Storage\LogoStorage;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\View\ViewRenderer;

final class CompetitionController
{
    private readonly ViewRenderer $renderer;
    private readonly SessionAuthenticator $auth;
    private readonly SessionManager $session;
    private readonly PdoCompetitionRepository $competitions;
    private readonly PdoCompetitionSectionRepository $sections;
    private readonly PdoCompetitionRuleRepository $rules;
    private readonly PdoKnockoutRoundRepository $knockoutRounds;
    private readonly CreateCompetitionService $createService;
    private readonly UpdateCompetitionService $updateService;
    private readonly UpdateCompetitionSectionsService $sectionsService;
    private readonly UpdateCompetitionRulesService $rulesService;
    private readonly UpdateKnockoutRoundsService $knockoutService;
    private readonly LogoStorage $logoStorage;

    public function __construct(Container $container)
    {
        $renderer = $container->get(ViewRenderer::class);
        $auth = $container->get(SessionAuthenticator::class);
        $session = $container->get(SessionManager::class);
        $competitions = $container->get(PdoCompetitionRepository::class);
        $sections = $container->get(PdoCompetitionSectionRepository::class);
        $rules = $container->get(PdoCompetitionRuleRepository::class);
        $knockoutRounds = $container->get(PdoKnockoutRoundRepository::class);
        $createService = $container->get(CreateCompetitionService::class);
        $updateService = $container->get(UpdateCompetitionService::class);
        $sectionsService = $container->get(UpdateCompetitionSectionsService::class);
        $rulesService = $container->get(UpdateCompetitionRulesService::class);
        $knockoutService = $container->get(UpdateKnockoutRoundsService::class);
        $logoStorage = $container->get(LogoStorage::class);

        if (!$renderer instanceof ViewRenderer) throw new \RuntimeException('ViewRenderer invalid');
        if (!$auth instanceof SessionAuthenticator) throw new \RuntimeException('Auth invalid');
        if (!$session instanceof SessionManager) throw new \RuntimeException('Session invalid');
        if (!$competitions instanceof PdoCompetitionRepository) throw new \RuntimeException('Competitions invalid');
        if (!$sections instanceof PdoCompetitionSectionRepository) throw new \RuntimeException('Sections invalid');
        if (!$rules instanceof PdoCompetitionRuleRepository) throw new \RuntimeException('Rules invalid');
        if (!$knockoutRounds instanceof PdoKnockoutRoundRepository) throw new \RuntimeException('KnockoutRounds invalid');
        if (!$createService instanceof CreateCompetitionService) throw new \RuntimeException('CreateService invalid');
        if (!$updateService instanceof UpdateCompetitionService) throw new \RuntimeException('UpdateService invalid');
        if (!$sectionsService instanceof UpdateCompetitionSectionsService) throw new \RuntimeException('SectionsService invalid');
        if (!$rulesService instanceof UpdateCompetitionRulesService) throw new \RuntimeException('RulesService invalid');
        if (!$knockoutService instanceof UpdateKnockoutRoundsService) throw new \RuntimeException('KnockoutService invalid');
        if (!$logoStorage instanceof LogoStorage) throw new \RuntimeException('LogoStorage invalid');

        $this->renderer = $renderer;
        $this->auth = $auth;
        $this->session = $session;
        $this->competitions = $competitions;
        $this->sections = $sections;
        $this->rules = $rules;
        $this->knockoutRounds = $knockoutRounds;
        $this->createService = $createService;
        $this->updateService = $updateService;
        $this->sectionsService = $sectionsService;
        $this->rulesService = $rulesService;
        $this->knockoutService = $knockoutService;
        $this->logoStorage = $logoStorage;
    }

    public function index(Request $request): void
    {
        echo $this->renderer->render('admin/competitions/index', [
            'title' => 'Competities',
            'competitions' => $this->competitions->findAll(),
        ]);
    }

    public function create(Request $request): void
    {
        echo $this->renderer->render('admin/competitions/create', [
            'title' => 'Nieuwe competitie',
            'sectionTypes' => SectionType::cases(),
        ]);
    }

    public function store(Request $request): void
    {
        $user = $this->auth->user();
        if ($user === null) {
            http_response_code(302);
            header('Location: /login');
            exit;
        }

        $post = $request->allPost();
        $logoPath = null;

        $fileInfo = $request->file('logo');
        if ($fileInfo !== null && is_numeric($fileInfo['error'] ?? null) && (int) $fileInfo['error'] === UPLOAD_ERR_OK) {
            try {
                $slug = is_string($post['slug'] ?? null) ? (string) $post['slug'] : 'new-competition';
                $logoPath = $this->logoStorage->store($fileInfo, $slug);
            } catch (\RuntimeException $e) {
                $this->session->flash('error', $e->getMessage());
                http_response_code(302);
                header('Location: /admin/competitions/create');
                exit;
            }
        }

        if ($logoPath !== null) {
            $post['logo_path'] = $logoPath;
        }

        try {
            $id = $this->createService->create($user->id, $post);
            $this->session->flash('success', 'Competitie aangemaakt.');
            http_response_code(302);
            header('Location: /admin/competitions/' . $id . '/edit');
            exit;
        } catch (\DomainException $e) {
            $this->session->flash('error', $e->getMessage());
            http_response_code(302);
            header('Location: /admin/competitions/create');
            exit;
        }
    }

    public function edit(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $competition = $this->competitions->findById($id);
        if ($competition === null) {
            http_response_code(302);
            header('Location: /admin/competitions');
            exit;
        }

        echo $this->renderer->render('admin/competitions/edit', [
            'title' => 'Competitie bewerken',
            'competition' => $competition,
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $post = $request->allPost();

        $fileInfo = $request->file('logo');
        if ($fileInfo !== null && is_numeric($fileInfo['error'] ?? null) && (int) $fileInfo['error'] === UPLOAD_ERR_OK) {
            try {
                $slug = is_string($post['slug'] ?? null) ? (string) $post['slug'] : 'competition';
                $post['logo_path'] = $this->logoStorage->store($fileInfo, $slug);
            } catch (\RuntimeException $e) {
                $this->session->flash('error', $e->getMessage());
                http_response_code(302);
                header('Location: /admin/competitions/' . $id . '/edit');
                exit;
            }
        }

        try {
            $this->updateService->update($id, $post);
            $this->session->flash('success', 'Competitie bijgewerkt.');
        } catch (\DomainException $e) {
            $this->session->flash('error', $e->getMessage());
        }

        http_response_code(302);
        header('Location: /admin/competitions/' . $id . '/edit');
        exit;
    }

    public function sections(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $competition = $this->competitions->findById($id);
        if ($competition === null) {
            http_response_code(302);
            header('Location: /admin/competitions');
            exit;
        }

        $existingSections = $this->sections->findByCompetition($id);

        echo $this->renderer->render('admin/competitions/sections', [
            'title' => 'Secties beheren',
            'competition' => $competition,
            'sections' => $existingSections,
            'sectionTypes' => SectionType::cases(),
        ]);
    }

    public function updateSections(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $post = $request->allPost();
        $rawSections = $post['sections'] ?? [];
        $sectionsData = is_array($rawSections) ? $rawSections : [];

        /** @var list<array<string, mixed>> $sections */
        $sections = array_values($sectionsData);

        try {
            $this->sectionsService->update($id, $sections);
            $this->session->flash('success', 'Secties bijgewerkt.');
        } catch (\DomainException $e) {
            $this->session->flash('error', $e->getMessage());
        }

        http_response_code(302);
        header('Location: /admin/competitions/' . $id . '/sections');
        exit;
    }

    public function rules(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $sectionId = (int) $request->routeParam('sectionId');
        $competition = $this->competitions->findById($id);
        $section = $this->sections->findById($sectionId);

        if ($competition === null || $section === null) {
            http_response_code(302);
            header('Location: /admin/competitions/' . $id . '/sections');
            exit;
        }

        $existingRules = $this->rules->findBySectionId($sectionId);

        echo $this->renderer->render('admin/competitions/rules', [
            'title' => 'Regels beheren',
            'competition' => $competition,
            'section' => $section,
            'rules' => $existingRules,
        ]);
    }

    public function updateRules(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $sectionId = (int) $request->routeParam('sectionId');
        $post = $request->allPost();
        $rawRules = $post['rules'] ?? [];
        $rulesData = is_array($rawRules) ? $rawRules : [];

        /** @var list<array<string, mixed>> $rules */
        $rules = array_values($rulesData);

        try {
            $this->rulesService->update($id, $sectionId, $rules);
            $this->session->flash('success', 'Regels bijgewerkt.');
        } catch (\DomainException $e) {
            $this->session->flash('error', $e->getMessage());
        }

        http_response_code(302);
        header('Location: /admin/competitions/' . $id . '/rules/' . $sectionId);
        exit;
    }

    public function knockoutRounds(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $competition = $this->competitions->findById($id);
        if ($competition === null) {
            http_response_code(302);
            header('Location: /admin/competitions');
            exit;
        }

        $rounds = $this->knockoutRounds->findAllRounds($id);

        echo $this->renderer->render('admin/competitions/knockout-rounds', [
            'title' => 'Knockout rondes',
            'competition' => $competition,
            'rounds' => $rounds,
        ]);
    }

    public function updateKnockoutRounds(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $post = $request->allPost();
        $rawRounds = $post['rounds'] ?? [];
        $roundsData = is_array($rawRounds) ? $rawRounds : [];

        /** @var list<array<string, mixed>> $rounds */
        $rounds = array_values($roundsData);

        try {
            $this->knockoutService->update($id, $rounds);
            $this->session->flash('success', 'Knockout rondes bijgewerkt.');
        } catch (\DomainException $e) {
            $this->session->flash('error', $e->getMessage());
        }

        http_response_code(302);
        header('Location: /admin/competitions/' . $id . '/knockout-rounds');
        exit;
    }
}
