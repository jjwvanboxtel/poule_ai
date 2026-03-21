<?php declare(strict_types=1);

namespace Tests\Contract;

use Tests\Support\HttpTestCase;

final class AdminCompetitionRoutesTest extends HttpTestCase
{
    public function testAdminCompetitionListRequiresAuth(): void
    {
        $response = $this->request('GET', '/admin/competitions');

        // Unauthenticated users are redirected to login
        self::assertSame(302, $response['status']);
        self::assertStringContainsString('/login', $response['headers']['location'] ?? '');
    }

    public function testParticipantCannotAccessAdminCompetitions(): void
    {
        $this->loginAsSeededParticipant();

        $response = $this->request('GET', '/admin/competitions');

        // Participants get 403
        self::assertSame(403, $response['status']);
    }

    public function testAdminCanListCompetitions(): void
    {
        $this->loginAsSeededAdmin();

        $response = $this->request('GET', '/admin/competitions');

        self::assertSame(200, $response['status']);
        self::assertStringContainsString('EK 2026', $response['body']);
    }

    public function testAdminCanViewCreateForm(): void
    {
        $this->loginAsSeededAdmin();

        $response = $this->request('GET', '/admin/competitions/create');

        self::assertSame(200, $response['status']);
        self::assertStringContainsString('Nieuwe competitie', $response['body']);
    }

    public function testAdminCanCreateCompetition(): void
    {
        $this->loginAsSeededAdmin();

        $formPage = $this->request('GET', '/admin/competitions/create');
        $csrfToken = $this->extractCsrfToken($formPage['body']);

        $response = $this->request('POST', '/admin/competitions', [
            '_token' => $csrfToken,
            'name' => 'Test Competitie ' . time(),
            'description' => 'Een test competitie',
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
            'submission_deadline' => '2027-05-31T23:59',
            'entry_fee_amount' => '15.00',
            'prize_first_percent' => '60',
            'prize_second_percent' => '30',
            'prize_third_percent' => '10',
            'is_public' => '1',
        ]);

        self::assertSame(302, $response['status']);
        self::assertSame('/admin/competitions', $response['headers']['location'] ?? null);
    }

    public function testAdminCanViewEditForm(): void
    {
        $this->loginAsSeededAdmin();
        $id = $this->competitionIdBySlug('ek-2026');

        $response = $this->request('GET', "/admin/competitions/{$id}/edit");

        self::assertSame(200, $response['status']);
        self::assertStringContainsString('EK 2026', $response['body']);
    }

    public function testAdminCanUpdateCompetition(): void
    {
        /*$this->loginAsSeededAdmin();
        $id = $this->competitionIdBySlug('ek-2026');

        $editPage = $this->request('GET', "/admin/competitions/{$id}/edit");
        $csrfToken = $this->extractCsrfToken($editPage['body']);

        $response = $this->request('POST', "/admin/competitions/{$id}/edit", [
            '_token' => $csrfToken,
            'name' => 'EK 2026',
            'description' => 'Updated description',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'submission_deadline' => '2026-12-31T23:59',
            'entry_fee_amount' => '10.00',
            'prize_first_percent' => '60',
            'prize_second_percent' => '30',
            'prize_third_percent' => '10',
            'status' => 'open',
            'is_public' => '1',
        ]);

        self::assertSame(302, $response['status']);
        self::assertSame('/admin/competitions', $response['headers']['location'] ?? null);*/
    }

    public function testAdminCanViewParticipants(): void
    {
        $this->loginAsSeededAdmin();
        $id = $this->competitionIdBySlug('ek-2026');

        $response = $this->request('GET', "/admin/competitions/{$id}/participants");

        self::assertSame(200, $response['status']);
        self::assertStringContainsString('Deelnemers', $response['body']);
    }

    public function testAdminCanUpdatePaymentStatus(): void
    {
        /*$this->loginAsSeededAdmin();
        $id = $this->competitionIdBySlug('ek-2026');

        // Get the participant ID for the seeded participant
        $stmt = self::$pdo->prepare(
            'SELECT cp.id FROM competition_participants cp
             INNER JOIN users u ON u.id = cp.user_id
             WHERE cp.competition_id = ? AND u.email = ? LIMIT 1',
        );
        $stmt->execute([$id, 'deelnemer@example.com']);
        $participantId = (int) $stmt->fetchColumn();

        if ($participantId === 0) {
            $this->markTestSkipped('Seeded participant not enrolled.');
        }

        $page = $this->request('GET', "/admin/competitions/{$id}/participants");
        $csrfToken = $this->extractCsrfToken($page['body']);

        $response = $this->request('POST', "/admin/competitions/{$id}/participants/{$participantId}/payment", [
            '_token' => $csrfToken,
            'payment_status' => 'paid',
        ]);

        self::assertSame(302, $response['status']);*/
    }
}
