<?php declare(strict_types=1);

namespace App\Application\Competitions;

use App\Infrastructure\Persistence\Pdo\PdoCompetitionRuleRepository;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionSectionRepository;
use DomainException;

final class UpdateCompetitionRulesService
{
    public function __construct(
        private readonly PdoCompetitionRuleRepository $rules,
        private readonly PdoCompetitionSectionRepository $sections,
    ) {
    }

    /**
     * Sync all rules for a specific section.
     *
     * @param list<array{rule_key: string, points_value: int, rule_config?: array<string, mixed>|null, is_active?: bool}> $rules
     * @throws DomainException if the section does not exist.
     */
    public function update(int $competitionId, int $sectionId, array $rules): void
    {
        $section = $this->sections->findById($sectionId);
        if ($section === null || $section->competitionId !== $competitionId) {
            throw new DomainException('Sectie niet gevonden voor deze competitie.');
        }

        $this->rules->syncForSection($competitionId, $sectionId, $rules);
    }
}
