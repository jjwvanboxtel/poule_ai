<?php declare(strict_types=1);

namespace App\Infrastructure\Persistence\Pdo;

use App\Application\Predictions\MatchPredictionRepositoryInterface;
use App\Domain\Prediction\MatchPrediction;

final class PdoMatchPredictionRepository extends AbstractPdoRepository implements MatchPredictionRepositoryInterface
{
    /**
     * @param list<MatchPrediction> $predictions
     */
    public function insertBatch(int $submissionId, array $predictions): void
    {
        foreach ($predictions as $prediction) {
            $this->execute(
                'INSERT INTO match_predictions
                 (prediction_submission_id, match_id, predicted_home_score, predicted_away_score, predicted_outcome,
                  predicted_yellow_cards_home, predicted_yellow_cards_away, predicted_red_cards_home, predicted_red_cards_away,
                  predicted_knockout_winner_entity_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $submissionId,
                    $prediction->matchId,
                    $prediction->predictedHomeScore,
                    $prediction->predictedAwayScore,
                    $prediction->predictedOutcome,
                    $prediction->predictedYellowCardsHome,
                    $prediction->predictedYellowCardsAway,
                    $prediction->predictedRedCardsHome,
                    $prediction->predictedRedCardsAway,
                    $prediction->predictedKnockoutWinnerEntityId,
                ],
            );
        }
    }

    /**
     * @return list<MatchPrediction>
     */
    public function findBySubmissionId(int $submissionId): array
    {
        return array_map(
            MatchPrediction::fromArray(...),
            $this->fetchAll(
                'SELECT * FROM match_predictions WHERE prediction_submission_id = ? ORDER BY match_id ASC',
                [$submissionId],
            ),
        );
    }
}
