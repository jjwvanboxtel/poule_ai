<?php declare(strict_types=1);

namespace App\Infrastructure\Persistence\Pdo;

use App\Domain\Competition\CompetitionSection;
use App\Domain\Competition\SectionType;

final class PdoCompetitionSectionRepository extends AbstractPdoRepository
{
    /**
     * @return list<CompetitionSection>
     */
    public function findByCompetitionId(int $competitionId): array
    {
        return array_map(
            CompetitionSection::fromArray(...),
            $this->fetchAll(
                'SELECT * FROM competition_sections
                 WHERE competition_id = ?
                 ORDER BY display_order ASC, id ASC',
                [$competitionId],
            ),
        );
    }

    public function findById(int $id): ?CompetitionSection
    {
        $row = $this->fetchOne(
            'SELECT * FROM competition_sections WHERE id = ? LIMIT 1',
            [$id],
        );

        return $row !== null ? CompetitionSection::fromArray($row) : null;
    }

    /**
     * Upsert all sections for a competition. Any existing section of the same type is updated;
     * missing section types are inserted. Sections not provided are deactivated.
     *
     * @param list<array{section_type: string, label: string, is_active: bool, display_order: int}> $sections
     */
    public function syncForCompetition(int $competitionId, array $sections): void
    {
        $this->transactional(function () use ($competitionId, $sections): void {
            // Deactivate all existing sections first
            $this->execute(
                'UPDATE competition_sections SET is_active = 0 WHERE competition_id = ?',
                [$competitionId],
            );

            $order = 1;
            foreach ($sections as $section) {
                $sectionType = $section['section_type'];
                $label = $section['label'];
                $isActive = $section['is_active'] ? 1 : 0;
                $displayOrder = $section['display_order'];

                $this->execute(
                    'INSERT INTO competition_sections
                         (competition_id, section_type, label, is_active, display_order)
                     VALUES (?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                         label         = VALUES(label),
                         is_active     = VALUES(is_active),
                         display_order = VALUES(display_order)',
                    [$competitionId, $sectionType, $label, $isActive, $displayOrder],
                );

                $order++;
            }
        });
    }

    public function upsert(
        int $competitionId,
        SectionType $sectionType,
        string $label,
        bool $isActive,
        int $displayOrder,
    ): int {
        $this->execute(
            'INSERT INTO competition_sections
                 (competition_id, section_type, label, is_active, display_order)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                 label         = VALUES(label),
                 is_active     = VALUES(is_active),
                 display_order = VALUES(display_order)',
            [$competitionId, $sectionType->value, $label, $isActive ? 1 : 0, $displayOrder],
        );

        return $this->lastInsertId();
    }
}
