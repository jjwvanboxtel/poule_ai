<?php declare(strict_types=1);

namespace App\Application\Competitions;

use App\Infrastructure\Persistence\Pdo\PdoCompetitionRuleRepository;

final class UpdateCompetitionRulesService
{
    public function __construct(private readonly PdoCompetitionRuleRepository $rules) {}

    /** @param list<array<string, mixed>> $rules */
    public function update(int $competitionId, int $sectionId, array $rules): void
    {
        foreach ($rules as $rule) {
            $ruleKey = is_string($rule['rule_key'] ?? null) ? $rule['rule_key'] : '';
            $pointsValue = is_numeric($rule['points_value'] ?? null) ? (int) $rule['points_value'] : 0;
            $isActive = !empty($rule['is_active']);
            if ($ruleKey === '') {
                continue;
            }
            $this->rules->save($competitionId, $sectionId, $ruleKey, $pointsValue, $isActive);
        }
    }
}
