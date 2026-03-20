<?php declare(strict_types=1);

namespace Tests\Contract;

use Tests\Support\HttpTestCase;

final class AdminMatchManagementRoutesTest extends HttpTestCase
{
    private function loginAsSeededAdmin(): void
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

    public function testMatchesListRedirectsToLoginWhenUnauthenticated(): void
    {
        $competitionId = $this->competitionIdBySlug('ek-2026');
        $response = $this->request('GET', "/admin/competitions/{$competitionId}/matches");
        self::assertSame(302, $response['status']);
        self::assertStringContainsString('/login', $response['headers']['location'] ?? '');
    }

    public function testMatchesListReturnsForbiddenForParticipant(): void
    {
        $this->loginAsSeededParticipant();
        $competitionId = $this->competitionIdBySlug('ek-2026');
        $response = $this->request('GET', "/admin/competitions/{$competitionId}/matches");
        self::assertSame(403, $response['status']);
    }

    public function testMatchesListOkForAdmin(): void
    {
        $this->loginAsSeededAdmin();
        $competitionId = $this->competitionIdBySlug('ek-2026');
        $response = $this->request('GET', "/admin/competitions/{$competitionId}/matches");
        self::assertSame(200, $response['status']);
        self::assertStringContainsString('Wedstrijden', $response['body']);
    }

    public function testMatchEditPageOkForAdmin(): void
    {
        $this->loginAsSeededAdmin();
        $competitionId = $this->competitionIdBySlug('ek-2026');
        $matchIds = $this->matchIdsForCompetition($competitionId);
        self::assertNotEmpty($matchIds, 'Seeded competition must have at least one match');

        $response = $this->request('GET', "/admin/competitions/{$competitionId}/matches/{$matchIds[0]}/edit");
        self::assertSame(200, $response['status']);
        self::assertStringContainsString('Wedstrijd', $response['body']);
    }

    public function testMatchResultEditPageOkForAdmin(): void
    {
        $this->loginAsSeededAdmin();
        $competitionId = $this->competitionIdBySlug('ek-2026');
        $matchIds = $this->matchIdsForCompetition($competitionId);
        self::assertNotEmpty($matchIds, 'Seeded competition must have at least one match');

        $response = $this->request('GET', "/admin/competitions/{$competitionId}/results/{$matchIds[0]}/edit");
        self::assertSame(200, $response['status']);
        self::assertStringContainsString('Uitslag', $response['body']);
    }

    public function testResultEditRedirectsToLoginWhenUnauthenticated(): void
    {
        $competitionId = $this->competitionIdBySlug('ek-2026');
        $matchIds = $this->matchIdsForCompetition($competitionId);
        self::assertNotEmpty($matchIds);

        $response = $this->request('GET', "/admin/competitions/{$competitionId}/results/{$matchIds[0]}/edit");
        self::assertSame(302, $response['status']);
        self::assertStringContainsString('/login', $response['headers']['location'] ?? '');
    }
}
