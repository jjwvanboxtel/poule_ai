<?php declare(strict_types=1);

namespace Tests\Unit\Competitions;

use App\Application\Competitions\CompetitionRepositoryInterface;
use App\Application\Competitions\EnrollParticipantService;
use App\Application\Competitions\ParticipantRepositoryInterface;
use App\Application\Competitions\UserReadRepositoryInterface;
use App\Domain\Competition\Competition;
use App\Domain\Competition\CompetitionStatus;
use App\Domain\User\User;
use App\Domain\User\UserRole;
use DomainException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class EnrollParticipantServiceTest extends TestCase
{
    private CompetitionRepositoryInterface&MockObject $competitions;
    private ParticipantRepositoryInterface&MockObject $participants;
    private UserReadRepositoryInterface&MockObject $users;
    private EnrollParticipantService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->competitions = $this->createMock(CompetitionRepositoryInterface::class);
        $this->participants = $this->createMock(ParticipantRepositoryInterface::class);
        $this->users = $this->createMock(UserReadRepositoryInterface::class);
        $this->service = new EnrollParticipantService(
            $this->competitions,
            $this->participants,
            $this->users,
        );
    }

    public function testEnrollsActiveUserInOpenCompetition(): void
    {
        $this->competitions->method('findById')->willReturn($this->openCompetition());
        $this->users->method('findById')->willReturn($this->activeUser());
        $this->competitions->method('findParticipantRow')->willReturn(null);
        $this->participants->expects($this->once())->method('enroll')->with(10, 55);

        $this->service->enroll(10, 55);
    }

    public function testRejectsEnrollmentForArchivedCompetition(): void
    {
        $archived = new Competition(
            id: 10,
            name: 'EK 2026',
            slug: 'ek-2026',
            description: '',
            startDate: '2026-06-01',
            endDate: '2026-06-30',
            submissionDeadline: '2000-01-01 00:00:00',
            entryFeeAmount: 10.0,
            prizeFirstPercent: 60,
            prizeSecondPercent: 30,
            prizeThirdPercent: 10,
            status: CompetitionStatus::Archived,
            isPublic: true,
            logoPath: null,
            createdByUserId: 1,
            createdAt: '2026-01-01 00:00:00',
            updatedAt: '2026-01-01 00:00:00',
        );

        $this->competitions->method('findById')->willReturn($archived);
        $this->users->method('findById')->willReturn($this->activeUser());

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('gearchiveerde');

        $this->service->enroll(10, 55);
    }

    public function testRejectsEnrollmentWhenCompetitionNotFound(): void
    {
        $this->competitions->method('findById')->willReturn(null);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('niet gevonden');

        $this->service->enroll(99, 55);
    }

    public function testRejectsEnrollmentWhenUserNotFound(): void
    {
        $this->competitions->method('findById')->willReturn($this->openCompetition());
        $this->users->method('findById')->willReturn(null);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('niet gevonden');

        $this->service->enroll(10, 99);
    }

    public function testRejectsEnrollmentWhenAlreadyEnrolled(): void
    {
        $this->competitions->method('findById')->willReturn($this->openCompetition());
        $this->users->method('findById')->willReturn($this->activeUser());
        $this->competitions->method('findParticipantRow')->willReturn([
            'id' => 1, 'payment_status' => 'unpaid', 'joined_at' => '2026-01-01',
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('al ingeschreven');

        $this->service->enroll(10, 55);
    }

    public function testRejectsInactiveUser(): void
    {
        $inactive = new User(
            id: 55,
            firstName: 'Test',
            lastName: 'User',
            email: 'test@example.com',
            phoneNumber: '',
            role: UserRole::Participant,
            isActive: false,
            lastLoginAt: null,
            createdAt: '2026-01-01 00:00:00',
            updatedAt: '2026-01-01 00:00:00',
        );

        $this->competitions->method('findById')->willReturn($this->openCompetition());
        $this->users->method('findById')->willReturn($inactive);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Inactieve');

        $this->service->enroll(10, 55);
    }

    private function openCompetition(): Competition
    {
        return new Competition(
            id: 10,
            name: 'EK 2026',
            slug: 'ek-2026',
            description: '',
            startDate: '2026-06-01',
            endDate: '2026-06-30',
            submissionDeadline: '2099-01-01 00:00:00',
            entryFeeAmount: 10.0,
            prizeFirstPercent: 60,
            prizeSecondPercent: 30,
            prizeThirdPercent: 10,
            status: CompetitionStatus::Open,
            isPublic: true,
            logoPath: null,
            createdByUserId: 1,
            createdAt: '2026-01-01 00:00:00',
            updatedAt: '2026-01-01 00:00:00',
        );
    }

    private function activeUser(): User
    {
        return new User(
            id: 55,
            firstName: 'Test',
            lastName: 'User',
            email: 'test@example.com',
            phoneNumber: '',
            role: UserRole::Participant,
            isActive: true,
            lastLoginAt: null,
            createdAt: '2026-01-01 00:00:00',
            updatedAt: '2026-01-01 00:00:00',
        );
    }
}
