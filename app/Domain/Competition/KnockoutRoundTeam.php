<?php declare(strict_types=1);

namespace App\Domain\Competition;

final class KnockoutRoundTeam
{
    public function __construct(
        public readonly int $id,
        public readonly int $knockoutRoundId,
        public readonly int $catalogEntityId,
        public readonly int $slotNumber,
    ) {}

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: self::intVal($row, 'id'),
            knockoutRoundId: self::intVal($row, 'knockout_round_id'),
            catalogEntityId: self::intVal($row, 'catalog_entity_id'),
            slotNumber: self::intVal($row, 'slot_number'),
        );
    }

    /** @param array<string, mixed> $row */
    private static function intVal(array $row, string $key): int
    {
        $v = $row[$key] ?? null;
        return is_numeric($v) ? (int) $v : 0;
    }
}
