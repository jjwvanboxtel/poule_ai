<?php declare(strict_types=1);

namespace App\Application\Competitions;

use App\Infrastructure\Persistence\Pdo\PdoCompetitionSectionRepository;

final class UpdateCompetitionSectionsService
{
    public function __construct(private readonly PdoCompetitionSectionRepository $sections) {}

    /** @param list<array<string, mixed>> $sections */
    public function update(int $competitionId, array $sections): void
    {
        foreach ($sections as $section) {
            $sectionType = is_string($section['section_type'] ?? null) ? $section['section_type'] : '';
            $label = is_string($section['label'] ?? null) ? $section['label'] : '';
            $isActive = !empty($section['is_active']);
            $displayOrder = is_numeric($section['display_order'] ?? null) ? (int) $section['display_order'] : 0;
            if ($sectionType === '') {
                continue;
            }
            $this->sections->upsert($competitionId, $sectionType, $label, $isActive, $displayOrder);
        }
    }
}
