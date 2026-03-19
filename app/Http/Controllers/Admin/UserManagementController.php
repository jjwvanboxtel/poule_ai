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

final class UserManagementController
{
    private readonly ViewRenderer $renderer;
    private readonly SessionAuthenticator $auth;
    private readonly SessionManager $session;
    private readonly PdoUserRepository $users;
    private readonly UpdateUserRoleService $roleService;
    private readonly UpdateUserStatusService $statusService;

    public function __construct(Container $container)
    {
        $renderer = $container->get(ViewRenderer::class);
        $auth = $container->get(SessionAuthenticator::class);
        $session = $container->get(SessionManager::class);
        $users = $container->get(PdoUserRepository::class);
        $roleService = $container->get(UpdateUserRoleService::class);
        $statusService = $container->get(UpdateUserStatusService::class);

        if (!$renderer instanceof ViewRenderer) throw new \RuntimeException('ViewRenderer invalid');
        if (!$auth instanceof SessionAuthenticator) throw new \RuntimeException('Auth invalid');
        if (!$session instanceof SessionManager) throw new \RuntimeException('Session invalid');
        if (!$users instanceof PdoUserRepository) throw new \RuntimeException('Users invalid');
        if (!$roleService instanceof UpdateUserRoleService) throw new \RuntimeException('RoleService invalid');
        if (!$statusService instanceof UpdateUserStatusService) throw new \RuntimeException('StatusService invalid');

        $this->renderer = $renderer;
        $this->auth = $auth;
        $this->session = $session;
        $this->users = $users;
        $this->roleService = $roleService;
        $this->statusService = $statusService;
    }

    public function index(Request $request): void
    {
        echo $this->renderer->render('admin/users/index', [
            'title' => 'Gebruikersbeheer',
            'users' => $this->users->findAll(),
        ]);
    }

    public function edit(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $user = $this->users->findById($id);
        if ($user === null) {
            http_response_code(302);
            header('Location: /admin/users');
            exit;
        }

        echo $this->renderer->render('admin/users/edit', [
            'title' => 'Gebruiker bewerken',
            'user' => $user,
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int) $request->routeParam('id');
        $post = $request->allPost();
        $actingAdmin = $this->auth->user();

        if ($actingAdmin === null) {
            http_response_code(302);
            header('Location: /login');
            exit;
        }

        $newRole = is_string($post['role'] ?? null) ? $post['role'] : '';
        $isActive = !empty($post['is_active']);

        try {
            if ($newRole !== '') {
                $this->roleService->updateRole($id, $newRole, $actingAdmin->id);
            }
            $this->statusService->updateStatus($id, $isActive, $actingAdmin->id);
            $this->session->flash('success', 'Gebruiker bijgewerkt.');
        } catch (\DomainException $e) {
            $this->session->flash('error', $e->getMessage());
        }

        http_response_code(302);
        header('Location: /admin/users/' . $id . '/edit');
        exit;
    }
}
