<?php declare(strict_types=1);

namespace App\Application\Auth;

use App\Domain\User\User;
use App\Domain\User\UserRole;
use App\Application\Auth\AdminAuditLogRepositoryInterface;
use App\Application\Auth\UserRepositoryInterface;

final class UpdateUserRoleService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly AdminAuditLogRepositoryInterface $auditLog,
    ) {}

    public function updateRole(int $targetUserId, string $newRole, int $actingAdminUserId): void
    {
        if (!in_array($newRole, ['admin', 'participant'], true)) {
            throw new \DomainException("Invalid role: {$newRole}.");
        }

        if ($newRole === 'participant' && $this->users->countActiveAdmins() <= 1) {
            throw new \DomainException('Cannot demote the last active admin.');
        }

        $user = $this->users->findById($targetUserId);
        if ($user === null) {
            throw new \DomainException('User not found.');
        }

        $updatedUser = new User(
            id: $user->id,
            firstName: $user->firstName,
            lastName: $user->lastName,
            email: $user->email,
            phoneNumber: $user->phoneNumber,
            role: UserRole::from($newRole),
            isActive: $user->isActive,
            lastLoginAt: $user->lastLoginAt,
            createdAt: $user->createdAt,
            updatedAt: $user->updatedAt,
        );

        $this->users->save($updatedUser);
        $this->auditLog->log($actingAdminUserId, 'update_role', 'user', $targetUserId, ['new_role' => $newRole]);
    }
}
