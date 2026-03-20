<?php declare(strict_types=1);

namespace Tests\Contract;

use Tests\Support\HttpTestCase;

final class AdminBonusQuestionRoutesTest extends HttpTestCase
{
    public function testAdminCanViewBonusQuestionsPage(): void
    {
        $this->loginAsSeededAdmin();
        $id = $this->competitionIdBySlug('ek-2026');

        $response = $this->request('GET', "/admin/competitions/{$id}/bonus-questions");

        self::assertSame(200, $response['status']);
        self::assertStringContainsString('Bonusvragen', $response['body']);
    }

    public function testAdminCanSaveBonusQuestions(): void
    {
        $this->loginAsSeededAdmin();
        $id = $this->competitionIdBySlug('ek-2026');

        $page = $this->request('GET', "/admin/competitions/{$id}/bonus-questions");
        $csrfToken = $this->extractCsrfToken($page['body']);

        $response = $this->request('POST', "/admin/competitions/{$id}/bonus-questions", [
            '_token' => $csrfToken,
            'questions[0][prompt]' => 'Welk land wint?',
            'questions[0][question_type]' => 'entity',
            'questions[0][entity_type_constraint]' => 'country',
            'questions[0][is_active]' => '1',
            'questions[0][display_order]' => '1',
        ]);

        self::assertSame(302, $response['status']);
    }

    public function testParticipantCannotAccessBonusQuestionsAdmin(): void
    {
        $this->loginAsSeededParticipant();
        $id = $this->competitionIdBySlug('ek-2026');

        $response = $this->request('GET', "/admin/competitions/{$id}/bonus-questions");

        self::assertSame(403, $response['status']);
    }

    public function testAdminCanViewKnockoutRoundsPage(): void
    {
        $this->loginAsSeededAdmin();
        $id = $this->competitionIdBySlug('ek-2026');

        $response = $this->request('GET', "/admin/competitions/{$id}/knockout-rounds");

        self::assertSame(200, $response['status']);
        self::assertStringContainsString('Knock-out', $response['body']);
    }
}
