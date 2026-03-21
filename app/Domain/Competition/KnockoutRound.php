<?php declare(strict_types=1);

namespace App\Domain\Competition;

final class KnockoutRound
{
    public function __construct(
        public readonly int $id,
        public readonly int $competitionId,
        public readonly string $label,
        public readonly int $roundOrder,
        public readonly int $teamSlotCount,
        public readonly bool $isActive,
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
            competitionId: self::intValue($row, 'competition_id'),
            label: self::stringValue($row, 'label'),
            roundOrder: self::intValue($row, 'round_order'),
            teamSlotCount: self::intValue($row, 'team_slot_count'),
            isActive: self::boolValue($row, 'is_active'),
            createdAt: self::stringValue($row, 'created_at'),
            updatedAt: self::stringValue($row, 'updated_at'),
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function intValue(array $row, string $key, int $default = 0): int
    {
        $value = $row[$key] ?? $default;

        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function stringValue(array $row, string $key, string $default = ''): string
    {
        $value = $row[$key] ?? $default;

        return is_scalar($value) ? (string) $value : $default;
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function boolValue(array $row, string $key, bool $default = true): bool
    {
        $value = $row[$key] ?? $default;

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL);
    }
}
