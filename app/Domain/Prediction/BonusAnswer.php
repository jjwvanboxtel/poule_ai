<?php declare(strict_types=1);

namespace App\Domain\Prediction;

final class BonusAnswer
{
    public function __construct(
        public readonly int $id,
        public readonly int $predictionSubmissionId,
        public readonly int $bonusQuestionId,
        public readonly ?string $answerText,
        public readonly ?float $answerNumber,
        public readonly ?int $answerEntityId,
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
            bonusQuestionId: self::intValue($row, 'bonus_question_id'),
            answerText: self::nullableStringValue($row, 'answer_text'),
            answerNumber: self::nullableFloatValue($row, 'answer_number'),
            answerEntityId: self::nullableIntValue($row, 'answer_entity_id'),
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
    private static function nullableFloatValue(array $row, string $key): ?float
    {
        $value = $row[$key] ?? null;

        return is_numeric($value) ? (float) $value : null;
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
