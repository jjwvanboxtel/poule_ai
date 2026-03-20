<?php declare(strict_types=1);

namespace App\Infrastructure\Persistence\Pdo;

use App\Application\Competitions\CompetitionParticipantRepositoryInterface;

final class PdoCompetitionParticipantRepository extends AbstractPdoRepository implements CompetitionParticipantRepositoryInterface
{
    /** @return list<array<string, mixed>> */
    public function findByCompetition(int $competitionId): array
    {
        return $this->fetchAll(
            'SELECT cp.*, u.first_name, u.last_name, u.email
             FROM competition_participants cp
             INNER JOIN users u ON u.id = cp.user_id
             WHERE cp.competition_id = ?
             ORDER BY cp.joined_at ASC',
            [$competitionId],
        );
    }

    /** @return array<string, mixed>|null */
    public function findByCompetitionAndUser(int $competitionId, int $userId): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM competition_participants WHERE competition_id = ? AND user_id = ? LIMIT 1',
            [$competitionId, $userId],
        );
    }

    public function updatePaymentStatus(int $competitionId, int $userId, string $status): void
    {
        $this->execute(
            "UPDATE competition_participants SET payment_status = ?, payment_marked_at = NOW()
             WHERE competition_id = ? AND user_id = ?",
            [$status, $competitionId, $userId],
        );
    }

    public function enroll(int $competitionId, int $userId): void
    {
        $this->execute(
            'INSERT INTO competition_participants (competition_id, user_id, payment_status)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE user_id = user_id',
            [$competitionId, $userId, 'unpaid'],
        );
    }

    public function countByCompetition(int $competitionId): int
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS cnt FROM competition_participants WHERE competition_id = ?',
            [$competitionId],
        );
        $cnt = $row['cnt'] ?? 0;
        return is_numeric($cnt) ? (int) $cnt : 0;
    }
}
