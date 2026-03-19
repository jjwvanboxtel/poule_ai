<?php declare(strict_types=1);

namespace App\Domain\Prediction;

final class PredictionSubmission
{
    public function __construct(
        public readonly int $id,
        public readonly int $competitionId,
        public readonly int $userId,
        public readonly string $submittedAt,
        public readonly string $submissionHash,
        public readonly bool $isLocked,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            id: self::intValue($row, 'id'),
            competitionId: self::intValue($row, 'competition_id'),
            userId: self::intValue($row, 'user_id'),
            submittedAt: self::stringValue($row, 'submitted_at'),
            submissionHash: self::stringValue($row, 'submission_hash'),
            isLocked: self::boolValue($row, 'is_locked'),
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

    /**
     * @param array<string, mixed> $row
     */
    private static function boolValue(array $row, string $key): bool
    {
        $value = $row[$key] ?? false;

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL);
    }
}
