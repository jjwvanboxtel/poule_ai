<?php declare(strict_types=1);

use App\Application\Predictions\BonusAnswerRepositoryInterface;
use App\Application\Predictions\BonusAnswerValidator;
use App\Application\Predictions\CompetitionDataProviderInterface;
use App\Application\Predictions\KnockoutPredictionValidator;
use App\Application\Predictions\KnockoutRoundRepositoryInterface;
use App\Application\Predictions\MatchPredictionRepositoryInterface;
use App\Application\Predictions\PredictionPayloadValidator;
use App\Application\Predictions\PredictionSubmissionRepositoryInterface;
use App\Application\Predictions\SubmitPredictionService;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Participant\DashboardController;
use App\Http\Controllers\Participant\PredictionController;
use App\Http\ViewModels\PredictionFormViewModel;
use App\Infrastructure\Persistence\Pdo\ConnectionFactory;
use App\Infrastructure\Persistence\Pdo\PdoBonusAnswerRepository;
use App\Infrastructure\Persistence\Pdo\PdoCompetitionRepository;
use App\Infrastructure\Persistence\Pdo\PdoKnockoutRoundRepository;
use App\Infrastructure\Persistence\Pdo\PdoMatchPredictionRepository;
use App\Infrastructure\Persistence\Pdo\PdoPredictionSubmissionRepository;
use App\Infrastructure\Persistence\Pdo\PdoUserRepository;
use App\Infrastructure\Security\SessionAuthenticator;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\View\ViewRenderer;

$container = new Container();

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

return $container;
