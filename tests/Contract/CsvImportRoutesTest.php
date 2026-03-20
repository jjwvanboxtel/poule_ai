<?php declare(strict_types=1);

namespace Tests\Contract;

use Tests\Support\HttpTestCase;

final class CsvImportRoutesTest extends HttpTestCase
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

    public function testImportPageRedirectsToLoginWhenUnauthenticated(): void
    {
        $competitionId = $this->competitionIdBySlug('ek-2026');
        $response = $this->request('GET', "/admin/competitions/{$competitionId}/import/entities");
        self::assertSame(302, $response['status']);
        self::assertStringContainsString('/login', $response['headers']['location'] ?? '');
    }

    public function testImportPageReturnsForbiddenForParticipant(): void
    {
        $this->loginAsSeededParticipant();
        $competitionId = $this->competitionIdBySlug('ek-2026');
        $response = $this->request('GET', "/admin/competitions/{$competitionId}/import/entities");
        self::assertSame(403, $response['status']);
    }

    public function testImportPageOkForAdmin(): void
    {
        $this->loginAsSeededAdmin();
        $competitionId = $this->competitionIdBySlug('ek-2026');
        $response = $this->request('GET', "/admin/competitions/{$competitionId}/import/entities");
        self::assertSame(200, $response['status']);
        self::assertStringContainsString('Entiteiten importeren', $response['body']);
    }

    public function testImportPostWithoutFileFails(): void
    {
        $this->loginAsSeededAdmin();
        $competitionId = $this->competitionIdBySlug('ek-2026');

        $formPage = $this->request('GET', "/admin/competitions/{$competitionId}/import/entities");
        $csrfToken = $this->extractCsrfToken($formPage['body']);

        $response = $this->request('POST', "/admin/competitions/{$competitionId}/import/entities", [
            '_token' => $csrfToken,
        ]);

        // Should redirect back to the import page (no file uploaded = validation failure)
        self::assertSame(302, $response['status']);
        self::assertStringContainsString(
            "/admin/competitions/{$competitionId}/import/entities",
            $response['headers']['location'] ?? '',
        );
    }
}
