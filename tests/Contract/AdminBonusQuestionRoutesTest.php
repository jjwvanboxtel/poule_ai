<?php declare(strict_types=1);

namespace Tests\Contract;

use Tests\Support\HttpTestCase;

final class AdminBonusQuestionRoutesTest extends HttpTestCase
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

    public function testBonusQuestionsRedirectsToLoginWhenUnauthenticated(): void
    {
        $competitionId = $this->competitionIdBySlug('ek-2026');
        $response = $this->request('GET', "/admin/competitions/{$competitionId}/bonus-questions");
        self::assertSame(302, $response['status']);
        self::assertStringContainsString('/login', $response['headers']['location'] ?? '');
    }

    public function testBonusQuestionsReturnsForbiddenForParticipant(): void
    {
        $this->loginAsSeededParticipant();
        $competitionId = $this->competitionIdBySlug('ek-2026');
        $response = $this->request('GET', "/admin/competitions/{$competitionId}/bonus-questions");
        self::assertSame(403, $response['status']);
    }

    public function testBonusQuestionsPageOkForAdmin(): void
    {
        $this->loginAsSeededAdmin();
        $competitionId = $this->competitionIdBySlug('ek-2026');
        $response = $this->request('GET', "/admin/competitions/{$competitionId}/bonus-questions");
        self::assertSame(200, $response['status']);
        self::assertStringContainsString('Bonusvragen', $response['body']);
    }

    public function testCreateBonusQuestionRedirectsOnSuccess(): void
    {
        $this->loginAsSeededAdmin();
        $competitionId = $this->competitionIdBySlug('ek-2026');

        $formPage = $this->request('GET', "/admin/competitions/{$competitionId}/bonus-questions");
        $csrfToken = $this->extractCsrfToken($formPage['body']);

        $response = $this->request('POST', "/admin/competitions/{$competitionId}/bonus-questions", [
            '_token' => $csrfToken,
            'prompt' => 'Wie wordt de topscorer van het toernooi?',
            'question_type' => 'text',
            'entity_type_constraint' => '',
            'display_order' => '10',
        ]);

        self::assertSame(302, $response['status']);
        self::assertStringContainsString(
            "/admin/competitions/{$competitionId}/bonus-questions",
            $response['headers']['location'] ?? '',
        );
    }

    public function testDeleteBonusQuestionRedirectsOnSuccess(): void
    {
        $this->loginAsSeededAdmin();
        $competitionId = $this->competitionIdBySlug('ek-2026');
        $questionId = $this->bonusQuestionIdByPrompt($competitionId, 'Welk land wint het toernooi?');

        $formPage = $this->request('GET', "/admin/competitions/{$competitionId}/bonus-questions");
        $csrfToken = $this->extractCsrfToken($formPage['body']);

        $response = $this->request(
            'POST',
            "/admin/competitions/{$competitionId}/bonus-questions/{$questionId}/delete",
            ['_token' => $csrfToken],
        );

        self::assertSame(302, $response['status']);
        self::assertStringContainsString(
            "/admin/competitions/{$competitionId}/bonus-questions",
            $response['headers']['location'] ?? '',
        );
    }
}
