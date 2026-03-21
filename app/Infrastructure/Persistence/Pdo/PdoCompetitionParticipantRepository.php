<?php declare(strict_types=1);

namespace App\Infrastructure\Persistence\Pdo;

use App\Application\Competitions\ParticipantRepositoryInterface;

final class PdoCompetitionParticipantRepository extends AbstractPdoRepository implements ParticipantRepositoryInterface
{
    /**
     * @return list<array{id: int, competition_id: int, user_id: int, payment_status: string, joined_at: string, first_name: string, last_name: string, email: string}>
     */
    public function findByCompetitionId(int $competitionId): array
    {
        $rows = $this->fetchAll(
            'SELECT cp.id, cp.competition_id, cp.user_id, cp.payment_status, cp.joined_at,
                    u.first_name, u.last_name, u.email
             FROM competition_participants cp
             INNER JOIN users u ON u.id = cp.user_id
             WHERE cp.competition_id = ?
             ORDER BY cp.joined_at ASC, u.last_name ASC',
            [$competitionId],
        );

        return array_map(
            static fn (array $row): array => [
                'id' => is_numeric($row['id'] ?? null) ? (int) $row['id'] : 0,
                'competition_id' => is_numeric($row['competition_id'] ?? null) ? (int) $row['competition_id'] : 0,
                'user_id' => is_numeric($row['user_id'] ?? null) ? (int) $row['user_id'] : 0,
                'payment_status' => is_scalar($row['payment_status'] ?? null) ? (string) $row['payment_status'] : 'unpaid',
                'joined_at' => is_scalar($row['joined_at'] ?? null) ? (string) $row['joined_at'] : '',
                'first_name' => is_scalar($row['first_name'] ?? null) ? (string) $row['first_name'] : '',
                'last_name' => is_scalar($row['last_name'] ?? null) ? (string) $row['last_name'] : '',
                'email' => is_scalar($row['email'] ?? null) ? (string) $row['email'] : '',
            ],
            $rows,
        );
    }

    /**
     * @return array{id: int, competition_id: int, user_id: int, payment_status: string, joined_at: string}|null
     */
    public function findById(int $id): ?array
    {
        $row = $this->fetchOne(
            'SELECT cp.id, cp.competition_id, cp.user_id, cp.payment_status, cp.joined_at
             FROM competition_participants cp
             WHERE cp.id = ? LIMIT 1',
            [$id],
        );

        if ($row === null) {
            return null;
        }

        return [
            'id' => is_numeric($row['id'] ?? null) ? (int) $row['id'] : 0,
            'competition_id' => is_numeric($row['competition_id'] ?? null) ? (int) $row['competition_id'] : 0,
            'user_id' => is_numeric($row['user_id'] ?? null) ? (int) $row['user_id'] : 0,
            'payment_status' => is_scalar($row['payment_status'] ?? null) ? (string) $row['payment_status'] : 'unpaid',
            'joined_at' => is_scalar($row['joined_at'] ?? null) ? (string) $row['joined_at'] : '',
        ];
    }

    public function enroll(int $competitionId, int $userId): int
    {
        $this->execute(
            'INSERT INTO competition_participants (competition_id, user_id, payment_status)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE user_id = user_id',
            [$competitionId, $userId, 'unpaid'],
        );

        return $this->lastInsertId();
    }

    public function updatePaymentStatus(int $participantId, string $status): void
    {
        $markedAt = $status === 'paid' ? date('Y-m-d H:i:s') : null;

        $this->execute(
            'UPDATE competition_participants
             SET payment_status = ?, payment_marked_at = ?
             WHERE id = ?',
            [$status, $markedAt, $participantId],
        );
    }

    public function remove(int $participantId): void
    {
        $this->execute(
            'DELETE FROM competition_participants WHERE id = ?',
            [$participantId],
        );
    }

    public function countByCompetition(int $competitionId): int
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS cnt FROM competition_participants WHERE competition_id = ?',
            [$competitionId],
        );

        $count = $row['cnt'] ?? 0;

        return is_numeric($count) ? (int) $count : 0;
    }
}
