<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Request;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\View\ViewRenderer;
use PDO;

final class MaintenanceController
{
    private readonly ViewRenderer $renderer;
    private readonly SessionManager $session;
    private readonly PDO $pdo;
    private readonly string $basePath;

    public function __construct(Container $container)
    {
        $renderer = $container->get(ViewRenderer::class);
        $session = $container->get(SessionManager::class);
        $pdo = $container->get(\PDO::class);
        $basePath = $container->get('base_path');

        if (!$renderer instanceof ViewRenderer || !$session instanceof SessionManager || !$pdo instanceof \PDO) {
            throw new \RuntimeException('MaintenanceController dependencies are invalid.');
        }

        $this->renderer = $renderer;
        $this->session = $session;
        $this->pdo = $pdo;
        $this->basePath = is_string($basePath) ? $basePath : '';
    }

    public function index(Request $request): void
    {
        echo $this->renderer->render('admin/maintenance/index', [
            'title' => 'Onderhoud',
        ]);
    }

    public function runMigrations(Request $request): void
    {
        $migrationFiles = glob($this->basePath . '/database/migrations/*.php') ?: [];
        sort($migrationFiles);

        $ran = 0;
        foreach ($migrationFiles as $file) {
            $migration = require $file;
            if (is_object($migration) && method_exists($migration, 'up')) {
                $migration->up($this->pdo);
                $ran++;
            }
        }

        $this->session->flash('success', "Migrations uitgevoerd: {$ran} bestanden verwerkt.");
        $this->redirect('/admin/maintenance');
    }

    public function clearCache(Request $request): void
    {
        // No cache layer in this application; placeholder for future use
        $this->session->flash('success', 'Cache leeg gemaakt (geen cache aanwezig).');
        $this->redirect('/admin/maintenance');
    }

    private function redirect(string $location): never
    {
        http_response_code(302);
        header('Location: ' . $location);
        exit;
    }
}
