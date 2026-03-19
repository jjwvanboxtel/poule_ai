<?php declare(strict_types=1);

namespace Tests\Unit\Competitions;

use App\Application\Competitions\UpdateCompetitionService;
use App\Domain\Competition\Competition;
use App\Domain\Competition\CompetitionStatus;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UpdateCompetitionServiceTest extends TestCase
{
    private PdoCompetitionRepository&MockObject $competitions;
    private UpdateCompetitionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->competitions = $this->createMock(PdoCompetitionRepository::class);
        $this->service = new UpdateCompetitionService($this->competitions);
    }

    public function testThrowsWhenNameEmpty(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Competition name is required.');

        $this->service->update(1, [
            'name' => '',
            'slug' => 'test',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'submission_deadline' => '2026-06-01',
            'prize_first_percent' => 60,
            'prize_second_percent' => 30,
            'prize_third_percent' => 10,
        ]);
    }

    public function testThrowsWhenSlugEmpty(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Competition slug is required.');

        $this->service->update(1, [
            'name' => 'Test',
            'slug' => '',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'submission_deadline' => '2026-06-01',
            'prize_first_percent' => 60,
            'prize_second_percent' => 30,
            'prize_third_percent' => 10,
        ]);
    }

    public function testThrowsWhenPrizesDoNotSum100(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Prize percentages must sum to 100.');

        $this->service->update(1, [
            'name' => 'Test',
            'slug' => 'test',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'submission_deadline' => '2026-06-01',
            'prize_first_percent' => 50,
            'prize_second_percent' => 30,
            'prize_third_percent' => 10,
        ]);
    }

    public function testThrowsWhenStartDateAfterEndDate(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Start date must be before end date.');

        $this->service->update(1, [
            'name' => 'Test',
            'slug' => 'test',
            'start_date' => '2026-12-31',
            'end_date' => '2026-01-01',
            'submission_deadline' => '2026-06-01',
            'prize_first_percent' => 60,
            'prize_second_percent' => 30,
            'prize_third_percent' => 10,
        ]);
    }

    public function testThrowsWhenActivatingWithoutActiveSection(): void
    {
        $this->competitions->method('hasActiveSection')->willReturn(false);
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Competition must have at least one active section to be activated.');

        $this->service->update(1, [
            'name' => 'Test',
            'slug' => 'test',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'submission_deadline' => '2026-06-01',
            'prize_first_percent' => 60,
            'prize_second_percent' => 30,
            'prize_third_percent' => 10,
            'status' => 'active',
        ]);
    }

    public function testCallsUpdateWhenValidData(): void
    {
        $this->competitions->method('hasActiveSection')->willReturn(true);
        $this->competitions->expects($this->once())->method('update');

        $this->service->update(1, [
            'name' => 'Test Competition',
            'slug' => 'test-competition',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'submission_deadline' => '2026-06-01',
            'prize_first_percent' => 60,
            'prize_second_percent' => 30,
            'prize_third_percent' => 10,
        ]);
    }
}
