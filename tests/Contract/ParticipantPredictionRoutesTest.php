<?php declare(strict_types=1);

namespace Tests\Contract;

use Tests\Support\HttpTestCase;

final class ParticipantPredictionRoutesTest extends HttpTestCase
{
    public function testDashboardShowsCompetitionLinksAndUnpaidMarker(): void
    {
        $this->loginAsSeededParticipant();

        $response = $this->request('GET', '/dashboard');

        self::assertSame(200, $response['status']);
        self::assertStringContainsString('EK 2026', $response['body']);
        self::assertStringContainsString('Onbetaald', $response['body']);
        self::assertStringContainsString('/competitions/ek-2026/prediction', $response['body']);
    }

    public function testPredictionRouteRendersAndFinalSubmissionBecomesReadOnly(): void
    {
        $this->loginAsSeededParticipant();

        $competitionId = $this->competitionIdBySlug('ek-2026');
        $matchIds = $this->matchIdsForCompetition($competitionId);
        $bonusQuestionId = $this->bonusQuestionIdByPrompt($competitionId, 'Welk land wint het toernooi?');
        $roundId = $this->firstKnockoutRoundId($competitionId);
        $nederlandId = $this->entityIdByName($competitionId, 'Nederland');
        $spanjeId = $this->entityIdByName($competitionId, 'Spanje');

        $formPage = $this->request('GET', '/competitions/ek-2026/prediction');
        $csrfToken = $this->extractCsrfToken($formPage['body']);

        self::assertSame(200, $formPage['status']);
        self::assertStringContainsString('Welk land wint het toernooi?', $formPage['body']);
        self::assertStringContainsString('Definitief indienen', $formPage['body']);

        $response = $this->request('POST', '/competitions/ek-2026/prediction/submit', [
            '_token' => $csrfToken,
            "matches[{$matchIds[0]}][predicted_home_score]" => '2',
            "matches[{$matchIds[0]}][predicted_away_score]" => '1',
            "matches[{$matchIds[0]}][predicted_outcome]" => 'home_win',
            "matches[{$matchIds[0]}][predicted_yellow_cards_home]" => '1',
            "matches[{$matchIds[0]}][predicted_yellow_cards_away]" => '2',
            "matches[{$matchIds[0]}][predicted_red_cards_home]" => '0',
            "matches[{$matchIds[0]}][predicted_red_cards_away]" => '0',
            "matches[{$matchIds[1]}][predicted_home_score]" => '1',
            "matches[{$matchIds[1]}][predicted_away_score]" => '1',
            "matches[{$matchIds[1]}][predicted_outcome]" => 'draw',
            "matches[{$matchIds[1]}][predicted_yellow_cards_home]" => '2',
            "matches[{$matchIds[1]}][predicted_yellow_cards_away]" => '2',
            "matches[{$matchIds[1]}][predicted_red_cards_home]" => '0',
            "matches[{$matchIds[1]}][predicted_red_cards_away]" => '1',
            "bonus_answers[{$bonusQuestionId}]" => (string) $nederlandId,
            'bonus_answers[' . $this->bonusQuestionIdByPrompt($competitionId, 'Hoeveel goals vallen er in de finale?') . ']' => '3',
            'bonus_answers[' . $this->bonusQuestionIdByPrompt($competitionId, 'Welke speler wordt topscorer?') . ']' => 'Memphis Depay',
            "knockout_rounds[{$roundId}][1]" => (string) $nederlandId,
            "knockout_rounds[{$roundId}][2]" => (string) $spanjeId,
        ]);

        self::assertSame(302, $response['status']);
        self::assertSame('/competitions/ek-2026/prediction?confirmed=1', $response['headers']['location'] ?? null);

        $confirmedPage = $this->request('GET', '/competitions/ek-2026/prediction?confirmed=1');

        self::assertSame(200, $confirmedPage['status']);
        self::assertStringContainsString('Voorspelling bevestigd', $confirmedPage['body']);
        self::assertStringContainsString('Read-only', $confirmedPage['body']);
        self::assertStringNotContainsString('Definitief indienen', $confirmedPage['body']);
    }

    public function testLateSubmissionIsRedirectedBackToReadOnlyPredictionPage(): void
    {
        $this->loginAsSeededParticipant();

        self::$pdo->exec("UPDATE competitions SET submission_deadline = '2000-01-01 00:00:00' WHERE slug = 'ek-2026'");

        $page = $this->request('GET', '/competitions/ek-2026/prediction');
        $csrfToken = $this->extractCsrfToken($page['body']);

        $response = $this->request('POST', '/competitions/ek-2026/prediction/submit', [
            '_token' => $csrfToken,
        ]);

        self::assertSame(302, $response['status']);
        self::assertSame('/competitions/ek-2026/prediction', $response['headers']['location'] ?? null);

        $readOnlyPage = $this->request('GET', '/competitions/ek-2026/prediction');
        self::assertStringContainsString('read-only', strtolower($readOnlyPage['body']));
    }
}
