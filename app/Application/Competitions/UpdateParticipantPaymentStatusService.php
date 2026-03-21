<?php declare(strict_types=1);

namespace App\Application\Competitions;

use DomainException;

final class UpdateParticipantPaymentStatusService
{
    public function __construct(
        private readonly ParticipantRepositoryInterface $participants,
    ) {
    }

    /**
     * Mark a competition participant as paid or unpaid.
     *
     * @throws DomainException if the participant row is not found.
     */
    public function update(int $participantId, string $status): void
    {
        if (!in_array($status, ['paid', 'unpaid'], true)) {
            throw new DomainException("Ongeldig betaalstatus: {$status}. Gebruik 'paid' of 'unpaid'.");
        }

        $participant = $this->participants->findById($participantId);
        if ($participant === null) {
            throw new DomainException('Deelnemer niet gevonden.');
        }

        $this->participants->updatePaymentStatus($participantId, $status);
    }
}
