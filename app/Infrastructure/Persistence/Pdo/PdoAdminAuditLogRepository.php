<?php declare(strict_types=1);

namespace App\Infrastructure\Persistence\Pdo;

use App\Application\Auth\AdminAuditLogRepositoryInterface;

final class PdoAdminAuditLogRepository extends AbstractPdoRepository implements AdminAuditLogRepositoryInterface
{
    /** @param array<string, mixed>|null $details */
    public function log(int $userId, string $action, string $entityType, ?int $entityId, ?array $details): void
    {
        $this->execute(
            'INSERT INTO admin_audit_logs (user_id, action, entity_type, entity_id, details_json)
             VALUES (?, ?, ?, ?, ?)',
            [$userId, $action, $entityType, $entityId, $details !== null ? json_encode($details) : null],
        );
    }

    /** @return list<array<string, mixed>> */
    public function findRecent(int $limit = 50): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT al.*, u.first_name, u.last_name
             FROM admin_audit_logs al
             JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC
             LIMIT :lim',
        );
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_values(array_filter($rows, static fn (mixed $r): bool => is_array($r)));
    }
}
