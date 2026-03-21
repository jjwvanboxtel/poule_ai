<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Competitions\UpdateBonusQuestionsService;
use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\View\ViewRenderer;
use DomainException;

final class BonusQuestionController
{
    private readonly ViewRenderer $renderer;
    private readonly SessionManager $session;
    private readonly PdoCompetitionRepository $competitions;
    private readonly UpdateBonusQuestionsService $updateService;

    public function __construct(Container $container)
    {
        $renderer = $container->get(ViewRenderer::class);
        $session = $container->get(SessionManager::class);
        $competitions = $container->get(PdoCompetitionRepository::class);
        $updateService = $container->get(UpdateBonusQuestionsService::class);

        if (
            !$renderer instanceof ViewRenderer
            || !$session instanceof SessionManager
            || !$competitions instanceof PdoCompetitionRepository
            || !$updateService instanceof UpdateBonusQuestionsService
        ) {
            throw new \RuntimeException('BonusQuestionController dependencies are invalid.');
        }

        $this->renderer = $renderer;
        $this->session = $session;
        $this->competitions = $competitions;
        $this->updateService = $updateService;
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

        $questions = $this->competitions->findActiveBonusQuestions($id);
        $entities = $this->competitions->findActiveEntitiesForCompetition($id);

        echo $this->renderer->render('admin/competitions/bonus-questions', [
            'title' => "Bonusvragen: {$competition->name}",
            'competition' => $competition,
            'questions' => $questions,
            'entities' => $entities,
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int) $request->routeParam('id');

        /** @var array<int, array<string, mixed>> $rawQuestions */
        $rawQuestions = $request->post('questions', []);

        if (!is_array($rawQuestions)) {
            $rawQuestions = [];
        }

        $questions = [];
        foreach ($rawQuestions as $q) {
            if (!is_array($q)) {
                continue;
            }
            $qId = isset($q['id']) && is_numeric($q['id']) ? (int) $q['id'] : 0;
            $entry = [
                'prompt' => is_scalar($q['prompt'] ?? null) ? (string) $q['prompt'] : '',
                'question_type' => is_scalar($q['question_type'] ?? null) ? (string) $q['question_type'] : 'text',
                'entity_type_constraint' => is_scalar($q['entity_type_constraint'] ?? null) && $q['entity_type_constraint'] !== ''
                    ? (string) $q['entity_type_constraint']
                    : null,
                'is_active' => isset($q['is_active']) && (bool) $q['is_active'],
                'display_order' => is_numeric($q['display_order'] ?? null) ? (int) $q['display_order'] : 0,
            ];
            if ($qId > 0) {
                $entry['id'] = $qId;
            }
            $questions[] = $entry;
        }

        try {
            $this->updateService->update($id, $questions);
            $this->session->flash('success', 'Bonusvragen opgeslagen.');
        } catch (DomainException $e) {
            $this->session->flash('error', $e->getMessage());
        }

        $this->redirect("/admin/competitions/{$id}/bonus-questions");
    }

    private function redirect(string $location): never
    {
        http_response_code(302);
        header('Location: ' . $location);
        exit;
    }
}
