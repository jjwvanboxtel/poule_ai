<?php declare(strict_types=1);

namespace App\Application\Predictions;

use App\Domain\Prediction\PredictionSubmission;

interface PredictionSubmissionRepositoryInterface
{
    public function findByCompetitionAndUser(int $competitionId, int $userId): ?PredictionSubmission;

    public function create(PredictionSubmission $submission): PredictionSubmission;
}
