<?php declare(strict_types=1);

namespace App\Infrastructure\Persistence\Pdo;

use App\Application\Predictions\KnockoutRoundRepositoryInterface;
use App\Domain\Prediction\KnockoutRoundPrediction;

final class PdoKnockoutRoundRepository extends AbstractPdoRepository implements KnockoutRoundRepositoryInterface
{
    /**
     * @return list<array{id: int, label: string, round_order: int, team_slot_count: int}>
     */
    public function findActiveRounds(int $competitionId): array
    {
        $rows = $this->fetchAll(
            'SELECT id, label, round_order, team_slot_count
             FROM knockout_rounds
             WHERE competition_id = ? AND is_active = 1
             ORDER BY round_order ASC',
            [$competitionId],
        );

        return array_map(
            static fn (array $row): array => [
                'id' => is_numeric($row['id'] ?? null) ? (int) $row['id'] : 0,
                'label' => is_scalar($row['label'] ?? null) ? (string) $row['label'] : '',
                'round_order' => is_numeric($row['round_order'] ?? null) ? (int) $row['round_order'] : 0,
                'team_slot_count' => is_numeric($row['team_slot_count'] ?? null) ? (int) $row['team_slot_count'] : 0,
            ],
            $rows,
        );
    }

    /**
     * @param list<KnockoutRoundPrediction> $predictions
     */
    public function insertPredictions(int $submissionId, array $predictions): void
    {
        foreach ($predictions as $prediction) {
            $this->execute(
                'INSERT INTO knockout_round_predictions
                 (prediction_submission_id, knockout_round_id, catalog_entity_id, slot_number)
                 VALUES (?, ?, ?, ?)',
                [
                    $submissionId,
                    $prediction->knockoutRoundId,
                    $prediction->catalogEntityId,
                    $prediction->slotNumber,
                ],
            );
        }
    }

    /**
     * @return list<KnockoutRoundPrediction>
     */
    public function findPredictionsBySubmissionId(int $submissionId): array
    {
        return array_map(
            KnockoutRoundPrediction::fromArray(...),
            $this->fetchAll(
                'SELECT * FROM knockout_round_predictions
                 WHERE prediction_submission_id = ?
                 ORDER BY knockout_round_id ASC, slot_number ASC',
                [$submissionId],
            ),
        );
    }
}
