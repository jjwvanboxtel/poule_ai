<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Competitions\UpdateBonusQuestionsService;
use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\View\ViewRenderer;

final class BonusQuestionController
{
    private readonly ViewRenderer $renderer;
    private readonly SessionManager $session;
    private readonly PdoCompetitionRepository $competitions;
    private readonly UpdateBonusQuestionsService $bonusService;

    public function __construct(Container $container)
    {
        $renderer = $container->get(ViewRenderer::class);
        $session = $container->get(SessionManager::class);
        $competitions = $container->get(PdoCompetitionRepository::class);
        $bonusService = $container->get(UpdateBonusQuestionsService::class);

        if (!$renderer instanceof ViewRenderer) throw new \RuntimeException('ViewRenderer invalid');
        if (!$session instanceof SessionManager) throw new \RuntimeException('Session invalid');
        if (!$competitions instanceof PdoCompetitionRepository) throw new \RuntimeException('Competitions invalid');
        if (!$bonusService instanceof UpdateBonusQuestionsService) throw new \RuntimeException('BonusService invalid');

        $this->renderer = $renderer;
        $this->session = $session;
        $this->competitions = $competitions;
        $this->bonusService = $bonusService;
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

        $questions = $this->competitions->findActiveBonusQuestions($id);

        echo $this->renderer->render('admin/competitions/bonus-questions', [
            'title' => 'Bonusvragen',
            'competition' => $competition,
            'questions' => $questions,
        ]);
    }

    public function store(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $post = $request->allPost();

        try {
            $this->bonusService->save($id, $post);
            $this->session->flash('success', 'Bonusvraag opgeslagen.');
        } catch (\Throwable $e) {
            $this->session->flash('error', $e->getMessage());
        }

        http_response_code(302);
        header('Location: /admin/competitions/' . $id . '/bonus-questions');
        exit;
    }

    public function destroy(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $questionId = (int) $request->routeParam('questionId');

        try {
            $this->bonusService->delete($questionId, $id);
            $this->session->flash('success', 'Bonusvraag verwijderd.');
        } catch (\Throwable $e) {
            $this->session->flash('error', $e->getMessage());
        }

        http_response_code(302);
        header('Location: /admin/competitions/' . $id . '/bonus-questions');
        exit;
    }
}
