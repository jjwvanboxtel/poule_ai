<?php declare(strict_types=1);

namespace App\Application\Competitions;

interface ParticipantRepositoryInterface
{
    public function enroll(int $competitionId, int $userId): int;

    /**
     * @return array{id: int, competition_id: int, user_id: int, payment_status: string, joined_at: string}|null
     */
    public function findById(int $id): ?array;

    public function remove(int $participantId): void;

    public function updatePaymentStatus(int $participantId, string $status): void;
}
