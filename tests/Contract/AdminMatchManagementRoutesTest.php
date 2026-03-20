<?php declare(strict_types=1);

namespace Tests\Contract;

use Tests\Support\HttpTestCase;

final class AdminMatchManagementRoutesTest extends HttpTestCase
{
    public function testAdminCanViewMatchList(): void
    {
        $this->loginAsSeededAdmin();
        $id = $this->competitionIdBySlug('ek-2026');

        $response = $this->request('GET', "/admin/competitions/{$id}/matches");

        self::assertSame(200, $response['status']);
        self::assertStringContainsString('Wedstrijden', $response['body']);
    }

    public function testAdminCanViewCreateMatchForm(): void
    {
        $this->loginAsSeededAdmin();
        $id = $this->competitionIdBySlug('ek-2026');

        $response = $this->request('GET', "/admin/competitions/{$id}/matches/create");

        self::assertSame(200, $response['status']);
        self::assertStringContainsString('Nieuwe wedstrijd', $response['body']);
    }

    public function testAdminCanCreateMatch(): void
    {
        $this->loginAsSeededAdmin();
        $id = $this->competitionIdBySlug('ek-2026');

        $formPage = $this->request('GET', "/admin/competitions/{$id}/matches/create");
        $csrfToken = $this->extractCsrfToken($formPage['body']);

        $entityIds = $this->matchIdsForCompetition($id);
        $nederlandId = $this->entityIdByName($id, 'Nederland');
        $duitslandId = $this->entityIdByName($id, 'Duitsland');

        if ($nederlandId === 0 || $duitslandId === 0) {
            $this->markTestSkipped('Required entities not found.');
        }

        $response = $this->request('POST', "/admin/competitions/{$id}/matches", [
            '_token' => $csrfToken,
            'home_entity_id' => (string) $nederlandId,
            'away_entity_id' => (string) $duitslandId,
            'stage' => 'group',
            'kickoff_at' => '2026-07-01T20:00',
        ]);

        self::assertSame(302, $response['status']);
        self::assertSame("/admin/competitions/{$id}/matches", $response['headers']['location'] ?? null);
    }

    public function testAdminCanViewResultEntryForm(): void
    {
        $this->loginAsSeededAdmin();
        $id = $this->competitionIdBySlug('ek-2026');

        $matchIds = $this->matchIdsForCompetition($id);
        if ($matchIds === []) {
            $this->markTestSkipped('No matches seeded for competition.');
        }

        $response = $this->request('GET', "/admin/competitions/{$id}/results/{$matchIds[0]}/edit");

        self::assertSame(200, $response['status']);
        self::assertStringContainsString('Uitslag', $response['body']);
    }

    public function testAdminCanSaveMatchResult(): void
    {
        $this->loginAsSeededAdmin();
        $id = $this->competitionIdBySlug('ek-2026');

        $matchIds = $this->matchIdsForCompetition($id);
        if ($matchIds === []) {
            $this->markTestSkipped('No matches seeded for competition.');
        }

        $page = $this->request('GET', "/admin/competitions/{$id}/results/{$matchIds[0]}/edit");
        $csrfToken = $this->extractCsrfToken($page['body']);

        $response = $this->request('POST', "/admin/competitions/{$id}/results/{$matchIds[0]}/edit", [
            '_token' => $csrfToken,
            'home_score' => '2',
            'away_score' => '1',
            'yellow_cards_home' => '1',
            'yellow_cards_away' => '2',
            'red_cards_home' => '0',
            'red_cards_away' => '0',
        ]);

        self::assertSame(302, $response['status']);
    }

    public function testAdminCanViewMaintenancePage(): void
    {
        $this->loginAsSeededAdmin();

        $response = $this->request('GET', '/admin/maintenance');

        self::assertSame(200, $response['status']);
        self::assertStringContainsString('Onderhoud', $response['body']);
    }

    public function testAdminCanViewUserManagement(): void
    {
        $this->loginAsSeededAdmin();

        $response = $this->request('GET', '/admin/users');

        self::assertSame(200, $response['status']);
        self::assertStringContainsString('Gebruikers', $response['body']);
    }

    public function testParticipantCannotAccessMatchAdmin(): void
    {
        $this->loginAsSeededParticipant();
        $id = $this->competitionIdBySlug('ek-2026');

        $response = $this->request('GET', "/admin/competitions/{$id}/matches");

        self::assertSame(403, $response['status']);
    }
}
