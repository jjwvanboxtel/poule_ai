<?php declare(strict_types=1);

namespace App\Infrastructure\Persistence\Pdo;

use App\Domain\Competition\CompetitionRule;

final class PdoCompetitionRuleRepository extends AbstractPdoRepository
{
    /** @return list<CompetitionRule> */
    public function findBySectionId(int $sectionId): array
    {
        return array_map(
            CompetitionRule::fromArray(...),
            $this->fetchAll(
                'SELECT * FROM competition_rules WHERE competition_section_id = ? ORDER BY rule_key ASC',
                [$sectionId],
            ),
        );
    }

    public function findByKey(int $sectionId, string $ruleKey): ?CompetitionRule
    {
        $row = $this->fetchOne(
            'SELECT * FROM competition_rules WHERE competition_section_id = ? AND rule_key = ? LIMIT 1',
            [$sectionId, $ruleKey],
        );
        return $row !== null ? CompetitionRule::fromArray($row) : null;
    }

    public function save(int $competitionId, int $sectionId, string $ruleKey, int $pointsValue, bool $isActive): void
    {
        $existing = $this->findByKey($sectionId, $ruleKey);
        if ($existing !== null) {
            $this->execute(
                'UPDATE competition_rules SET points_value = ?, is_active = ? WHERE id = ?',
                [$pointsValue, $isActive ? 1 : 0, $existing->id],
            );
        } else {
            $this->execute(
                'INSERT INTO competition_rules (competition_id, competition_section_id, rule_key, points_value, is_active)
                 VALUES (?, ?, ?, ?, ?)',
                [$competitionId, $sectionId, $ruleKey, $pointsValue, $isActive ? 1 : 0],
            );
        }
    }

    public function deleteBySection(int $sectionId): void
    {
        $this->execute('DELETE FROM competition_rules WHERE competition_section_id = ?', [$sectionId]);
    }
}
