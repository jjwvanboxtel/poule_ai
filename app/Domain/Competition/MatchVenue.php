<?php declare(strict_types=1);

namespace App\Domain\Competition;

final class MatchVenue
{
    public function __construct(
        public readonly int $id,
        public readonly int $competitionId,
        public readonly string $name,
        public readonly string $city,
    ) {}

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: self::intVal($row, 'id'),
            competitionId: self::intVal($row, 'competition_id'),
            name: self::strVal($row, 'name'),
            city: self::strVal($row, 'city'),
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
}
