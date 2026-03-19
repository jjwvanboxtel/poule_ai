<?php declare(strict_types=1);

namespace App\Infrastructure\Persistence\Pdo;

use App\Domain\Competition\CompetitionSection;

final class PdoCompetitionSectionRepository extends AbstractPdoRepository
{
    /** @return list<CompetitionSection> */
    public function findByCompetition(int $competitionId): array
    {
        return array_map(
            CompetitionSection::fromArray(...),
            $this->fetchAll(
                'SELECT * FROM competition_sections WHERE competition_id = ? ORDER BY display_order ASC',
                [$competitionId],
            ),
        );
    }

    public function findById(int $id): ?CompetitionSection
    {
        $row = $this->fetchOne('SELECT * FROM competition_sections WHERE id = ? LIMIT 1', [$id]);
        return $row !== null ? CompetitionSection::fromArray($row) : null;
    }

    public function upsert(int $competitionId, string $sectionType, string $label, bool $isActive, int $displayOrder): void
    {
        $this->execute(
            'INSERT INTO competition_sections (competition_id, section_type, label, is_active, display_order)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE label = ?, is_active = ?, display_order = ?',
            [$competitionId, $sectionType, $label, $isActive ? 1 : 0, $displayOrder, $label, $isActive ? 1 : 0, $displayOrder],
        );
    }

    public function updateActive(int $id, bool $isActive): void
    {
        $this->execute(
            'UPDATE competition_sections SET is_active = ? WHERE id = ?',
            [$isActive ? 1 : 0, $id],
        );
    }
}
