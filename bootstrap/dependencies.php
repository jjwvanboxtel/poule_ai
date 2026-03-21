<?php declare(strict_types=1);

use App\Application\Auth\AuditLogRepositoryInterface;
use App\Application\Auth\UpdateUserRoleService;
use App\Application\Auth\UpdateUserStatusService;
use App\Application\Auth\UserRepositoryInterface;
use App\Application\Competitions\CompetitionRepositoryInterface;
use App\Application\Competitions\CreateCompetitionService;
use App\Application\Competitions\EnrollParticipantService;
use App\Application\Competitions\ParticipantRepositoryInterface;
use App\Application\Competitions\UpdateBonusQuestionsService;
use App\Application\Competitions\UpdateCompetitionRulesService;
use App\Application\Competitions\UpdateCompetitionSectionsService;
use App\Application\Competitions\UpdateCompetitionService;
use App\Application\Competitions\UpdateKnockoutRoundsService;
use App\Application\Competitions\UpdateParticipantPaymentStatusService;
use App\Application\Competitions\UserReadRepositoryInterface;
use App\Application\Imports\Csv\EntityCsvParser;
use App\Application\Imports\Csv\EntityCsvValidator;
use App\Application\Imports\EntityCsvImportService;
use App\Application\Predictions\BonusAnswerRepositoryInterface;
use App\Application\Predictions\BonusAnswerValidator;
use App\Application\Predictions\CompetitionDataProviderInterface;
use App\Application\Predictions\KnockoutPredictionValidator;
use App\Application\Predictions\KnockoutRoundRepositoryInterface;
use App\Application\Predictions\MatchPredictionRepositoryInterface;
use App\Application\Predictions\PredictionPayloadValidator;
use App\Application\Predictions\PredictionSubmissionRepositoryInterface;
use App\Application\Predictions\SubmitPredictionService;
use App\Http\Controllers\Admin\BonusQuestionController;
use App\Http\Controllers\Admin\CompetitionController;
use App\Http\Controllers\Admin\CompetitionEnrollmentController;
use App\Http\Controllers\Admin\CompetitionParticipantController;
use App\Http\Controllers\Admin\EntityImportController;
use App\Http\Controllers\Admin\KnockoutRoundController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\MatchManagementController;
use App\Http\Controllers\Admin\MatchResultController;
use App\Http\Controllers\Admin\StandingsRecalculationController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Participant\DashboardController;
use App\Http\Controllers\Participant\PredictionController;
use App\Http\ViewModels\PredictionFormViewModel;
use App\Infrastructure\Persistence\Pdo\ConnectionFactory;
use App\Infrastructure\Persistence\Pdo\PdoAdminAuditLogRepository;
use App\Infrastructure\Persistence\Pdo\PdoBonusAnswerRepository;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionParticipantRepository;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRuleRepository;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionSectionRepository;
use App\Infrastructure\Persistence\Pdo\PdoKnockoutRoundRepository;
use App\Infrastructure\Persistence\Pdo\PdoMatchPredictionRepository;
use App\Infrastructure\Persistence\Pdo\PdoPredictionSubmissionRepository;
use App\Infrastructure\Persistence\Pdo\PdoUserRepository;
use App\Infrastructure\Security\SessionAuthenticator;
use App\Infrastructure\Storage\LogoStorage;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\View\ViewRenderer;
$container = new Container();

// Base path (needed by some services)
$container->set('base_path', BASE_PATH);

// Session manager (singleton)
$container->singleton(SessionManager::class, static function (Container $c): SessionManager {
    /** @var array{security: array{session_name: string, session_lifetime: int}} $config */
    $config = $c->get('config');

    return new SessionManager(
        $config['security']['session_name'],
        $config['security']['session_lifetime'],
    );
});

// PDO connection (singleton per request)
$container->singleton(\PDO::class, static function (Container $c): \PDO {
    /** @var array{database: array<string, mixed>} $config */
    $config = $c->get('config');

    return ConnectionFactory::fromConfig($config['database']);
});

// View renderer (singleton)
$container->singleton(ViewRenderer::class, static function (Container $c): ViewRenderer {
    /** @var array{app: array{debug: bool}} $config */
    $config = $c->get('config');

    return new ViewRenderer(
        BASE_PATH . '/resources/views',
        $c->get(SessionManager::class),
        $config['app']['debug'] ?? false,
    );
});

// Session authenticator
$container->singleton(SessionAuthenticator::class, static function (Container $c): SessionAuthenticator {
    return new SessionAuthenticator(
        $c->get(SessionManager::class),
        $c->get(\PDO::class),
    );
});

$container->singleton(PdoUserRepository::class, static function (Container $c): PdoUserRepository {
    return new PdoUserRepository($c->get(\PDO::class));
});

$container->singleton(PdoCompetitionRepository::class, static function (Container $c): PdoCompetitionRepository {
    return new PdoCompetitionRepository($c->get(\PDO::class));
});

$container->singleton(PdoPredictionSubmissionRepository::class, static function (Container $c): PdoPredictionSubmissionRepository {
    return new PdoPredictionSubmissionRepository($c->get(\PDO::class));
});

$container->singleton(PdoMatchPredictionRepository::class, static function (Container $c): PdoMatchPredictionRepository {
    return new PdoMatchPredictionRepository($c->get(\PDO::class));
});

$container->singleton(PdoBonusAnswerRepository::class, static function (Container $c): PdoBonusAnswerRepository {
    return new PdoBonusAnswerRepository($c->get(\PDO::class));
});

$container->singleton(PdoKnockoutRoundRepository::class, static function (Container $c): PdoKnockoutRoundRepository {
    return new PdoKnockoutRoundRepository($c->get(\PDO::class));
});

$container->singleton(CompetitionDataProviderInterface::class, static function (Container $c): CompetitionDataProviderInterface {
    return $c->get(PdoCompetitionRepository::class);
});

$container->singleton(CompetitionRepositoryInterface::class, static function (Container $c): CompetitionRepositoryInterface {
    return $c->get(PdoCompetitionRepository::class);
});

$container->singleton(ParticipantRepositoryInterface::class, static function (Container $c): ParticipantRepositoryInterface {
    return $c->get(PdoCompetitionParticipantRepository::class);
});

$container->singleton(UserRepositoryInterface::class, static function (Container $c): UserRepositoryInterface {
    return $c->get(PdoUserRepository::class);
});

$container->singleton(UserReadRepositoryInterface::class, static function (Container $c): UserReadRepositoryInterface {
    return $c->get(PdoUserRepository::class);
});

$container->singleton(PredictionSubmissionRepositoryInterface::class, static function (Container $c): PredictionSubmissionRepositoryInterface {
    return $c->get(PdoPredictionSubmissionRepository::class);
});

$container->singleton(MatchPredictionRepositoryInterface::class, static function (Container $c): MatchPredictionRepositoryInterface {
    return $c->get(PdoMatchPredictionRepository::class);
});

$container->singleton(BonusAnswerRepositoryInterface::class, static function (Container $c): BonusAnswerRepositoryInterface {
    return $c->get(PdoBonusAnswerRepository::class);
});

$container->singleton(KnockoutRoundRepositoryInterface::class, static function (Container $c): KnockoutRoundRepositoryInterface {
    return $c->get(PdoKnockoutRoundRepository::class);
});

$container->singleton(PredictionPayloadValidator::class, static function (Container $c): PredictionPayloadValidator {
    return new PredictionPayloadValidator($c->get(CompetitionDataProviderInterface::class));
});

$container->singleton(BonusAnswerValidator::class, static function (Container $c): BonusAnswerValidator {
    return new BonusAnswerValidator($c->get(CompetitionDataProviderInterface::class));
});

$container->singleton(KnockoutPredictionValidator::class, static function (Container $c): KnockoutPredictionValidator {
    return new KnockoutPredictionValidator(
        $c->get(CompetitionDataProviderInterface::class),
        $c->get(KnockoutRoundRepositoryInterface::class),
    );
});

$container->singleton(SubmitPredictionService::class, static function (Container $c): SubmitPredictionService {
    return new SubmitPredictionService(
        $c->get(\PDO::class),
        $c->get(PredictionPayloadValidator::class),
        $c->get(BonusAnswerValidator::class),
        $c->get(KnockoutPredictionValidator::class),
        $c->get(PredictionSubmissionRepositoryInterface::class),
        $c->get(MatchPredictionRepositoryInterface::class),
        $c->get(BonusAnswerRepositoryInterface::class),
        $c->get(KnockoutRoundRepositoryInterface::class),
    );
});

$container->bind(PredictionFormViewModel::class, static function (Container $c): PredictionFormViewModel {
    return new PredictionFormViewModel(
        $c->get(CompetitionDataProviderInterface::class),
        $c->get(KnockoutRoundRepositoryInterface::class),
    );
});

$container->bind(RegisterController::class, static function (Container $c): RegisterController {
    return new RegisterController($c);
});

$container->bind(LoginController::class, static function (Container $c): LoginController {
    return new LoginController($c);
});

$container->bind(DashboardController::class, static function (Container $c): DashboardController {
    return new DashboardController($c);
});

$container->bind(PredictionController::class, static function (Container $c): PredictionController {
    return new PredictionController($c);
});

// ── Admin infrastructure ──────────────────────────────────────────────────────

$container->singleton(PdoCompetitionSectionRepository::class, static function (Container $c): PdoCompetitionSectionRepository {
    return new PdoCompetitionSectionRepository($c->get(\PDO::class));
});

$container->singleton(PdoCompetitionRuleRepository::class, static function (Container $c): PdoCompetitionRuleRepository {
    return new PdoCompetitionRuleRepository($c->get(\PDO::class));
});

$container->singleton(PdoCompetitionParticipantRepository::class, static function (Container $c): PdoCompetitionParticipantRepository {
    return new PdoCompetitionParticipantRepository($c->get(\PDO::class));
});

$container->singleton(PdoAdminAuditLogRepository::class, static function (Container $c): PdoAdminAuditLogRepository {
    return new PdoAdminAuditLogRepository($c->get(\PDO::class));
});

$container->singleton(AuditLogRepositoryInterface::class, static function (Container $c): AuditLogRepositoryInterface {
    return $c->get(PdoAdminAuditLogRepository::class);
});

$container->singleton(LogoStorage::class, static function (Container $c): LogoStorage {
    return new LogoStorage(BASE_PATH);
});

// ── Admin application services ────────────────────────────────────────────────

$container->singleton(CreateCompetitionService::class, static function (Container $c): CreateCompetitionService {
    return new CreateCompetitionService($c->get(CompetitionRepositoryInterface::class));
});

$container->singleton(UpdateCompetitionService::class, static function (Container $c): UpdateCompetitionService {
    return new UpdateCompetitionService($c->get(CompetitionRepositoryInterface::class));
});

$container->singleton(UpdateCompetitionSectionsService::class, static function (Container $c): UpdateCompetitionSectionsService {
    return new UpdateCompetitionSectionsService($c->get(PdoCompetitionSectionRepository::class));
});

$container->singleton(UpdateCompetitionRulesService::class, static function (Container $c): UpdateCompetitionRulesService {
    return new UpdateCompetitionRulesService(
        $c->get(PdoCompetitionRuleRepository::class),
        $c->get(PdoCompetitionSectionRepository::class),
    );
});

$container->singleton(UpdateParticipantPaymentStatusService::class, static function (Container $c): UpdateParticipantPaymentStatusService {
    return new UpdateParticipantPaymentStatusService($c->get(ParticipantRepositoryInterface::class));
});

$container->singleton(EnrollParticipantService::class, static function (Container $c): EnrollParticipantService {
    return new EnrollParticipantService(
        $c->get(CompetitionRepositoryInterface::class),
        $c->get(ParticipantRepositoryInterface::class),
        $c->get(UserReadRepositoryInterface::class),
    );
});

$container->singleton(UpdateBonusQuestionsService::class, static function (Container $c): UpdateBonusQuestionsService {
    return new UpdateBonusQuestionsService(
        $c->get(\PDO::class),
        $c->get(CompetitionRepositoryInterface::class),
    );
});

$container->singleton(UpdateKnockoutRoundsService::class, static function (Container $c): UpdateKnockoutRoundsService {
    return new UpdateKnockoutRoundsService(
        $c->get(\PDO::class),
        $c->get(CompetitionRepositoryInterface::class),
    );
});

$container->singleton(UpdateUserRoleService::class, static function (Container $c): UpdateUserRoleService {
    return new UpdateUserRoleService(
        $c->get(UserRepositoryInterface::class),
        $c->get(AuditLogRepositoryInterface::class),
    );
});

$container->singleton(UpdateUserStatusService::class, static function (Container $c): UpdateUserStatusService {
    return new UpdateUserStatusService(
        $c->get(UserRepositoryInterface::class),
        $c->get(AuditLogRepositoryInterface::class),
    );
});

$container->singleton(EntityCsvParser::class, static function (Container $c): EntityCsvParser {
    return new EntityCsvParser();
});

$container->singleton(EntityCsvValidator::class, static function (Container $c): EntityCsvValidator {
    return new EntityCsvValidator();
});

$container->singleton(EntityCsvImportService::class, static function (Container $c): EntityCsvImportService {
    return new EntityCsvImportService(
        $c->get(\PDO::class),
        $c->get(EntityCsvParser::class),
        $c->get(EntityCsvValidator::class),
    );
});

// ── Admin controllers ─────────────────────────────────────────────────────────

$container->bind(CompetitionController::class, static function (Container $c): CompetitionController {
    return new CompetitionController($c);
});

$container->bind(BonusQuestionController::class, static function (Container $c): BonusQuestionController {
    return new BonusQuestionController($c);
});

$container->bind(CompetitionEnrollmentController::class, static function (Container $c): CompetitionEnrollmentController {
    return new CompetitionEnrollmentController($c);
});

$container->bind(CompetitionParticipantController::class, static function (Container $c): CompetitionParticipantController {
    return new CompetitionParticipantController($c);
});

$container->bind(KnockoutRoundController::class, static function (Container $c): KnockoutRoundController {
    return new KnockoutRoundController($c);
});

$container->bind(MatchManagementController::class, static function (Container $c): MatchManagementController {
    return new MatchManagementController($c);
});

$container->bind(MatchResultController::class, static function (Container $c): MatchResultController {
    return new MatchResultController($c);
});

$container->bind(StandingsRecalculationController::class, static function (Container $c): StandingsRecalculationController {
    return new StandingsRecalculationController($c);
});

$container->bind(UserManagementController::class, static function (Container $c): UserManagementController {
    return new UserManagementController($c);
});

$container->bind(EntityImportController::class, static function (Container $c): EntityImportController {
    return new EntityImportController($c);
});

$container->bind(MaintenanceController::class, static function (Container $c): MaintenanceController {
    return new MaintenanceController($c);
});

return $container;
