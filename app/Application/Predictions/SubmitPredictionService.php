<?php declare(strict_types=1);

namespace App\Application\Predictions;

use App\Domain\Competition\Competition;
use App\Domain\Prediction\PredictionSubmission;
use App\Domain\User\User;
use DomainException;
use PDO;

final class SubmitPredictionService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly PredictionPayloadValidator $payloadValidator,
        private readonly BonusAnswerValidator $bonusAnswerValidator,
        private readonly KnockoutPredictionValidator $knockoutPredictionValidator,
        private readonly PredictionSubmissionRepositoryInterface $predictionSubmissionRepository,
        private readonly MatchPredictionRepositoryInterface $matchPredictionRepository,
        private readonly BonusAnswerRepositoryInterface $bonusAnswerRepository,
        private readonly KnockoutRoundRepositoryInterface $knockoutRoundRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function submit(User $user, Competition $competition, array $payload): PredictionSubmission
    {
        if (!$competition->isOpen()) {
            throw new DomainException('De uiterste inleverdatum is verstreken of de competitie is niet open.');
        }

        if ($this->predictionSubmissionRepository->findByCompetitionAndUser($competition->id, $user->id) !== null) {
            throw new DomainException('Je hebt al een definitieve voorspelling ingediend.');
        }

        $matchPredictions = $this->payloadValidator->validate($competition, $payload);
        $bonusAnswers = $this->bonusAnswerValidator->validate(
            $competition,
            is_array($payload['bonus_answers'] ?? null) ? $payload['bonus_answers'] : [],
        );
        $knockoutPredictions = $this->knockoutPredictionValidator->validate(
            $competition,
            is_array($payload['knockout_rounds'] ?? null) ? $payload['knockout_rounds'] : [],
        );

        $submittedAt = date('Y-m-d H:i:s');
        $submission = new PredictionSubmission(
            id: 0,
            competitionId: $competition->id,
            userId: $user->id,
            submittedAt: $submittedAt,
            submissionHash: hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR)),
            isLocked: true,
        );

        $this->pdo->beginTransaction();

        try {
            $storedSubmission = $this->predictionSubmissionRepository->create($submission);
            $submissionId = $storedSubmission->id;

            if ($matchPredictions !== []) {
                $this->matchPredictionRepository->insertBatch($submissionId, $matchPredictions);
            }

            if ($bonusAnswers !== []) {
                $this->bonusAnswerRepository->insertBatch($submissionId, $bonusAnswers);
            }

            if ($knockoutPredictions !== []) {
                $this->knockoutRoundRepository->insertPredictions($submissionId, $knockoutPredictions);
            }

            $this->pdo->commit();

            return $storedSubmission;
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }
}
