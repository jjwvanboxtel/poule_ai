<?php declare(strict_types=1);

namespace App\Application\Predictions;

use App\Domain\Prediction\KnockoutRoundPrediction;

interface KnockoutRoundRepositoryInterface
{
    /**
     * @return list<array{id: int, label: string, round_order: int, team_slot_count: int}>
     */
    public function findActiveRounds(int $competitionId): array;

    /**
     * @param list<KnockoutRoundPrediction> $predictions
     */
    public function insertPredictions(int $submissionId, array $predictions): void;

    /**
     * @return list<KnockoutRoundPrediction>
     */
    public function findPredictionsBySubmissionId(int $submissionId): array;
}
