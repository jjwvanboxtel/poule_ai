<?php declare(strict_types=1);

namespace App\Domain\Competition;

final class KnockoutRoundTeam
{
    public function __construct(
        public readonly int $id,
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
}
