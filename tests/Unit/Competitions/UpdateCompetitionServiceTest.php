<?php declare(strict_types=1);

namespace Tests\Unit\Competitions;

use App\Application\Competitions\CompetitionRepositoryInterface;
use App\Application\Competitions\UpdateCompetitionService;
use App\Domain\Competition\Competition;
use App\Domain\Competition\CompetitionStatus;
use DomainException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UpdateCompetitionServiceTest extends TestCase
{
    private CompetitionRepositoryInterface&MockObject $competitions;
    private UpdateCompetitionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->competitions = $this->createMock(CompetitionRepositoryInterface::class);
        $this->service = new UpdateCompetitionService($this->competitions);
    }

    public function testRejectsPrizeDistributionNotTotalling100(): void
    {
        $this->competitions->method('findById')->willReturn($this->makeCompetition());

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('100%');

        $this->service->update(1, $this->validData(['prize_first_percent' => 50]));
    }

    public function testThrowsWhenCompetitionNotFound(): void
    {
        $this->competitions->method('findById')->willReturn(null);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('niet gevonden');

        $this->service->update(99, $this->validData());
    }

    public function testSuccessfullyUpdatesCompetition(): void
    {
        $competition = $this->makeCompetition();
        $updated = $this->makeCompetition(name: 'Updated Name');

        $this->competitions
            ->method('findById')
            ->willReturnOnConsecutiveCalls($competition, $updated);

        $this->competitions->method('findBySlug')->willReturn(null);
        $this->competitions->expects($this->once())->method('update');

        $result = $this->service->update(1, $this->validData());

        self::assertSame('Updated Name', $result->name);
    }

    public function testPrizeDistributionExactly100Passes(): void
    {
        $competition = $this->makeCompetition();

        $this->competitions
            ->method('findById')
            ->willReturnOnConsecutiveCalls($competition, $competition);

        $this->competitions->method('findBySlug')->willReturn(null);

        $result = $this->service->update(1, $this->validData());

        self::assertSame(1, $result->id);
    }

    private function makeCompetition(string $name = 'EK 2026'): Competition
    {
        return new Competition(
            id: 1,
            name: $name,
            slug: 'ek-2026',
            description: 'test',
            startDate: '2026-06-01',
            endDate: '2026-06-30',
            submissionDeadline: '2026-05-31 23:59:59',
            entryFeeAmount: 10.0,
            prizeFirstPercent: 60,
            prizeSecondPercent: 30,
            prizeThirdPercent: 10,
            status: CompetitionStatus::Draft,
            isPublic: true,
            logoPath: null,
            createdByUserId: 1,
            createdAt: '2026-01-01 00:00:00',
            updatedAt: '2026-01-01 00:00:00',
        );
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array{name: string, description: string, start_date: string, end_date: string, submission_deadline: string, entry_fee_amount: float, prize_first_percent: int, prize_second_percent: int, prize_third_percent: int, status: string, is_public: bool, logo_path: string|null}
     */
    private function validData(array $overrides = []): array
    {
        /** @var array{name: string, description: string, start_date: string, end_date: string, submission_deadline: string, entry_fee_amount: float, prize_first_percent: int, prize_second_percent: int, prize_third_percent: int, status: string, is_public: bool, logo_path: string|null} */
        return array_merge([
            'name' => 'EK 2026',
            'description' => 'test',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'submission_deadline' => '2026-05-31 23:59:59',
            'entry_fee_amount' => 10.0,
            'prize_first_percent' => 60,
            'prize_second_percent' => 30,
            'prize_third_percent' => 10,
            'status' => 'draft',
            'is_public' => true,
            'logo_path' => null,
        ], $overrides);
    }
}
