<?php declare(strict_types=1);

namespace App\Infrastructure\Persistence\Pdo;

use App\Application\Predictions\PredictionSubmissionRepositoryInterface;
use App\Domain\Prediction\PredictionSubmission;

final class PdoPredictionSubmissionRepository extends AbstractPdoRepository implements PredictionSubmissionRepositoryInterface
{
    public function findByCompetitionAndUser(int $competitionId, int $userId): ?PredictionSubmission
    {
        $row = $this->fetchOne(
            'SELECT * FROM prediction_submissions WHERE competition_id = ? AND user_id = ? LIMIT 1',
            [$competitionId, $userId],
        );

        return $row !== null ? PredictionSubmission::fromArray($row) : null;
    }

    public function create(PredictionSubmission $submission): PredictionSubmission
    {
        $this->execute(
            'INSERT INTO prediction_submissions
             (competition_id, user_id, submitted_at, submission_hash, is_locked)
             VALUES (?, ?, ?, ?, ?)',
            [
                $submission->competitionId,
                $submission->userId,
                $submission->submittedAt,
                $submission->submissionHash,
                $submission->isLocked ? 1 : 0,
            ],
        );

        return new PredictionSubmission(
            id: $this->lastInsertId(),
            competitionId: $submission->competitionId,
            userId: $submission->userId,
            submittedAt: $submission->submittedAt,
            submissionHash: $submission->submissionHash,
            isLocked: $submission->isLocked,
        );
    }
}
