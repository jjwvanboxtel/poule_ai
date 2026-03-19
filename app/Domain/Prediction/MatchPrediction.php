<?php declare(strict_types=1);

namespace App\Domain\Prediction;

final class MatchPrediction
{
    public function __construct(
        public readonly int $id,
        public readonly int $predictionSubmissionId,
        public readonly int $matchId,
        public readonly ?int $predictedHomeScore,
        public readonly ?int $predictedAwayScore,
        public readonly ?string $predictedOutcome,
        public readonly ?int $predictedYellowCardsHome,
        public readonly ?int $predictedYellowCardsAway,
        public readonly ?int $predictedRedCardsHome,
        public readonly ?int $predictedRedCardsAway,
        public readonly ?int $predictedKnockoutWinnerEntityId,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            id: self::intValue($row, 'id'),
            predictionSubmissionId: self::intValue($row, 'prediction_submission_id'),
            matchId: self::intValue($row, 'match_id'),
            predictedHomeScore: self::nullableIntValue($row, 'predicted_home_score'),
            predictedAwayScore: self::nullableIntValue($row, 'predicted_away_score'),
            predictedOutcome: self::nullableStringValue($row, 'predicted_outcome'),
            predictedYellowCardsHome: self::nullableIntValue($row, 'predicted_yellow_cards_home'),
            predictedYellowCardsAway: self::nullableIntValue($row, 'predicted_yellow_cards_away'),
            predictedRedCardsHome: self::nullableIntValue($row, 'predicted_red_cards_home'),
            predictedRedCardsAway: self::nullableIntValue($row, 'predicted_red_cards_away'),
            predictedKnockoutWinnerEntityId: self::nullableIntValue($row, 'predicted_knockout_winner_entity_id'),
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function intValue(array $row, string $key): int
    {
        $value = $row[$key] ?? null;

        return is_numeric($value) ? (int) $value : 0;
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function nullableIntValue(array $row, string $key): ?int
    {
        $value = $row[$key] ?? null;

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function nullableStringValue(array $row, string $key): ?string
    {
        $value = $row[$key] ?? null;

        return is_scalar($value) ? (string) $value : null;
    }
}
