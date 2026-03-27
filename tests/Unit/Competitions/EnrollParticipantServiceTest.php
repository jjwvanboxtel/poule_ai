<?php declare(strict_types=1);

namespace Tests\Unit\Competitions;

use App\Application\Competitions\EnrollParticipantService;
use App\Domain\Competition\Competition;
use App\Domain\Competition\CompetitionStatus;
use App\Application\Competitions\CompetitionParticipantRepositoryInterface;
use App\Application\Competitions\CompetitionRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class EnrollParticipantServiceTest extends TestCase
{
    private CompetitionRepositoryInterface&MockObject $competitions;
    private CompetitionParticipantRepositoryInterface&MockObject $participants;
    private EnrollParticipantService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->competitions = $this->createMock(CompetitionRepositoryInterface::class);
        $this->participants = $this->createMock(CompetitionParticipantRepositoryInterface::class);
        $this->service = new EnrollParticipantService($this->competitions, $this->participants);
    }

    private function makeOpenCompetition(): Competition
    {
        return new Competition(
            id: 1,
            name: 'Test',
            slug: 'test',
            description: '',
            startDate: '2026-01-01',
            endDate: '2026-12-31',
            submissionDeadline: date('Y-m-d H:i:s', strtotime('+1 year')),
            entryFeeAmount: 0.0,
            prizeFirstPercent: 60,
            prizeSecondPercent: 30,
            prizeThirdPercent: 10,
            status: CompetitionStatus::Open,
            isPublic: true,
            logoPath: null,
            createdByUserId: 1,
            createdAt: '2026-01-01',
            updatedAt: '2026-01-01',
        );
    }

    public function testThrowsWhenCompetitionNotFound(): void
    {
        $this->competitions->method('findById')->willReturn(null);
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Competition not found.');

        $this->service->enroll(99, 1);
    }

    public function testThrowsWhenCompetitionNotOpen(): void
    {
        $closed = new Competition(
            id: 1, name: 'Test', slug: 'test', description: '',
            startDate: '2024-01-01', endDate: '2024-12-31',
            submissionDeadline: '2024-06-01 00:00:00',
            entryFeeAmount: 0.0,
            prizeFirstPercent: 60, prizeSecondPercent: 30, prizeThirdPercent: 10,
            status: CompetitionStatus::Closed,
            isPublic: true, logoPath: null, createdByUserId: 1,
            createdAt: '2024-01-01', updatedAt: '2024-01-01',
        );

        $this->competitions->method('findById')->willReturn($closed);
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Competition is not open for enrollment.');

        $this->service->enroll(1, 1);
    }

    public function testThrowsWhenAlreadyEnrolled(): void
    {
        $this->competitions->method('findById')->willReturn($this->makeOpenCompetition());
        $this->participants->method('findByCompetitionAndUser')->willReturn(['user_id' => 1]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('User is already enrolled in this competition.');

        $this->service->enroll(1, 1);
    }

    public function testEnrollsSuccessfully(): void
    {
        $this->competitions->method('findById')->willReturn($this->makeOpenCompetition());
        $this->participants->method('findByCompetitionAndUser')->willReturn(null);
        $this->participants->expects($this->once())->method('enroll');

        $this->service->enroll(1, 2);
    }
}
