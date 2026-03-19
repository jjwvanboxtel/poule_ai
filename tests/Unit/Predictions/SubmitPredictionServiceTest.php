<?php declare(strict_types=1);

namespace Tests\Unit\Predictions;

use App\Application\Predictions\BonusAnswerRepositoryInterface;
use App\Application\Predictions\BonusAnswerValidator;
use App\Application\Predictions\CompetitionDataProviderInterface;
use App\Application\Predictions\KnockoutPredictionValidator;
use App\Application\Predictions\KnockoutRoundRepositoryInterface;
use App\Application\Predictions\MatchPredictionRepositoryInterface;
use App\Application\Predictions\PredictionPayloadValidator;
use App\Application\Predictions\PredictionSubmissionRepositoryInterface;
use App\Application\Predictions\SubmitPredictionService;
use App\Domain\Competition\Competition;
use App\Domain\Competition\CompetitionSection;
use App\Domain\Competition\CompetitionStatus;
use App\Domain\Competition\SectionType;
use App\Domain\Prediction\BonusAnswer;
use App\Domain\Prediction\KnockoutRoundPrediction;
use App\Domain\Prediction\MatchPrediction;
use App\Domain\Prediction\PredictionSubmission;
use App\Domain\User\User;
use App\Domain\User\UserRole;
use DomainException;
use PDO;
use PHPUnit\Framework\TestCase;

final class SubmitPredictionServiceTest extends TestCase
{
    private PDO $pdo;
    private InMemoryCompetitionDataProvider $competitionDataProvider;
    private InMemoryPredictionSubmissionRepository $submissionRepository;
    private InMemoryMatchPredictionRepository $matchPredictionRepository;
    private InMemoryBonusAnswerRepository $bonusAnswerRepository;
    private InMemoryKnockoutRoundRepository $knockoutRoundRepository;
    private SubmitPredictionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->competitionDataProvider = new InMemoryCompetitionDataProvider();
        $this->submissionRepository = new InMemoryPredictionSubmissionRepository();
        $this->matchPredictionRepository = new InMemoryMatchPredictionRepository();
        $this->bonusAnswerRepository = new InMemoryBonusAnswerRepository();
        $this->knockoutRoundRepository = new InMemoryKnockoutRoundRepository();

        $this->competitionDataProvider->sections = [
            new CompetitionSection(1, 10, SectionType::GroupStageScores, 'Scores', true, 1, '2026-03-15 12:00:00', '2026-03-15 12:00:00'),
            new CompetitionSection(2, 10, SectionType::MatchOutcomes, 'Uitslagen', true, 2, '2026-03-15 12:00:00', '2026-03-15 12:00:00'),
            new CompetitionSection(3, 10, SectionType::Cards, 'Kaarten', true, 3, '2026-03-15 12:00:00', '2026-03-15 12:00:00'),
            new CompetitionSection(4, 10, SectionType::BonusQuestions, 'Bonus', true, 4, '2026-03-15 12:00:00', '2026-03-15 12:00:00'),
            new CompetitionSection(5, 10, SectionType::Knockout, 'Knockout', true, 5, '2026-03-15 12:00:00', '2026-03-15 12:00:00'),
        ];
        $this->competitionDataProvider->matches = [[
            'id' => 101,
            'home_entity_id' => 1,
            'away_entity_id' => 2,
            'home_label' => 'Nederland',
            'away_label' => 'Duitsland',
            'stage' => 'group',
            'kickoff_at' => '2026-06-05 20:00:00',
        ]];
        $this->competitionDataProvider->bonusQuestions = [
            ['id' => 201, 'prompt' => 'Welk land wint het toernooi?', 'question_type' => 'entity', 'entity_type_constraint' => 'country', 'display_order' => 1],
        ];
        $this->competitionDataProvider->entities = [
            ['id' => 1, 'entity_type' => 'country', 'display_name' => 'Nederland', 'short_code' => 'NED'],
            ['id' => 2, 'entity_type' => 'country', 'display_name' => 'Duitsland', 'short_code' => 'GER'],
            ['id' => 3, 'entity_type' => 'country', 'display_name' => 'Spanje', 'short_code' => 'ESP'],
        ];
        $this->knockoutRoundRepository->rounds = [
            ['id' => 301, 'label' => 'Finale', 'round_order' => 1, 'team_slot_count' => 2],
        ];

        $this->service = new SubmitPredictionService(
            $this->pdo,
            new PredictionPayloadValidator($this->competitionDataProvider),
            new BonusAnswerValidator($this->competitionDataProvider),
            new KnockoutPredictionValidator($this->competitionDataProvider, $this->knockoutRoundRepository),
            $this->submissionRepository,
            $this->matchPredictionRepository,
            $this->bonusAnswerRepository,
            $this->knockoutRoundRepository,
        );
    }

    public function testRejectsIncompleteActiveSections(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Vul de score volledig in');

        $this->service->submit($this->user(), $this->openCompetition(), [
            'matches' => [
                101 => [
                    'predicted_home_score' => '',
                    'predicted_away_score' => '1',
                    'predicted_outcome' => 'home_win',
                    'predicted_yellow_cards_home' => '1',
                    'predicted_yellow_cards_away' => '2',
                    'predicted_red_cards_home' => '0',
                    'predicted_red_cards_away' => '0',
                ],
            ],
            'bonus_answers' => [201 => 1],
            'knockout_rounds' => [301 => [1 => 1, 2 => 3]],
        ]);
    }

    public function testRejectsPastDeadlineSubmission(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('niet open');

        $competition = new Competition(
            id: 10,
            name: 'EK 2026',
            slug: 'ek-2026',
            description: 'test',
            startDate: '2026-06-01',
            endDate: '2026-06-30',
            submissionDeadline: '2000-01-01 00:00:00',
            entryFeeAmount: 10.0,
            prizeFirstPercent: 60,
            prizeSecondPercent: 30,
            prizeThirdPercent: 10,
            status: CompetitionStatus::Open,
            isPublic: true,
            logoPath: null,
            createdByUserId: 1,
            createdAt: '2026-03-15 12:00:00',
            updatedAt: '2026-03-15 12:00:00',
        );

        $this->service->submit($this->user(), $competition, []);
    }

    public function testRejectsInvalidBonusEntityAnswer(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('bonusvraag');

        $this->service->submit($this->user(), $this->openCompetition(), [
            'matches' => [
                101 => [
                    'predicted_home_score' => '2',
                    'predicted_away_score' => '1',
                    'predicted_outcome' => 'home_win',
                    'predicted_yellow_cards_home' => '1',
                    'predicted_yellow_cards_away' => '2',
                    'predicted_red_cards_home' => '0',
                    'predicted_red_cards_away' => '0',
                ],
            ],
            'bonus_answers' => [201 => 999],
            'knockout_rounds' => [301 => [1 => 1, 2 => 3]],
        ]);
    }

    public function testRejectsSecondFinalSubmission(): void
    {
        $this->submissionRepository->submission = new PredictionSubmission(1, 10, 55, '2026-03-20 10:00:00', 'hash', true);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('al een definitieve voorspelling');

        $this->service->submit($this->user(), $this->openCompetition(), []);
    }

    public function testPersistsFinalSubmissionAtomically(): void
    {
        $result = $this->service->submit($this->user(), $this->openCompetition(), [
            'matches' => [
                101 => [
                    'predicted_home_score' => '2',
                    'predicted_away_score' => '1',
                    'predicted_outcome' => 'home_win',
                    'predicted_yellow_cards_home' => '1',
                    'predicted_yellow_cards_away' => '2',
                    'predicted_red_cards_home' => '0',
                    'predicted_red_cards_away' => '0',
                ],
            ],
            'bonus_answers' => [201 => 1],
            'knockout_rounds' => [301 => [1 => 1, 2 => 3]],
        ]);

        self::assertTrue($result->isLocked);
        self::assertSame(1, $result->id);
        self::assertCount(1, $this->matchPredictionRepository->storedPredictions);
        self::assertCount(1, $this->bonusAnswerRepository->storedAnswers);
        self::assertCount(2, $this->knockoutRoundRepository->storedPredictions);
    }

    private function user(): User
    {
        return new User(
            id: 55,
            firstName: 'Test',
            lastName: 'Gebruiker',
            email: 'test@example.com',
            phoneNumber: '0612345678',
            role: UserRole::Participant,
            isActive: true,
            lastLoginAt: null,
            createdAt: '2026-03-15 12:00:00',
            updatedAt: '2026-03-15 12:00:00',
        );
    }

    private function openCompetition(): Competition
    {
        return new Competition(
            id: 10,
            name: 'EK 2026',
            slug: 'ek-2026',
            description: 'test',
            startDate: '2026-06-01',
            endDate: '2026-06-30',
            submissionDeadline: '2099-01-01 00:00:00',
            entryFeeAmount: 10.0,
            prizeFirstPercent: 60,
            prizeSecondPercent: 30,
            prizeThirdPercent: 10,
            status: CompetitionStatus::Open,
            isPublic: true,
            logoPath: null,
            createdByUserId: 1,
            createdAt: '2026-03-15 12:00:00',
            updatedAt: '2026-03-15 12:00:00',
        );
    }
}

final class InMemoryCompetitionDataProvider implements CompetitionDataProviderInterface
{
    /** @var list<CompetitionSection> */
    public array $sections = [];

    /** @var list<array{id: int, home_entity_id: int, away_entity_id: int, home_label: string, away_label: string, stage: string, kickoff_at: string}> */
    public array $matches = [];

    /** @var list<array{id: int, prompt: string, question_type: string, entity_type_constraint: ?string, display_order: int}> */
    public array $bonusQuestions = [];

    /** @var list<array{id: int, entity_type: string, display_name: string, short_code: ?string}> */
    public array $entities = [];

    public function findActiveSections(int $competitionId): array
    {
        return $this->sections;
    }

    public function findMatchesForCompetition(int $competitionId): array
    {
        return $this->matches;
    }

    public function findActiveBonusQuestions(int $competitionId): array
    {
        return $this->bonusQuestions;
    }

    public function findActiveEntitiesForCompetition(int $competitionId, ?string $entityType = null): array
    {
        if ($entityType === null) {
            return $this->entities;
        }

        return array_values(array_filter(
            $this->entities,
            static fn (array $entity): bool => $entity['entity_type'] === $entityType,
        ));
    }
}

final class InMemoryPredictionSubmissionRepository implements PredictionSubmissionRepositoryInterface
{
    public ?PredictionSubmission $submission = null;

    public function findByCompetitionAndUser(int $competitionId, int $userId): ?PredictionSubmission
    {
        return $this->submission;
    }

    public function create(PredictionSubmission $submission): PredictionSubmission
    {
        $this->submission = new PredictionSubmission(
            id: 1,
            competitionId: $submission->competitionId,
            userId: $submission->userId,
            submittedAt: $submission->submittedAt,
            submissionHash: $submission->submissionHash,
            isLocked: true,
        );

        return $this->submission;
    }
}

final class InMemoryMatchPredictionRepository implements MatchPredictionRepositoryInterface
{
    /** @var list<MatchPrediction> */
    public array $storedPredictions = [];

    public function insertBatch(int $submissionId, array $predictions): void
    {
        $this->storedPredictions = $predictions;
    }

    public function findBySubmissionId(int $submissionId): array
    {
        return $this->storedPredictions;
    }
}

final class InMemoryBonusAnswerRepository implements BonusAnswerRepositoryInterface
{
    /** @var list<BonusAnswer> */
    public array $storedAnswers = [];

    public function insertBatch(int $submissionId, array $answers): void
    {
        $this->storedAnswers = $answers;
    }

    public function findBySubmissionId(int $submissionId): array
    {
        return $this->storedAnswers;
    }
}

final class InMemoryKnockoutRoundRepository implements KnockoutRoundRepositoryInterface
{
    /** @var list<array{id: int, label: string, round_order: int, team_slot_count: int}> */
    public array $rounds = [];

    /** @var list<KnockoutRoundPrediction> */
    public array $storedPredictions = [];

    public function findActiveRounds(int $competitionId): array
    {
        return $this->rounds;
    }

    public function insertPredictions(int $submissionId, array $predictions): void
    {
        $this->storedPredictions = $predictions;
    }

    public function findPredictionsBySubmissionId(int $submissionId): array
    {
        return $this->storedPredictions;
    }
}
