<?php declare(strict_types=1);

namespace App\Application\Competitions;

use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;

final class CreateCompetitionService
{
    public function __construct(private readonly PdoCompetitionRepository $competitions) {}

    /** @param array<string, mixed> $data */
    public function create(int $userId, array $data): int
    {
        $name = is_string($data['name'] ?? null) ? trim($data['name']) : '';
        $slug = is_string($data['slug'] ?? null) ? trim($data['slug']) : '';
        $startDate = is_string($data['start_date'] ?? null) ? $data['start_date'] : '';
        $endDate = is_string($data['end_date'] ?? null) ? $data['end_date'] : '';
        $p1 = is_numeric($data['prize_first_percent'] ?? null) ? (int) $data['prize_first_percent'] : 0;
        $p2 = is_numeric($data['prize_second_percent'] ?? null) ? (int) $data['prize_second_percent'] : 0;
        $p3 = is_numeric($data['prize_third_percent'] ?? null) ? (int) $data['prize_third_percent'] : 0;

        if ($name === '') {
            throw new \DomainException('Competition name is required.');
        }
        if ($slug === '') {
            throw new \DomainException('Competition slug is required.');
        }
        if ($startDate === '' || $endDate === '') {
            throw new \DomainException('Start date and end date are required.');
        }
        if ($startDate >= $endDate) {
            throw new \DomainException('Start date must be before end date.');
        }
        if ($p1 + $p2 + $p3 !== 100) {
            throw new \DomainException('Prize percentages must sum to 100.');
        }

        $data['created_by_user_id'] = $userId;
        return $this->competitions->insert($data);
    }
}
