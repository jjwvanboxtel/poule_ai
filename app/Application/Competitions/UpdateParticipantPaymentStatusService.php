<?php declare(strict_types=1);

namespace App\Application\Competitions;

use App\Infrastructure\Persistence\Pdo\PdoCompetitionParticipantRepository;

final class UpdateParticipantPaymentStatusService
{
    public function __construct(private readonly PdoCompetitionParticipantRepository $participants) {}

    public function update(int $competitionId, int $userId, string $status): void
    {
        if (!in_array($status, ['paid', 'unpaid'], true)) {
            throw new \DomainException("Invalid payment status: {$status}.");
        }
        $this->participants->updatePaymentStatus($competitionId, $userId, $status);
    }
}
