<?php declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Application\Auth\UpdateUserRoleService;
use App\Application\Auth\UpdateUserStatusService;
use App\Domain\User\User;
use App\Domain\User\UserRole;
use App\Application\Auth\AdminAuditLogRepositoryInterface;
use App\Application\Auth\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AdminRoleGuardTest extends TestCase
{
    private UserRepositoryInterface&MockObject $users;
    private AdminAuditLogRepositoryInterface&MockObject $auditLog;
    private UpdateUserRoleService $roleService;
    private UpdateUserStatusService $statusService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->users = $this->createMock(UserRepositoryInterface::class);
        $this->auditLog = $this->createMock(AdminAuditLogRepositoryInterface::class);
        $this->roleService = new UpdateUserRoleService($this->users, $this->auditLog);
        $this->statusService = new UpdateUserStatusService($this->users, $this->auditLog);
    }

    private function makeAdminUser(int $id): User
    {
        return new User(
            id: $id, firstName: 'Admin', lastName: 'User', email: 'admin@example.com',
            phoneNumber: '', role: UserRole::Admin, isActive: true,
            lastLoginAt: null, createdAt: '2026-01-01', updatedAt: '2026-01-01',
        );
    }

    private function makeParticipantUser(int $id): User
    {
        return new User(
            id: $id, firstName: 'Participant', lastName: 'User', email: 'user@example.com',
            phoneNumber: '', role: UserRole::Participant, isActive: true,
            lastLoginAt: null, createdAt: '2026-01-01', updatedAt: '2026-01-01',
        );
    }

    public function testThrowsWhenInvalidRole(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Invalid role: superuser.');

        $this->roleService->updateRole(1, 'superuser', 2);
    }

    public function testThrowsWhenDemotingLastAdmin(): void
    {
        $this->users->method('countActiveAdmins')->willReturn(1);
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot demote the last active admin.');

        $this->roleService->updateRole(1, 'participant', 1);
    }

    public function testCanDemoteAdminWhenMultipleAdminsExist(): void
    {
        $this->users->method('countActiveAdmins')->willReturn(2);
        $this->users->method('findById')->willReturn($this->makeAdminUser(1));
        $this->users->expects($this->once())->method('save');
        $this->auditLog->expects($this->once())->method('log');

        $this->roleService->updateRole(1, 'participant', 2);
    }

    public function testThrowsWhenDeactivatingLastAdmin(): void
    {
        $admin = $this->makeAdminUser(1);
        $this->users->method('findById')->willReturn($admin);
        $this->users->method('countActiveAdmins')->willReturn(1);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot deactivate the last active admin.');

        $this->statusService->updateStatus(1, false, 2);
    }

    public function testCanDeactivateParticipant(): void
    {
        $participant = $this->makeParticipantUser(5);
        $this->users->method('findById')->willReturn($participant);
        $this->users->expects($this->once())->method('save');
        $this->auditLog->expects($this->once())->method('log');

        $this->statusService->updateStatus(5, false, 1);
    }
}
