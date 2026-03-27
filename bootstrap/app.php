<?php declare(strict_types=1);

use App\Http\Controllers\ErrorController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Participant\DashboardController;
use App\Http\Controllers\Participant\PredictionController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\BonusQuestionController;
use App\Http\Controllers\Admin\CompetitionController;
use App\Http\Controllers\Admin\CompetitionEnrollmentController;
use App\Http\Controllers\Admin\CompetitionParticipantController;
use App\Http\Controllers\Admin\EntityImportController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\MatchManagementController;
use App\Http\Controllers\Admin\MatchResultController;
use App\Http\Controllers\Admin\StandingsRecalculationController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Middleware\EnforceCompetitionDeadline;
use App\Http\Middleware\RequireAdmin;
use App\Http\Middleware\RequireAuth;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Requests\Request;
use App\Infrastructure\Security\SessionAuthenticator;
use App\Support\Routing\Router;
use App\Support\Sessions\SessionManager;

// Load .env file if present
(static function (): void {
    $envFile = BASE_PATH . '/.env';
    if (!file_exists($envFile)) {
        return;
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (!str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");

        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
})();

// Load configuration
$config = [
    'app' => require BASE_PATH . '/config/app.php',
    'database' => require BASE_PATH . '/config/database.php',
    'security' => require BASE_PATH . '/config/security.php',
    'scoring' => require BASE_PATH . '/config/scoring.php',
];

// Build dependency container
$container = require BASE_PATH . '/bootstrap/dependencies.php';
$container->set('config', $config);

// Start session
/** @var SessionManager $session */
$session = $container->get(SessionManager::class);
$session->start();

// Build request from superglobals
$request = Request::fromGlobals();

// Build router and register routes
$router = new Router($container);

$withMiddleware = static function (array $middlewareClasses, array $handler) use ($container): callable {
    return static function (Request $request) use ($middlewareClasses, $handler, $container): void {
        $controllerInvoker = static function (Request $handledRequest) use ($handler, $container): void {
            [$controllerClass, $method] = $handler;
            $controller = $container->has($controllerClass)
                ? $container->get($controllerClass)
                : new $controllerClass($container);

            $controller->$method($handledRequest);
        };

        $pipeline = array_reduce(
            array_reverse($middlewareClasses),
            static function (callable $next, string $middlewareClass) use ($container): callable {
                return static function (Request $pipelineRequest) use ($next, $middlewareClass, $container): void {
                    $middleware = new $middlewareClass($container);
                    $middleware->handle($pipelineRequest, $next);
                };
            },
            $controllerInvoker,
        );

        $pipeline($request);
    };
};

$router->get('/', static function (Request $request) use ($container): void {
    /** @var SessionAuthenticator $auth */
    $auth = $container->get(SessionAuthenticator::class);

    http_response_code(302);
    header('Location: ' . ($auth->check() ? '/dashboard' : '/login'));
    exit;
});

$router->get('/register', $withMiddleware([VerifyCsrfToken::class], [RegisterController::class, 'create']));
$router->post('/register', $withMiddleware([VerifyCsrfToken::class], [RegisterController::class, 'store']));
$router->get('/login', $withMiddleware([VerifyCsrfToken::class], [LoginController::class, 'create']));
$router->post('/login', $withMiddleware([VerifyCsrfToken::class], [LoginController::class, 'store']));
$router->post('/logout', $withMiddleware([VerifyCsrfToken::class, RequireAuth::class], [LoginController::class, 'destroy']));

$router->get('/dashboard', $withMiddleware([VerifyCsrfToken::class, RequireAuth::class], [DashboardController::class, 'index']));
$router->get(
    '/competitions/{slug}/prediction',
    $withMiddleware([VerifyCsrfToken::class, RequireAuth::class], [PredictionController::class, 'show']),
);
$router->post(
    '/competitions/{slug}/prediction/submit',
    $withMiddleware([VerifyCsrfToken::class, RequireAuth::class, EnforceCompetitionDeadline::class], [PredictionController::class, 'submit']),
);

// Admin routes
$adminMiddleware = [VerifyCsrfToken::class, RequireAuth::class, RequireAdmin::class];

$router->get('/admin', $withMiddleware($adminMiddleware, [AdminDashboardController::class, 'index']));
$router->get('/admin/competitions', $withMiddleware($adminMiddleware, [CompetitionController::class, 'index']));
$router->get('/admin/competitions/create', $withMiddleware($adminMiddleware, [CompetitionController::class, 'create']));
$router->post('/admin/competitions', $withMiddleware($adminMiddleware, [CompetitionController::class, 'store']));
$router->get('/admin/competitions/{id}/edit', $withMiddleware($adminMiddleware, [CompetitionController::class, 'edit']));
$router->post('/admin/competitions/{id}/edit', $withMiddleware($adminMiddleware, [CompetitionController::class, 'update']));
$router->get('/admin/competitions/{id}/sections', $withMiddleware($adminMiddleware, [CompetitionController::class, 'sections']));
$router->post('/admin/competitions/{id}/sections', $withMiddleware($adminMiddleware, [CompetitionController::class, 'updateSections']));
$router->get('/admin/competitions/{id}/rules/{sectionId}', $withMiddleware($adminMiddleware, [CompetitionController::class, 'rules']));
$router->post('/admin/competitions/{id}/rules/{sectionId}', $withMiddleware($adminMiddleware, [CompetitionController::class, 'updateRules']));
$router->get('/admin/competitions/{id}/knockout-rounds', $withMiddleware($adminMiddleware, [CompetitionController::class, 'knockoutRounds']));
$router->post('/admin/competitions/{id}/knockout-rounds', $withMiddleware($adminMiddleware, [CompetitionController::class, 'updateKnockoutRounds']));
$router->get('/admin/competitions/{id}/participants', $withMiddleware($adminMiddleware, [CompetitionParticipantController::class, 'index']));
$router->post('/admin/competitions/{id}/participants/{userId}/payment', $withMiddleware($adminMiddleware, [CompetitionParticipantController::class, 'updatePayment']));
$router->get('/admin/competitions/{id}/enroll', $withMiddleware($adminMiddleware, [CompetitionEnrollmentController::class, 'create']));
$router->post('/admin/competitions/{id}/enroll', $withMiddleware($adminMiddleware, [CompetitionEnrollmentController::class, 'store']));
$router->get('/admin/competitions/{id}/bonus-questions', $withMiddleware($adminMiddleware, [BonusQuestionController::class, 'index']));
$router->post('/admin/competitions/{id}/bonus-questions', $withMiddleware($adminMiddleware, [BonusQuestionController::class, 'store']));
$router->post('/admin/competitions/{id}/bonus-questions/{questionId}/delete', $withMiddleware($adminMiddleware, [BonusQuestionController::class, 'destroy']));
$router->get('/admin/competitions/{id}/matches', $withMiddleware($adminMiddleware, [MatchManagementController::class, 'index']));
$router->post('/admin/competitions/{id}/matches', $withMiddleware($adminMiddleware, [MatchManagementController::class, 'create']));
$router->get('/admin/competitions/{id}/matches/{matchId}/edit', $withMiddleware($adminMiddleware, [MatchManagementController::class, 'edit']));
$router->post('/admin/competitions/{id}/matches/{matchId}/edit', $withMiddleware($adminMiddleware, [MatchManagementController::class, 'update']));
$router->post('/admin/competitions/{id}/matches/{matchId}/delete', $withMiddleware($adminMiddleware, [MatchManagementController::class, 'destroy']));
$router->get('/admin/competitions/{id}/results/{matchId}/edit', $withMiddleware($adminMiddleware, [MatchResultController::class, 'edit']));
$router->post('/admin/competitions/{id}/results/{matchId}/edit', $withMiddleware($adminMiddleware, [MatchResultController::class, 'update']));
$router->post('/admin/competitions/{id}/recalculate', $withMiddleware($adminMiddleware, [StandingsRecalculationController::class, 'recalculate']));
$router->get('/admin/competitions/{id}/import/entities', $withMiddleware($adminMiddleware, [EntityImportController::class, 'create']));
$router->post('/admin/competitions/{id}/import/entities', $withMiddleware($adminMiddleware, [EntityImportController::class, 'store']));
$router->get('/admin/users', $withMiddleware($adminMiddleware, [UserManagementController::class, 'index']));
$router->get('/admin/users/{id}/edit', $withMiddleware($adminMiddleware, [UserManagementController::class, 'edit']));
$router->post('/admin/users/{id}/edit', $withMiddleware($adminMiddleware, [UserManagementController::class, 'update']));
$router->get('/admin/maintenance', $withMiddleware($adminMiddleware, [MaintenanceController::class, 'index']));

// Dispatch
try {
    $router->dispatch($request);
} catch (\Throwable $e) {
    $errorController = new ErrorController($container);
    $errorController->serverError($request, $e);
}
