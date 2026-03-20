<?php declare(strict_types=1);

namespace App\Infrastructure\Persistence\Pdo;

use App\Application\Auth\AuditLogRepositoryInterface;

final class PdoAdminAuditLogRepository extends AbstractPdoRepository implements AuditLogRepositoryInterface
{
    /**
     * Record an admin action.
     *
     * @param array<string, mixed>|null $oldValue
     * @param array<string, mixed>|null $newValue
     */
    public function log(
        int $userId,
        string $action,
        string $entityType,
        ?int $entityId,
        ?array $oldValue,
        ?array $newValue,
        string $ipAddress = '',
    ): void {
        $this->execute(
            'INSERT INTO admin_audit_logs
                 (user_id, action, entity_type, entity_id, old_value_json, new_value_json, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $userId,
                $action,
                $entityType,
                $entityId,
                $oldValue !== null ? json_encode($oldValue, JSON_THROW_ON_ERROR) : null,
                $newValue !== null ? json_encode($newValue, JSON_THROW_ON_ERROR) : null,
                $ipAddress,
            ],
        );
    }

    /**
     * @return list<array{id: int, user_id: int, action: string, entity_type: string, entity_id: int|null, ip_address: string, created_at: string}>
     */
    public function findRecent(int $limit = 50): array
    {
        $rows = $this->fetchAll(
            'SELECT id, user_id, action, entity_type, entity_id, ip_address, created_at
             FROM admin_audit_logs
             ORDER BY created_at DESC
             LIMIT ' . $limit,
        );

        return array_map(
            static fn (array $row): array => [
                'id' => is_numeric($row['id'] ?? null) ? (int) $row['id'] : 0,
                'user_id' => is_numeric($row['user_id'] ?? null) ? (int) $row['user_id'] : 0,
                'action' => is_scalar($row['action'] ?? null) ? (string) $row['action'] : '',
                'entity_type' => is_scalar($row['entity_type'] ?? null) ? (string) $row['entity_type'] : '',
                'entity_id' => is_numeric($row['entity_id'] ?? null) ? (int) $row['entity_id'] : null,
                'ip_address' => is_scalar($row['ip_address'] ?? null) ? (string) $row['ip_address'] : '',
                'created_at' => is_scalar($row['created_at'] ?? null) ? (string) $row['created_at'] : '',
            ],
            $rows,
        );
    }
}
