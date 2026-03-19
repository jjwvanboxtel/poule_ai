<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoAdminAuditLogRepository;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\View\ViewRenderer;

final class MaintenanceController
{
    private readonly ViewRenderer $renderer;
    private readonly SessionManager $session;
    private readonly PdoAdminAuditLogRepository $auditLog;

    public function __construct(Container $container)
    {
        $renderer = $container->get(ViewRenderer::class);
        $session = $container->get(SessionManager::class);
        $auditLog = $container->get(PdoAdminAuditLogRepository::class);

        if (!$renderer instanceof ViewRenderer) throw new \RuntimeException('ViewRenderer invalid');
        if (!$session instanceof SessionManager) throw new \RuntimeException('Session invalid');
        if (!$auditLog instanceof PdoAdminAuditLogRepository) throw new \RuntimeException('AuditLog invalid');

        $this->renderer = $renderer;
        $this->session = $session;
        $this->auditLog = $auditLog;
    }

    public function index(Request $request): void
    {
        $logs = $this->auditLog->findRecent(100);

        echo $this->renderer->render('admin/maintenance/index', [
            'title' => 'Onderhoud',
            'logs' => $logs,
        ]);
    }
}
