<?php declare(strict_types=1);

namespace App\Domain\Competition;

final class MatchResult
{
    public function __construct(
        public readonly int $id,
        public readonly int $matchId,
        public readonly ?int $homeScore,
        public readonly ?int $awayScore,
        public readonly ?string $resultOutcome,
        public readonly int $yellowCardsHome,
        public readonly int $yellowCardsAway,
        public readonly int $redCardsHome,
        public readonly int $redCardsAway,
        public readonly ?string $recordedAt,
    ) {}

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: self::intVal($row, 'id'),
            matchId: self::intVal($row, 'match_id'),
            homeScore: self::nullIntVal($row, 'home_score'),
            awayScore: self::nullIntVal($row, 'away_score'),
            resultOutcome: self::nullStrVal($row, 'result_outcome'),
            yellowCardsHome: self::intVal($row, 'yellow_cards_home'),
            yellowCardsAway: self::intVal($row, 'yellow_cards_away'),
            redCardsHome: self::intVal($row, 'red_cards_home'),
            redCardsAway: self::intVal($row, 'red_cards_away'),
            recordedAt: self::nullStrVal($row, 'recorded_at'),
        );
    }

    /** @param array<string, mixed> $row */
    private static function intVal(array $row, string $key): int
    {
        $v = $row[$key] ?? null;
        return is_numeric($v) ? (int) $v : 0;
    }

    /** @param array<string, mixed> $row */
    private static function nullIntVal(array $row, string $key): ?int
    {
        $v = $row[$key] ?? null;
        if ($v === null) {
            return null;
        }
        return is_numeric($v) ? (int) $v : null;
    }

    /** @param array<string, mixed> $row */
    private static function nullStrVal(array $row, string $key): ?string
    {
        $v = $row[$key] ?? null;
        if ($v === null) {
            return null;
        }
        return is_scalar($v) ? (string) $v : null;
    }
}
