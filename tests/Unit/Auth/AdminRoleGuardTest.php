<?php declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Application\Auth\AuditLogRepositoryInterface;
use App\Application\Auth\UpdateUserRoleService;
use App\Application\Auth\UpdateUserStatusService;
use App\Application\Auth\UserRepositoryInterface;
use App\Domain\User\User;
use App\Domain\User\UserRole;
use DomainException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AdminRoleGuardTest extends TestCase
{
    private UserRepositoryInterface&MockObject $users;
    private AuditLogRepositoryInterface&MockObject $auditLog;
    private UpdateUserRoleService $roleService;
    private UpdateUserStatusService $statusService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->users = $this->createMock(UserRepositoryInterface::class);
        $this->auditLog = $this->createMock(AuditLogRepositoryInterface::class);
        $this->roleService = new UpdateUserRoleService($this->users, $this->auditLog);
        $this->statusService = new UpdateUserStatusService($this->users, $this->auditLog);
    }

    public function testCannotDowngradeLastActiveAdmin(): void
    {
        $this->users->method('findById')->willReturn($this->adminUser());
        $this->users->method('countActiveAdmins')->willReturn(1);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('laatste actieve beheerder');

        $this->roleService->update(1, 'participant', 1);
    }

    public function testCanDowngradeAdminWhenAnotherAdminExists(): void
    {
        $this->users->method('findById')->willReturn($this->adminUser());
        $this->users->method('countActiveAdmins')->willReturn(2);
        $this->users->expects($this->once())->method('save');
        $this->auditLog->expects($this->once())->method('log');

        $this->roleService->update(1, 'participant', 99);
    }

    public function testRejectsInvalidRole(): void
    {
        $this->users->method('findById')->willReturn($this->participantUser());

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Ongeldig rol');

        $this->roleService->update(2, 'superadmin', 99);
    }

    public function testThrowsWhenUserNotFoundForRoleUpdate(): void
    {
        $this->users->method('findById')->willReturn(null);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('niet gevonden');

        $this->roleService->update(99, 'participant', 1);
    }

    public function testCannotDeactivateLastActiveAdmin(): void
    {
        $this->users->method('findById')->willReturn($this->adminUser());
        $this->users->method('countActiveAdmins')->willReturn(1);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('laatste actieve beheerder');

        $this->statusService->update(1, false, 1);
    }

    public function testCanDeactivateAdminWhenAnotherAdminExists(): void
    {
        $this->users->method('findById')->willReturn($this->adminUser());
        $this->users->method('countActiveAdmins')->willReturn(2);
        $this->users->expects($this->once())->method('save');
        $this->auditLog->expects($this->once())->method('log');

        $this->statusService->update(1, false, 99);
    }

    public function testThrowsWhenUserNotFoundForStatusUpdate(): void
    {
        $this->users->method('findById')->willReturn(null);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('niet gevonden');

        $this->statusService->update(99, false, 1);
    }

    public function testAuditLogIsRecordedAfterRoleChange(): void
    {
        $this->users->method('findById')->willReturn($this->participantUser());
        $this->users->method('countActiveAdmins')->willReturn(1);
        $this->users->method('save');

        $this->auditLog
            ->expects($this->once())
            ->method('log')
            ->with(
                $this->equalTo(99),
                $this->equalTo('update_user_role'),
                $this->equalTo('user'),
                $this->equalTo(2),
                $this->anything(),
                $this->anything(),
                $this->anything(),
            );

        $this->roleService->update(2, 'admin', 99);
    }

    private function adminUser(): User
    {
        return new User(
            id: 1,
            firstName: 'Admin',
            lastName: 'Gebruiker',
            email: 'admin@example.com',
            phoneNumber: '',
            role: UserRole::Admin,
            isActive: true,
            lastLoginAt: null,
            createdAt: '2026-01-01 00:00:00',
            updatedAt: '2026-01-01 00:00:00',
        );
    }

    private function participantUser(): User
    {
        return new User(
            id: 2,
            firstName: 'Test',
            lastName: 'User',
            email: 'user@example.com',
            phoneNumber: '',
            role: UserRole::Participant,
            isActive: true,
            lastLoginAt: null,
            createdAt: '2026-01-01 00:00:00',
            updatedAt: '2026-01-01 00:00:00',
        );
    }
}
