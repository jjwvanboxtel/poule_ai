<?php declare(strict_types=1);

namespace App\Application\Auth;

use App\Domain\User\User;
use App\Domain\User\UserRole;
use App\Infrastructure\Persistence\Pdo\PdoAdminAuditLogRepository;
use App\Infrastructure\Persistence\Pdo\PdoUserRepository;

final class UpdateUserStatusService
{
    public function __construct(
        private readonly PdoUserRepository $users,
        private readonly PdoAdminAuditLogRepository $auditLog,
    ) {}

    public function updateStatus(int $targetUserId, bool $isActive, int $actingAdminUserId): void
    {
        $user = $this->users->findById($targetUserId);
        if ($user === null) {
            throw new \DomainException('User not found.');
        }

        if (!$isActive && $user->role === UserRole::Admin && $this->users->countActiveAdmins() <= 1) {
            throw new \DomainException('Cannot deactivate the last active admin.');
        }

        $updatedUser = new User(
            id: $user->id,
            firstName: $user->firstName,
            lastName: $user->lastName,
            email: $user->email,
            phoneNumber: $user->phoneNumber,
            role: $user->role,
            isActive: $isActive,
            lastLoginAt: $user->lastLoginAt,
            createdAt: $user->createdAt,
            updatedAt: $user->updatedAt,
        );

        $this->users->save($updatedUser);
        $this->auditLog->log($actingAdminUserId, 'update_status', 'user', $targetUserId, ['is_active' => $isActive]);
    }
}
