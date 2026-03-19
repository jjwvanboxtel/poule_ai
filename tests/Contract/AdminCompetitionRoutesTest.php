<?php declare(strict_types=1);

namespace Tests\Contract;

use Tests\Support\HttpTestCase;

final class AdminCompetitionRoutesTest extends HttpTestCase
{
    protected function loginAsSeededAdmin(): void
    {
        $loginPage = $this->request('GET', '/login');
        $csrfToken = $this->extractCsrfToken($loginPage['body']);

        $response = $this->request('POST', '/login', [
            '_token' => $csrfToken,
            'email' => 'admin@example.com',
            'password' => 'secret',
            'intended' => '',
        ]);

        self::assertSame(302, $response['status']);
    }

    public function testAdminRouteRedirectsToLoginWhenUnauthenticated(): void
    {
        $response = $this->request('GET', '/admin');
        self::assertSame(302, $response['status']);
        self::assertStringContainsString('/login', $response['headers']['location'] ?? '');
    }

    public function testAdminCompetitionsRedirectsToLoginWhenUnauthenticated(): void
    {
        $response = $this->request('GET', '/admin/competitions');
        self::assertSame(302, $response['status']);
    }

    public function testAdminRouteReturnsForbiddenForParticipant(): void
    {
        $this->loginAsSeededParticipant();
        $response = $this->request('GET', '/admin');
        self::assertSame(403, $response['status']);
    }

    public function testAdminCompetitionsForbiddenForParticipant(): void
    {
        $this->loginAsSeededParticipant();
        $response = $this->request('GET', '/admin/competitions');
        self::assertSame(403, $response['status']);
    }

    public function testAdminDashboardOkForAdmin(): void
    {
        $this->loginAsSeededAdmin();
        $response = $this->request('GET', '/admin');
        self::assertSame(200, $response['status']);
        self::assertStringContainsString('Admin Dashboard', $response['body']);
    }

    public function testAdminCompetitionsListOkForAdmin(): void
    {
        $this->loginAsSeededAdmin();
        $response = $this->request('GET', '/admin/competitions');
        self::assertSame(200, $response['status']);
        self::assertStringContainsString('Competities', $response['body']);
    }

    public function testAdminCompetitionCreateFormOkForAdmin(): void
    {
        $this->loginAsSeededAdmin();
        $response = $this->request('GET', '/admin/competitions/create');
        self::assertSame(200, $response['status']);
        self::assertStringContainsString('Nieuwe competitie', $response['body']);
    }

    public function testAdminUsersListOkForAdmin(): void
    {
        $this->loginAsSeededAdmin();
        $response = $this->request('GET', '/admin/users');
        self::assertSame(200, $response['status']);
        self::assertStringContainsString('Gebruikersbeheer', $response['body']);
    }

    public function testAdminMaintenanceOkForAdmin(): void
    {
        $this->loginAsSeededAdmin();
        $response = $this->request('GET', '/admin/maintenance');
        self::assertSame(200, $response['status']);
        self::assertStringContainsString('Onderhoud', $response['body']);
    }
}
