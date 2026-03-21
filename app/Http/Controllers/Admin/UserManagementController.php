<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Auth\UpdateUserRoleService;
use App\Application\Auth\UpdateUserStatusService;
use App\Http\Requests\Request;
use App\Infrastructure\Persistence\Pdo\PdoUserRepository;
use App\Infrastructure\Security\SessionAuthenticator;
use App\Support\Container;
use App\Support\Sessions\SessionManager;
use App\Support\View\ViewRenderer;
use DomainException;

final class UserManagementController
{
    private readonly ViewRenderer $renderer;
    private readonly SessionManager $session;
    private readonly SessionAuthenticator $auth;
    private readonly PdoUserRepository $users;
    private readonly UpdateUserRoleService $roleService;
    private readonly UpdateUserStatusService $statusService;

    public function __construct(Container $container)
    {
        $renderer = $container->get(ViewRenderer::class);
        $session = $container->get(SessionManager::class);
        $auth = $container->get(SessionAuthenticator::class);
        $users = $container->get(PdoUserRepository::class);
        $roleService = $container->get(UpdateUserRoleService::class);
        $statusService = $container->get(UpdateUserStatusService::class);

        if (
            !$renderer instanceof ViewRenderer
            || !$session instanceof SessionManager
            || !$auth instanceof SessionAuthenticator
            || !$users instanceof PdoUserRepository
            || !$roleService instanceof UpdateUserRoleService
            || !$statusService instanceof UpdateUserStatusService
        ) {
            throw new \RuntimeException('UserManagementController dependencies are invalid.');
        }

        $this->renderer = $renderer;
        $this->session = $session;
        $this->auth = $auth;
        $this->users = $users;
        $this->roleService = $roleService;
        $this->statusService = $statusService;
    }

    public function index(Request $request): void
    {
        $allUsers = $this->users->findAll();

        echo $this->renderer->render('admin/users/index', [
            'title' => 'Gebruikers beheren',
            'users' => $allUsers,
        ]);
    }

    public function updateRole(Request $request): void
    {
        $actingAdmin = $this->auth->user();
        if ($actingAdmin === null) {
            $this->redirect('/login');
        }
        /** @var \App\Domain\User\User $actingAdmin */
        $targetId = (int) $request->routeParam('id');
        $newRole = $this->stringValue($request->post('role', ''));

        try {
            $this->roleService->update($targetId, $newRole, $actingAdmin->id, $request->getClientIp());
            $this->session->flash('success', 'Gebruikersrol bijgewerkt.');
        } catch (DomainException $e) {
            $this->session->flash('error', $e->getMessage());
        }

        $this->redirect('/admin/users');
    }

    public function updateStatus(Request $request): void
    {
        $actingAdmin = $this->auth->user();
        if ($actingAdmin === null) {
            $this->redirect('/login');
        }
        /** @var \App\Domain\User\User $actingAdmin */
        $targetId = (int) $request->routeParam('id');
        $isActive = $this->stringValue($request->post('is_active', '0')) === '1';

        try {
            $this->statusService->update($targetId, $isActive, $actingAdmin->id, $request->getClientIp());
            $this->session->flash('success', 'Gebruikersstatus bijgewerkt.');
        } catch (DomainException $e) {
            $this->session->flash('error', $e->getMessage());
        }

        $this->redirect('/admin/users');
    }

    private function stringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    private function redirect(string $location): never
    {
        http_response_code(302);
        header('Location: ' . $location);
        exit;
    }
}
