<?php declare(strict_types=1);

namespace App\Application\Competitions;

use App\Infrastructure\Persistence\Pdo\PdoKnockoutRoundRepository;

final class UpdateKnockoutRoundsService
{
    public function __construct(private readonly PdoKnockoutRoundRepository $repo) {}

    /** @param list<array<string, mixed>> $rounds */
    public function update(int $competitionId, array $rounds): void
    {
        foreach ($rounds as $round) {
            $teamSlotCount = is_numeric($round['team_slot_count'] ?? null) ? (int) $round['team_slot_count'] : 0;
            if ($teamSlotCount <= 0) {
                throw new \DomainException('Team slot count must be greater than 0.');
            }
            $label = is_string($round['label'] ?? null) ? trim($round['label']) : '';
            $roundOrder = is_numeric($round['round_order'] ?? null) ? (int) $round['round_order'] : 0;
            $isActive = !empty($round['is_active']);

            if (isset($round['id']) && is_numeric($round['id'])) {
                $this->repo->updateRound((int) $round['id'], $label, $roundOrder, $teamSlotCount, $isActive);
            } else {
                $this->repo->saveRound($competitionId, $label, $roundOrder, $teamSlotCount, $isActive);
            }
        }
    }
}
