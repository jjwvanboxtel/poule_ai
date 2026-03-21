<?php declare(strict_types=1);

namespace App\Infrastructure\Persistence\Pdo;

use App\Domain\Competition\CompetitionRule;

final class PdoCompetitionRuleRepository extends AbstractPdoRepository
{
    /**
     * @return list<CompetitionRule>
     */
    public function findByCompetitionId(int $competitionId): array
    {
        return array_map(
            CompetitionRule::fromArray(...),
            $this->fetchAll(
                'SELECT * FROM competition_rules
                 WHERE competition_id = ?
                 ORDER BY competition_section_id ASC, id ASC',
                [$competitionId],
            ),
        );
    }

    /**
     * @return list<CompetitionRule>
     */
    public function findBySectionId(int $sectionId): array
    {
        return array_map(
            CompetitionRule::fromArray(...),
            $this->fetchAll(
                'SELECT * FROM competition_rules
                 WHERE competition_section_id = ?
                 ORDER BY id ASC',
                [$sectionId],
            ),
        );
    }

    /**
     * Insert a new rule and return its ID.
     *
     * @param array<string, mixed>|null $ruleConfig
     */
    public function insert(
        int $competitionId,
        int $sectionId,
        string $ruleKey,
        int $pointsValue,
        ?array $ruleConfig = null,
        bool $isActive = true,
    ): int {
        $configJson = $ruleConfig !== null
            ? json_encode($ruleConfig, JSON_THROW_ON_ERROR)
            : null;

        $this->execute(
            'INSERT INTO competition_rules
                 (competition_id, competition_section_id, rule_key, points_value, rule_config_json, is_active)
             VALUES (?, ?, ?, ?, ?, ?)',
            [$competitionId, $sectionId, $ruleKey, $pointsValue, $configJson, $isActive ? 1 : 0],
        );

        return $this->lastInsertId();
    }

    /**
     * @param array<string, mixed>|null $ruleConfig
     */
    public function update(
        int $id,
        string $ruleKey,
        int $pointsValue,
        ?array $ruleConfig = null,
        bool $isActive = true,
    ): void {
        $configJson = $ruleConfig !== null
            ? json_encode($ruleConfig, JSON_THROW_ON_ERROR)
            : null;

        $this->execute(
            'UPDATE competition_rules
             SET rule_key = ?, points_value = ?, rule_config_json = ?, is_active = ?
             WHERE id = ?',
            [$ruleKey, $pointsValue, $configJson, $isActive ? 1 : 0, $id],
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM competition_rules WHERE id = ?', [$id]);
    }

    /**
     * Replace all rules for a section with the provided list.
     *
     * @param list<array{rule_key: string, points_value: int, rule_config?: array<string, mixed>|null, is_active?: bool}> $rules
     */
    public function syncForSection(int $competitionId, int $sectionId, array $rules): void
    {
        $this->transactional(function () use ($competitionId, $sectionId, $rules): void {
            $this->execute(
                'DELETE FROM competition_rules WHERE competition_section_id = ?',
                [$sectionId],
            );

            foreach ($rules as $rule) {
                $this->insert(
                    $competitionId,
                    $sectionId,
                    $rule['rule_key'],
                    $rule['points_value'],
                    $rule['rule_config'] ?? null,
                    $rule['is_active'] ?? true,
                );
            }
        });
    }
}
