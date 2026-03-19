<?php declare(strict_types=1);

namespace App\Domain\Prediction;

final class KnockoutRoundPrediction
{
    public function __construct(
        public readonly int $id,
        public readonly int $predictionSubmissionId,
        public readonly int $knockoutRoundId,
        public readonly int $catalogEntityId,
        public readonly int $slotNumber,
        public readonly string $createdAt,
        public readonly string $updatedAt,
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
            knockoutRoundId: self::intValue($row, 'knockout_round_id'),
            catalogEntityId: self::intValue($row, 'catalog_entity_id'),
            slotNumber: self::intValue($row, 'slot_number'),
            createdAt: self::stringValue($row, 'created_at'),
            updatedAt: self::stringValue($row, 'updated_at'),
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
    private static function stringValue(array $row, string $key): string
    {
        $value = $row[$key] ?? '';

        return is_scalar($value) ? (string) $value : '';
    }
}
