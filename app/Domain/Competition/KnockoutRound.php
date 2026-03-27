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
    ) {}

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: self::intVal($row, 'id'),
            competitionId: self::intVal($row, 'competition_id'),
            label: self::strVal($row, 'label'),
            roundOrder: self::intVal($row, 'round_order'),
            teamSlotCount: self::intVal($row, 'team_slot_count'),
            isActive: self::boolVal($row, 'is_active'),
        );
    }

    /** @param array<string, mixed> $row */
    private static function intVal(array $row, string $key): int
    {
        $v = $row[$key] ?? null;
        return is_numeric($v) ? (int) $v : 0;
    }

    /** @param array<string, mixed> $row */
    private static function strVal(array $row, string $key): string
    {
        $v = $row[$key] ?? '';
        return is_scalar($v) ? (string) $v : '';
    }

    /** @param array<string, mixed> $row */
    private static function boolVal(array $row, string $key): bool
    {
        $v = $row[$key] ?? false;
        if (is_bool($v)) {
            return $v;
        }
        if (is_numeric($v)) {
            return (int) $v === 1;
        }
        return filter_var($v, FILTER_VALIDATE_BOOLEAN);
    }
}
