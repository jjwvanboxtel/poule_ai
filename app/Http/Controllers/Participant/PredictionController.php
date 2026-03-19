<?php declare(strict_types=1);

namespace App\Http\Controllers\Participant;

use App\Application\Predictions\BonusAnswerRepositoryInterface;
use App\Application\Predictions\KnockoutRoundRepositoryInterface;
use App\Application\Predictions\MatchPredictionRepositoryInterface;
use App\Application\Predictions\PredictionSubmissionRepositoryInterface;
use App\Application\Predictions\SubmitPredictionService;
use App\Domain\Prediction\PredictionSubmission;
use App\Http\Controllers\ErrorController;
use App\Http\Requests\Request;
use App\Http\ViewModels\PredictionFormViewModel;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Infrastructure\Security\SessionAuthenticator;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\View\ViewRenderer;
use DomainException;

final class PredictionController
{
    private readonly ViewRenderer $renderer;
    private readonly SessionAuthenticator $authenticator;
    private readonly SessionManager $session;
    private readonly PdoCompetitionRepository $competitions;
    private readonly PredictionSubmissionRepositoryInterface $submissions;
    private readonly MatchPredictionRepositoryInterface $matchPredictions;
    private readonly BonusAnswerRepositoryInterface $bonusAnswers;
    private readonly KnockoutRoundRepositoryInterface $knockoutRounds;
    private readonly PredictionFormViewModel $viewModel;
    private readonly SubmitPredictionService $submitPredictionService;
    private readonly ErrorController $errors;

    public function __construct(Container $container)
    {
        $renderer = $container->get(ViewRenderer::class);
        $authenticator = $container->get(SessionAuthenticator::class);
        $session = $container->get(SessionManager::class);
        $competitions = $container->get(PdoCompetitionRepository::class);
        $submissions = $container->get(PredictionSubmissionRepositoryInterface::class);
        $matchPredictions = $container->get(MatchPredictionRepositoryInterface::class);
        $bonusAnswers = $container->get(BonusAnswerRepositoryInterface::class);
        $knockoutRounds = $container->get(KnockoutRoundRepositoryInterface::class);
        $viewModel = $container->get(PredictionFormViewModel::class);
        $submitPredictionService = $container->get(SubmitPredictionService::class);

        if (
            !$renderer instanceof ViewRenderer
            || !$authenticator instanceof SessionAuthenticator
            || !$session instanceof SessionManager
            || !$competitions instanceof PdoCompetitionRepository
            || !$submissions instanceof PredictionSubmissionRepositoryInterface
            || !$matchPredictions instanceof MatchPredictionRepositoryInterface
            || !$bonusAnswers instanceof BonusAnswerRepositoryInterface
            || !$knockoutRounds instanceof KnockoutRoundRepositoryInterface
            || !$viewModel instanceof PredictionFormViewModel
            || !$submitPredictionService instanceof SubmitPredictionService
        ) {
            throw new \RuntimeException('PredictionController dependencies are invalid.');
        }

        $this->renderer = $renderer;
        $this->authenticator = $authenticator;
        $this->session = $session;
        $this->competitions = $competitions;
        $this->submissions = $submissions;
        $this->matchPredictions = $matchPredictions;
        $this->bonusAnswers = $bonusAnswers;
        $this->knockoutRounds = $knockoutRounds;
        $this->viewModel = $viewModel;
        $this->submitPredictionService = $submitPredictionService;
        $this->errors = new ErrorController($container);
    }

    public function show(Request $request): void
    {
        $competition = $this->competitions->findBySlug($request->routeParam('slug'));
        if ($competition === null) {
            $this->errors->notFound($request);

            return;
        }

        $user = $this->authenticator->user();
        if ($user === null) {
            http_response_code(302);
            header('Location: /login');
            exit;
        }

        $participant = $this->competitions->findParticipantRow($competition->id, $user->id);
        if ($participant === null && $competition->isOpen()) {
            $this->competitions->enrollUserInCompetition($competition->id, $user->id);
            $participant = $this->competitions->findParticipantRow($competition->id, $user->id);
        }

        if ($participant === null) {
            $this->errors->forbidden($request);

            return;
        }

        $submission = $this->submissions->findByCompetitionAndUser($competition->id, $user->id);

        $pageData = $this->viewModel->build(
            competition: $competition,
            participant: $participant,
            submission: $submission,
            oldInput: $this->flashedArray('old'),
            errors: $this->flashedArray('errors'),
            matchPredictions: $submission !== null ? $this->matchPredictions->findBySubmissionId($submission->id) : [],
            bonusAnswers: $submission !== null ? $this->bonusAnswers->findBySubmissionId($submission->id) : [],
            knockoutPredictions: $submission !== null ? $this->knockoutRounds->findPredictionsBySubmissionId($submission->id) : [],
        );

        if ($request->query('confirmed', '') === '1' && $submission !== null) {
            echo $this->renderer->render('participants/prediction-confirmation', [
                'title' => 'Voorspelling bevestigd',
                'page' => $pageData,
            ]);

            return;
        }

        echo $this->renderer->render('participants/prediction-form', [
            'title' => 'Mijn voorspelling',
            'page' => $pageData,
        ]);
    }

    public function submit(Request $request): void
    {
        $competition = $this->competitions->findBySlug($request->routeParam('slug'));
        if ($competition === null) {
            $this->errors->notFound($request);

            return;
        }

        $user = $this->authenticator->user();
        if ($user === null) {
            http_response_code(302);
            header('Location: /login');
            exit;
        }

        $participant = $this->competitions->findParticipantRow($competition->id, $user->id);
        if ($participant === null && $competition->isOpen()) {
            $this->competitions->enrollUserInCompetition($competition->id, $user->id);
            $participant = $this->competitions->findParticipantRow($competition->id, $user->id);
        }

        if ($participant === null) {
            $this->errors->forbidden($request);

            return;
        }

        try {
            $this->submitPredictionService->submit($user, $competition, $request->allPost());
        } catch (DomainException $exception) {
            $this->session->flash('error', $exception->getMessage());
            $this->session->flash('errors', ['summary' => $exception->getMessage()]);
            $this->session->flash('old', $request->allPost());
            $this->redirect('/competitions/' . $competition->slug . '/prediction');
        }

        $this->session->flash('success', 'Je voorspelling is definitief opgeslagen.');
        $this->redirect('/competitions/' . $competition->slug . '/prediction?confirmed=1');
    }

    /**
     * @return array<int|string, mixed>
     */
    private function flashedArray(string $key): array
    {
        $value = $this->session->getFlash($key, []);

        return is_array($value) ? $value : [];
    }

    private function redirect(string $location): void
    {
        http_response_code(302);
        header('Location: ' . $location);
        exit;
    }
}
