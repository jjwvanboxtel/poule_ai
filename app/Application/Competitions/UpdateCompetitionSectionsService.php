<?php declare(strict_types=1);

namespace App\Application\Competitions;

use App\Infrastructure\Persistence\Pdo\PdoCompetitionSectionRepository;

final class UpdateCompetitionSectionsService
{
    public function __construct(
        private readonly PdoCompetitionSectionRepository $sections,
    ) {
    }

    /**
     * Sync all sections for a competition.
     *
     * @param list<array{section_type: string, label: string, is_active: bool, display_order: int}> $sections
     */
    public function update(int $competitionId, array $sections): void
    {
        $this->sections->syncForCompetition($competitionId, $sections);
    }
}
