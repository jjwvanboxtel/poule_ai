<?php declare(strict_types=1);

namespace App\Application\Predictions;

use App\Domain\Competition\CompetitionSection;

interface CompetitionDataProviderInterface
{
    /**
     * @return list<CompetitionSection>
     */
    public function findActiveSections(int $competitionId): array;

    /**
     * @return list<array{
     *     id: int,
     *     home_entity_id: int,
     *     away_entity_id: int,
     *     home_label: string,
     *     away_label: string,
     *     stage: string,
     *     kickoff_at: string
     * }>
     */
    public function findMatchesForCompetition(int $competitionId): array;

    /**
     * @return list<array{
     *     id: int,
     *     prompt: string,
     *     question_type: string,
     *     entity_type_constraint: ?string,
     *     display_order: int
     * }>
     */
    public function findActiveBonusQuestions(int $competitionId): array;

    /**
     * @return list<array{
     *     id: int,
     *     entity_type: string,
     *     display_name: string,
     *     short_code: ?string
     * }>
     */
    public function findActiveEntitiesForCompetition(int $competitionId, ?string $entityType = null): array;
}
