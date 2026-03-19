<?php declare(strict_types=1);

namespace App\Application\Predictions;

use App\Domain\Prediction\MatchPrediction;

interface MatchPredictionRepositoryInterface
{
    /**
     * @param list<MatchPrediction> $predictions
     */
    public function insertBatch(int $submissionId, array $predictions): void;

    /**
     * @return list<MatchPrediction>
     */
    public function findBySubmissionId(int $submissionId): array;
}
