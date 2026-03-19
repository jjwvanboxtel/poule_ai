<?php declare(strict_types=1);

namespace App\Infrastructure\Persistence\Pdo;

use App\Domain\Competition\MatchResult;

final class PdoMatchResultRepository extends AbstractPdoRepository
{
    public function findByMatchId(int $matchId): ?MatchResult
    {
        $row = $this->fetchOne('SELECT * FROM match_results WHERE match_id = ? LIMIT 1', [$matchId]);
        return $row !== null ? MatchResult::fromArray($row) : null;
    }

    public function save(int $matchId, ?int $homeScore, ?int $awayScore, ?string $outcome, int $yellowHome, int $yellowAway, int $redHome, int $redAway): void
    {
        $this->execute(
            'INSERT INTO match_results (match_id, home_score, away_score, result_outcome, yellow_cards_home, yellow_cards_away, red_cards_home, red_cards_away, recorded_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE
                home_score = ?, away_score = ?, result_outcome = ?,
                yellow_cards_home = ?, yellow_cards_away = ?, red_cards_home = ?, red_cards_away = ?, recorded_at = NOW()',
            [$matchId, $homeScore, $awayScore, $outcome, $yellowHome, $yellowAway, $redHome, $redAway,
             $homeScore, $awayScore, $outcome, $yellowHome, $yellowAway, $redHome, $redAway],
        );
    }

    /** @return list<array<string, mixed>> */
    public function findByCompetition(int $competitionId): array
    {
        return $this->fetchAll(
            'SELECT mr.*, m.home_entity_id, m.away_entity_id
             FROM match_results mr
             JOIN matches m ON m.id = mr.match_id
             WHERE m.competition_id = ?',
            [$competitionId],
        );
    }
}
